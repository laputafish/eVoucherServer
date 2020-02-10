<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Menu extends Model
{
  use NodeTrait;

  public $fillable = [
    'parent_id',
    'type',
    'label_tag',
    'icon_class',
    'link'
  ];

}
