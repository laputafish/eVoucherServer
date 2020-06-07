<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Command extends Model {
//  protected $primary_key = null;
//  public $incrementing = false;

//  public $timestamps = false;
  protected $fillable = [
    'name',
    'last_checked_at',
    'enabled',
    'loop',
    'mode',
    'forced',
    'reset_log',
    'message'
  ];
}
