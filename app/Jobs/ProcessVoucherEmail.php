<?php

namespace App\Jobs;

use App\Helpers\EmailHelper;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Voucher;
use App\Models\VoucherCode;

class ProcessVoucherEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $voucher;
    protected $voucherCode;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Voucher $voucher, VoucherCode $voucherCode)
    {
    	$this->voucher = $voucher;
    	$this->voucherCode = $voucherCode;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
	    $smtpServer = $this->voucher->getSmtpServer();
	    EmailHelper::setSmtpServer($smtpServer);
	    EmailHelper::sendVoucherEmail($this->voucher, $this->voucherCode);
			$this->voucherCode->sent_on = date('Y-m-d H:i:s');
			$this->voucherCode->status = 'completed';
			$this->voucherCode->save();
    }
}
