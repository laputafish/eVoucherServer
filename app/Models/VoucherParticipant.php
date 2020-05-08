<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherParticipant extends Model
{
	protected $fillable = [
		'voucher_id',
		'form_content',
		'participant_key',
		'remark'
	];
	
	public function voucher() {
		return $this->belongsTo('App\Models\Voucher');
	}
}
