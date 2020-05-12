<?php namespace App\Http\Controllers\ApiV2;

use App\Helpers\VoucherTemplateHelper;

class TestController extends BaseController {
  public function getTemplatePath() {
    $newPath = VoucherTemplateHelper::createTemplatePath();
    return $newPath;
  }
}