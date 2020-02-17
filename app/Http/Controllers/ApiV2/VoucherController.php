<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\VoucherCode;

class VoucherController extends BaseModuleController
{
  protected $modelName = 'Voucher';

  protected $orderBy = 'created_at';
  protected $orderDirection = 'desc';

  protected $updateRules = [
    'description' => 'string',
    'agent_id' => 'required|integer',
    'activation_date' => 'nullable|date',
    'expiry_date' => 'nullable|date',
    'template' => 'string',
    'qr_code_composition' => 'nullable|string',
    'code_fields' => 'string'
  ];

  protected function prepareIndexQuery($query)
  {
    $query = parent::prepareIndexQuery($query);
    $query = $query
      ->with(['codeInfos' => function ($q) {
        $q->orderBy('order');
      }])
      ->with('agent', 'codeInfos', 'emails');
    return $query;
  }

  protected function onIndexDataReady($rows)
  {
    $rows = parent::onIndexDataReady($rows);

    foreach ($rows as $row) {
      $row->code_count = $row->codeInfos->count();
      $row->code_sent = $row->codeInfos()->whereStatus('completed')->count();
      $row->email_count = $row->emails->count();
    }

    return $rows;
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

  public function onUpdateComplete($request, $row) {
    $input = $request->all();
    $this->saveVoucherCodes($row->id, $input['code_infos']);
    $this->saveEmails($row->id, $input['emails']);
  }

//  public function update($id) {
//    $input = $this->getInput($this->updateRules);
//    $row = $this->model->find($id);
//    $row->update([
//      'description' => $input['description'],
//      'agent_id' => $input['agent_id'],
//      'activation_date' => $input['activation_date'],
//      'expiry_date' => $input['expiry_date'],
//      'template' => $input['template'],
//      'qr_code_composition' => $input['qr_code_composition'],
//      'code_fields' => $input['code_fields'],
//      'status' => $input['status'],
//    ]);
//    $this->saveVoucherCodes($id, $input['code_infos']);
//    $this->saveEmails($id, $input['emails']);
//
//    $row = $this->model->find($id);
//    return response()->json([
//      'status' => true,
//      'result' => $row
//    ]);
//  }

  public function store() {
    $newRow = $this->model->create([
      'description' => $input['description'],
      'agent_id' => $input['agent_id'],
      'activation_date' => $input['activation_date'],
      'expiry_date' => $input['expiry_date'],
      'template' => $input['template'],
      'code_fields' => $input['code_fields'],
      'status' => $input['status']
    ]);
    $id = $newRow->id;
    $this->saveVoucherCodes($id, $input['code_infos']);
    $this->saveEmails($id, $input['emails']);

    $responseRow = $this->getRow($id);
    return response()->json([
      'status'=>true,
      'result'=>$responseRow
    ]);
  }

  private function saveVoucherCodes($id, $codeInfos) {
    $voucher = $this->model->find($id);
    $inputIds = array_map(function($codeInfo) {
      return $codeInfo['id'];
    }, $codeInfos);

    // Delete obsolate codes
    $voucher->codeInfos()->whereNotIn('id', $inputIds)->delete();

    // Add/Update
    $existingIds = $voucher->codeInfos()->pluck('id')->toArray();
    $newIds = array_diff($inputIds, $existingIds);
    $keepIds = array_intersect($inputIds, $existingIds);

    // add new record
    array_walk($codeInfos, function($walkingCodeInfo) use($voucher, $newIds, $keepIds) {
      if (in_array($walkingCodeInfo['id'], $newIds)) {
        $codeInfo = new VoucherCode([
          'order' => $walkingCodeInfo['order'],
          'code' => $walkingCodeInfo['code'],
          'extra_fields' => $walkingCodeInfo['extra_fields'],
          'sent_on' => $walkingCodeInfo['sent_on'],
          'status' => $walkingCodeInfo['status']
        ]);
        $voucher->codeInfos()->save($codeInfo);
      } else if (in_array($walkingCodeInfo['id'], $keepIds)) {
        $codeInfos = $voucher->codeInfos()->whereIn('id', $keepIds)->get();
        foreach($codeInfos as $loopCodeInfo) {
          $loopCodeInfo->update([
            'order' => $walkingCodeInfo['order'],
            'code' => $walkingCodeInfo['code'],
            'extra_fields' => $walkingCodeInfo['extra_fields'],
            'sent_on' => $walkingCodeInfo['sent_on'],
            'status' => $walkingCodeInfo['status']
          ]);
          if (is_null($loopCodeInfo->key) || empty($loopCodeInfo->key)) {
            $loopCodeInfo->key = newKey();
            $loopCodeInfo->save();
          }
        }
      }
    });
  }

  private function saveEmails($id, $emails) {
    $voucher = $this->model->find($id);
    $inputIds = array_map(function($email) {
      return $email['id'];
    }, $emails);

    // Delete obsolate codes
    $voucher->emails()->whereNotIn('id', $inputIds)->delete();

    // Add/Update
    $existingIds = $voucher->emails()->pluck('id')->toArray();
    $newIds = array_diff($inputIds, $existingIds);
    $keepIds = array_intersect($inputIds, $existingIds);

    // add new record
    array_walk($emails, function($email) use($voucher, $newIds, $keepIds) {
      if (in_array($info['id'], $newIds)) {
        $new = new VoucherEmail([
          'voucher_code_id' => $email['voucher_code_in'],
          'email' => $email['email'],
          'sent_at' => $email['sent_at'],
          'status' => $email['status'],
          'remark' => $email['remark']
        ]);
        $voucher->emails()->save($new);
      } else if (in_array($info['id'], $keepIds)) {
        $voucher->emails()->whereIn('id', $keepIds)->update([
          'voucher_code_id' => $email['voucher_code_in'],
          'email' => $email['email'],
          'sent_at' => $email['sent_at'],
          'status' => $email['status'],
          'remark' => $email['remark']
        ]);
      }
    });
  }

  public function getRow($id) {
    $row = $this->model->with(['codeInfos', 'emails'])->find($id);
    return $row;
  }
}