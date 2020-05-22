<?php namespace App\Helpers;

use App\Models\TempUploadFile;

class TempUploadFileHelper {
	public static function newTempFile($userId, $voucherId, $tempFilePath) {
		$newKey = newKey();
		$ext = pathinfo($tempFilePath, PATHINFO_EXTENSION);
		$targetFileName = $newKey.'.'.$ext;
		
		$tempUploadFile = TempUploadFile::create([
			'user_id' => $userId,
			'key' => $newKey,
			'filename' => $targetFileName,
			'voucher_id' => $voucherId
		]);
		
		$fullPath = storage_path('app/uploads/'.$targetFileName);
		if (!file_exists(dirname($fullPath))) {
			mkdir(dirname($fullPath), 0755, true);
		}
		rename($tempFilePath, $fullPath);

		return $newKey;
	}
	
	public static function removeUserTempFiles($userId) {
		$tempUploadFiles = TempUploadFile::where('user_id', $userId)->get();
		foreach($tempUploadFiles as $row) {
			$fullPath = storage_path('app/uploads/'.$row->filename);
			if (file_exists($fullPath)) {
				unlink($fullPath);
			}
			TempUploadFile::where('key', $row->key)->delete();
		}
	}
	
}