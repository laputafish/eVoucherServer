<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherCustomForm extends Model {
	protected $table = 'voucher_custom_forms';
	protected $fillable = [
	  'voucher_id',
    'form_key',
    'name',
    'type',
    'form_configs'
	];
	
	public function voucher() {
		return $this->belongsTo(Voucher::class);
	}
}
