<?php

namespace App\Models;

class Role extends BaseModal
{
  protected $fillable = [
    'name',
    'description',
  ];

  public function users()
  {
    return $this->belongsToMany('App\User');
  }
}
