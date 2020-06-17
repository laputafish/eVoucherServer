<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\SimpleMessageEvent;

use App\Models\Voucher;
use App\Events\VoucherStatusUpdatedEvent;

class SendVoucherStatusUpdated extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'send:voucherStatusUpdated {voucherId}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Send VoucherStatusUpdated Event.';

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
    $voucherId = (int) $this->argument('voucherId');
    $voucher = Voucher::find($voucherId);
    if (isset($voucher)) {
      event(new VoucherStatusUpdatedEvent($voucher));
    } else {
      echo '*** Incorrect Voucher Id ***';
    }
    //
  }
}
