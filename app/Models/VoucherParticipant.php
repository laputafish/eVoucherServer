<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherParticipant extends Model
{
	protected $fillable = [
		'voucher_id',
		'name',
		'email',
		'phone',
		'form_content',
		'participant_key',
		'status',
		'error_message',
		'sent_at',
		'remark'
	];
	
	public function voucher() {
		return $this->belongsTo('App\Models\Voucher');
	}
	
	public function code() {
		return $this->hasOne('App\Models\VoucherCode', 'participant_id');
	}
	
	public function mailingStatus() {
		return $this->hasMany(VoucherMailiingStatus::class, 'participant_id');
	}
}
