<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherRedemptionLocation extends Model
{
	protected $fillable = [
		'voucher_id',
		'order',
		'name',
		'location_code',
		'qrcode',
		'password',
		'redemption_count'
	];
	
	public function voucher() {
		return $this->belongsTo(Voucher::class);
	}
}