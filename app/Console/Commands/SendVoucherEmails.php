<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Helpers\LogHelper;
use App\Helpers\VoucherHelper;

class SendVoucherEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:voucherEmails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Voucher Emails';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      LogHelper::log('SendVoucherEmails command starts.');
      VoucherHelper::checkAndSendEmails();
        //
    }
}
