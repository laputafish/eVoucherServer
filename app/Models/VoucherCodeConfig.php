<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherCodeConfig extends Model {
  protected $fillable = [
    'voucher_id',
    'composition',
    'code_group',
    'code_type',
    'width',
    'height'
  ];

  public function voucher() {
    return $this->belongsTo('App\Models\Voucher');
  }
}
