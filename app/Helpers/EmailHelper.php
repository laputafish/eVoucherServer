<?php namespace App\Helpers;

class EmailHelper {
	public static function setSmtpServer($smtpServer) {
		$smtpConfig = [
			'driver' => $smtpServer['mail_driver'],
			'host' => $smtpServer['mail_host'],
			'port' => $smtpServer['mail_port'],
			'username' => $smtpServer['mail_username'],
			'password' => $smtpServer['mail_password'],
			'encryption' => $smtpServer['mail_encryption'],
			'from' => [
				'address' => $smtpServer['mail_from_address'],
				'name' => $smtpServer['mail_from_name']
			]
		];
		
		\Config::set('mail', $smtpConfig);
	}
	
	public static function send($emailData, $emailBody, $emailTemplate) {
		$message = '';
		try {
			\Mail::send($emailTemplate, $emailBody, function($msg) use($emailData) {
				$msg->to($emailData['toAddress'], $emailData['toName'])
					->subject($emailData['subject']);
				$msg->from($emailData['fromAddress'], $emailData['fromName']);
			});
		} catch(\Exception $e) {
			$message = $e->getMessage();
		}
		return $message;
	}
	
	public static function getParticipantParams($voucher, $participant) {
		$participantConfigs = json_decode($voucher->participant_configs, true);
		$inputObjs = $participantConfigs['inputObjs'];
		
		$primaryFields = [
//			'name' => $participant->name,
//			'phone' => $participant->phone,
//			'email' => $participant->email
		];

		$otherFields = TemplateHelper::createParamsFromInputObjs($participant->form_content, $inputObjs);
		return array_merge($primaryFields, $otherFields);
	}
	
	public static function sendVoucherEmail($voucher, $voucherCode)
	{
		$emailTemplate = VoucherTemplateHelper::readVoucherTemplate($voucher, 'email');
		$participant = $voucherCode->participant;
		$voucher->codeConfigs;
		
		$tagValues = TagGroupHelper::getTagValues(null, $voucherCode);
		print_r($tagValues);
		dd('ok');
		
		$voucherParams = TemplateHelper::createParams(
			$voucher->toArray(),
			$voucherCode
		);
		
		$participantParams = static::getParticipantParams($voucher, $participant);
		
		$finalParams = array_merge($voucherParams, $participantParams);
		
		if (strpos($emailTemplate, '{voucher}')!==false) {
			$voucherTemplateContent = '';
			$voucherContent = TemplateHelper::processTemplate(
				$voucherTemplateContent,
				$voucher->codeConfigs,
				$finalParams
			);
			$finalParams = array_merge($finalParams, [
				'voucher' => $voucherContent
			]);
		}
		$finalEmailContent = TemplateHelper::processTemplate(
			$emailTemplate,
			$voucher->codeConfigs,
			$finalParams
		);
	}
			
//
//			$voucherContent = statis::parseEmailContent($emailContent);
//			$params = [
//				// voucher
//
//				'voucher' => $voucherContent,
//
//				// Participants
//				'name' => $pt->name,
//				'email' => $pt->email,
//				'phone' => $pt->phone,
//				'activation_key' => '1234567890',
//				'deadline' => '2020-02-02',
//				'policy_no' => 'ABC1234567890'
//			];
//
	
//			$message = (new VoucherMail($finalEmailContent)
//			$mail = Mail::to([
//				'address' => $participant->email,
//				'name' => $participant->name,
//			])->subject($voucher->email_subject);
//
//			if (!empty($voucher->email_cc)) {
//				$mail = $mail->cc($voucher->email_cc);
//			}
//			if (!empty($voucher->email_bcc)) {
//				$mail = $mail->bcc($voucher->email_bcc);
//			}
//
//}
//
//$fromAddress = 'yoovtest@gmail.com';
//$fromName = 'YOOV Ticket Group';
//$subject = 'SUBJECT XXXX';
//
//$data = [
//	'fromAddress' => $fromAddress,
//	'fromName' => $fromName,
//	'subject' => $subject
//];
//
//$emailBody = [
//	'param1' => 'param1',
//	'param2' => 'param2',
//	'name' => '((name))',
//	'body' => '((body))'
//];
//
//$message = EmailHelper::send($data, $emailBody, 'email.testMail');
//
//}
}