<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Voucher;
use App\Models\VoucherCode;
use App\Models\VoucherParticipant;

use App\Helpers\TemplateHelper;

use Illuminate\Http\Request;

class CouponController extends BaseController {
	public function showForm($id, $timestamp=null) {
		if (is_null($timestamp)) {
			$key = $id;
			$voucherCode = VoucherCode::where('key', $key)->first();
			$voucher = $voucherCode->voucher;
			$processedTemplate = '';
		} else {
			$voucher = Voucher::find($id);
			$processedTemplate = '';
		}
		if (isset($voucher)) {
			$ogTitle = $voucher->form_sharing_title;
			$ogDescription = $voucher->form_sharing_description;
			$ogMediaId = $voucher->form_sharing_image_id;
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
	
	public function showCoupon($id, $timestamp=null) {
		$voucher = null;
		if (is_null($timestamp)) {
			$key = $id;
			$voucherCode = VoucherCode::where('key', $key)->first();
			if (isset($voucherCode)) {
				$voucher = $voucherCode->voucher;
				$processedTemplate = $this->processLeafletWithCode($voucherCode);
			} else {
				$participant = VoucherParticipant::where('participant_key', $key)->first();
				if (isset($participant)) {
					$voucher = $participant->voucher;
					$processedTemplate = $this->processLeafletNoCode($voucher);
				}
			}
		} else {
			$voucher = Voucher::find($id);
			$processedTemplate = '';
		}
		if (isset($voucher)) {
			$og = [
				'title' => $voucher->sharing_title,
				'description' => $voucher->sharing_description,
				'imageSrc' => url('media/image/' .$voucher->sharing_image_id),
				'url' => request()->fullUrl()
			];
			$script = $voucher->script;
		} else {
			$og = [
				'title' => 'Sample: Title',
				'description' => 'Sample: Description',
				'imageSrc' => url('media/image/0'),
				'url' => request()->fullUrl()
			];
			$script = '';
		}
		
		return view('templates.coupon', [
			'og' => $og,
			'template' => $processedTemplate,
			'script' => $script
		]);
	}
	
	public function getTemplateHtml(Request $request)
	{
		$key = $request->get('key');
		$voucherCode = VoucherCode::where('key', $key)->first();
		$processedTemplate = $this->processLeafletWithCode($voucherCode);
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
	
	private function processLeafletNoCode($voucher) {
		$voucher->codeConfigs;
		
		$params = TemplateHelper::createParams(
			$voucher->toArray()
		);
		
		return TemplateHelper::processTemplate(
			$voucher->template,
			$voucher->codeConfigs,
			$params
		);
	}
	
	private function processLeafletWithCode($voucherCode) {
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
