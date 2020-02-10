<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherCode extends Model
{
  public $fillable = [
    'voucher_id',
    'code',
    'serial_no',
    'amount',
    'expiry_date',
    'activation_date'
  ];

}
