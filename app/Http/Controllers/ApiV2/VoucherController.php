<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;

class VoucherController extends BaseModuleController
{
  protected $modelName = 'Voucher';

  public function index() {
    $data = $this->model->with('agent', 'codeInfos', 'emails')->get();
    foreach($data as $row) {
      $row->code_count = $row->codeInfos->count();
      $row->code_sent = $row->codeInfos()->whereStatus('completed')->count();
      $row->email_count = $row->emails->count();
    }

    return response()->json([
      'status'=>true,
      'result'=>[
        'data'=>$data,
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
      'qr_code_composition' => '',
      'status' => 'pending',
      'code_fields' => '',
      'codeInfos' => []
    ];
  }

  public function show($id) {
    if ($id == 0) {
      $record = $this->getBlankRecord();
    } else {
      $record = $this->getRow($id)->toArray();
    }
    return response()->json([
      'status'=>true,
      'result'=>[
        'data'=>$record
      ]
    ]);
  }

  public function update($id) {
    $input = \Input::all();
    $row = $this->model->find($id);
    $row->update([
      'description' => $input['description'],
      'agent_id' => $input['agent_id'],
      'activation_date' => $input['activation_date'],
      'expiry_date' => $input['expiry_date'],
      'template' => $input['template'],
      'qr_code_composition' => $input['qr_code_composition'],
      'code_fields' => $input['code_fields'],
      'status' => $input['status']
    ]);
    $this->saveVoucherCodes($id, $input['code_infos']);
    $this->saveEmails($id, $input['emails']);

    $row = $this->model->find($id);
    return response()->json([
      'status' => true,
      'result' => $row
    ]);
  }

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
    array_walk($codeInfos, function($info) use($voucher, $newIds, $keepIds) {
      if (in_array($info['id'], $newIds)) {
        $codeInfo = new VoucherCode([
          'order' => $info['order'],
          'code' => $info['code'],
          'extra_fields' => $info['extra_fields'],
          'sent_on' => $info['sent_on'],
          'status' => $info['status']
        ]);
        $voucher->codeInfos()->save($codeInfo);
      } else if (in_array($info['id'], $keepIds)) {
        $voucher->codeInfos()->whereIn('id', $keepIds)->update([
          'order' => $info['order'],
          'code' => $info['code'],
          'extra_fields' => $info['extra_fields'],
          'sent_on' => $info['sent_on'],
          'status' => $info['status']
        ]);
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