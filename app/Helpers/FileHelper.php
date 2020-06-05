<?php namespace App\Helpers;

class FileHelper {
	public static function getFirstFile($folder) {
		$pattern = $folder.'/*.html';
		$result = null;
		foreach (glob($pattern) as $file) {
			$result = $file;
			break;
		}
		return $result;
	}
	
	public static function checkCreateFolder($folderPath) {
		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0777, true);
		}
	}
}