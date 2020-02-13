<?php namespace App\Helpers;

define('ENCRYPTION_KEY', 'yoovYoov');

class UploadFileHelper {
  public static function saveTempFile($uploadedFile) {
    $tempDir = storage_path('app/temp');
    $originalName = $_FILES['file']['name'];
    $filename = static::createFilename($originalName);
    $outputPath = $tempDir . '/' . $filename;
    move_uploaded_file($uploadedFile['tmp_name'], $outputPath);
    return $outputPath;
  }

  public static function createFilename($filename)
  {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $key = newKey();
    return $key . '.' . $ext;
  }

  public static function newKey() {
    $result = date('Ymd_His') . '_' . substr((string)microtime(), 2, 8);
    return md5($result); // ENCRYPTION_KEY);
  }
}