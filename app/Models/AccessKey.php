<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessKey extends Model
{
  protected $primaryKey = null;
  public $incrementing = false;

  protected $fillable = [
    'user_id',
    'key',
    'module',
    'command',
    'params',
    'remark'
  ];

  public function user() {
    return $this->belongsTo('App\Models\User');
  }
}
