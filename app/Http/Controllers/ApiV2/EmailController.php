<?php namespace App\Http\Controllers\ApiV2;

use Illuminate\Http\Request;

class EmailController extends BaseController
{
  public function checkConfig(Request $request)
  {
    $message = '';

    $serverConfig = $request->get('smtpServer');
    print_r($serverConfig);
    $smtpConfig = [
      'driver' => $serverConfig['mail_driver'],
      'host' => $serverConfig['mail_host'],
      'port' => $serverConfig['mail_port'],
      'username' => $serverConfig['mail_username'],
      'password' => $serverConfig['mail_password'],
      'encryption' => $serverConfig['mail_encryption'],
      'from_'

    ];

//    \Config::set('mail', $smtpConfig);
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