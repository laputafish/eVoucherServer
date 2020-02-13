<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempLeaflet extends Model
{
  protected $fillable = [
    'key',
    'title',
    'qr_code_size',
    'template',
    'params'
  ];

}
