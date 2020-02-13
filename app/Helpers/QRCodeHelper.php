<?php namespace App\Helpers;

use LaravelQRCode\Facades\QRCode;

class QRCodeHelper {
  public static function get($text) {
    $qrCodeStr = QRCode::text($text);
    $imgData = base64_encode($qrCodeStr);
    $src = 'data: '.mime_content_type('voucher.jpg').';base64,'.$imgData;
    return $src;
  }

  public static function getImg($text) {
    $src = static::get($text);
    return '<img src="'.$src.'">';
  }
}
