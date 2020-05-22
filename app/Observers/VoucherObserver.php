<?php namespace App\Observers;

use App\Models\Voucher;
use App\Helpers\MediaHelper;

class VoucherObserver {
	public function deleting(Voucher $voucher) {
		$voucher->codeInfos()->delete();
		$voucher->emails()->delete();
		$voucher->codeConfigs()->delete();
		$voucher->customForms()->delete();
		$voucher->participants()->delete();
		foreach($voucher->medias as $media) {
			$media->agent_id = $voucher->agent_id;
			$media->save();
		}
		$voucher->medias()->sync([]);
		$templateFullPath = $voucher->getTemplateFullPath('vouchers');
		if (file_exists($templateFullPath)) {
			unlink($templateFullPath);
		}
		
		if (!empty($voucher->sharing_image_id)) {
			MediaHelper::deleteMedia($voucher->sharing_image_id);
		}
		
		if (!empty($voucher->form_sharing_image_id)) {
			MediaHelper::deleteMedia($voucher->form_sharing_image_id);
		}
	}
	
}