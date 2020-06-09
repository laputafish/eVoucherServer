<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class VoucherCodeViewsUpdatedEvent implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $voucherCode;
  public $totalViews;
  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct($voucherCode)
  {
  	$voucher = $voucherCode->voucher;
  	$this->voucherCode = [
      'id' => $voucherCode->id,
      'views' => $voucherCode->views,
	    'voucher_id' => $voucherCode->voucher_id
    ];
  	$this->totalViews = $voucher->codes()->sum('views');
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return \Illuminate\Broadcasting\Channel|array
   */
  public function broadcastOn()
  {
	  return ['voucher'.$this->voucherCode['voucher_id'].'.channel'];
  }

  public function broadcastAs() {
    return 'VoucherCodeViewsUpdated';
  }
}
