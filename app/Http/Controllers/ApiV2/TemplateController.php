<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Agent;
use App\Helpers\TemplateHelper;
use App\Helpers\QRCodeHelper;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

  public function view($key) {
    $leaflet = $this->model->where('key', $key)->first();

    $template = $leaflet->template;

//    $qrCode = QrCode::size(250)->generate('ItSolutionStuff.com');
//
//
//    $imgEle = QRCodeHelper::getImg('QR Code generator for laravel!');


    // Parameters
    $params =  unserialize($leaflet->params);

    // QR Codes
    $qrCode = '';
    if (!empty($params['qr_code'])) {
      $qrCode = QrCode::margin(0)->size($leaflet->qr_code_size)->generate($params['qr_code']);
    }
    $template = str_replace('{qr_code}', $qrCode, $template);

    // Parameters
    foreach($params as $key=>$value) {
      $template = str_replace( '{'.$key.'}', $value, $template);
    }

    return view('templates.leaflet', [
      'title' => $leaflet->title,
      'template' => $template
    ]);
  }
}
