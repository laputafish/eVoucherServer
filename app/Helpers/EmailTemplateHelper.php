<?php namespace App\Helpers;

class EmailTemplateHelper {
	public static function sendHtml($smtpConfig, $mailInfo) {
		$result = '';
		\Config::set('mail', $smtpConfig);
		try {
			\Mail::send(['html'=>'email.htmlEmail'], ['content'=>$mailInfo['body']],
				function ($message)
				use (
					$mailInfo
				) {
					$message
						->subject($mailInfo['subject'])
						->to($mailInfo['toEmail'], isset($mailInfo['toName']) ? $mailInfo['toName'] : null)
						->from($mailInfo['fromEmail'], $mailInfo['fromName']);
					
					if (!empty($mailInfo['cc'])) $message->cc($mailInfo['cc']);
					if (!empty($mailInfo['bcc'])) $message->bcc($mailInfo['bcc']);
				}
			);
		} catch (\Exception $e) {
			$result = $e->getMessage();
		}
		return $result;
	}
}