<?php namespace App\Helpers;

use App\Models\VoucherCode;
use App\Models\Voucher;
use App\Events\VoucherCodeStatusUpdatedEvent;

class VoucherHelper {
	public static function addNewParticipant($voucher, $participant) {
		return 'not implemented yet!';
	}
	
  public static function addNewCodes($voucher, $codeArray, $arParticipantIds=[]) {
  
		$codeParticipantMapping = [];
		if (!empty($arParticipantIds)) {
			for($i = 0; $i < count($codeArray); $i++) {
				$codeParticipantMapping[$codeArray[$i][0]] = $arParticipantIds[$i];
			}
		}
		// codeParticipantMapping = [
	  //      {code => participant_id}*
	  // ]
  
    ini_set('max_execution_time', 300 );

    $existingCodeInfos = $voucher->codeInfos;
    $existingCodes = $existingCodeInfos->pluck('code')->toArray();

    $codeInfosToAdd = array_filter($codeArray, function($item) use ($existingCodes) {
      return !in_array($item[0], $existingCodes);
    });

    $codeInfosToUpdate = array_filter($codeArray, function($item) use ($existingCodes) {
      return in_array($item[0], $existingCodes);
    });

	  // update code order ensure from 1 to n
    $codeInfos = VoucherCode::whereVoucherId($voucher->id)->orderby('order')->get();
    foreach($codeInfos as $i=>$codeInfo) {
      $codeInfo->update(['order' => $i + 1]);
    }

    // Append codes
    $j = count($codeInfos);
    $batchData = [];
    $now = date('Y-m-d H:i:s');

    foreach($codeInfosToAdd as $loopCodeInfo) {
      $keyCode = array_shift($loopCodeInfo);
      $participantId = 0;
      if (count($codeParticipantMapping)>0) {
      	$participantId = $codeParticipantMapping[$keyCode];
      }
      $batchData[] = [
        'voucher_id' => $voucher->id,
	      'participant_id' => $participantId,
        'code' => $keyCode,
        'order' => $j++,
        'extra_fields' => implode('|', $loopCodeInfo),
        'key' => newKey(),
        'created_at' => $now,
        'updated_at' => $now
      ];
    }
    $insertData = collect($batchData);
    $chunks = $insertData->chunk(1000);
    foreach( $chunks as $chunk) {
      \DB::table('voucher_codes')->insert($chunk->toArray());
    }
    
    // update code count
    $codeCount = VoucherCode::whereVoucherId($voucher->id)->count();
    Voucher::whereId($voucher->id)->update(['code_count'=>$codeCount]);
    
    return [
      'codeCount' => $codeCount,
	    
      'new' => count($codeInfosToAdd),
      'existing' => count($codeInfosToUpdate)
    ];
  }

  public static function checkAndSendEmails()
  {
    CommandHelper::start('sendVoucherEmails', function ($command) {
      return self::handle($command);
    });
  }

  public static function handle($command) {
	  $voucherCodes = VoucherCode::where('status', 'ready')->get();
	  $status = true;
	  foreach($voucherCodes as $voucherCode) {
      $voucher = $voucherCode->voucher;

	    // update voucher status
      if ($voucher->status != 'sending') {
        static::updateVoucherStatus($voucher,'sending');
      }

      $res = static::sendVoucherEmail($voucherCode);
      $status = $res['status'];
      if ($status) {
        static::updateVoucherCodeStatus($voucherCode, 'completed', date('Y-m-d H:i:s'));
      } else {
        static::updateVoucherCodeStatus($voucherCode, 'fails', date('Y-m-d H:i:s'), $res['message']);
      }

      if ($voucher->codes()->where('status', 'ready')->count() == 0) {
        static::updateVoucherStatus($voucher,'completed');
      }
    }
    // return true to enable looping even there is error
    return $status;
  }

  public static function resetVoucherCodeStatus($voucherCode) {
	  $voucherCode->status = 'ready';
	  $voucherCode->error_message = '';
	  $voucherCode->sent_on = null;
	  $voucherCode->save();
	  event(new VoucherCodeStatusUpdatedEvent($voucherCode));
	  return $voucherCode;
  }
  private static function updateVoucherStatus($voucher, $status) {
	  $voucher->status = $status;
	  $voucher->save();
  }
  private static function updateVoucherCodeStatus($voucherCode, $status, $dt='', $message='') {
    $voucherCode->status = $status;
    $voucherCode->sent_on = $dt;
    $voucherCode->error_message = $message;
    $voucherCode->save();
  }

  public static function sendVoucherEmail($voucherCode, $voucher=null) {
	  if (is_null($voucher)) {
      $voucher = $voucherCode->voucher;
    }

    $template = VoucherTemplateHelper::readVoucherTemplate($voucher, 'email');
    $participant = $voucherCode->participant;
//    $voucher->codeConfigs;

    LogHelper::log('Get all tag values');
    // Apply tag values
    //
    // null as TagGroups to use actual tag values
    $allTagValues = TagGroupHelper::getTagValues(null, $voucherCode);

//    $codeTagValues = [];
//    $tagValues = [];
//    // extract code tags
//    foreach($allTagValues as $tagName=>$tagValue) {
//      if ($tagName == 'qrcode' || $tagName == 'barcode') {
//        $codeTagValues[$tagName] = $tagValue;
//      } else {
//        $tagValues[$tagName] = $tagValue;
//      }
//    }

    LogHelper::log('Apply tag values');
    $appliedTemplate = TemplateHelper::applyTags($template, $allTagValues, $voucher->codeConfigs);
//    $appliedTemplate = TemplateHelper::applyCodeTags($template, $codeTagValues);

    // Send email
LogHelper::log('Send email');
    $smtpServer = $voucher->getSmtpServer();
    $smtpConfig = SmtpServerHelper::getConfig($smtpServer);
    $mailInfo = [
      'subject' => $voucher->email_subject,
      'toEmail' => $participant->email,
      'toName' => $participant->name,
      'cc' => $voucher->mail_cc,
      'bcc' => $voucher->email_bcc,
      'body' => $appliedTemplate,
      'fromEmail' => $smtpConfig['from']['address'],
      'fromName' => $smtpConfig['from']['name']
    ];

    $errorMsg = EmailTemplateHelper::sendHtml($smtpConfig, $mailInfo);

    // Prepare message if err
    $status = true;
    $message = '';
    if ($errorMsg) {
      $status = false;
      $message = $errorMsg;
      $voucherCode->status = 'fails';
      $voucherCode->error_message = $errorMsg;
      $voucherCode->sent_on = date('Y-m-d H:i:s');
      $voucherCode->save();
    } else {
      $voucherCode->status = 'completed';
      $voucherCode->error_message = '';
      $voucherCode->sent_on = date('Y-m-d H:i:s');
      $voucherCode->save();
    }
    event(new VoucherCodeStatusUpdatedEvent($voucherCode));
LogHelper::log('VoucherHelper::sendVoucherEmail :: message: '. $message);
    return [
      'status' => $status,
      'message' => $message
    ];
  }
}