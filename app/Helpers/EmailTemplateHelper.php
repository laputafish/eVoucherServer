<?php namespace App\Helpers;

class EmailTemplateHelper {
//	public static function sendHtml2($smtpConfig, $mailInfo) {
//		$result = '';
//		\Config::set('mail', $smtpConfig);
//
//		// save images to temporary folder
//
//		try {
//			$tempKey = newKey();
////			$folder = storage_path('app/temp/email_sending/'.$tempKey);
//			$body = $mailInfo['body'];
//			$imageFileNames = static::extractImageFiles($body, $message, $tempKey);
//
//			\Mail::send(
//				['html'=>'email.htmlEmail'],
//				[
//					'content'=>$mailInfo['body'],
//					'emailKey'=>$tempKey,
//					'imageFileNames' => $imageFileNames
//				],
//				function ($message)
//				use (
//					$tempKey,
//					$mailInfo
//				) {
//
//					$message
//						->subject($mailInfo['subject'])
//						->to($mailInfo['toEmail'], isset($mailInfo['toName']) ? $mailInfo['toName'] : null)
//						->from($mailInfo['fromEmail'], $mailInfo['fromName']);
//
//					if (!empty($mailInfo['cc'])) $message->cc($mailInfo['cc']);
//					if (!empty($mailInfo['bcc'])) $message->bcc($mailInfo['bcc']);
//				}
//			);
//		} catch (\Exception $e) {
//			$result = $e->getMessage();
//		}
//		return $result;
//	}
	
	public static function sendHtml($smtpConfig, $mailInfo) {
		$result = '';
		\Config::set('mail', $smtpConfig);
		
		// save images to temporary folder
		
		try {
			$emailKey = newKey();
			\Mail::send(
				['html'=>'email.htmlEmail'],
				[
					'content'=>$mailInfo['body'],
					'emailKey'=>$emailKey,
				],
				function ($message)
				use (
					$emailKey,
					$mailInfo
				) {
					$message
						->subject($mailInfo['subject'])
						->to($mailInfo['toEmail'], isset($mailInfo['toName']) ? $mailInfo['toName'] : null)
						->from($mailInfo['fromEmail'], $mailInfo['fromName']);

					if (!empty($mailInfo['cc'])) $message->cc($mailInfo['cc']);

					if (!empty($mailInfo['bcc'])) $message->bcc($mailInfo['bcc']);
//					return 'ok';
				}
			);
		} catch (\Exception $e) {
//			echo '**********************';
			$result = $e->getMessage();
		}
		return $result;
	}
	
//	private static function extractImageFiles(&$content, &$message, $tempKey) {
//		$result = [];
//
//		$folder = storage_path('app/temp/email_sending/'.$tempKey);
//		FileHelper::checkCreateFolder($folder);
//
//		$reg = '#data:image\/(\w+);base64,([^"]*)#';
//		$matched = preg_match_all($reg, $content, $matches);
////		print_r($matches[1]);
//		if ($matched !== false) {
//			foreach($matches[1] as $i=>$ext) {
//				$fileName = 'image_'.$i.'.'.$ext;
//				$filePath = $folder.'/'.$fileName;
//				$imageData = base64_decode($matches[2][$i]);
//				file_put_contents($filePath, $imageData);
//				$result[] = $filePath;
//
//				$replacement = $message->embed($filePath);
////				$replacement = '{image_'.$i.'}';
//				$content = preg_replace($reg, $replacement , $content, 1);
//
////				echo $message->embed($filePath).PHP_EOL;
////				$replacement = $message->embed($filePath);
////				$content = preg_replace($reg, $replacement , $content);
//			}
//		}
//		return $result;
//	}
//
//	//
//	private static function base64ToImage($imageBase64Str, $tempKey) {
////		$image = $request->input('image'); // image base64 encoded
//		preg_match("/data:image\/(.*?);/",$imageBase64Str,$image_extension); // extract the image extension
//
//		$image = preg_replace('/data:image\/(.*?);base64,/','',$imageBase64Str); // remove the type part
//		$image = str_replace(' ', '+', $image);
//		$imageFileName = 'image_' . time() . '.' . $image_extension[1]; //generating unique file name;
//		Storage::disk('public')->put($imageFileName,base64_decode($image));
//	}
}