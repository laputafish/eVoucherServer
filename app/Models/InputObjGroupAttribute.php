<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputObjGroupAttribute extends Model {
  protected $fillable = [
    'input_obj_group_id',
    'attribute_key',
    'order'
  ];
}
