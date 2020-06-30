<?php namespace App\Http\Controllers\ApiV2;

use App\Exports\VoucherCodeExport;
use App\Exports\VoucherParticipantExport;

use App\Models\Voucher;
use App\Models\VoucherCode;
use App\Models\AccessKey;

use App\Helpers\TempUploadFileHelper;
use App\Helpers\EmailTemplateHelper;
use App\Helpers\TagGroupHelper;
use App\Helpers\SmtpServerHelper;
use App\Helpers\TemplateHelper;
use App\Helpers\EmailHelper;
use App\Helpers\VoucherTemplateHelper;

use Illuminate\Http\Request;

use App\Events\VoucherMailingStatusUpdatedEvent;

class EventTestController extends BaseModuleController {
	public function sendVoucherMailingStatusUpdatedEvent($id) {
		$voucher = Voucher::find($id);
		
		event(new VoucherMailingStatusUpdatedEvent($voucher));
		return 'event sent.';
	}
}