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
}