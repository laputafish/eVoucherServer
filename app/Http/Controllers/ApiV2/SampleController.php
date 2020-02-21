<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Agent;
use App\Models\VoucherCode;
use App\Models\TempLeaflet;

use App\Helpers\TemplateHelper;
use App\Helpers\QRCodeHelper;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;

class SampleController extends BaseController
{
  public function download() {
    $path = storage_path('app/samples/samples.zip');
    $headers = [
      'Content-Type' => 'application.zip'
    ];
    $basename = pathinfo($path, PATHINFO_BASENAME);
    return response()->download($path, $basename, $headers);
  }
}
