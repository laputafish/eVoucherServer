<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
  public $fillable = [
    'description',
    'agent_id',
    'activation_date',
    'expiry_date',
    'template',
    'status'
  ];
}
