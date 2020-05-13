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
	
  public static function readTempVoucherTemplate($tempLeaflet) {
	  $tempLeafletId = $tempLeaflet->id;
	  $result = '';
	  $templateFullPath = $tempLeaflet->getTemplateFullPath('vouchers');
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
  
  public static function readVoucherTemplate($voucher) {
    $voucherId = $voucher->id;
    $result = '';
    $templateFullPath = $voucher->getTemplateFullPath('vouchers');
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

  public static function writeTempVoucherTemplate($tempLeaflet, $template) {
	  $templatePath = VoucherTemplateHelper::createTemplatePath($tempLeaflet->id);
	  $templateFile = 'v'.$tempLeaflet->id.'.tpl';
	  $templateFileFolder = storage_path('app/tempLeaflets/'.$templatePath);
	
	  // save file
	  if (!file_exists($templateFileFolder)) {
		  mkdir($templateFileFolder,0777,true);
	  }
	  $templateFullPath = $templateFileFolder.'/'.$templateFile;
	  if (file_exists($templateFullPath)) {
		  unlink($templateFullPath);
	  }
	  $f = fopen($templateFullPath, 'wb');
	  fwrite($f, $template);
	  fclose($f);
	
	  $tempLeaflet->template_path = $templatePath;
	  $tempLeaflet->save();
  }
  
  public static function writeVoucherTemplate($voucher, $template) {

	  $templatePath = VoucherTemplateHelper::createTemplatePath($voucher->id);
	  $templateFile = 'v'.$voucher->id.'.tpl';
	  $templateFileFolder = storage_path('app/vouchers/'.$templatePath);
	  
	  // save file
	  if (!file_exists($templateFileFolder)) {
		  mkdir($templateFileFolder,0777,true);
	  }
	  $templateFullPath = $templateFileFolder.'/'.$templateFile;
	  if (file_exists($templateFullPath)) {
		  unlink($templateFullPath);
	  }
	  $f = fopen($templateFullPath, 'wb');
	  fwrite($f, $template);
	  fclose($f);
	  
	  $voucher->template_path = $templatePath;
	  $voucher->save();
  }
}