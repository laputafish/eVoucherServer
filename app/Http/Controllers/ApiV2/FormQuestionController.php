<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\Media;
use App\Models\Voucher;
use App\Models\TempQuestionForm;

use App\Helpers\UploadFileHelper;
use App\Helpers\VoucherHelper;

use App\Imports\AgentCodeImport;
use Illuminate\Http\Request;

class FormQuestionController extends BaseController
{
	private $INPUT_OBJ_TYPES = [
		'simple-text',
		'number',
		'name',
		'email',
		'phone',
		'text',
		'single-choice',
		'multiple-choice'
	];
	
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
									$objTypeConfig =
									$newInputObj = [
										'id' => $rowNo,
										'name' => '',
										'order' => $rowNo,
										'inputType' => $objType,
										'question' => '',
										'required' => true,
										'options' => [],
										'notes' => ''
									];
									$values = ['', '', '', '', '', ''];
									for ($j = 1; $j < 6; $j++) {
										if (!is_null($sheet0[$rowNo][$j])) {
											$values[$j] = $sheet0[$rowNo][$j];
										}
									}
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
											$newInputObj['notes'] = $values[4];
											$newInputObj['options'][] = $values[5];
											
											if ($objType == 'single-choice' || $objType == 'multiple-choice') {
												for ($k = 6; $k < count($sheet0[$rowNo]); $k++) {
													if (!is_null($sheet0[$rowNo][$k]) && !empty($sheet0[$rowNo][$k])) {
														$newInputObj['options'][] = $sheet0[$rowNo][$k];
													} else {
														break;
													}
												}
											}
											break;
										case 'remark':
										case 'images':
											$newInputObj['question'] = $values[3];
											break;
										case 'submit':
											break;
									}
									$inputObjs[] = $newInputObj;
								} else { // first cell is empty, exit
									break;
								}
//									$cells = [];
//									for ($cellNo = 0; $cellNo < count($fields); $cellNo++) {
//										if ($cellNo < count($sheet0[$rowNo])) {
//											$value = $sheet0[$rowNo][$cellNo];
//											$type = getType($value);
//											if (empty($value) || $type == 'null') {
//												break;
//											}
//											if (($type == 'integer' || $type == 'double') && $value >= 36526 && $value <= 55153) {
//												$type = 'date';
//												$value = $this->excel2Date($value);
//											}
//											$fields[$cellNo]['type'] = $type;
//											$cells[] = $value;
//										} else {
//											$cells[] = '';
//										}
//									}
//									$data[] = $cells;
//								} else {
//									break;
//								}
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
			'notes',
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
	
	public function createPreview(Request $request)
	{
		$this->user->questionForms()->delete();
		$key = newKey();
		$formConfigs = $request->get('formConfigs');
		$temp = new TempQuestionForm([
			'form_key' => $key,
			'form_configs' => json_encode($formConfigs)
		]);
		$this->user->questionForms()->save($temp);
	 	return response()->json([
	 		'status' => true,
		  'result' => [
		  	'key' => $key
		  ]
	  ]);
	}
	
	private function getTempFormConfigs($key) {
		$row = TempQuestionForm::where('form_key', $key)->first();
		return isset($row) ? json_decode($row->form_configs, true) : null;
	}
	
	public function showQuestionForm($key) {
		$isTemp = substr($key, 0, 1)=='_';
		$formConfigs = [];
		if ($isTemp) {
			$key = substr($key, 1);
			$formConfigs = $this->getTempFormConfigs($key);
		}
		return view('templates.question_form')->with(['formConfigs' => $formConfigs]);
	}

}
