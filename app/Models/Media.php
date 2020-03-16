<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
  protected $table = 'medias';

  protected $fillable = [
    'user_id',
    'type',
    'path',
    'scope',
    'filename'
  ];

  public function user() {
    return $this->belongsTo(User::class);
  }
  
  public function getFilePathAttribute() {
  	$isTemp = $this->type === 'temp';
  	$partialPath = ($isTemp ? 'temp/' : 'images/').$this->path.'/';
  	return storage_path('app/'.$partialPath.$this->filename);
  }
}
