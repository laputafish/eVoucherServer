<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;

class AgentController extends BaseModuleController
{
  protected $modelName = 'Agent';
  protected $orderBy = 'name';

  protected $filterFields = [
    'name',
    'alias',
    'tel_no',
    'fax_no',
    'web_url',
    'email'
  ];

  protected $updateRules = [
    'name' => 'string',
    'alias' => 'nullable|string',
    'contact' => 'nullable|string',
    'tel_no' => 'nullable|string',
    'fax_no' => 'nullable|string',
    'web_url' => 'nullable|string',
    'email' => 'nullable|string',
    'remark' => 'nullable|string'
  ];

  protected $storeRules = [
    'name' => 'string',
    'alias' => 'nullable|string',
    'contact' => 'nullable|string',
    'tel_no' => 'nullable|string',
    'fax_no' => 'nullable|string',
    'web_url' => 'nullable|string',
    'email' => 'nullable|string',
    'remark' => 'nullable|string'
  ];

  public function store() {
    $input = $this->getInput($this->storeRules);
    $validator = \Validator::make($input, $this->storeRules);
    if ($validator->fails()) {
      $errors = $validator->messages();
      return response()->json([
        'status' => false,
        'result' => $errors
      ]);
    } else {
      $newRow = $this->model->create($input);

    }
    return response()->json([
      'status' => true,
      'result' => $newRow
    ]);
  }

  public function getBlankRecord() {
    return [
      'id' => 0,
      'name' => '',
      'alias' => '',
      'contact' => '',
      'teL_no' => '',
      'fax_no' => '',
      'web_url' => '',
      'email' => '',
      'remark' => ''
    ];
  }

  protected function onIndexDataReady( $request, $rows) {
    foreach($rows as $row) {
      $row->voucher_count = $row->vouchers()->count();
    }
    return $rows;
  }

  public function destroy($id) {
    $status = true;
    $result = [];

    $agent = $this->model->find($id);
    $voucherCount = $agent->vouchers()->count();

    if ($voucherCount > 0) {
      $status = false;
      $vouchers = $agent->vouchers()->select('description', 'created_at')->get();
      $result =  [
        'messageTag' => 'related_vouchers_exists',
        'message' => 'Related vouchers exists!',
        'data' => $vouchers
      ];
    } else {
      $agent->delete();
    }

    return response()->json([
      'status' => $status,
      'result' => $result
    ]);
  }
}