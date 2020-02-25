<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Agent;
use App\Models\VoucherCode;
use App\Models\TempLeaflet;

use App\Helpers\TemplateHelper;
use App\Helpers\QRCodeHelper;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use App\Models\System;

class SystemController extends BaseController
{
  public function getConfig() {
    $configs = System::all();
    $result = [];
    foreach($configs as $config) {
      $result[$config->key] = $config->value;
    }
    return response()->json([
      'status' => true,
      'result' => $result
    ]);
  }
}
