<?php namespace App\Helpers;

use App\Models\VoucherCode;
use App\Models\Voucher;

use App\Events\VoucherStatusUpdatedEvent;
use App\Events\VoucherMailingStatusUpdatedEvent;
use App\Events\VoucherParticipantStatusUpdatedEvent;

use App\Helpers\LogHelper;

class ParticipantHelper {
	
	public static function getFieldValues($formContent) {
		return explode('||', $formContent);
	}
	
	public static function sendEmail($participant, $voucher=null) {
		if (is_null($voucher)) {
			$voucher = $participant->voucher;
		}
		//***************************************
		// participant status => 'processing'
		//***************************************
		$participant->status = 'processing';
		$participant->save();
		event(new VoucherParticipantStatusUpdatedEvent($participant));
		$template = VoucherTemplateHelper::readVoucherTemplate($voucher, 'email');
		
		// Get voucher code
		if ($voucher->has_one_code) {
			$voucherCode = $voucher->codes()->first();
		} else {
			$voucherCode = $participant->code;
		}

		if (isset($voucherCode)) {
			//***************************************
			// Apply tag values
			//***************************************
			$allTagValues = TagGroupHelper::getTagValues(null, $voucherCode);
			
//			print_r($allTagValues);
//			return false;
			
			LogHelper::log('Apply tag values');
			$appliedTemplate = TemplateHelper::applyTags($template, $allTagValues, $voucher->codeConfigs);
			
			// Send email
			LogHelper::log('Send email');
			$smtpServer = $voucher->getSmtpServer();
			if (isset($smtpServer)) {
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
			} else {
				$errorMsg = 'No SMTP server assigned!';
			}
		} else {
			$errorMsg = 'No assigned code!';
		}
		
		//*************
		// Update status
		//*************
		$status = 'completed';
		$message = '';
		if ($errorMsg) {
			$status = 'fails';
			if (strpos($errorMsg, 'exceeded') !== false) {
				$message = 'Messaging limits exceeded!';
			} else {
				$message = $errorMsg;
			}
		}
		static::updateParticipantStatus(
			$participant,
			$status,
			$message);
		
		return true; //  $status; // $res['status'];
	}
	
	private static function updateParticipantStatus(
		$participant,
		$status,
		$message = '') {
		
		$participant->status = $status;
		$participant->error_message = substr($message, 0, 190);
		$participant->sent_at = date('Y-m-d H:i:s');
		$participant->save();
		
		LogHelper::log('VoucherHelper::sendVoucherEmail :: message: '. $message);
		event(new VoucherParticipantStatusUpdatedEvent($participant));
	}
}