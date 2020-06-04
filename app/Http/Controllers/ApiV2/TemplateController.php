<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Agent;
use App\Models\VoucherCode;
use App\Models\TempLeaflet;

use App\Helpers\TemplateHelper;
use App\Helpers\QRCodeHelper;
use App\Helpers\VoucherTemplateHelper;
use App\Helpers\TempUploadFileHelper;

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
    $newTempLeaflet = $this->model->create([
      'user_id' => 0,
      'title' => $record['description'],
      'code_configs' => serialize($record['code_configs']),
      'key' => $key,
//      'template_path' => $templatePath,
//      'template' => $record['template'],
      'params' => serialize($params)
    ]);
	  $newTempLeaflet->template_path = VoucherTemplateHelper::writeVoucherTemplate('tempLeaflets', $newTempLeaflet->id, $record['template']);
	  $newTempLeaflet->save();
	
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
    $tempLeaflet = TempLeaflet::where('key', $key)->first();
    $status = true;
    if (isset($tempLeaflet)) {
    	$voucherTemplate = VoucherTemplateHelper::readTempVoucherTemplate($tempLeaflet);
      $result = TemplateHelper::processTemplate(
        $voucherTemplate,
        unserialize($tempLeaflet->code_configs),
        unserialize($tempLeaflet->params)
      );
      // TempLeaflet::where('key', $key)->delete();
    } else {
      $status = false;
      $result = [
        'message' => 'Temporary Key Expired.',
        'messageTag' => 'temporary_key_expired'
      ];
    }
    return response()->json([
      'status' => $status,
      'result' => $result
    ]);
  }
  private function processLeaflet($key) {
    $voucherCode = VoucherCode::where('key', $key)->first();
    $voucher = $voucherCode->voucher;
    $voucher->codeConfigs;

    $params = TemplateHelper::createParams(
      $voucher->toArray(),
      $voucherCode
    );

    $voucherTemplate = VoucherTemplateHelper::readVoucherTemplate($voucher);
    $result = TemplateHelper::processTemplate(
      $voucherTemplate,
      $voucher->codeConfigs,
      $params
    );
    return response()->json([
      'status' => true,
      'result' => $result
    ]);
  }

  public function view($key) {
    $leaflet = $this->model->where('key', $key)->first();
    $params =  unserialize($leaflet->params);
    $processed = TemplateHelper::processTemplate(
      $leaflet->template,
      $leaflet->codeConfigs,
      $params);

    return view('templates.leaflet', [
      'title' => $leaflet->title,
      'template' => $processed
    ]);
  }
	
	public function createPreview(Request $request)
	{
		$content = $request->get('content');
		
		$newKey = newKey();
		$filePath = storage_path('app/temp/'.$newKey.'.html');
		if (file_exists($filePath)) {
			unlink($filePath);
		}
		$f = fopen($filePath, 'wb');
		fwrite($f, $content);
		fclose($f);
		
		$key = TempUploadFileHelper::newTempFile($this->user->id, 0, $filePath, 'common');
		return response()->json([
			'status' => true,
			'result' => [
				'key' => $key
			]
		]);
	}
	
	public function showPreview(Request $request, $key) {
		$keyInDb = $key;
		
		if ($keyInDb[0] === '_') {
			$keyInDb = substr($key, 1);
		}
		$content = TempUploadFileHelper::getUploadFileContentByKey($keyInDb);
		
		return view('email.preview_template')->with([
			'key' => $key,
			'content'=>$content
		]);
	}
}
