<?php namespace App\Http\Controllers\ApiV2;

use App\Exports\VoucherCodeExport;
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

    switch ($module) {
      case 'voucher':
        return $this->processVoucher(
          $command,
          $params
        );
        break;
    }
    return response('Unauthenticated.', 401);
  }

  private function processVoucher($command, $params) {
    switch ($command) {
      case 'export':
        $voucherId = $params['id'];
        $voucher = Voucher::find($voucherId);
        $description = empty($voucher->description) ? 'no_description' : $voucher->description;
        $filename = str_replace(' ', '_', $description).'.xlsx';
        return \Excel::download(new VoucherCodeExport($params['id']), $filename);
        break;
    }
    return  response('Unauthenticated.', 401);
  }
}
