<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
  protected $table = 'medias';

  protected $fillable = [
    'user_id',
    'type',
    'path',
    'scope',
    'filename'
  ];

  public function user() {
    return $this->belongsTo(User::class);
  }
    //
}
