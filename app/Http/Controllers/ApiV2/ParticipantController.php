<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\Media;
use App\Models\Voucher;
use App\Models\VoucherCode;
use App\Models\TempUploadFile;
use App\Models\VoucherParticipant;

use App\Helpers\UploadFileHelper;
use App\Helpers\VoucherHelper;
use App\Helpers\TempUploadFileHelper;
use App\Helpers\LogHelper;
use App\Helpers\ParticipantHelper;

use App\Imports\AgentCodeImport;

use Illuminate\Http\Request;

class ParticipantController extends BaseController
{
	protected $modelName = 'VoucherParticipant';
	
	public function changeStatus($id, $status) {
		$row = $this->model->find($id);
		if (isset($row)) {
			$row->status = $status;
			$row->sent_at = null;
			$row->save();
		}
		
		$voucher = $row->voucher;
		$statusSummary = VoucherHelper::getStatusSummary($voucher->id);
		return response()->json([
			'status' => true,
			'result' => [
				'status_summary' => $statusSummary['result']['summary']
			]
		]);
	}
	
	public function sendEmail($id) {
		LogHelper::$enabled = false;
		$participant = $this->model->find($id);
		
		$res = false;
		$status = '';
		$sentAt = '';
		$message = '';
		
		if (isset($participant)) {
			$voucher = $participant->voucher;
			if ($voucher->has_one_code || isset($participant->code)) {
				$res = ParticipantHelper::sendEmail($participant, $voucher);
				$message = $res ? 'Sending email ...' : 'Error: Fails to send.';
			} else {
				$res = false;
				$message = 'No code assigned!';
			}
			
			if (!$res) {
				$status = $participant->status = 'fails';
				$sentAt = $participant->sent_at = date('Y-m-d H:i:s');
				$message = $participant->error_message = $message;
				$participant->save();
			}
		} else {
			$res = false;
			$message = 'Invalid participant!';
		}
		
		return response()->json([
			'status' => $res,
			'result' => [
				'sent_at' => $sentAt,
				'status' => $status,
				'message' => $message
			]
		]);
	}

	
}