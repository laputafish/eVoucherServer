<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class VoucherCodeRedeemedEvent implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;
	
	public $voucherCode;
	public $totalRedeemed;

	public function __construct($voucherCode)
	{
		$voucher = $voucherCode->voucher;
		$this->voucherCode = [
			'id' => $voucherCode->id,
			'voucher_id' => $voucherCode->voucher_id,
			'redeemed_on' => $voucherCode->redeemed_on
		];
		$this->totalRedeemed = $voucher->codes()->whereNotNull('redeemed_on')->count();
	}

	public function broadcastOn()
	{
		return ['voucher'.$this->voucherCode['voucher_id'].'.channel'];
	}
	
	public function broadcastAs() {
		return 'VoucherCodeRedeemed';
	}
}
