<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherMailingStatus extends Model {
	protected $table = 'voucher_mailing_status';
	
	protected $primary_key = null;
	public $incrementing = false;
	
//	public $timestamps = false;
	protected $fillable = [
		'participant_id',
		'sent_at',
		'message',
		'status',
		'created_at',
		'updated_at'
	];
	
//	protected $appends = ['visibility'];
//	protected $with = ['resource'];
}
