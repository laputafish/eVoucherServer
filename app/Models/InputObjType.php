<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputObjType extends Model {
  protected $fillable = [
    'type',
    'icon',
    'new_icon',
    'text',
    'label',
    'is_input',
    'fixed',
    'default'
  ];
}
