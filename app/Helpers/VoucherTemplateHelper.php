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
	
	public static function getTemplateFullPath($folder, $subFolders, $id, $fileNamePrefix='', $fileNameSuffix='') {
		$result = null;
		if (!is_null($subFolders) && !empty($subFolders)) {
			$result = storage_path('app/'.$folder.'/'.
				$subFolders.'/'.
				$fileNamePrefix.$id.$fileNameSuffix.'.tpl');
	  }
		return $result;
	}
	
	public static function readTempVoucherTemplate($tempLeaflet) {
//	  $tempLeafletId = $tempLeaflet->id;
	  $result = '';
	  $templateFullPath = static::getTemplateFullPath(
	  	'tempLeaflets',
		  $tempLeaflet->template_path,
		  $tempLeaflet->id,
		  'v');
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
  
  public static function readVoucherTemplate($voucher, $fileNameSuffix='') {
    $voucherId = $voucher->id;
    $result = '';
//    $templateFullPath = $voucher->getTemplateFullPath('vouchers');
    $templateFullPath = static::getTemplateFullPath(
    	'vouchers',
	    $voucher->template_path,
	    $voucher->id,
	    'v',
	    $fileNameSuffix);
    if (!is_null($templateFullPath)) {
      if (file_exists($templateFullPath)) {
        $filesize = filesize($templateFullPath);
        if ($filesize > 0) {
	        $fp = fopen($templateFullPath, 'rb');
	        $result = fread($fp, $filesize);
	        fclose($fp);
        }
      }
    }
    return $result;
  }

//  public static function writeTempVoucherTemplate($tempLeaflet, $template) {
//	  $templatePath = VoucherTemplateHelper::createTemplatePath($tempLeaflet->id);
//	  $templateFile = 'v'.$tempLeaflet->id.'.tpl';
//	  $templateFileFolder = storage_path('app/tempLeaflets/'.$templatePath);
//
//	  // save file
//	  if (!file_exists($templateFileFolder)) {
//		  mkdir($templateFileFolder,0777,true);
//	  }
//	  $templateFullPath = $templateFileFolder.'/'.$templateFile;
//	  if (file_exists($templateFullPath)) {
//		  unlink($templateFullPath);
//	  }
//	  $f = fopen($templateFullPath, 'wb');
//	  fwrite($f, $template);
//	  fclose($f);
//
//	  $tempLeaflet->template_path = $templatePath;
//	  $tempLeaflet->save();
//  }
  
  public static function writeVoucherTemplate($folder, $voucherId, $template, $suffix='') {

	  $templatePath = VoucherTemplateHelper::createTemplatePath($voucherId);
	  $templateFile = 'v'.$voucherId.$suffix.'.tpl';
	  $templateFileFolder = storage_path('app/' .$folder.'/'.$templatePath);
	  
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
	  return $templatePath;
  }
}