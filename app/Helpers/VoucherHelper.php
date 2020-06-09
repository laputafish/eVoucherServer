<?php namespace App\Helpers;

use App\Models\VoucherCode;
use App\Models\Voucher;

use App\Events\VoucherStatusUpdatedEvent;
use App\Events\VoucherMailingStatusUpdatedEvent;
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

  public static function handle($command)
  {
	  $status = static::processVoucherCodes('processing');
	  $status = static::processVoucherCodes('pending');
	  return $status;
  }
  
  public static function processVoucherCodes($voucherCodeStatus) {
	  $processingVoucherIds = Voucher::whereStatus('sending')->pluck('id')->toArray();
	  $voucherCodes = VoucherCode::where('status', $voucherCodeStatus)
		  ->whereIn('voucher_id', $processingVoucherIds)
		  ->get();
		  
	  $success = true;
	  foreach($voucherCodes as $voucherCode) {

      $voucher = $voucherCode->voucher;


      $success = static::sendVoucherEmail($voucherCode);

		
		  event(new VoucherMailingStatusUpdatedEvent($voucher));
		  if (VoucherCode::whereVoucherId($voucher->id)->whereStatus('pending')->count()==0) {
      	$voucher->status = 'completed';
      	$voucher->save();
      	event(new VoucherStatusUpdatedEvent($voucher));
      }
    }
    // return true to enable looping even there is error
    return $success;
  }

  public static function resetVoucherCodeStatus($voucherCode) {
	  $voucherCode->status = 'ready';
	  $voucherCode->error_message = '';
	  $voucherCode->sent_on = null;
	  $voucherCode->save();
	  event(new VoucherCodeStatusUpdatedEvent($voucherCode));
	  return $voucherCode;
  }
//  private static function updateVoucherStatus($voucher, $status) {
//	  $voucher->status = $status;
//	  $voucher->save();
//  }
  private static function updateVoucherCodeStatus($voucherCode, $status, $dt='', $message='') {
    $voucherCode->status = $status;
    $voucherCode->sent_on = $dt;
    $voucherCode->error_message = $message;
    $voucherCode->save();
    event(new VoucherCodeStatusUpdatedEvent($voucherCode));
  }

  public static function getMailingSummary($id) {
		$status = false;
		$result = [
			'message' => 'Unkknown internal error!'
		];
	  $voucher = Voucher::find($id);
	  
	  if (!isset($voucher)) {
	  	$result['message'] = 'Voucher id "'.$id.'" is undefined.';
	  } else {
		  $statusList = $voucher->codes()->pluck('status')->toArray();
		
//		  $sendingTo = null;
//		  $processingCode = $voucher->codes()->where('status', 'processing')->first();
//		  if (isset($processingCode) && isset($processingCode->participant)) {
//			  $sendingTo = $processingCode->participant;
//		  }
		  $status = true;
		  $result = [
			  'summary' => [
//				  'sending_to' => $sendingTo,
				  'pending' => count(array_filter($statusList, function ($item) {
					  return $item == 'pending';
				  })),
				  'completed' => count(array_filter($statusList, function ($item) {
					  return $item == 'completed';
				  })),
				  'fails' => count(array_filter($statusList, function ($item) {
					  return $item == 'fails';
				  })),
				  'processing' => count(array_filter($statusList, function ($item) {
					  return $item == 'processing';
				  })),
			  ]
		  ];
	  }
	  
	  return [
		  'status' => $status,
		  'result' => $result
	  ];
  }
	
	public static function sendVoucherEmail($voucherCode, $voucher=null) {
		if (is_null($voucher)) {
			$voucher = $voucherCode->voucher;
		}

    // Update voucher code status
    $voucherCode->status = 'processing';
    $voucherCode->save();
    event(new VoucherCodeStatusUpdatedEvent($voucherCode));
		$template = VoucherTemplateHelper::readVoucherTemplate($voucher, 'email');
		$participant = $voucherCode->participant;

		// Apply tag values
		//
		// null as TagGroups to use actual tag values
		$allTagValues = TagGroupHelper::getTagValues(null, $voucherCode);
		LogHelper::log('Apply tag values');
		$appliedTemplate = TemplateHelper::applyTags($template, $allTagValues, $voucher->codeConfigs);
		
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
		
		
//		$path = storage_path('logs/template_sending_email.html');
//		if (file_exists($path)) {
//			unlink($path);
//		}
//		file_put_contents($path, $appliedTemplate);
		
		
		
		
		$errorMsg = EmailTemplateHelper::sendHtml(
			$smtpConfig,
			$mailInfo);
		
		// Prepare message if err
		$status = true;
		$message = '';
		if ($errorMsg) {
			$status = false;
			if (strpos($errorMsg, 'exceeded') !== false) {
				$message = 'Messaging limits exceeded!';
			} else {
				$message = $errorMsg;
			}
			$voucherCode->status = 'fails';
			$voucherCode->error_message = $message;
			$voucherCode->sent_on = date('Y-m-d H:i:s');
			$voucherCode->save();
		} else {
			$voucherCode->status = 'completed';
			$voucherCode->error_message = '';
			$voucherCode->sent_on = date('Y-m-d H:i:s');
			$voucherCode->save();
		}
		LogHelper::log('VoucherHelper::sendVoucherEmail :: message: '. $message);

//		$res = [
//			'status' => $status,
//			'message' => $message
//		];
//
//    $status = $res['status'];
    if ($status) {
      static::updateVoucherCodeStatus($voucherCode, 'completed', date('Y-m-d H:i:s'));
    } else {
      static::updateVoucherCodeStatus($voucherCode, 'fails', date('Y-m-d H:i:s'), $message);
    }

		return true; //  $status; // $res['status'];
	}
	
	
}