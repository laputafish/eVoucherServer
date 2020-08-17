<?php namespace App\Observers;

use App\Models\Voucher;
use App\Helpers\MediaHelper;
use App\Helpers\VoucherTemplateHelper;

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
		
		// Remove voucher template
		$templateFullPath = VoucherTemplateHelper::getTemplateFullPath('vouchers', $voucher->template_path, $voucher->id, 'v');
		if (file_exists($templateFullPath)) {
			unlink($templateFullPath);
		}
		
		// Remove voucher email tempalte
		$emailTemplateFullPath = VoucherTemplateHelper::getTemplateFullPath('vouchers', $voucher->template_path, $voucher->id, 'v', 'email');
		if (file_exists($emailTemplateFullPath)) {
			unlink($emailTemplateFullPath);
		}
		
		if (!empty($voucher->sharing_image_id)) {
			MediaHelper::deleteMedia($voucher->sharing_image_id);
		}
		
		if (!empty($voucher->form_sharing_image_id)) {
			MediaHelper::deleteMedia($voucher->form_sharing_image_id);
		}
	}
}
