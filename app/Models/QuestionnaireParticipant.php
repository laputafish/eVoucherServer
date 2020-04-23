<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionnaireParticipant extends Model {
	protected $table = 'questionnaire_participants';
	protected $fillable = [
		'field_values'
	];
}
