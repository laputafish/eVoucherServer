<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\Voucher;
use App\Models\Agent;
use App\Models\VoucherCode;
use App\Models\VoucherCodeConfig;

use App\Helpers\AccessKeyHelper;

use Illuminate\Http\Request ;

class VoucherController extends BaseModuleController
{
  protected $modelName = 'Voucher';

  protected $orderBy = 'vouchers.created_at';
  protected $orderDirection = 'desc';
//  protected $indexWith = 'agents';

  protected $filterFields = [
    'description',
    'agent.name'
  ];

  protected $defaultQrcode = [
    'id' => 0,
    'composition' => '',
    'code_group' => 'qrcode',
    'code_type' => 'QRCODE',
    'width' => 13,
    'height' => 13
  ];

  protected $defaultBarcode = [
    'id' => 0,
    'composition' => '',
    'code_group' => 'barcode',
    'code_type' => 'C128',
    'width' => 3,
    'height' => 67
  ];

  protected $updateRules = [
    'description' => 'string',
    'agent_id' => 'required|integer',
    'activation_date' => 'nullable|date',
    'expiry_date' => 'nullable|date',
    'template' => 'nullable|string',
    'qr_code_size' => 'nullable|integer',
    'qr_code_composition' => 'nullable|string',
    'code_fields' => 'nullable|string',
    'status' => 'nullable|string'
  ];

  protected $storeRules = [
    'description' => 'string',
    'agent_id' => 'required|integer',
    'activation_date' => 'nullable|date',
    'expiry_date' => 'nullable|date',
    'template' => 'nullable|string',
    'qr_code_size' => 'nullable|integer',
    'qr_code_composition' => 'nullable|string',
    'code_fields' => 'nullable|string',
    'status' => 'nullable|string'
  ];

  protected function onShowDataReady($request, $row) {
    // include code configs
    $row->codeConfigs;

//    $this->checkOrInit($row->codeConfigs);

    // remove qr_code_composition
    // this field is obsolate
    if (!empty(trim($row->qr_code_composition))) {
      $codeConfig = $row->codeConfigs->filter(function($config) {
        return $config->code_group === 'qrcode';
      });
      if ($codeConfig->count == 0) {

      }
      $row->qr_code_composition = '';
      $row->save();
    }
    unset($row->codeInfos);
    return $row;
  }

//  private function checkOrInit($codeConfigs) {
//
//  }
//
  protected function onIndexFilter($request, $query) {
    $query = parent::onIndexFilter($request, $query);
    if ($request->has('agentId')) {
      $query = $query->where('agent_id', $request->get('agentId'));
    }
    return $query;
  }

  protected function prepareIndexQuery($request, $query)
  {
    $query = parent::prepareIndexQuery($request, $query);

    if (!$request->has('select')) {
      $query = $query
        ->with(['codeInfos' => function ($q) {
          $q->orderBy('order');
        }])
        ->with('agent', 'codeInfos', 'emails');
    }

    return $query;
  }

  public function export($id) {
    $accessKey = AccessKeyHelper::create(
      $this->user,
      'voucher',
      'export',
      serialize(['id'=>$id])
    );
    return response()->json([
      'status'=>true,
      'result'=>[
        'key'=>$accessKey
      ]
    ]);
  }

  protected function onIndexSelect($request, $query) {
    if ($request->get('type','')=='selection') {
      $query = $query->select('id', 'description', 'created_at', 'code_count');
    } else {
      if ($request->has('select')) {
        $fields = explode(',', $request->get('select'));
        // Ignore fields from relation at this moment
        $directFields = array_filter($fields, function ($field) {
          return strpos($field, '.') === false;
        });

        foreach ($directFields as $i => $field) {
          if (strpos($field, '.') === false) {
            $directFields[$i] = 'vouchers.' . $field;
          }
        }
        $query = $query->select($directFields);
      } else {
        $query = parent::onIndexSelect($request, $query);
      }
    }
    return $query;
  }

  protected function onIndexDataReady($request, $rows)
  {
    $rows = parent::onIndexDataReady($request, $rows);
    if (!$request->has('select')) {
      foreach ($rows as $row) {
        $row->code_count = $row->codeInfos->count();
        $row->code_sent = $row->codeInfos()->whereStatus('completed')->count();
        $row->email_count = $row->emails->count();
      }
    }

    return $rows;
  }

  public function show(Request $request, $id) {
    if ($id == 0) {
      $id = $this->createNewVoucher();
    }
    return parent::show($request, $id);
  }

  public function createNewVoucher() {
    $newVoucher = [
      'description' => '',
      'agent_id' => Agent::first()->id,
      'activation_date' => null,
      'expiry_date' => null,
      'template' => '',
      'qr_code_composition' => '',
      'status' => 'pending',
      'code_fields' => ''
    ];
    $voucher = $this->model->create($newVoucher);

    $defaultQrcode = new VoucherCodeConfig($this->defaultQrcode);
    $voucher->codeConfigs()->save($defaultQrcode);

    $defaultBarcode = new VoucherCodeConfig($this->defaultBarcode);
    $voucher->codeConfigs()->save($defaultBarcode);
    return $voucher->id;
  }


  public function getBlankRecord2() {
    return [
      'id' => 0,
      'description' => '',
      'agent_id' => Agent::first()->id,
      'activation_date' => '',
      'expiry_date' => '',
      'template' => '',
      'qr_code_composition' => '',
      'status' => 'pending',
      'code_fields' => '',
      'code_configs' => [
        $this->defaultQrCode,
        $this->defaultBarcode
      ],
    ];
  }

  public function onUpdateComplete($request, $row) {
    $input = $request->all();
//    $this->saveVoucherCodes($row->id, $input['code_infos']);
    $this->saveCodeConfigs($row->id, $input['code_configs']);
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

  public function store(Request $request) {
    $input = $request->validate($this->storeRules);
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

//    $this->saveVoucherCodes($id,
//      array_key_exists('code_infos', $input) ?
//        $input['code_infos'] :
//        []
//    );

    $this->saveEmails($id,
      array_key_exists('emails', $input) ?
      $input['emails'] :
      []
    );

    $responseRow = $this->getRow($id);
    return response()->json([
      'status'=>true,
      'result'=>$responseRow
    ]);
  }

  private function saveCodeConfigs($id, $codeConfigs) {
    $voucher = $this->model->find($id);
    $inputIds = array_map(function($codeConfig) {
      return $codeConfig['id'];
    }, $codeConfigs);

    // Delete obsolate codes
    $voucher->codeConfigs()->whereNotIn('id', $inputIds)->delete();

    // Add/Update
    $existingIds = $voucher->codeConfigs()->pluck('id')->toArray();
    $newIds = array_diff($inputIds, $existingIds);
    $keepIds = array_intersect($inputIds, $existingIds);

    // add new record
    array_walk($codeConfigs, function($walkingCodeConfig) use($voucher, $newIds, $keepIds) {
      if ($walkingCodeConfig['code_group'] == 'qrcode') {
        $walkingCodeConfig['height'] = $walkingCodeConfig['width'];
      }
      if (in_array($walkingCodeConfig['id'], $newIds)) {
        $codeConfig = new VoucherCodeConfig([
          'composition' => $walkingCodeConfig['composition'],
          'code_group' => $walkingCodeConfig['code_group'],
          'code_type' => $walkingCodeConfig['code_type'],
          'width' => $walkingCodeConfig['width'],
          'height' => $walkingCodeConfig['height']
        ]);
        $voucher->codeConfigs()->save($codeConfig);
      } else if (in_array($walkingCodeConfig['id'], $keepIds)) {
        $codeConfig = $voucher->codeConfigs()->find($walkingCodeConfig['id']);
        if (isset($codeConfig)) {
          $codeConfig->update([
            'composition' => $walkingCodeConfig['composition'],
            'code_group' => $walkingCodeConfig['code_group'],
            'code_type' => $walkingCodeConfig['code_type'],
            'width' => $walkingCodeConfig['width'],
            'height' => $walkingCodeConfig['height']
          ]);
        }
      }
    });
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
          'remark' => $walkingCodeInfo['remark'],
          'status' => $walkingCodeInfo['status']
        ]);
        $voucher->codeInfos()->save($codeInfo);
      } else if (in_array($walkingCodeInfo['id'], $keepIds)) {
        $codeInfo = $voucher->codeInfos()->find($walkingCodeInfo['id']);
        if (isset($codeInfo)) {
          $codeInfo->update([
            'order' => $walkingCodeInfo['order'],
            'code' => $walkingCodeInfo['code'],
            'extra_fields' => $walkingCodeInfo['extra_fields'],
            'sent_on' => $walkingCodeInfo['sent_on'],
            'remark' => $walkingCodeInfo['remark'],
            'status' => $walkingCodeInfo['status']
          ]);
//          if (is_null($codeInfo->key) || empty($codeInfo->key)) {
//            $codeInfo->key = newKey();
//            $codeInfo->save();
//          }
        }
      }
    });
    $codeInfosNoKey = $voucher->codeInfos()->where('key', '')->orWhere('key', null)->get();
    foreach($codeInfosNoKey as $row) {
      $row->key = newKey();
      $row->save();
    }
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
      if (in_array($email['id'], $newIds)) {
        $new = new VoucherEmail([
          'voucher_code_id' => $email['voucher_code_in'],
          'email' => $email['email'],
          'sent_at' => $email['sent_at'],
          'status' => $email['status'],
          'remark' => $email['remark']
        ]);
        $voucher->emails()->save($new);
      } else if (in_array($email['id'], $keepIds)) {
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

  public function destroy($id) {
    $row = $this->model->find($id);
    $this->beforeDestroy($row);
    $row->delete();
    return response()->json([
      'status'=>true
    ]);
  }

  protected function beforeDestroy($row) {

  }

//  protected function onIndexJoin($query) {
//    $query = $query->leftJoin('agents', 'vouchers.agent_id', '=', 'agents.id');
//    return $query;
//  }

//  public function index(Request $request) {
//    $filterValue = 'shell';
//    $query = Voucher::where(function ($q) use($filterValue) {
//      $q->whereHas('agent', function($q) {
//        $q->where('name', 'like', '%ell%');
//      });
//      $q->orWhere('description', 'like', '%ell%');
//    });
//    $rows = $query->get();
//    return response()->json([
//      'status' => true,
//      'result' => $rows
//    ]);
//  }

  public function getCodes(Request $request, $id) {
    $voucher = $this->model->find($id);
    if (isset($voucher)) {
      if ($request->has('page')) {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $query = $voucher->codeInfos();
        $totalCount = $query->count();
        $lastPage = ceil($totalCount / $limit);
        if ($page > $lastPage) {
          $page = $lastPage;
        }
        // $data = $query->get();
        $offset = ($page - 1) * $limit;
        $data = $query->skip($offset)->take($limit)->get();
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator($data, $totalCount, $limit, $page);
        $result = $pagedData;
        return response()->json([
          'status' => true,
          'result' => $result
        ]);
      }
    }
    return response()->json([
      'status' => true,
      'result' => []
    ]);
  }
}