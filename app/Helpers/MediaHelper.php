<?php namespace App\Helpers;

use LaravelQRCode\Facades\QRCode;

class MediaHelper {
  public static function checkMediaFolder($folder='app/images') {
    $mediaPath = static::getMediaFolder($folder);
    if (!file_exists($mediaPath)) {
      mkdir($mediaPath, 755, true);
    }
    return $mediaPath;
  }

  public static function getMediaFolder($folder='app/images') {
    return storage_path($folder);
  }
}
