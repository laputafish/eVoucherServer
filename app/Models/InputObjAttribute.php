<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputObjAttribute extends Model {
  protected $fillable = [
    'name',
    'caption',
    'style_name',
    'option_group'
  ];
}
