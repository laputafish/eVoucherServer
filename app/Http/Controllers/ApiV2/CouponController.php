<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Voucher;
use App\Models\VoucherCode;
use App\Models\VoucherParticipant;

use App\Helpers\TemplateHelper;
use App\Helpers\VoucherTemplateHelper;
use App\Helpers\TagGroupHelper;
use App\Helpers\MediaHelper;
use App\Helpers\VoucherHelper;

use Illuminate\Http\Request;

use App\Events\VoucherCodeViewsUpdatedEvent;
use App\Events\VoucherCodeRedeemedEvent;

class CouponController extends BaseController {
	public function showForm($id, $timestamp=null) {
		$voucher = null;
		$isFormal = is_null($timestamp);
		$processedTemplate = '';
		
		if ($isFormal) {
			$key = $id;
			$voucherCode = VoucherCode::where('key', $key)->first();
			if (isset($voucherCode)) {
				$voucher = $voucherCode->voucher;
				$processedTemplate = '';
			} else {
				$processedTemplate = '';
			}
		} else {
			$voucher = Voucher::find($id);
			$processedTemplate = '';
		}
		if (!is_null($voucher)) {
			$mediaSize = MediaHelper::getMediaDimension($voucher->form_sharing_image_id);
			$og = [
				'title' => $voucher->form_sharing_title,
				'description' => $voucher->form_sharing_description,
				'imageSrc' => url('media/image/'.$voucher->form_sharing_image_id),
				'url' => request()->fullUrl(),
				'image:width' => $mediaSize['width'],
				'image:height' => $mediaSize['height']
			];
			$script = $voucher->script;
//			$ogTitle = ;
//			$ogDescription = ;
//			$ogMediaId = $voucher->form_sharing_image_id;
//			$ogImageWidth = $mediaSize['width'];
//			$ogImageHeight = $mediaSize['height'];
		} else {
			$mediaSize = MediaHelper::getMediaDimension(0);
			$og = [
				'title' => 'Sample: Title',
				'description' => 'Sample: Description',
				'url' => request()->fullUrl(),
				'image:width' => $mediaSize['width'],
				'image:height' => $mediaSize['height']
			];
			$script = '';
//			$ogTitle = 'Sample: Title';
//			$ogDescription = 'Sample: Description';
//			$ogMediaId = 0;
//			$ogImageWidth = $mediaSize['width'];
//			$ogImageHeight = $mediaSize['height'];
		}
//		$ogUrl = request()->fullUrl();
		return view('templates.coupon', [
			'og' => $og,
			'template' => $processedTemplate,
			'script' => $script
//			'ogTitle' => $ogTitle,
//			'ogDescription' => $ogDescription,
//			'ogImageSrc' => url('media/image/' .$ogMediaId),
//			'ogUrl' => $ogUrl,
//			'ogImageWidth' => $ogImageWidth,
//			'ogImageHeight' => $ogImageHeight,
//			'template' => $processedTemplate
		]);
	}
	
	public function redeemByQrcode($id) {
		$key = $id;
		$qrcode = \Input::get('redemptionQrcode');
		if (empty($qrcode)) {
			\Session::flash('message', 'Invalid Redemption QR');
			\Session::flash('message_cht', '無效的兌換QR碼');
			return redirect()->back();
		}
		$voucherCode = VoucherCode::where('key', $key)->first();
		$voucher = $voucherCode->voucher;
		$location = VoucherHelper::getLocationByQrcode($voucher, $qrcode);
		
		if (is_null($location)) {
			\Session::flash('message', 'Invalid Redemption QR');
			\Session::flash('message_cht', '無效的兌換QR碼');
			return redirect()->back();
		}
		$voucherCode->redeemed_on = date('Y-m-d H:i:s');
		$voucherCode->redemption_location_id = $location->id;
		$voucherCode->save();
		event(new VoucherCodeRedeemedEvent($voucherCode));
		return redirect()->back();
	}
	
	public function redeem($id) {
		$key = $id;
		$password = \Input::get('redemptionCode');
		if (empty($password)) {
			\Session::flash('message', 'Redemption code required!');
			\Session::flash('message_cht', '需要兌換輸入碼!');
			return redirect()->back();
		}
		$voucherCode = VoucherCode::where('key', $key)->first();
		$voucher = $voucherCode->voucher;
		
		if ($voucher->redemption_code != $password) {
			\Session::flash('message', 'Incorrect redemption code!');
			\Session::flash('message_cht', '兌換碼錯誤!');
			return redirect()->back();
		}
		
		if (!$voucher->expired) {
			$voucherCode->redeemed_on = date('Y-m-d H:i:s');
			$voucherCode->save();
			event(new VoucherCodeRedeemedEvent($voucherCode));
		} else {
			\Session::flash('message', 'Redemption Fails: Expired!');
			\Session::flash('message_cht', '未能兌換: 已逾期!');
		}
		return redirect()->back();
	}
	
	public function showCoupon($id, $timestamp=null) {
		$voucher = null;
		$isFormal = is_null($timestamp);
    $appliedTemplate = '';
    $redeemedOn = null;
    
		if ($isFormal) {
			$key = $id;
			$voucherCode = VoucherCode::where('key', $key)->first();
			
			if (isset($voucherCode)) {
				$voucher = $voucherCode->voucher;
				$appliedTemplate = $this->processLeafletWithCode($voucherCode);
				$voucherCode->views++;
				$voucherCode->save();
				$redeemedOn = $voucherCode->redeemed_on;
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
		
		$redemptionQrcodes = [];
		$redemptionPasswords = [];
		$expiryDate = '';
		$expired = true;
		
		$today = \Carbon\Carbon::today()->format('Y-m-d');
		
		if (isset($voucher)) {
			$mediaSize = MediaHelper::getMediaDimension($voucher->sharing_image_id);
			$og = [
				'title' => $voucher->sharing_title,
				'description' => $voucher->sharing_description,
				'imageSrc' => str_replace('https:', 'http:', url('media/image/' .$voucher->sharing_image_id)),
				'image:type' => 'image/'.MediaHelper::getMediaExt($voucher->sharing_image_id),
				'url' => request()->fullUrl(),
				'image:width' => $mediaSize['width'],
				'image:height' => $mediaSize['height']
			];
			$script = $voucher->script;
			$redemptionMethod = $voucher->redemption_method;
			switch ($redemptionMethod) {
				case 'qrcode':
				case 'qrcode_password':
					$redemptionQrcodes = VoucherHelper::getRedemptionCodes($voucher);
					$redemptionPasswords = VoucherHelper::getRedemptionPasswords($voucher);
					break;
			}
//			if (!is_null($voucher->expiry_date) && !empty($voucher->expiry_date)) {
//				$expiryDate = $voucher->expiry_date;
//				$expired = $today > $expiryDate;
//			} else {
//				$expired = false;
//			}
			$expired = $voucher->expired;
			$expiryDate = $voucher->expiry_date;
		} else {
			$mediaSize = MediaHelper::getMediaDimension(0);
			$og = [
				'title' => 'Sample: Title',
				'description' => 'Sample: Description',
				'imageSrc' => url('media/image/0'),
				'url' => request()->fullUrl(),
				'image:width' => $mediaSize['width'],
				'image:height' => $mediaSize['height']
			];
			$script = '';
			$redemptionMethod = 'none';
		}
		
		return view('templates.coupon', [
			'og' => $og,
			'key' => $id,
			'redemptionMethod' => $redemptionMethod,
			'redemptionQrcodes' => empty($redemptionQrcodes) ? '' : implode('||', $redemptionQrcodes),
			'redemptionPasswords' => empty($redemptionPasswords) ? '' : implode('||', $redemptionPasswords),
			'redeemedOn' => $redeemedOn,
			'template' => $appliedTemplate,
			'script' => $script,
			'expired' => $expired,
			'expiryDate' => $expiryDate
		]);
	}
//	public function showCoupon2($id, $timestamp=null) {
//
//		$voucher = null;
//		$isFormal = is_null($timestamp);
//    $appliedTemplate = '';
//    $redeemedOn = null;
//
//		if ($isFormal) {
//			$key = $id;
//			$voucherCode = VoucherCode::where('key', $key)->first();
//			if (isset($voucherCode)) {
//				$voucher = $voucherCode->voucher;
//				$appliedTemplate = $this->processLeafletWithCode($voucherCode);
//				$voucherCode->views++;
//				$voucherCode->save();
//				$redeemedOn = $voucherCode->redeemed_on;
//				event(new VoucherCodeViewsUpdatedEvent($voucherCode));
//			} else {
//				$participant = VoucherParticipant::where('participant_key', $key)->first();
//				if (isset($participant)) {
//					$voucher = $participant->voucher;
//					$appliedTemplate = $this->processLeafletNoCode($voucher);
//				}
//			}
//		} else {
//			$voucher = Voucher::find($id);
//			$appliedTemplate = '';
//		}
//
//		$redemptionQrcodes = [];
//		$redemptionPasswords = [];
//
//		if (isset($voucher)) {
//			$mediaSize = MediaHelper::getMediaDimension($voucher->sharing_image_id);
//			$og = [
//				'title' => $voucher->sharing_title,
//				'description' => $voucher->sharing_description,
//				'imageSrc' => url('media/image/' .$voucher->sharing_image_id),
//				'url' => request()->fullUrl(),
//				'image:width' => $mediaSize['width'],
//				'image:height' => $mediaSize['height']
//			];
//			$script = $voucher->script;
//			$redemptionMethod = $voucher->redemption_method;
//			switch ($redemptionMethod) {
//				case 'qrcode':
//				case 'qrcode_password':
//					$redemptionQrcodes = VoucherHelper::getRedemptionCodes($voucher);
//					$redemptionPasswords = VoucherHelper::getRedemptionPasswords($voucher);
//					break;
//			}
//		} else {
//			$mediaSize = MediaHelper::getMediaDimension(0);
//			$og = [
//				'title' => 'Sample: Title',
//				'description' => 'Sample: Description',
//				'imageSrc' => url('media/image/0'),
//				'url' => request()->fullUrl(),
//				'image:width' => $mediaSize['width'],
//				'image:height' => $mediaSize['height']
//			];
//			$script = '';
//			$redemptionMethod = 'none';
//		}
//
//		return view('templates.coupon', [
//			'og' => $og,
//			'key' => $id,
//			'redemptionMethod' => $redemptionMethod,
//			'redemptionQrcodes' => empty($redemptionQrcodes) ? '' : implode('||', $redemptionQrcodes),
//			'redemptionPasswords' => empty($redemptionPasswords) ? '' : implode('||', $redemptionPasswords),
//			'redeemedOn' => $redeemedOn,
//			'template' => $appliedTemplate,
//			'script' => $script
//		]);
//	}
	
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
    $template = VoucherTemplateHelper::readVoucherTemplate($voucher);

    
    $allTagValues = TagGroupHelper::getTagValues(null, $voucherCode);
		$appliedTemplate = TemplateHelper::applyTags($template, $allTagValues, $voucher->codeConfigs);
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
