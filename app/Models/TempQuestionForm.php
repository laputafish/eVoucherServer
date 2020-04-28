<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempQuestionForm extends Model {
	protected $primaryKey = null;
	public $incrementing = false;
	
	protected $table = 'temp_question_forms';
	public $timestamps = false;
	
	protected $fillable = [
		'user_id',
		'form_key',
		'form_configs'
	];
}
