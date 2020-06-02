<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VoucherMail extends Mailable
{
    use Queueable, SerializesModels;

    public $voucher;
    public $mailContent;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($voucher, $mailContent)
    {
      $this->mailContent = $mailContent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
    	$smtpServer = $voucher->getSmtpServer();
    	
      return $this->from([
      	'address' => $smtpServer['mail_from_address'],
	      'name' => $smtpServer['mail_from_name']
      ])->view('view.email.voucher')
	      ->with('mailContent', $mailContent);
    }
}
