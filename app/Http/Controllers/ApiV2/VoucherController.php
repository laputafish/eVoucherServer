<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\Voucher;
use App\Models\Agent;
use App\Models\VoucherCode;
use App\Models\VoucherCodeConfig;
use App\Models\VoucherCustomForm;

use App\Helpers\AccessKeyHelper;
use App\Helpers\MediaHelper;
use App\Helpers\QuestionnaireHelper;

use Illuminate\Http\Request;

class VoucherController extends BaseModuleController
{
	protected $modelName = 'Voucher';
	
	protected $orderBy = 'vouchers.created_at';
	protected $orderDirection = 'desc';
	protected $indexWith = 'agent';
	
	protected $filterFields = [
		'description',
		'notes',
		'agent.name'
	];
	
	protected $defaultQrcode = [
		'id' => 0,
		'composition' => '',
		'code_group' => 'qrcode',
		'code_type' => 'QRCODE',
		'width' => 7,
		'height' => 7
	];
	
	protected $defaultBarcode = [
		'id' => 0,
		'composition' => '',
		'code_group' => 'barcode',
		'code_type' => 'C128',
		'width' => 3,
		'height' => 67
	];
	
	protected $storeRules = [
		'description' => 'nullable|string',
		'notes' => 'nullable|string',
		'agent_id' => 'required|integer',
		'activation_date' => 'nullable|date',
		'expiry_date' => 'nullable|date',
		'voucher_type' => 'in:voucher,form',
		
		'template' => 'nullable|string',
		'has_template' => 'boolean',
		
		'has_custom_link' => 'boolean',
		// 'custom_link_key' => 'string',
		
		'entrance_page_type' => 'in:questionnaire,custom,none',
		'entrance_page_id' => 'integer',
		'entrance_page_type_after_quota' => 'in:questionnaire,custom,none',
		'entrance_page_id_after_quota' => 'integer',
		
		'questionnaire' => 'nullable|string',
		'questionnaire_fields' => 'nullable|string',
		// 'questionnaire_configs' => [],
    // 'thankyou_configs' => [],
    // 'sorry_configs' => [],
		
		'goal_type' => 'in:fixed,codes,none',
		'goal_count' => 'integer',

    'action_type_before_goal' => 'in:form_voucher,form_custom,custom',
    'custom_form_key_before_goal' => 'nullable|string',

		'action_type_after_goal' => 'in:form_custom,custom,none',
		'custom_form_key_after_goal' => 'nullable|string',
		
		'code_fields' => 'nullable|string',
		'code_count' => 'integer',
		'participant_count' => 'integer',
		
		'qr_code_size' => 'nullable|integer',
		'qr_code_composition' => 'nullable|string',
		
		'sharing_title' => 'nullable|string',
		'sharing_description' => 'nullable|string',
		'sharing_image_id' => 'integer',

		'form_sharing_title' => 'nullable|string',
		'form_sharing_description' => 'nullable|string',
		'form_sharing_image_id' => 'integer',

		'status' => 'in:preparing,pending,ready_to_send,completed'
	];
	
	protected $updateRules = [
		'description' => 'nullable|string',
		'notes' => 'nullable|string',
		'agent_id' => 'required|integer',
		'activation_date' => 'nullable|date',
		'expiry_date' => 'nullable|date',
		'voucher_type' => 'in:voucher,form',
		
		'template' => 'nullable|string',
		'has_template' => 'boolean',
		
		'has_custom_link' => 'boolean',
		// 'custom_link_key' => 'string',
		
		'entrance_page_type' => 'in:questionnaire,custom,none',
		'entrance_page_id' => 'integer',
		'entrance_page_type_after_quota' => 'in:questionnaire,custom,none',
		'entrance_page_id_after_quota' => 'integer',
		
		'questionnaire' => 'nullable|string',
		'questionnaire_fields' => 'nullable|string',
		// 'questionnaire_configs' => [],
    // 'thankyou_configs' => [],
    // 'sorry_configs' => [],

		'goal_type' => 'in:fixed,codes,none',
		'goal_count' => 'integer',
		
		'action_type_after_goal' => 'in:form,custom,none',
		'action_page_after_goal' => 'nullable|string',
		
		'code_fields' => 'nullable|string',
		'code_count' => 'integer',
		
		'participant_count' => 'integer',
		
		'qr_code_size' => 'nullable|integer',
		'qr_code_composition' => 'nullable|string',
		
		'sharing_title' => 'nullable|string',
		'sharing_description' => 'nullable|string',
		'sharing_image_id' => 'integer',

    'form_sharing_title' => 'nullable|string',
    'form_sharing_description' => 'nullable|string',
    'form_sharing_image_id' => 'integer',

		'status' => 'in:preparing,pending,ready_to_send,completed'
	];

//  public function __construct() {
//    $this->updateRules = $this->storeRules;
//    parent::__construct();
//  }
	
	protected $updateRulesCode = [
		'order' => 'nullable|integer',
		'code' => 'string',
		'extra_fields' => 'nullable|string',
		'key' => 'string',
		'sent_on' => 'nullable|date',
		'status' => 'in:pending,ready,completed',
		'remark' => 'nullable|string'
	];
	
	public function indexxx(Request $request)
	{
		$query = $this->model;
		if ($request->has('page')) {
			$page = $request->get('page', 1);
			$limit = $request->get('limit', 20);
//      $offset = ($request->get('page', 1)-1)*$limit;
			$totalCount = $query->count();
			$lastPage = ceil($totalCount / $limit);
			if ($page > $lastPage) {
				$page = $lastPage;
			}
			// $data = $query->get();
			$offset = ($page - 1) * $limit;
			$data = $query->skip($offset)->take($limit)->get();
			
			$pagedData = new \Illuminate\Pagination\LengthAwarePaginator($data, $totalCount, $limit, $page);
			$pagedData->setCollection($this->onIndexDataReady($request, $pagedData->getCollection()));
			$result = $pagedData;
		} else {
			$data = $query->get();
			$result = $this->onIndexDataReady($request, $data);
		}
		
		return response()->json([
			'status' => true,
			'result' => $result
		]);
	}
//
	protected function onUpdating($input, $row = null)
	{
		if (is_null($input['description'])) {
			$input['description'] = '';
		}
		if (is_null($input['notes'])) {
			$input['notes'] = '';
		}
		
		if (!empty($row->sharing_image_id)) {
			
			$newSharingMediaId = array_key_exists('sharing_image_id', $input) ?
				$input['sharing_image_id'] :
				0;
			
			if ($row->sharing_image_id !== $newSharingMediaId) {
				MediaHelper::deleteMedia($row->sharing_image_id);
				
				// Change to image from temporary
				if (!empty($newSharingMediaId)) {
					MediaHelper::changeMediaType($newSharingMediaId, 'image');
					MediaHelper::changeImageResolution($newSharingMediaId, 256);
				}
			}
		}
		
		return $input;
	}

//  public function show(Request $request, $id) {
//    if ($id == 0) {
//      $record = $this->getBlankRecord();
//    } else {
////      $row = $this->getRow($id);
////      $record = $row->toArray();
////      $record
//      $row = $this->model->find($id);
//      $record = $row->toArray();
//    }
//    return response()->json([
//      'status' => true,
//      'result' => [
//        'data' => $record
//      ]
//    ]);
//  }

  protected function beforeShowData($id) {
	  $row = parent::getRow($id);
    if ($row->codeInfos()->count() === 0) {
      if (!empty($row->code_fields)) {
        $row->code_fields = '';
        $row->save();
      }
    }
  }

	protected function getRow($id)
	{
		$row = parent::getRow($id);
		
    // get custom templates
		$customForms = [];
    foreach($row->customForms as $i=>$customForm) {
    	$customForm->form_configs = json_decode($customForm->form_configs, true);
    	$customForms[] = $customForm;
    }
    $row->custom_forms = $customForms;

    // get form configs
    $row->form_configs = json_decode($row->questionnaire_configs, true);
    unset($row->questionnaire_configs);

    return $row;
	}
	
	protected function onShowDataReady3($request, $row)
	{
    // get custom templates
    $row->customForms;

    // get form configs
    $row->form_configs = json_decode($row->questionnaire_configs, true);
//    $row->thankyou_configs = json_decode($row->thankyou_configs, true);
//    $row->sorry_configs = json_decode($row->sorry_configs, true);

    unset($row->questionnaire_configs);

    return $row;
	}

	protected function onShowDataReady2($request, $row)
	{
		// include code configs
		$row->codeConfigs;

//    $this->checkOrInit($row->codeConfigs);
		
		// remove qr_code_composition
		// this field is obsolate
		if (!empty(trim($row->qr_code_composition))) {
			$codeConfig = $row->codeConfigs->filter(function ($config) {
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
	protected function onIndexFilter($request, $query, $filterFields=[])
	{
		$query = parent::onIndexFilter($request, $query);
		if ($request->has('agentId')) {
			$query = $query->where('agent_id', $request->get('agentId'));
		}
		return $query;
	}


//  protected function prepareIndexQuery($request, $query)
//  {
////    $query = parent::prepareIndexQuery($request, $query);
////
////    if (!$request->has('select')) {
////      $query = $query
////        ->with(['codeInfos' => function ($q) {
////          $q->orderBy('order');
////        }])
////        ->with('agent', 'codeInfos', 'emails');
////    }
//    $query = $query->with('agent');
//    return $query;
//  }
	
	public function export($id)
	{
		$accessKey = AccessKeyHelper::create(
			$this->user,
			'voucher',
			'export',
			serialize(['id' => $id])
		);
		return response()->json([
			'status' => true,
			'result' => [
				'key' => $accessKey
			]
		]);
	}
	
	protected function onIndexSelect($request, $query)
	{
		if ($request->get('type', '') == 'selection') {
			$query = $query->select('id', 'description', 'notes', 'created_at', 'code_count', 'participant_count');
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
		foreach ($rows as $row) {
			$codeConfigs = $row->codeConfigs;
			$qrCodes = $codeConfigs->where('code_group', 'qrcode')->pluck('composition');
			$barCodes = $codeConfigs->where('code_group', 'barcode')->pluck('composition');
			$row->qrcode_comp = $qrCodes->count() > 0 && !is_null($qrCodes[0]) ? $qrCodes[0] : '';
			$row->barcode_comp = $barCodes->count() > 0 && !is_null($barCodes[0]) ? $barCodes[0] : '';
			unset($row->codeConfigs);
		}
//    if (!$request->has('select')) {
//      foreach ($rows as $row) {
//        $row->code_count = $row->codeInfos->count();
//        $row->code_sent = $row->codeInfos()->whereStatus('completed')->count();
//        $row->email_count = $row->emails->count();
//      }
//    }
		
		return $rows;
	}

//  public function show(Request $request, $id) {
//    if ($id == 0) {
//      $id = $this->createNewVoucher();
//    }
//    return parent::show($request, $id);
//  }
//
//  public function createNewVoucher() {
//    $newVoucher = [
//      'description' => '',
//      'agent_id' => Agent::first()->id,
//      'activation_date' => null,
//      'expiry_date' => null,
//      'template' => '',
//      'qr_code_composition' => '',
//      'status' => 'pending',
//      'code_fields' => ''
//    ];
//    $voucher = $this->model->create($newVoucher);
//
//    $defaultQrcode = new VoucherCodeConfig($this->defaultQrcode);
//    $voucher->codeConfigs()->save($defaultQrcode);
//
//    $defaultBarcode = new VoucherCodeConfig($this->defaultBarcode);
//    $voucher->codeConfigs()->save($defaultBarcode);
//    return $voucher->id;
//  }
	
	
	public function clearCodes(Request $request, $id)
	{
		$voucher = $this->model->find($id);
		if (isset($voucher)) {
			$codeCount = $voucher->codeInfos()->count();
			$voucher->codeInfos()->delete();
			$voucher->codeConfigs()
				->where('code_group', 'barcode')
				->orWhere('code_group', 'qrcode')
				->update([
					'composition' => ''
				]);
			$voucher->code_count = 0;
			$voucher->code_fields = '';
			$voucher->save();
			
			return response()->json([
				'status' => true,
				'result' => [
					'deleted' => $codeCount
				]
			]);
		}
		return response()->json([
			'status' => false,
			'result' => [
				'messageTag' => 'invalid_voucher_id',
				'message' => 'Invalid Voucher ID!'
			]
		]);
	}
	
	public function getBlankRecord()
	{
		return [
			'id' => 0,
			'description' => '',
			'notes' => '',
			'agent_id' => Agent::first()->id,
			'activation_date' => '',
			'expiry_date' => '',
			'voucher_type' => 'voucher',
			
			'template' => '',
			'has_template' => 0,
			
			'has_custom_link' => 0, // obsolate
			'custom_link_key' => '',
			
			'entrance_page_type' => 'none',
			'entrance_page_id' => 0,
			'entrance_page_type_after_quota' => 'none',
			'entrance_page_id_after_quota' => 0,
			
			'questionnaire' => '',
			'questionnaire_fields' => '',
			
			'goal_type' => 'fixed',
			'goal_count' => 0,
			
			'action_type_after_goal' => 'none',
			'action_page_after_goal' => '',

//      'qr_code_composition' => '',
			'code_fields' => '',
			'code_count' => 0,
			
			'sharing_image_id' => 0,
			'sharing_title' => '',
			'sharing_description' => '',
			
			'status' => 'pending',
			
			// from voucher_code_configs
			'code_configs' => [
				$this->defaultQrcode,
				$this->defaultBarcode
			],
			
			// voucher_templates
//			'custom_forms' => []
		];
	}
	
	private function onVoucherUpdated($request, $row)
	{
		// Voucher codes is saved independently
		$input = $request->all();
		if (array_key_exists('code_configs', $input)) {
			$this->saveCodeConfigs($row->id, $input['code_configs']);
		}
		
		if (array_key_exists('emails', $input)) {
			$this->saveEmails($row->id, $input['emails']);
		}
		
		// Update Code count
		$codeCount = $row->codeInfos()->count();
		$row->code_count = $codeCount;
		
		// Update participant count
		$participantCount = $row->participants()->count();
		$row->participant_count = $participantCount;
		
		// Update custom link key
		if (empty($row->custom_link_key)) {
			$row->custom_link_key = newKey();
		}

		// Form Configs
		if (array_key_exists('form_configs', $input)) {
	    $formConfigs = $input['form_configs'];
      $row->questionnaire_configs = formConfigsToData($formConfigs);
		}

    if (array_key_exists('custom_forms', $input)) {
	    $this->updateCustomForms($row, $input['custom_forms']);
    }
    
		$row->save();
	}

	private function updateCustomForms($row, $customForms) {
	  $inputFormKeys = array_map(function($customForm) {
	    return $customForm['form_key'];
    }, $customForms);

	  $existingFormKeys = $row->customForms()->pluck('form_key')->toArray();

	  $newFormKeys = array_filter($inputFormKeys, function($formKey) use($existingFormKeys) {
	    return !in_array($formKey, $existingFormKeys);
    });

	  $obsolateFormKeys = array_filter($existingFormKeys, function($formKey) use($inputFormKeys) {
      return !in_array($formKey, $inputFormKeys);
    });
	  
	  // delete obsolate forms
		$row->customForms()->whereIn('form_key', $obsolateFormKeys)->delete();
		
		foreach($customForms as $customForm) {
			$customForm['form_configs'] = formConfigsToData($customForm['form_configs']);
			$formKey = $customForm['form_key'];
			if (in_array($formKey, $newFormKeys)) {
				// Add
				$newForm = new VoucherCustomForm($customForm);
				$row->customForms()->save($newForm);
			} else {
				unset($customForm['id']);
				$row->customForms()->where('form_key', $formKey)->update($customForm);
				// Update
			}
		}


  }
	
	protected function onStoreComplete($request, $row)
	{
		$this->onVoucherUpdated($request, $row);
	}
	
	protected function onUpdateComplete($request, $row)
	{
    $this->onVoucherUpdated($request, $row);
	}

	public function store(Request $request)
	{
		$input = $request->validate($this->storeRules);
		if (empty($input['custom_link_key'])) {
			$input['custom_link_key'] = newKey();
		}
		$input['description'] = nullOrBlank($input['description']);
		$input['notes'] = nullOrBlank($input['notes']);
		
		$newRow = $this->model->create($input);
		$id = $newRow->id;
		$this->onStoreComplete($request, $newRow);
		$this->saveEmails($id,
			array_key_exists('emails', $input) ?
				$input['emails'] :
				[]
		);
		
		$responseRow = $this->getRow($id);
		return response()->json([
			'status' => true,
			'result' => $responseRow
		]);
	}
	
	private function saveCodeConfigs($id, $inputCodeConfigs)
	{
		$codeConfigs = [];
		$voucher = $this->model->find($id);
		$inputIds = [];
		foreach ($inputCodeConfigs as $loopCodeConfig) {
			if (array_key_exists('id', $loopCodeConfig)) {
				$inputIds[] = $loopCodeConfig['id'];
				$codeConfigs[] = $loopCodeConfig;
			}
		}
		
		// Delete obsolate codes
		$voucher->codeConfigs()->whereNotIn('id', $inputIds)->delete();
		
		// Add/Update
		$existingIds = $voucher->codeConfigs()->pluck('id')->toArray();
		$newIds = array_diff($inputIds, $existingIds);
		$keepIds = array_intersect($inputIds, $existingIds);
		
		// add new record
		array_walk($codeConfigs, function ($walkingCodeConfig) use ($voucher, $newIds, $keepIds) {
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
		
		// Ensure barcode and qrcode exists
		if ($voucher->codeConfigs()->whereCodeGroup('qrcode')->count() == 0) {
			$qrcodeConfig = new VoucherCodeConfig($this->defaultQrcode);
			$voucher->codeConfigs()->save($qrcodeConfig);
		}
		
		if ($voucher->codeConfigs()->whereCodeGroup('barcode')->count() == 0) {
			$barcodeConfig = new VoucherCodeConfig($this->defaultBarcode);
			$voucher->codeConfigs()->save($barcodeConfig);
		}
	}
	
	private function saveVoucherCodes($id, $codeInfos)
	{
		$voucher = $this->model->find($id);
		$inputIds = array_map(function ($codeInfo) {
			return $codeInfo['id'];
		}, $codeInfos);
		
		// Delete obsolate codes
		$voucher->codeInfos()->whereNotIn('id', $inputIds)->delete();
		
		// Add/Update
		$existingIds = $voucher->codeInfos()->pluck('id')->toArray();
		$newIds = array_diff($inputIds, $existingIds);
		$keepIds = array_intersect($inputIds, $existingIds);
		
		// add new record
		array_walk($codeInfos, function ($walkingCodeInfo) use ($voucher, $newIds, $keepIds) {
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
		foreach ($codeInfosNoKey as $row) {
			$row->key = newKey();
			$row->save();
		}
	}
	
	private function saveEmails($id, $emails)
	{
		$voucher = $this->model->find($id);
		$inputIds = array_map(function ($email) {
			return $email['id'];
		}, $emails);
		
		// Delete obsolate codes
		$voucher->emails()->whereNotIn('id', $inputIds)->delete();
		
		// Add/Update
		$existingIds = $voucher->emails()->pluck('id')->toArray();
		$newIds = array_diff($inputIds, $existingIds);
		$keepIds = array_intersect($inputIds, $existingIds);
		
		// add new record
		array_walk($emails, function ($email) use ($voucher, $newIds, $keepIds) {
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
	
	protected function onShowWith($query)
	{
		$query = $query->with('codeConfigs');
		return $query;
	}
//  public function getRow($id) {
//    $query
//    $row = $this->model->with(['codeInfos', 'emails'])->find($id);
//    return $row;
//  }
//  public function getRow($id) {
//    $row = $this->model->with(['codeInfos', 'emails'])->find($id);
//    return $row;
//  }
	
	public function destroy($id)
	{
		$row = $this->model->find($id);
		$this->beforeDestroy($row);
		$row->delete();
		return response()->json([
			'status' => true
		]);
	}
	
	protected function beforeDestroy($row)
	{
	
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
	
	public function updateCode(Request $request, $voucherId, $id)
	{
		$status = false;
		$result = [];
		$input = $this->getInput($this->updateRulesCode);
		$voucher = $this->model->find($voucherId);
		if (isset($voucher)) {
			$voucher->codeInfos()->whereId($id)->update($input);
			$status = true;
			$result = $voucher->codeInfos()->whereId($id)->first();
		}
		return response()->json([
			'status' => $status,
			'result' => $result
		]);
	}
	
	public function getParticipants(Request $request, $id) {
		$voucher = $this->model->find($id);
		$inputObjFields = $this->getInputObjFields($voucher);
		if (isset($voucher)) {
			if ($request->has('page')) {
				$page = $request->get('page', 1);
				$limit = $request->get('limit', 20);
				$query = $voucher->participants();
				$query = parent::onIndexFilter($request, $query, ['form_content', 'remark']);
				
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
				
				$arResult = $result->toArray();
				$arResult['data'] = $this->parseParticipantData($arResult['data'], $inputObjFields);
				echo 'arResult[data]: '.PHP_EOL.PHP_EOL;
				print_r($arResult['data']);
				return 'ok';
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
	
	private function getInputObjFields($voucher) {
		$result = [];
		$formConfigs = json_decode($voucher->questionnaiare_configs, true);
		if (isset($formConfigs)) {
			if (isset($formConfigs['inputObjs'])) {
				$inputObjs = $formConfigs['inputObjs'];
				foreach($inputObjs as $i=>$inputObj) {
					$fieldName = 'field'.$i;
					switch ($inputObjs['inputType']) {
						case 'simple-text':
						case 'number':
						case 'email':
						case 'text':
						case 'single-choice':
						case 'multiple-choice':
							$result[] = $fieldName;
							break;
						case 'name':
						case 'phone':
							$result[] = $fieldName.'_0';
							$result[] = $fieldName.'_1';
							break;
					}
				}
			}
		}
		return $result;
	}
	public function getCodes(Request $request, $id)
	{
		$voucher = $this->model->find($id);
		if (isset($voucher)) {
			if ($request->has('page')) {
				$page = $request->get('page', 1);
				$limit = $request->get('limit', 20);
				$query = $voucher->codeInfos();
				$query = parent::onIndexFilter($request, $query, ['code', 'extra_fields', 'key']);
				
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