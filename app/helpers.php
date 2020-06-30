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

function deleteDir($dirPath) {
	if (! is_dir($dirPath)) {
		throw new InvalidArgumentException("$dirPath must be a directory");
	}
	if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
		$dirPath .= '/';
	}
	$files = glob($dirPath . '*', GLOB_MARK);
	foreach ($files as $file) {
		if (is_dir($file)) {
			deleteDir($file);
		} else {
			unlink($file);
		}
	}
	rmdir($dirPath);
}

function nameToTag($str) {
	return str_replace(' ', '_', strtolower($str));
}

function any_in_array($needles, $ar) {
	$result = false;
	foreach($needles as $needle) {
		if (in_array($needle, $ar)) {
			$result = true;
			break;
		}
	}
	return $result;
}

function getRandomWord($len = 10) {
	$word = array_merge(range('a', 'z'), range('A', 'Z'));
	shuffle($word);
	return substr(implode($word), 0, $len);
}

function echoln($msg) {
	echo $msg."<br/>";
}