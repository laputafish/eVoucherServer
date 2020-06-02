<?php

function newKey()
{
	$result = date('Ymd_His') . '_' . substr((string)microtime(), 2, 8);
	return md5($result); // ENCRYPTION_KEY);
}

function strToKeyValues($str, $separator = ';')
{
	$result = [];
	if (isset($str)) {
		$segs = explode($separator, $str);
		foreach ($segs as $seg) {
			if (!empty($seg)) {
				$keyPair = explode(':', $seg);
				if (count($keyPair) > 1) {
					$result[$keyPair[0]] = $keyPair[1];
				}
			}
		}
	}
	return $result;
}

function keyValueArrayToStr($ar, $keyValueSeparator = ':', $itemSeparator = ';')
{
	$lines = [];
	foreach ($ar as $key => $value) {
		$lines[] = $key . $keyValueSeparator . $value;
	}
	return implode($itemSeparator, $lines);
}

function nullOrBlank($value)
{
	return is_null($value) ? '' : $value;
}

function getKeyFromKeyValue($keyValue)
{
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

function platformSlashes($path)
{
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

function formConfigsToData($formConfigs) {
	array_walk_recursive($formConfigs,function(&$formConfigs) {
		$formConfigs=strval($formConfigs);
	});
	return json_encode($formConfigs);
}

function explodeByCount($separator, $target, $count, $default) {
	$result = array_fill(0, $count, $default);
	$segs = explode($separator, $target);
	$end = min($count, count($segs));
	for($i = 0; $i < $end; $i++) {
		$result[$i] = $segs[$i];
	}
	return $result;
}