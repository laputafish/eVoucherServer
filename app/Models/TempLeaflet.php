<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempLeaflet extends Model
{
  protected $primaryKey = null;
  public $incrementing = false;

  protected $fillable = [
    'key',
    'title',
    'code_configs',
    'template',
    'params'
  ];

}
