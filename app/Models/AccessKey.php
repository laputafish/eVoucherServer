<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessKey extends Model
{
  protected $fillable = [
    'user_id',
    'key',
    'module',
    'command',
    'params'
  ];

  public function user() {
    return $this->belongsTo('App\Models\User');
  }
}
