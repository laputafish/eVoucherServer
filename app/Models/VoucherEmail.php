<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherEmail extends Model
{
  protected $fillable = [
    'voucher_id',
    'voucher_code_id',
    'email',
    'sent_at',
    'status',
    'remark'
  ];
}
