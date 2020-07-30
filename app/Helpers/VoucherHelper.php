<?php namespace App\Helpers;

use App\Models\VoucherCode;
use App\Models\Voucher;
use App\Models\VoucherParticipant;

use App\Events\VoucherStatusUpdatedEvent;
use App\Events\VoucherMailingStatusUpdatedEvent;
use App\Events\VoucherCodeStatusUpdatedEvent;
use App\Events\VoucherParticipantStatusUpdatedEvent;

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

  public static function getLocationByQrcode($voucher, $qrcode) {
		return $voucher->redemptionLocations()->whereQrcode($qrcode)->first();
  }
  
  public static function getRedemptionCodes($voucher) {
		$result = $voucher->redemptionLocations()->pluck('qrcode')->toArray();
		return $result;
  }

  public static function getRedemptionPasswords($voucher) {
		$result = $voucher->redemptionLocations()->pluck('password')->toArray();
		return $result;
  }

  public static function checkAndSendEmails()
  {
    CommandHelper::start('sendVoucherEmails', function ($command) {
      return self::handle($command);
    });
  }

  public static function handle($command)
  {
	  $status = static::processParticipants('processing');
	  $status = static::processParticipants('pending');
	  return $status;
  }
  
//  public static function handle($command)
//  {
//	  $status = static::processVoucherCodes('processing');
//	  $status = static::processVoucherCodes('pending');
//	  return $status;
//  }
  
  public static function processParticipants($participantStatus) {
	  $processingVouchers = Voucher::whereStatus('sending')->select('id', 'has_one_code')->get();
		$processingVoucherIds = $processingVouchers->map(function($voucher) { return $voucher->id; });
	  
    $voucherParticipants = VoucherParticipant::where('status', $participantStatus)
      ->whereIn('voucher_id', $processingVoucherIds)
      ->get();
		  
	  $success = true;
	  foreach($voucherParticipants as $voucherParticipant) {
			$voucher = Voucher::find($voucherParticipant->voucher_id);
		
		  // Exit if vouncher not in sending mode
		  if ($voucher->status != 'sending') {
			  break;
		  }
			
			// if not single code mode and participant without code assigned, next
		  if (!$voucher->has_one_code && is_null($voucherParticipant->code)) {
				continue;
			}
      
      // Send email
      $success = static::sendVoucherEmail($voucherParticipant, $voucher);
      
      // Push emailing status
		  event(new VoucherMailingStatusUpdatedEvent($voucher));
		  
		  // if no more email to send, set voucher status as completed
		  if (VoucherParticipant::whereVoucherId($voucher->id)->whereStatus('pending')->count()==0) {
      	$voucher->status = 'completed';
      	$voucher->save();
      	event(new VoucherStatusUpdatedEvent($voucher));
      }
    }
	
	  $vouchers = Voucher::whereStatus('sending')->get();
	  foreach($vouchers as $voucher) {
	  	if ($voucher->has_one_code) {
	  		if (!$voucher->participants()->where('status', 'pending')->exists()) {
	  			if ($voucher->status != 'completed') {
					  $voucher->status = 'completed';
					  $voucher->save();
					  event(new VoucherStatusUpdatedEvent($voucher));
				  }
			  }
		  } else {
	  		if (!$voucher->participants()->whereHas('code')->where('status', 'pending')->exists()) {
	  			if ($voucher->status != 'completed') {
	  				$voucher->status = 'completed';
	  				$voucher->save();
					  event(new VoucherStatusUpdatedEvent($voucher));
				  }
			  }
		  }
	  }
    
    // return true to enable looping even there is error
    return $success;
  }
  
//  public static function processVoucherCodes($voucherCodeStatus) {
//	  $processingVoucherIds = Voucher::whereStatus('sending')->pluck('id')->toArray();
//
//	  if ($voucherCodeStatus == 'pending') {
//      $voucherCodes = VoucherCode::where('status', $voucherCodeStatus)
//        ->whereHas('participant')
//        ->whereIn('voucher_id', $processingVoucherIds)
//        ->get();
//    } else {
//      $voucherCodes = VoucherCode::where('status', $voucherCodeStatus)
//        ->whereIn('voucher_id', $processingVoucherIds)
//        ->get();
//    }
//
//	  $success = true;
//	  foreach($voucherCodes as $voucherCode) {
//      $voucher = Voucher::find($voucherCode->voucher_id);
//      if ($voucher->status != 'sending') {
//        break;
//      }
//      $success = static::sendVoucherEmail($voucherCode);
//		  event(new VoucherMailingStatusUpdatedEvent($voucher));
//		  if (VoucherCode::whereVoucherId($voucher->id)->whereStatus('pending')->count()==0) {
//      	$voucher->status = 'completed';
//      	$voucher->save();
//      	event(new VoucherStatusUpdatedEvent($voucher));
//      }
//    }
//    // return true to enable looping even there is error
//    return $success;
//  }

  public static function resetVoucherCodeStatus($voucherCode) {
	  $voucherCode->status = 'ready';
	  $voucherCode->error_message = '';
	  $voucherCode->sent_on = null;
	  $voucherCode->save();
	  event(new VoucherCodeStatusUpdatedEvent($voucherCode));
	  return $voucherCode;
  }

  public static function getStatusSummary($id, $basedOnStatusOnly=true) {
		$status = false;
		$result = [
			'message' => 'Unkknown internal error!'
		];
	  $voucher = Voucher::find($id);
	  if (!isset($voucher)) {
	  	$result['message'] = 'Voucher id "'.$id.'" is undefined.';
	  } else {
	  	if ($basedOnStatusOnly || $voucher->has_one_code) {
			  $statusList = $voucher->participants()->select('status')->get();
		  } else {
	  		$statusList = $voucher->participants()->whereHas('code')->select('status')->get();
		  }
		  $status = true;
		  $result = [
			  'summary' => [
				  'pending' => $statusList->filter(function($item) {
            return $item->status == 'pending';
          })->count(),
          'completed' => $statusList->filter(function($item) {
            return $item->status == 'completed';
          })->count(),
				  'fails' => $statusList->filter(function($item) {
            return $item->status == 'fails';
          })->count(),
				  'processing' => $statusList->filter(function($item) {
            return $item->status == 'processing';
          })->count(),
				  'hold' => $statusList->filter(function($item) {
				  	return $item->status == 'hold';
				  })->count()
			  ]
		  ];
	  }
	  return [
		  'status' => $status,
		  'result' => $result
	  ];
  }
  
//  public static function getMailingSummary($id) {
//		$status = false;
//		$result = [
//			'message' => 'Unkknown internal error!'
//		];
//	  $voucher = Voucher::find($id);
//
//	  if (!isset($voucher)) {
//	  	$result['message'] = 'Voucher id "'.$id.'" is undefined.';
//	  } else {
//		  $statusList = $voucher->codes()->select('status', 'participant_id')->get();
//
//		  $status = true;
//		  $result = [
//			  'summary' => [
//				  'pending' => $statusList->filter(function($item) {
//            return $item->status == 'pending' && $item->participant_id != 0;
//          })->count(),
//          'completed' => $statusList->filter(function($item) {
//            return $item->status == 'completed' && $item->participant_id != 0;
//          })->count(),
//				  'fails' => $statusList->filter(function($item) {
//            return $item->status == 'fails' && $item->participant_id != 0;
//          })->count(),
//				  'processing' => $statusList->filter(function($item) {
//            return $item->status == 'processing' && $item->participant_id != 0;
//          })->count(),
//				  'hold' => $statusList->filter(function($item) {
//				  	return $item->status == 'hold' && $item->participant_id != 0;
//				  })->count()
//			  ]
//		  ];
//	  }
//
//	  return [
//		  'status' => $status,
//		  'result' => $result
//	  ];
//  }
	
	public static function sendVoucherEmail($participant, $voucher=null) {
		if (is_null($voucher)) {
			$voucher = $participant->voucher;
		}
		
		//***************************************
    // voucher code status => 'processing'
		//***************************************
    $participant->status = 'processing';
    $participant->save();
    event(new VoucherParticipantStatusUpdatedEvent($participant));
		$template = VoucherTemplateHelper::readVoucherTemplate($voucher, 'email');
		$voucherCode = $voucher->has_one_code ? $voucher->codes()->first() : $participant->code;
		
		//*************
		// Apply tag values
		//*************
		// null as TagGroups to use actual tag values
		//
		$allTagValues = TagGroupHelper::getTagValues(null, $voucherCode);
		LogHelper::log('Apply tag values');
		$appliedTemplate = TemplateHelper::applyTags($template, $allTagValues, $voucher->codeConfigs);

		//*************
		// Send email
		//*************
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
		$errorMsg = EmailTemplateHelper::sendHtml(
			$smtpConfig,
			$mailInfo);
		
		//*************
		// Update status
		//*************
		$res = true;
		$status = 'completed';
		$message = '';
		if ($errorMsg) {
			$status = 'fails';
			$res = false;
			if (strpos($errorMsg, 'exceeded') !== false) {
				$message = 'Messaging limits exceeded!';
			} else {
				$message = $errorMsg;
			}
		}
		
		static::updateParticipantStatus(
			$participant,
			$status,
			$message
		);

		return true; //  $status; // $res['status'];
	}
	
//	public static function sendVoucherEmail($voucherCode, $voucher=null) {
//		if (is_null($voucher)) {
//			$voucher = $voucherCode->voucher;
//		}
//
//		//***************************************
//    // voucher code status => 'processing'
//		//***************************************
//    $voucherCode->status = 'processing';
//    $voucherCode->save();
//    event(new VoucherCodeStatusUpdatedEvent($voucherCode));
//		$template = VoucherTemplateHelper::readVoucherTemplate($voucher, 'email');
//		$participant = $voucherCode->participant;
//
//		//*************
//		// Apply tag values
//		//*************
//		// null as TagGroups to use actual tag values
//		//
//		$allTagValues = TagGroupHelper::getTagValues(null, $voucherCode);
//		LogHelper::log('Apply tag values');
//		$appliedTemplate = TemplateHelper::applyTags($template, $allTagValues, $voucher->codeConfigs);
//
//		//*************
//		// Send email
//		//*************
//		LogHelper::log('Send email');
//		$smtpServer = $voucher->getSmtpServer();
//		$smtpConfig = SmtpServerHelper::getConfig($smtpServer);
//		$mailInfo = [
//			'subject' => $voucher->email_subject,
//			'toEmail' => $participant->email,
//			'toName' => $participant->name,
//			'cc' => $voucher->mail_cc,
//			'bcc' => $voucher->email_bcc,
//			'body' => $appliedTemplate,
//			'fromEmail' => $smtpConfig['from']['address'],
//			'fromName' => $smtpConfig['from']['name']
//		];
//		$errorMsg = EmailTemplateHelper::sendHtml(
//			$smtpConfig,
//			$mailInfo);
//
//		//*************
//		// Update status
//		//*************
//		$res = true;
//		$status = 'completed';
//		$message = '';
//		if ($errorMsg) {
//			$status = 'fails';
//			$res = false;
//			if (strpos($errorMsg, 'exceeded') !== false) {
//				$message = 'Messaging limits exceeded!';
//			} else {
//				$message = $errorMsg;
//			}
//		}
//    static::updateCodeStatus(
//    	$voucherCode,
//	    $status,
//	    $message);
//
//		return true; //  $status; // $res['status'];
//	}
//

	private static function updateParticipantStatus(
		$participant,
		$status,
		$message='')
	{
		$participant->status = $status;
		$participant->error_message = $message;
		$participant->sent_at = date('Y-m-d H:i:s');
		$participant->save();
		
		LogHelper::log('VoucherHelper::sendVoucherEmail :: message: ' . $message);
		event(new VoucherParticipantStatusUpdatedEvent($participant));
	}

	private static function updateCodeStatus(
		$voucherCode,
		$status,
		$message='') {
		
		$voucherCode->status = $status;
		$voucherCode->error_message = $message;
		$voucherCode->sent_on = date('Y-m-d H:i:s');
		$voucherCode->save();
		
		LogHelper::log('VoucherHelper::sendVoucherEmail :: message: '. $message);
		event(new VoucherCodeStatusUpdatedEvent($voucherCode));
	}
}