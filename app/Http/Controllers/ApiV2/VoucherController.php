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
use App\Helpers\VoucherTemplateHelper;

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
		'code_color' => '0,0,0',
		'width' => 7,
		'height' => 7
	];
	
	protected $defaultBarcode = [
		'id' => 0,
		'composition' => '',
		'code_group' => 'barcode',
		'code_type' => 'C128',
		'code_color' => '0,0,0',
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
		
//		'template' => 'nullable|string',
		'has_template' => 'boolean',
		
		'has_custom_link' => 'boolean',
		// 'custom_link_key' => 'string',
		
//		'entrance_page_type' => 'in:questionnaire,custom,none',
//		'entrance_page_id' => 'integer',
//		'entrance_page_type_after_quota' => 'in:questionnaire,custom,none',
//		'entrance_page_id_after_quota' => 'integer',
		
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
		
//		'template' => 'nullable|string',
		'has_template' => 'boolean',
		
		'has_custom_link' => 'boolean',
		// 'custom_link_key' => 'string',
		
//		'entrance_page_type' => 'in:questionnaire,custom,none',
//		'entrance_page_id' => 'integer',
//		'entrance_page_type_after_quota' => 'in:questionnaire,custom,none',
//		'entrance_page_id_after_quota' => 'integer',
		
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

	protected $updateRulesCode = [
		'order' => 'nullable|integer',
		'code' => 'string',
		'extra_fields' => 'nullable|string',
		'key' => 'string',
		'sent_on' => 'nullable|date',
		'status' => 'in:pending,ready,completed',
		'remark' => 'nullable|string'
	];
	
	protected function onUpdating($input, $row = null)
	{
		if (is_null($input['description'])) {
			$input['description'] = '';
		}
		if (is_null($input['notes'])) {
			$input['notes'] = '';
		}
		
		$newSharingImageId = array_key_exists('sharing_image_id', $input) ?
			$input['sharing_image_id'] : 0;
		$this->updateSharingImage($row->sharing_image_id, $newSharingImageId);
		
		$newSharingImageId = array_key_exists('form_sharing_image_id', $input) ?
			$input['form_sharing_image_id'] : 0;
		$this->updateSharingImage($row->form_sharing_image_id, $newSharingImageId);
		
		if ($this->user->isNotA('supervisor')) {
			$input['user_id'] = $this->user->id;
		}
		
		MediaHelper::removeUserTempFiles($this->user->id);
		return $input;
	}
	
	protected function updateSharingImage($oldSharingImageId, $newSharingImageId) {
		if ($oldSharingImageId !== $newSharingImageId) {
			MediaHelper::deleteMedia($oldSharingImageId);
			
			// Change to image from temporary
			if (!empty($newSharingImageId)) {
				MediaHelper::changeMediaType($newSharingImageId, 'image');
				MediaHelper::changeImageResolution($newSharingImageId, 256);
			}
		}
	}
	
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
		
		$this->updateCounts($row);
		
    // get custom templates
		$customForms = [];
    foreach($row->customForms as $i=>$customForm) {
    	$customForm->form_configs = json_decode($customForm->form_configs, true);
    	$customForms[] = $customForm;
    }
    $row->custom_forms = $customForms;

    // get form configs
		
		$row->template = VoucherTemplateHelper::readVoucherTemplate($row);
    $row->form_configs = json_decode($row->questionnaire_configs, true);
		$row->participant_configs = json_decode($row->participant_configs, true);
		
		// unset($row->questionnaire_configs);
		return $row;
	}
	
	protected function onIndexFilter($request, $query, $filterFields=[])
	{
		$query = parent::onIndexFilter($request, $query);
		if ($request->has('agentId')) {
			$query = $query->where('agent_id', $request->get('agentId'));
		}
		return $query;
	}

	public function exportParticipants($id) {
		$accessKey = AccessKeyHelper::create(
			$this->user,
			'voucher_participants',
			'export',
			serialize(['id'=>$id])
		);
		return response()->json([
			'status' => true,
			'result' => [
				'key' => $accessKey
			]
		]);
	}
	
	public function exportCodes($id)
	{
		$accessKey = AccessKeyHelper::create(
			$this->user,
			'voucher_codes',
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
		return $rows;
	}

	public function clearParticipants(Request $request, $id) {
		$voucher = $this->model->find($id);
		if (isset($voucher)) {
			$participantCount = $voucher->participants()->count();
			$voucher->participants()->delete();
			$voucher->participant_count = 0;
			$voucher->code_fields = '';
			$voucher->save();
			
			return response()->json([
				'status' => true,
				'result' => [
					'deleted' => $participantCount
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
		$firstAgent = Agent::whereUserId($this->user->id)->first();
		$agentId = isset($firstAgent) ? $firstAgent->id : 0;
		return [
			'id' => 0,
			'description' => '',
			'notes' => '',
			'agent_id' => $agentId,
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
			// 'questionnaire_configs' => '', // no need, it will be assigned from form_configs
			
			'goal_type' => 'fixed',
			'goal_count' => 0,
			
			'action_type_before_goal' => 'form_voucher',
			'custom_form_key_before_goal' => '',
			
			'action_type_after_goal' => 'none',
			'custom_form_key_after_goal' => '',

//      'qr_code_composition' => '',
			'code_fields' => '',
			'code_count' => 0,

			'participant_count' => 0,
			
			'sharing_image_id' => 0,
			'sharing_title' => '',
			'sharing_description' => '',
			
			'form_sharing_image_id' => 0,
			'form_sharing_title' => '',
			'form_sharing_description' => '',
			
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
		// save template
		$template = $request->get('template', '');
		VoucherTemplateHelper::writeVoucherTemplate($row, $template);
		
		// Voucher codes is saved independently
		$input = $request->all();
		if (array_key_exists('code_configs', $input)) {
			$this->saveCodeConfigs($row->id, $input['code_configs']);
		}
		
		if (array_key_exists('emails', $input)) {
			$this->saveEmails($row->id, $input['emails']);
		}
		
		$this->updateCounts($row);
		
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

	private function updateCounts($voucher) {
		// Update Code count
		$codeCount = $voucher->codeInfos()->count();
		$voucher->code_count = $codeCount;
		
		// Update participant count
		$participantCount = $voucher->participants()->count();
		$voucher->participant_count = $participantCount;
	}
	
	public function update(Request $request, $id) {
		$result = parent::update($request, $id);
		return $result;
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
	
  protected function onStoring($input) {
		$result = parent::onStoring($input);
		if (empty($result['custom_link_key'])) {
			$result['custom_link_key'] = newKey();
		}
		$input['description'] = is_null($result['description']) ? '' : $result['description'];
		$input['notes'] = is_null($result['notes']) ? '' : $result['notes'];
		return $result;
  }
  
	protected function onStoreCompleted($request, $row)
	{
		$this->onVoucherUpdated($request, $row);
	}
	
	protected function onUpdateCompleted($request, $row)
	{
    $this->onVoucherUpdated($request, $row);
	}

	public function store(Request $request)
	{
		$input = $request->validate($this->storeRules);
		$input = $this->onStoring($input);
//		$input['description'] = '';
//		$input['notes'] = '';
//		echo 'description = blank: '.($input['description'] == '').PHP_EOL;
//		echo 'description is null: '.is_null($input['description']).PHP_EOL;
////		print_r($input);
//		return 'ok';
		$newRow = $this->model->create($input);
		$id = $newRow->id;
		$this->onStoreCompleted($request, $newRow);
		$this->saveEmails($id,
			array_key_exists('emails', $input) ?
				$input['emails'] :
				[]
		);
		
//		$voucher = $this->model->find($id);
//		$t = $voucher->getTemplateFullPath('vouchers');
//		return $t;
		
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
					'code_color' => is_null($walkingCodeConfig['code_color']) ? '' : $walkingCodeConfig['code_color'],
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
						'code_color' => is_null($walkingCodeConfig['code_color']) ? '' : $walkingCodeConfig['code_color'],
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
	
	public function deleteParticipant(Request $request, $id, $participantId) {
		$voucher = Voucher::find($id);
		
		if (isset($voucher)) {
			// Remove entry in code infos
			$voucher->codeInfos()->whereParticipantId($participantId)->update(['participant_id'=>0]);
			
			// Remove participant
			$voucher->participants()->where('id', $participantId)->delete();
			
			$participantCount = $voucher->participants()->count();
			$voucher->participant_count = $participantCount;
			$voucher->save();
		}
		
		return 	response()->json([
			'status' => true,
			'result' => [
				'participant_count' => $participantCount
			]
		]);
	}
	
	public function getParticipants(Request $request, $id) {
		$voucher = $this->model->find($id);
//		$inputObjFields = $this->getInputObjFields($voucher);
		$voucher->participant_count = $voucher->participants()->count();
		$voucher->save();
		
		$inputObjs = $voucher->input_objs;
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
				$pagedData->getCollection()->transform(function($value) {
					$value->code;
					return $value;
				});
				$result = $pagedData;
				
				$arResult = $result->toArray();
				$arResult['data'] = $this->parseParticipantData($arResult['data'], $inputObjs);

				return response()->json([
					'status' => true,
					'result' => $arResult
				]);
			}
		}
		return response()->json([
			'status' => true,
			'result' => []
		]);
	}
	
	private function parseParticipantData($data, $inputObjs) {
//		echo 'parseParticipantData: '.PHP_EOL;
		$result = [];
		foreach($data as $i=>$record) {
//			echo 'parseParticipantData i='.$i.': '.PHP_EOL;
//			echo PHP_EOL.PHP_EOL;
//			print_r($record);
//			echo PHP_EOL.PHP_EOL;
			$formContent = $record['form_content'];
			$fieldValues = explode('||', $formContent);
			$record['code_key'] = isset($record['code']) ? $record['code']['key'] : null;
			foreach($inputObjs as $i=>$inputObj) {
				$fieldValue = $fieldValues[$i];
				$fieldName = 'field'.$i;
				switch ($inputObj['inputType']) {
					case 'simple-text':
					case 'number':
					case 'email':
					case 'gender':
					case 'text':
					case 'single-choice':
					case 'gender':
					case 'phone':
					case 'multiple-choice':
						$record[$fieldName] = $fieldValue;
						break;
					case 'name':
						$fieldValueSegs = explodeByCount('|', $fieldValue, 2, ' ');
						$record[$fieldName.'_0'] = $fieldValueSegs[0];
						$record[$fieldName.'_1'] = $fieldValueSegs[1];
						break;
				}
			}
			unset($record['form_content']);
			$result[] = $record;
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
				$pagedData->getCollection()->transform(function($value) {
					$value->participant;
					return $value;
				});
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