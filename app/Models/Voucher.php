<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
  protected $fillable = [
    'description',
    'agent_id',
    'activation_date',
    'expiry_date',
    'template',
    'qr_code_composition',
    'status'
  ];

  public function codeInfos() {
    return $this->hasMany('App\Models\VoucherCode');
  }

  public function emails() {
    return $this->hasMany('App\Models\VoucherEmail');
  }

  public function agent() {
    return $this->belongsTo('App\Models\Agent');
  }
}
