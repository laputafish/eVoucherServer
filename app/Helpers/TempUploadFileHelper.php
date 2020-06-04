<?php namespace App\Helpers;

use App\Models\TempUploadFile;

class TempUploadFileHelper {
	
	public static function newTempFile($userId, $voucherId, $tempFilePath, $fileType='excel', $return='key') {
		$newKey = newKey();
		$ext = pathinfo($tempFilePath, PATHINFO_EXTENSION);
		$targetFileName = $newKey.'.'.$ext;
		
		$tempUploadFile = TempUploadFile::create([
			'user_id' => $userId,
			'key' => $newKey,
			'filename' => $targetFileName,
			'voucher_id' => $voucherId,
			'type' => $fileType,
		]);
		
		$fullPath = storage_path('app/uploads/'.$targetFileName);
//		echo 'fullPath = '.$fullPath.PHP_EOL;
		if (!file_exists(dirname($fullPath))) {
			mkdir(dirname($fullPath), 0755, true);
		}
		rename($tempFilePath, $fullPath);

		$result = $tempUploadFile;
		switch ($return) {
			case 'key':
				$result = $newKey;
				break;
			case 'record':
				$result = $tempUploadFile;
				break;
			case 'path':
				$result = $fullPath;
				break;
			case 'all':
				$result = [
					'key' => $newKey,
					'record' => $tempUploadFile,
					'path'=> $fullPath
				];
				break;
		}
		return $result;
	}
	
	public static function removeUserTempFiles ($userId) {
		$tempUploadFiles = TempUploadFile::where('user_id', $userId)->get();
		foreach($tempUploadFiles as $row) {
			switch ($row->type) {
				case 'image':
				case 'excel':
					$fullPath = storage_path('app/uploads/' . $row->filename);
					if (file_exists($fullPath)) {
						unlink($fullPath);
					}
					break;
				case 'zip':
					$extractFolder = storage_path('app/uploads/' . $row->key);
					if (file_exists($extractFolder)) {
						deleteDir($extractFolder);
					}
					$fullPath = storage_path('app/uploads/' . $row->filename);
					if (file_exists($fullPath)) {
						unlink($fullPath);
					}
					break;
			}
			TempUploadFile::where('key', $row->key)->delete();
		}
	}
	
	public static function getUploadFileContentByKey($key) {
		$tempUploadFile = TempUploadFile::where('key', $key)->first();
		$fileName = $tempUploadFile['filename'];
		$filePath = storage_path('app/uploads').'/'.$fileName;
		
		$result = '';
		if (!is_null($filePath)) {
			if (file_exists($filePath)) {
				$filesize = filesize($filePath);
				if ($filesize > 0) {
					$fp = fopen($filePath, 'rb');
					$result = fread($fp, $filesize);
					fclose($fp);
				}
			}
		}
		return $result;
	}
}
