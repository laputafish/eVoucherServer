<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VoucherPlainText extends Mailable
{
    use Queueable, SerializesModels;
	
		public $voucherCode;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($key)
    {
    	$this->voucherCode = VoucherCode::where('key', $key)->first();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
	    $voucher = $this->voucherCode->voucher;
	    $smtpServer = $voucher->getSmtpServer();
	
	    return $this->from([
		    'address' => $smtpServer['mail_from_address'],
		    'name' => $smtpServer['mail_from_name']
	    ])->text('view.email.voucher_text');
	
    }
}
