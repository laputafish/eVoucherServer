<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherCode extends Model
{
  protected $fillable = [
    'voucher_id',
    'order',
    'code',
    'extra_fields',
    'key',
    'sent_on',
    'status',
	  'participant_id',
    'remark'
  ];

  public function voucher() {
    return $this->belongsTo('App\Models\Voucher');
  }
}
