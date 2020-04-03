<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Voucher;
use App\Models\VoucherCode;

use App\Helpers\TemplateHelper;

use Illuminate\Http\Request;

class CouponController extends BaseController {
	public function showCoupon($id, $timestamp=null) {
		if (is_null($timestamp)) {
			$key = $id;
			$voucherCode = VoucherCode::where('key', $key)->first();
			$voucher = $voucherCode->voucher;
			$processedTemplate = $this->processLeaflet($key);
		} else {
			$voucher = Voucher::find($id);
			$processedTemplate = '';
		}
		if (isset($voucher)) {
			$ogTitle = $voucher->sharing_title;
			$ogDescription = $voucher->sharing_description;
			$ogMediaId = $voucher->sharing_image_id;
		} else {
			$ogTitle = 'Sample: Title';
			$ogDescription = 'Sample: Description';
			$ogMediaId = 0;
		}
		$ogUrl = request()->fullUrl();
		return view('templates.coupon', [
			'ogTitle' => $ogTitle,
			'ogDescription' => $ogDescription,
			'ogImageSrc' => url('media/image/' .$ogMediaId),
			'ogUrl' => $ogUrl,
			'template' => $processedTemplate
		]);
	}
	
	public function getTemplateHtml(Request $request)
	{
		$key = $request->get('key');
		$processedTemplate = $this->processLeaflet($key);
		return $processedTemplate;
	}
	
//	private function processTempLeaflet($key) {
//		$leaflet = TempLeaflet::where('key', $key)->first();
//		$status = true;
//		if (isset($leaflet)) {
//			$result = TemplateHelper::processTemplate(
//				$leaflet->template,
//				unserialize($leaflet->code_configs),
//				unserialize($leaflet->params)
//			);
//			// TempLeaflet::where('key', $key)->delete();
//		} else {
//			$status = false;
//			$result = [
//				'message' => 'Temporary Key Expired.',
//				'messageTag' => 'temporary_key_expired'
//			];
//		}
//		return response()->json([
//			'status' => $status,
//			'result' => $result
//		]);
//	}
	private function processLeafletById($id) {
		$voucher = Voucher::find($id);
		
	}
	private function processLeaflet($key) {
		$voucherCode = VoucherCode::where('key', $key)->first();
		$voucher = $voucherCode->voucher;
		$voucher->codeConfigs;
		
		$params = TemplateHelper::createParams(
			$voucher->toArray(),
			$voucherCode
		);
		
		return TemplateHelper::processTemplate(
			$voucher->template,
			$voucher->codeConfigs,
			$params
		);
	}
	
}
