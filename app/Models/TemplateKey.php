<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateKey extends Model
{
  public $timestamps = false;

  protected $fillable = [
    'key',
    'field_path',
    'enabled'
  ];

}
