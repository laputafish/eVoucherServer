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
    $result = date('Ymd_His') . '_' . substr((string)microtime(), 2, 8);
    $encoded = md5($result); // ENCRYPTION_KEY);
    return $encoded . '.' . $ext;
  }

}