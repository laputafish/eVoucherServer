<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use App\Helpers\VoucherHelper;

class VoucherMailingStatusUpdatedEvent implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;
	
	public $voucher;
	public $mailingSummary;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($voucher)
	{
		$this->voucher = $voucher;
		$summaryResult = VoucherHelper::getMailingSummary($voucher->id);
		$this->mailingSummary = $summaryResult['result'];
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return \Illuminate\Broadcasting\Channel|array
	 */
	public function broadcastOn()
	{
		return new Channel('voucher.channel');
	}
	
	public function broadcastAs() {
		return 'VoucherMailingStatusUpdated';
	}
}
