<?php namespace App\Http\Controllers\ApiV2;

use App\Exports\FormConfigsExport;

use App\Models\Menu;
use App\Models\Media;
use App\Models\Voucher;
use App\Models\TempQuestionForm;
use App\Models\VoucherCustomForm;

use App\Helpers\UploadFileHelper;
use App\Helpers\VoucherHelper;
use App\Helpers\QuestionnaireHelper;
use App\Helpers\InputObjHelper;

use App\Imports\AgentCodeImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormQuestionController extends BaseController
{
//	private $INPUT_OBJ_TYPES = [
//	  'page',
//		'simple-text',
//		'number',
//		'name',
//		'email',
//		'phone',
//		'text',
//		'single-choice',
//		'multiple-choice'
//	];
	
	public function upload(Request $request)
	{
		$status = false;
		$message = '';
		$result = [];
		
		$fields = [];
		$data = [];
		if ($request->hasFile('file')) {
			if (!$request->file('file')->isValid()) {
				echo "Error: " . $_FILES["file"]["error"] . "		";
			} else {
				$tempFilePath = UploadFileHelper::saveTempFile($_FILES['file']);
				$ar = \Excel::toArray(null, $tempFilePath);
				
				if (count($ar) > 0) {
					$sheet0 = $ar[0];
					if (count($sheet0) > 0) {
						$row0 = $sheet0[0];
						// Cells of first row is heading/field names
						if (count($row0) > 0) {
							// iterate on each cell
							$cells = [];
							foreach ($row0 as $i => $loopCell) {
								if (empty($loopCell)) {
									break;
								}
								$cells[] = [
									'title' => $loopCell,
									'type' => 'string'
								];
							}
							$fields = $cells;
						}
						if ($this->isValidQuestionFile($fields)) {
							$inputObjs = [];
							for ($rowNo = 1; $rowNo < count($sheet0); $rowNo++) {
								// check first cell if empty
								if (!empty($sheet0[$rowNo][0])) {
									$objType = strtolower($sheet0[$rowNo][0]);
									$newInputObj = [
										'id' => $rowNo,
										'name' => '',
										'order' => $rowNo,
										'inputType' => $objType,
										'question' => '',
										'required' => true,
										'options' => [],
										'note1' => '',
                    'note2' => ''
									];
									$values = ['', '', '', '', '', '', '', ''];
									// values
                  // [1]: description => name
                  // [2]: required
                  // [3]: question/remark/image link
                  // [4]: note1
                  // [5]: note2
                  // [6]: options[0]
                  // [7]: options[1]
									for ($j = 1; $j < 7; $j++) {
										if (!is_null($sheet0[$rowNo][$j])) {
											$values[$j] = $sheet0[$rowNo][$j];
										}
									}
									$valid = true;
									switch ($objType) {
										case 'simple-text':
										case 'number':
										case 'name':
										case 'email':
										case 'phone':
										case 'text':
										case 'single-choice':
										case 'multiple-choice':
											$newInputObj['name'] = $values[1];
											if (in_array(strtolower($values[2]), ['yes', 'true'])) {
												$newInputObj['required'] = true;
											} else if ($values[2] === true) {
												$newInputObj['required'] = true;
											}
											$newInputObj['question'] = $values[3];
											$newInputObj['note1'] = $values[4];
											$newInputObj['note2'] = $values[5];

											$newInputObj['options'][] = $values[6];
											if ($objType == 'single-choice' || $objType == 'multiple-choice') {
												for ($k = 7; $k < count($sheet0[$rowNo]); $k++) {
													if (!is_null($sheet0[$rowNo][$k]) && !empty($sheet0[$rowNo][$k])) {
														$newInputObj['options'][] = $sheet0[$rowNo][$k];
													} else {
														break;
													}
												}
											}
											break;
										case 'output-remark':
                      $newInputObj['question'] = str_replace(chr(10), '|', $values[3]);

//                      for ($i = 0; $i < strlen($values[3]); $i++) {
//                        echo $values[3][$i].' ('.ord($values[3][$i]).') '.PHP_EOL;
//                      }
                      $newInputObj['options'][] = $values[6];
                      $newInputObj['options'][] = $values[7];
                      break;
                    case 'output-submit':
                    case 'output-image':
                      $newInputObj['question'] = $values[3];
                    case 'system-page':
                      $newInputObj['options'][] = $values[6];
                      $newInputObj['options'][] = $values[7];
  										break;
										default:
											$valid = false;
									}
									if ($valid) {
										$inputObjs[] = $newInputObj;
									}
								} else { // first cell is empty, exit
									break;
								}
							}
							$result = $inputObjs;
						} else {
							$result = [
								'message' => 'Mismatched Column Headers!',
								'messageTag' => 'mismatched_column_headers'
							];
						}
					} else {
						$result = [
							'message' => 'No data in file!',
							'messageTag' => 'no_data_in_file'
						];
					}
				} else {
					$result = [
						'message' => 'No data in file!',
						'messageTag' => 'no_data_in_file'
					];
				}
				unlink($tempFilePath);
			}
		}
		
		return response()->json([
			'status' => $status,
			'result' => $result
		]);
	}
	
	private function isValidQuestionFile($fields)
	{
		$correctColHeaders = [
			'type',
			'description',
			'required',
			'question/remark/image link',
			'note1',
      'note2',
			'options'
		];
		$result = true;
		for ($i = 0; $i < count($fields); $i++) {
			if (strtolower($fields[$i]['title']) != $correctColHeaders[$i]) {
				$result = false;
				break;
			}
		}
		return $result;
	}
	
	private function createCodeFieldsStr($fields)
	{
		$ar = [];
		foreach ($fields as $field) {
			$ar[] = $field['title'] . ':' . $field['type'];
		}
		return implode('|', $ar);
	}
	
	private function excel2Date($excelDateValue)
	{
		$dateTimeObject = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDateValue);
		return $dateTimeObject->format('Y-m-d');
	}
	
	public function saveFormConfigs(Request $request)
	{
		$this->user->questionForms()->delete();
		$key = newKey();

		$formConfigs = $request->get('formConfigs');
		$temp = new TempQuestionForm([
			'form_key' => $key,
			'description' => $request->get('description'),
			'form_configs' => QuestionnaireHelper::preprocessFormConfigs($formConfigs)
		]);
		$this->user->questionForms()->save($temp);
	 	return response()->json([
	 		'status' => true,
		  'result' => [
		  	'key' => $key
		  ]
	  ]);
	}
	
	public function showQuestionForm($key) {
		$isTemp = substr($key, 0, 1)=='_';
		$formConfigs = [];
		if ($isTemp) {
			$key = substr($key, 1);
			$formConfigs = $this->getTempFormConfigs($key);
		} else {
			$formConfigs = $this->getFormConfigs($key);
		}
		if (is_null($formConfigs)) {
			return view('errors.404');
		}
		return view('templates.custom_form')->with([
			'formKey' => $key,
			'formConfigs' => $formConfigs
		]);
	}

	public function getFormConfigs($key) {
		$result = null;
		$configs = Voucher::where('custom_link_key', $key)->value('questionnaire_configs');
		if (isset($configs)) {
			$result = json_decode($configs, true);
		}
		return $result;
	}
	
	private function getTempFormConfigs($key) {
		$result = null;
		$configs = TempQuestionForm::where('form_key', $key)->value('form_configs');
		if (isset($configs)) {
			$result = json_decode($configs, true);
		}
		return $result;
//		$formConfigs = isset($row) ? json_decode($row->form_configs, true) : null;
//		return $formConfigs;
	}
	
	public function downloadFormConfigs($key)
	{
		return $this->processFormConfigs($key);
	}
	
	private function processFormConfigs($key) {
		$isTemp = false;
		if (substr($key,0,1) === '_') {
			$key = substr($key, 1);
			$isTemp = true;
		}
		
		if ($isTemp) {
			$questionForm = TempQuestionForm::where('form_key', $key)->first();
      $formConfigs = $questionForm->form_configs;
      $description = empty($questionForm->description) ?
        'no_description' :
        $questionForm->description;
		}

		if (isset($formConfigs)) {
      $formConfigs = json_decode($formConfigs, true);
      $filename = str_replace(' ', '_', $description).'.xlsx';
		  return \Excel::download(new FormConfigsExport($formConfigs), $filename);
		} else {
		  return response('Cannot get form configs!', 401);
    }
	}

	public function postQuestionForm(Request $request) {
		$formKey = $request->get('formKey', '');
		$voucher = Voucher::where('custom_link_key', $formKey)->first();
		
		// Check form and return with fresh
		$participant = [];
		$inputObjs = $voucher->input_objs;
		$res = InputObjHelper::getInputObjRuleAndMessages($inputObjs);
		
		$validator = Validator::make($request->all(), $res['rules'], $res['messages']);
		if ($validator->fails()) {
			return redirect('q/'.$formKey)->withInput()->withErrors($validator);
		}
		
		$participantCount = $voucher->participant_count;
		$targetCount = -1;
		switch ($voucher->goal_type) {
			case 'fixed':
				$targetCount = $voucher->goal_count;
				break;
			case 'codes':
				$targetCount = $voucher->code_count;
				break;
		}
		
		// Before goal
		if ($targetCount == -1 || $participantCount < TargetCount) {
			$participantRow = $this->saveFormParticipant($voucher, $participant);
			
			// assign key
			if ($voucher->goal_type == 'fixed' || $voucher->goal_type == 'none') {
				$participantKey = newKey();
				$participantRow->update(['participant_key' => $participantKey]);
			} else {
				$voucherCode = VoucherCode::where('voucher_id', $voucher->id)
					->where('participant_id', 0)->first();
				
				$voucherCode->update(['participant_id' => $participantRow->id]);
				$participantKey = $voucherCode->key;
			}
			
			
			switch ($voucher->action_type_before_goal) {
				case 'form_voucher':
					return response()->redirect('coupons/' . $participantKey);
				case 'form_custom':
					return $this->showCustomForm( $voucher->custom_form_key_before_goal);
				case 'custom':
					// this is not reachable as it will open custom page already.
					break;
			}
		} else {
		// After goal
			$participantRow = $this->saveFormParticipant($voucher, $participant);
			$participantKey = newKey();
			$participantRow->update(['participant_key' => $participantKey]);
			return $this->showCustomForm( $voucher->custom_form_key_after_goal);
		}
	}
	
	private function saveFormParticipant($customForm, $participant) {
	
	}
	
	private function showCustomForm($formKey) {
		$customForm = VoucherCustomForm::where('form_key', $formKey)->first();
		if (isset($customForm)) {
			return view('templaes.custom_form')->with([
				'formKey' => $formKey,
				'formConfigs' => json_decode($customForm->form_configs, true)
			]);
		} else {
			return view('errors.404');
		}
	}
}
