<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class UserSetting extends Model {
//	protected $table = 'medias';

//	protected $primary_key = null;
//	public $incrementing = false;

//	public $timestamps = false;
	protected $fillable = [
		'user_id',
		'key_name',
		'key_value',
		'remark'
	];
	
//	protected $appends = ['visibility'];
//	protected $with = ['resource'];

	public function user() {
		return $this->belongsTo(User::class);
	}
}
