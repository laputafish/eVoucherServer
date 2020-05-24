<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputObjGroup extends Model {
  protected $fillable = [
    'input_obj_type_id',
    'caption',
    'order',
    'remark'
  ];

  public function attributes() {
    return $this->hasMany(InputObjGroupAttribute::class, 'input_obj_group_id');
  }

  public function inputObjType() {
    return $this->belongsTo(InputObjType::class);
  }
}
