<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;

class VoucherController extends BaseController
{
  protected $modelName = 'Voucher';

  public function index() {
    return response()->json([
      'status'=>true,
      'result'=>[
        'data'=>[],
        'pageable'=>[],
        'total'=>0
      ]
    ]);
  }

  public function getBlankRecord() {
    return [
      'id' => 0,
      'description' => '',
      'agent_id' => 0,
      'activation_date' => '',
      'expiry_date' => '',
      'template' => '',
      'status' => 'pending'
    ];
  }

  public function show($id) {
    if ($id == 0) {
      $record = $this->getBlankRecord();
    } else {
      $record = $this->model->find($id)->toArray();
    }
    return response()->json([
      'status'=>true,
      'result'=>$record
    ]);
  }
}