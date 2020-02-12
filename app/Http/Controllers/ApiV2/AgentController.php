<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;

class AgentController extends BaseController
{
  protected $modelName = 'Agent';

  public function index()
  {
    $data = $this->model->orderby('name')->get();
    foreach($data as $row) {
      $row->voucher_count = $row->vouchers()->count();
    }
    return response()->json([
      'status'=>true,
      'result' => [
        'data' => $data,
        'pageable' => [],
        'total' => 0
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
      'qr_code_composition' => '',
      'status' => 'pending',
      'code_fields' => '',
      'codeInfos' => []
    ];
  }

}