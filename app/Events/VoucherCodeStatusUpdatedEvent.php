<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class VoucherCodeStatusUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $voucherCode;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($voucherCode)
    {
	    $participant = $voucherCode->participant;
	    $this->voucherCode = [
		    'id' => $voucherCode->id,
		    'voucher_id' => $voucherCode->voucher_id,
		    'status' => $voucherCode->status,
		    'sent_on' => $voucherCode->sent_on,
		    'error_message' => $voucherCode->error_message,
		    'participant_name' => $participant->name,
		    'participant_email' => $participant->email
	    ];
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
//    	echo 'this->voucherCode: '.PHP_EOL;
//    	print_r($this->voucherCode);
//    	echo PHP_EOL;
//
      return ['voucher'.$this->voucherCode['voucher_id'].'.channel'];
    }

    public function broadcastAs() {
      return 'VoucherCodeStatusUpdated';
    }
}
