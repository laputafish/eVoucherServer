<?php namespace App\Helpers;

class LogHelper {
  public static $enabled = true;

  public static function log($msg) {
    if (static::$enabled) {
      echo date('Y-m-d H:i:s: ') . $msg . PHP_EOL;
    }
  }
  public static function reset() {
    $logFile = storage_path('logs/sendEmails.log');
    $fp = fopen($logFile, 'w');
    fclose($fp);
  }
}