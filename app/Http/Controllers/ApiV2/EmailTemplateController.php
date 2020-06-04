<?php namespace App\Http\Controllers\ApiV2;

use App\Exports\VoucherCodeExport;
use App\Exports\VoucherParticipantExport;

use App\Models\Voucher;
use App\Models\VoucherCode;
use App\Models\AccessKey;

use App\Helpers\TempUploadFileHelper;
use App\Helpers\EmailTemplateHelper;

use Illuminate\Http\Request;

class EmailTemplateController extends BaseModuleController
{
	public function test(Request $request) {
		$template = $request->get('template');
		$email = $request->get('email');
		$smtpServer = $request->get('smtpServer');
		$tagGroups = $request->get('tagGroups');
		
		$tagValues = TagGroupHelper::getTagValues($tagGroups);
		
		return response()->json([
			'status' => true,
			'result' => [
			
			]
		]);
	}

}