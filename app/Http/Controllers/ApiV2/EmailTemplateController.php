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

class EmailTemplateController extends BaseModuleController
{
	public function test(Request $request) {
		$voucher = Voucher::find(2068);
		$voucherCode = $voucher->codes()->first();
		
		$this->sendVoucherEmail($voucher, $voucherCode);
		return 'ok';
	}
	
	public function sendVoucherEmail($voucher, $voucherCode)
	{
		$emailTemplate = VoucherTemplateHelper::readVoucherTemplate($voucher, 'email');
		$participant = $voucherCode->participant;
		$voucher->codeConfigs;
		
		$tagValues = TagGroupHelper::getTagValues(null, $voucherCode);
		echo 'tagValues';
		print_r($tagValues);
		return ; //dd('ok');
		
		$voucherParams = TemplateHelper::createParams(
			$voucher->toArray(),
			$voucherCode
		);
		
		$participantParams = static::getParticipantParams($voucher, $participant);
		
		$finalParams = array_merge($voucherParams, $participantParams);
		
		if (strpos($emailTemplate, '{voucher}')!==false) {
			$voucherTemplateContent = '';
			$voucherContent = TemplateHelper::processTemplate(
				$voucherTemplateContent,
				$voucher->codeConfigs,
				$finalParams
			);
			$finalParams = array_merge($finalParams, [
				'voucher' => $voucherContent
			]);
		}
		$finalEmailContent = TemplateHelper::processTemplate(
			$emailTemplate,
			$voucher->codeConfigs,
			$finalParams
		);
	}
	
	// end of testing
	
	public function test2(Request $request) {
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