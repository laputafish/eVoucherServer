<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class VoucherParticipantStatusUpdatedEvent implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;
	
	public $statusInfo;
	
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($participant)
	{
		$voucher = $participant->voucher;
		$code = $participant->code;
		
		$this->statusInfo = [
			'participant_id' => $participant->id,
			'code_id' => isset($code) ? $code->id : 0,
			'voucher_id' => $voucher->id,
			'status' => $participant->status,
			'sent_at' => $participant->sent_at,
			'error_message' => $participant->error_message,
			'participant_name' => $participant->name,
			'participant_email' => $participant->email
		];
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return \Illuminate\Broadcasting\Channel|array
	 */
	public function broadcastOn()
	{
		return ['voucher'.$this->statusInfo['voucher_id'].'.channel'];
	}
	
	public function broadcastAs() {
		return 'VoucherParticipantStatusUpdated';
	}
}
