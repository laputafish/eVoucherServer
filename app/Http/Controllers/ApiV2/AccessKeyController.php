<?php namespace App\Http\Controllers\ApiV2;

use App\Exports\VoucherCodeExport;
use App\Exports\VoucherParticipantExport;

use App\Models\Voucher;
use App\Models\AccessKey;

class AccessKeyController extends BaseModuleController
{
  protected $modelName = 'AccessKey';

  public function downloadFile($key) {
    $accessKey = AccessKey::where('key', $key)->first();
    $module = $accessKey->module;
    $command = $accessKey->command;
    $params = unserialize($accessKey->params);

    AccessKey::where('key', $key)->delete();

    switch ($command) {
	    case 'export':
	    	switch ($module) {
			    case 'voucher_codes':
				    return $this->exportVoucherCodes($params);
			    case 'voucher_participants':
				    return $this->exportVoucherParticipants($params);
		    }
		    break;
    }
    return response('Unauthenticated.', 401);
  }

  private function exportVoucherParticipants($params) {
	  $voucherId = $params['id'];
	  $voucher = Voucher::find($voucherId);
	  $description = empty($voucher->description) ? 'voucher_participants_without_description' :
		  'participants_of_'.$voucher->description;
	  $filename = str_replace(' ', '_', $description).'.xlsx';
	  return \Excel::download(new VoucherParticipantExport($params['id']), $filename);
  }
  
  private function exportVoucherCodes($params) {
    $voucherId = $params['id'];
    $voucher = Voucher::find($voucherId);
    $description = empty($voucher->description) ? 'no_description' : $voucher->description;
    $filename = str_replace(' ', '_', $description).'.xlsx';
    return \Excel::download(new VoucherCodeExport($params['id']), $filename);
  }
}
