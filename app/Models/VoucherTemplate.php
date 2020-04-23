<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherTemplate extends Model {
	protected $table = 'voucher_templates';
	protected $fillable = [
		'type',
		'content',
		'questionnaire_fields'
	];
	
	public function voucher() {
		return $this->belongsTo(VoucherTemplate::class);
	}
}
