<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Agent;
use App\Models\VoucherCode;
use App\Models\TempLeaflet;
use App\Models\SmtpServer;

use App\Helpers\TemplateHelper;
use App\Helpers\QRCodeHelper;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use App\Models\System;

class SystemController extends BaseController
{
  public function getConfigs() {
    $configs = System::all();
    $result = [];
    foreach($configs as $config) {
      $result[$config->key] = $config->value;
    }
    if (array_key_exists('smtp_server_id', $result)) {
      $result['smtp_server'] = SmtpServer::find($result['smtp_server_id']);
    } else {
      $result['smtp_server'] = null;
    }
    return response()->json([
      'status' => true,
      'result' => $result
    ]);
  }
}
