<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Voucher;
use App\Helpers\VoucherTemplateHelper;

class VoucherTemplateController extends BaseController {

  public function migrateTemplates() {
    $vouchers = Voucher::all();
    $voucherCount = $vouchers->count();
    $convertedCount = 0;
    foreach($vouchers as $voucher) {
      $converted = $this->migrateVoucherTemplate($voucher);
      if ($converted) {
        $convertedCount++;
      }
    }
    return response()->json([
      'converted' => $convertedCount,
      'total' => $voucherCount
    ]);
  }

  public function migrateVoucherTemplate($voucher) {
    $result = false;
    $template = $voucher->template;
    if (empty(trim($template))) {
      $voucher->template = '';
      $voucher->save();
    } else {
      $templatePath = VoucherTemplateHelper::createTemplatePath($voucher->id);
      $templateFile = 'v'.$voucher->id.'.tpl';
      $templateFileFolder = storage_path('app/vouchers/'.$templatePath);
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
      $voucher->template = '';
      $voucher->save();
      $result = true;
    }
    return $result;
  }
}