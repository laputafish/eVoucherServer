<?php namespace App\Helpers;

class TempUploadFilePath {
	public static function newTempFile($userId, $voucherId, $tempFilePath) {
		$newKey = newKey();
		$tempUploadFile = TempUploadFile::create([
			'user_id' => $userId,
			'key' => $newKey,
			'filename' => $newKey,
			'voucher_id' => $voucherId
		]);
		
		$fullPath = storage_path('app/uploads/'.$newKey);
		mkdir(dirname($fullPath), 0755, true);
		rename($tempFilePath, $fullPath);

		return $newKey;
	}
	
}