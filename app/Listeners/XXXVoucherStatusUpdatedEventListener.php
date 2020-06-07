<?php

namespace App\Listeners;

use App\Events\VoucherStatusUpdatedEvent;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class XXXVoucherStatusUpdatedEventListener
{
  /**
   * Create the event listener.
   *
   * @return void
   */
  public function __construct()
  {
    //
  }

  /**
   * Handle the event.
   *
   * @param  object  $event
   * @return void
   */
  public function handle(VoucherStatusUpdatedEvent $event)
  {
    //
  }
}
