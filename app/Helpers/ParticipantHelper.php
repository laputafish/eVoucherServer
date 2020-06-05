<?php namespace App\Helpers;

class ParticipantHelper {
	public static function getFieldValues($formContent) {
		return explode('||', $formContent);
	}
}