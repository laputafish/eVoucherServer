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
		return response()->json([
			'status' => true,
			'result' => []
		]);
	}
	
	public function sendEmail($id) {
		LogHelper::$enabled = false;
		$participant = $this->model->find($id);
		$status = false;
		if (isset($participant)) {
			$status = ParticipantHelper::sendEmail($participant);
		}
		
		$message = $status ? 'Email has been successfully sent.' : 'Error: Fails to send.';
		return response()->json([
			'status' => $status,
			'result' => [
				'message' => $message
			]
		]);
	}

	
}