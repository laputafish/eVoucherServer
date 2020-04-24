<?php

function newKey() {
  $result = date('Ymd_His') . '_' . substr((string)microtime(), 2, 8);
  return md5($result); // ENCRYPTION_KEY);
}

function nullOrBlank($value) {
	return is_null($value) ? '' : $value;
}
function getKeyFromKeyValue($keyValue) {
  $result = '';
  if (!empty($keyValue)) {
    $segs = explode(':', $keyValue);
    $result = $segs[0];
  }
  return $result;
}

function is_dir_empty($dir)
{
	if (!is_readable($dir)) {
		return NULL;
	} else {
		return (count(scandir($dir)) == 2);
	}
}

function platformSlashes($path ) {
	if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
		$path = str_replace('/', '\\', $path);
	}
	return $path;
}

function cascadePurgeFolders($dir, $baseDir)
{
	if ($dir != $baseDir) {
		$folder = dirname($dir);
		if (is_dir_empty($dir)) {
			
			rmdir($dir);
		}
		cascadePurgeFolders($folder, $baseDir);
	}
}
