<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\VoucherTemplateHelper;

class VoucherRedemptionLocation extends Model
{
	protected $fillable = [
		'voucher_id',
		'order',
		'name',
		'location_code',
		'qrcode',
		'redemption_count'
	];
	
	public function voucher() {
		return $this->belongsTo(Voucher::class);
	}
}