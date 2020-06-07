<?php namespace App\Http\Controllers\ApiV2;

use Illuminate\Http\Request;

use App\Helpers\SmtpServerHelper;

class SmtpServerController extends BaseController
{
  public function sendTestEmail(Request $request)
  {
    $message = '';

    $serverConfig = $request->get('smtpServer');
    $smtpConfig = SmtpServerHelper::getConfig($serverConfig);

    \Config::set('mail', $smtpConfig);
    $fromEmail = $serverConfig['mail_from_address'];
    $fromName = $serverConfig['mail_from_name'];
//    $fromEmail = 'yoovsuper@gmail.com';
//    $fromName = 'YOOV SUPER';
    $toEmail = $request->get('receiverEmailAddress');
    $mailBody = [
      'name' => '',
      'body' => 'A test mail.'
    ];
    try {
      \Mail::send('email.testMail', $mailBody,
        function ($message)
        use (
          $fromEmail,
          $fromName,
          $toEmail
        ) {
          $message->to($toEmail)
            ->subject('Yoov Ticket Test Mail');
          $message->from($fromEmail, $fromName);
        }
      );
    } catch (\Exception $e) {
      $message = $e->getMessage();
    }

    $status = empty($message);
    return response()->json([
      'status' => $status,
      'result' => [
        'message' => $message
      ]
    ]);
  }
}