<?php namespace App\Helpers;

use App\Models\TemplateKey;
use App\Models\Voucher;

class QuestionnaireHelper {
	public static function getBlankFormConfigs() {
		return [
			'pageConfig' => [
				'bgColor' => 'white',
			],
			'inputObjs' => []
		];
	}
}