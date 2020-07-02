<?php namespace App\Http\Controllers\ApiV2;

use Illuminate\Http\Request;

class UserController extends BaseModuleController
{
	
	public function getSettings(Request $request)
	{
		$settings = $this->user->settings;
		$result = [];
		foreach ($settings as $setting) {
			$result[$setting->key_name] = $setting->key_value;
		}
		return response()->json([
			'status' => true,
			'result' => $result
		]);
	}
	
	public function setSettings(Request $request)
	{
		$input = $request->all();
		$keys = $this->user->settings()->pluck('key_name')->toArray();
		
		$updateCount = 0;
		$newCount = 0;
		foreach ($input as $keyName => $keyValue) {
			if (in_array($keyName, $keys)) {
				$this->user->settings()->update(['key_value' => $keyValue]);
				$updateCount++;
			} else {
				$keyInfo = new UserSetting([
					'key_name' => $keyName,
					'key_value' => $keyValue
				]);
				$this->user->settings()->save($keyInfo);
				$newCount++;
			}
		}
		return response()->json([
			'status' => true,
			'result' => [
				'newCount' => $newCount,
				'updateCount' => $updateCount
			]
		]);
	}
}