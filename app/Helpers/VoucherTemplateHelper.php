<?php namespace App\Helpers;

use App\Models\VoucherCode;
use App\Models\Voucher;

class VoucherTemplateHelper {
  public static function createTemplatePath($voucherId) {
    {
      $md5 = md5($voucherId);
      return substr($md5, 0, 1) . '/' .
        substr($md5, 1, 1) . '/' .
        substr($md5, 2, 1);
    }
  }

  public static function readVoucherTemplate($voucher) {
    $voucherId = $voucher->id;
    $result = '';
    $templateFullPath = $voucher->template_full_path;
    if (!is_null($templateFullPath)) {
      if (file_exists($templateFullPath)) {
        $filesize = filesize($templateFullPath);
        $fp = fopen($templateFullPath, 'rb');
        $result = fread($fp, $filesize);
        fclose($fp);
      }
    }
    return $result;
  }

  public static function writeVoucherTemplate($voucher, $template) {
    $voucherId = $voucher->id;
    $templatePath = $voucher->template_path;
    if (empty($templatepath)) {
      $templatePath = self::createTemplatePath($voucherId);
      $voucher->template_path = $templatePath;
      $voucher->save();
    }
    $templateFullPath = $voucher->templateFullPath;
    if (file_exists($templateFullPath)) {
      unlink($templateFullPath);
    }
    $fp = fopen($templateFullPath, 'wb');
    fwrite($fp, $template);
    fclose($fp);
  }
}