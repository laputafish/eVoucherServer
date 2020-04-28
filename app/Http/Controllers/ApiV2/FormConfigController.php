<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\Media;
use App\Models\Voucher;
use App\Models\TempQuestionForm;
use Illuminate\Routing\Controller as _Controller;

use Illuminate\Http\Request;

class FormConfigController extends _Controller
{
	public function getFormConfigs($key)
	{
		$isTemp = substr($key, 0, 1)=='_';
		if ($isTemp) {
			$key = substr($key, 1);
			$formConfigs = $this->getTempFormConfigs($key);
		}
		return response()->json([
			'status' => true,
			'result' => [
				'formConfigs' => $formConfigs
			]
		]);
	}
	
	private function getTempFormConfigs($key) {
		$row = TempQuestionForm::where('form_key', $key)->first();
		return isset($row) ? json_decode($row->form_configs) : null;
	}
}