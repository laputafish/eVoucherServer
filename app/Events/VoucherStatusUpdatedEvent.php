<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class VoucherStatusUpdatedEvent implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $voucher;
  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct($voucher)
  {
    $this->voucher = $voucher;
  }

//    public function broadcastWith() {
//      return [
//        'voucherCode' => $this->voucherCode
//      ];
//    }
  /**
   * Get the channels the event should broadcast on.
   *
   * @return \Illuminate\Broadcasting\Channel|array
   */
  public function broadcastOn()
  {
	  return ['voucher'.$this->voucher->id.'.channel'];
  }

  public function broadcastAs() {
    return 'VoucherStatusUpdated';
  }
}
