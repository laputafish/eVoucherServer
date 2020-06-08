<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Voucher;
use App\Models\VoucherCode;
use App\Models\VoucherParticipant;

use App\Helpers\TemplateHelper;
use App\Helpers\VoucherTemplateHelper;
use App\Helpers\TagGroupHelper;

use Illuminate\Http\Request;

use App\Events\VoucherCodeViewsUpdatedEvent;

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
		$isFormal = is_null($timestamp);

		if ($isFormal) {
			$key = $id;
			$voucherCode = VoucherCode::where('key', $key)->first();
			if (isset($voucherCode)) {
				$voucher = $voucherCode->voucher;
				$appliedTemplate = $this->processLeafletWithCode($voucherCode);
				$voucherCode->views++;
				$voucherCode->save();
				event(new VoucherCodeViewsUpdatedEvent($voucherCode));
			} else {
				$participant = VoucherParticipant::where('participant_key', $key)->first();
				if (isset($participant)) {
					$voucher = $participant->voucher;
					$appliedTemplate = $this->processLeafletNoCode($voucher);
				}
			}
		} else {
			$voucher = Voucher::find($id);
			$appliedTemplate = '';
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
			'template' => $appliedTemplate,
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
		
		$voucherTemplate = VoucherTemplateHelper::readVoucherTemplate($voucher);
		return TemplateHelper::processTemplate(
			$voucherTemplate,
			$voucher->codeConfigs,
			$params
		);
		
//		return TemplateHelper::processTemplate(
//			$voucher->template,
//			$voucher->codeConfigs,
//			$params
//		);
	}
	
	private function processLeafletWithCode($voucherCode) {
		$voucher = $voucherCode->voucher;
		$voucher->codeConfigs;
//echo '111'.PHP_EOL;
    $template = VoucherTemplateHelper::readVoucherTemplate($voucher);
//echo '222'.PHP_EOL;
    $allTagValues = TagGroupHelper::getTagValues(null, $voucherCode);
//echo '333'.PHP_EOL;
		$appliedTemplate = TemplateHelper::applyTags($template, $allTagValues, $voucher->codeConfigs);
//return 'ok';
//		$params = TemplateHelper::createParams(
//			$voucher->toArray(),
//			$voucherCode
//		);
		return $appliedTemplate;
//		return TemplateHelper::processTemplate(
//			$template,
//			$voucher->codeConfigs,
//			$params
//		);
//		return TemplateHelper::processTemplate(
//			$voucher->template,
//			$voucher->codeConfigs,
//			$params
//		);
	}
	
	private function processLeafletWithCode2($voucherCode) {
		$voucher = $voucherCode->voucher;
		$voucher->codeConfigs;
    $template = VoucherTemplateHelper::readVoucherTemplate($voucher);

		$params = TemplateHelper::createParams(
			$voucher->toArray(),
			$voucherCode
		);

		return TemplateHelper::processTemplate(
			$template,
			$voucher->codeConfigs,
			$params
		);
//		return TemplateHelper::processTemplate(
//			$voucher->template,
//			$voucher->codeConfigs,
//			$params
//		);
	}

}
