<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class _t extends Model {
  protected $table = 'medias';
  
  protected $primary_key = null;
  public $incrementing = false;
  
  public $timestamps = false;
  protected $fillable = [];
  
  protected $appends = ['visibility'];
  protected $with = ['resource'];
}
