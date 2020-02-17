<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Agent;
use App\Models\VoucherCode;

use App\Helpers\TemplateHelper;
use App\Helpers\QRCodeHelper;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;

class TemplateController extends BaseController
{
  protected $modelName = 'TempLeaflet';

  public function createTemp()
  {
    $input = \Input::all();
    $record = $input['record'];
    if ($record['agent_id'] !== 0) {
      $record['agent'] = Agent::find($record['agent_id'])->toArray();
    }
    $params = TemplateHelper::createParams($record, $input['codeInfo']);

    $key = newKey();
    $new = $this->model->create([
      'user_id' => 0,
      'title' => $record['description'],
      'qr_code_size' => $record['qr_code_size'],
      'key' => $key,
      'template' => $record['template'],
      'params' => serialize($params)
    ]);

    return response()->json([
      'status'=>true,
      'result'=>$key
    ]);
  }

  public function getTemplateHtml(Request $request)
  {
    $key = $request->get('key');
    $isTemp = $request->get('isTemp');

    if ($isTemp) {
      $processedTemplate = $this->processTempLeaflet($key);
    } else {
      $processedTemplate = $this->processLeaflet($key);
    }
    return $processedTemplate;
  }

  private function processTempLeaflet($key) {
    $leaflet = $this->model->where('key', $key)->first();
    $result = TemplateHelper::processTemplate(
      $leaflet->template,
      $leaflet->qr_code_size,
      unserialize($leaflet->params)
    );
    return response()->json([
      'status' => true,
      'result' => $result
    ]);
  }
  private function processLeaflet($key) {
    $voucherCode = VoucherCode::where('key', $key)->first();
    $voucher = $voucherCode->voucher;

    return $voucher->id;
    $params = TemplateHelper::createParams(
      $voucher,
      $voucherCode
    );

    return view('templates.leaflet', [
      'title' => 'xx',
      'template' => $voucher->template
    ]);

    return response()->json(['code'=>$voucher->template]);

    $result = TemplateHelper::processTemplate(
      $voucher->template,
      $voucherCode->qr_code_size,
      $params
    );
    return $result;
    return response()->json([
      'status' => true,
      'result' => $result
    ]);
  }

  public function view($key) {
    $leaflet = $this->model->where('key', $key)->first();
    $params =  unserialize($leaflet->params);
    $processed = TemplateHelper::processTemplate($leaflet->template, $leaflet->qr_code_size, $params);

    return view('templates.leaflet', [
      'title' => $leaflet->title,
      'template' => $processed
    ]);
  }
}
