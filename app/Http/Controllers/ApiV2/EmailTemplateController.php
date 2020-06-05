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

use Illuminate\Http\Request;

class EmailTemplateController extends BaseModuleController
{
	public function test(Request $request) {
		$template = $request->get('template');
		$email = $request->get('email');
		$smtpServer = $request->get('smtpServer');
		$tagGroups = $request->get('tagGroups');
		$subject = $request->get('subject');
		$cc = $request->get('cc');
		$bcc = $request->get('bcc');
		
		$tagValues = TagGroupHelper::getTagValues($tagGroups);
		$appliedTemplate = TemplateHelper::applyTags($template, $tagValues);

		$smtpConfig = SmtpServerHelper::getConfig($smtpServer);

		$errorMsg = EmailTemplateHelper::sendHtml(
			$smtpConfig,
			[
				'subject' => $subject,
				'toEmail' => $email,
				'cc' => $cc,
				'bcc' => $bcc,
				'body' => $appliedTemplate,
				'fromEmail' => $smtpConfig['from']['address'],
				'fromName' => $smtpConfig['from']['name']
			]
		);
		
		$status = true;
		$message = '';
		if ($errorMsg) {
			$status = false;
			$message = $errorMsg;
			
		}

		return response()->json([
			'status' => $status,
			'result' => [
				'message' => $message
			]
		]);
	}

}