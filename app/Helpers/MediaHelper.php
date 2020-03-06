<?php namespace App\Helpers;

use LaravelQRCode\Facades\QRCode;

class MediaHelper {
  public static function checkMediaFolder() {
    $mediaPath = static::getMediaFolder();
    if (!file_exists($mediaPath)) {
      mkdir($mediaPath, 755, true);
    }
    return $mediaPath;
  }

  public static function getMediaFolder() {
    return storage_path('app/images');
  }
}
