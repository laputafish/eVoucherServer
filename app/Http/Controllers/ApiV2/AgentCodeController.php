<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\Media;
use App\Models\Voucher;
use App\Models\VoucherCode;
use App\Models\TempUploadFile;
use App\Models\VoucherParticipant;

use App\Helpers\UploadFileHelper;
use App\Helpers\VoucherHelper;
use App\Helpers\TempUploadFileHelper;

use App\Imports\AgentCodeImport;

class AgentCodeController extends BaseController
{
	public function parse($key)
	{
		$fieldDefs = \Input::get('fieldInfos');
		$tempUploadFile = TempUploadFile::where('key', $key)->first();
		if (!isset($tempUploadFile)) {
			return response()->json([
				'status' => false,
				'result' => [
					'message' => 'Invalid key!',
					'messageTag' => 'invalid_key'
				]
			]);
		}
		$fullPath = storage_path('app/uploads/'.$tempUploadFile->filename);
		$voucherId = $tempUploadFile->voucher_id;
		$voucher = Voucher::find($voucherId);
		$isVoucherType = $voucher->voucher_type == 'voucher';
		$codeIndex = $this->getCodeIndex($fieldDefs);
		
		$ar = \Excel::toArray(null, $fullPath);
		if (count($ar) > 0) {
			$sheet0 = $ar[0];
			$data = [];
			if (count($sheet0)>1) {
				$voucherData = [];
				$participantData = [];
				for ($rowNo = 1; $rowNo <count($sheet0); $rowNo++) {
					// check first cell if empty
					if (!empty($sheet0[$rowNo][0])) {
						
						$codeValue = '';
						$voucherCells = [];
						// voucherCells = [
						//    {cell0}, // first value is code
						//    {cell1}, // other values are all others
						//    {cell2},
						//    ...
						// ]
						$participantCells = [
							'name' => '',
							'phone' => '',
							'email' => '',
							'all' => []
						];
						$hasVoucher = false;
						$hasParticipant = false;
						
						// participantCells = [
						//    ['name'] => '',
						//    ['phone'] => '',
						//    ['email'] => '',
						//    all: [
						//      ['title'=>'name','dataType'=>'integer|string|date','value'=>'...'],
						//      ['title'=>'phone','dataType'=>'integer|string|date','value'=>'...'],
						//      ['title'=>'email','dataType'=>'integer|string|date','value'=>'...']
						//    ]
						// ]
						$cells = [];
						for ($cellIndex = 0; $cellIndex < count($fieldDefs); $cellIndex++) {
							$fieldDef = $fieldDefs[$cellIndex];
							if ($cellIndex < count($sheet0[$rowNo])) {
								$value = $sheet0[$rowNo][$cellIndex];
								$type = getType($value);
								if (empty($value) || $type == 'null') {
									break;
								}
								if (($type == 'integer'||$type == 'double') && $value >= 36526 && $value <= 55153) {
									$type = 'date';
									$value = $this->excel2Date($value);
								}
								
								switch($fieldDef['fieldType']) {
									case 'code':
										$fieldDefs[$cellIndex]['type'] = 'string';
										$hasVoucher = true;
										array_unshift($voucherCells, $value);
										break;
									case 'code-other':
										$hasVoucher = true;
										$voucherCells[] = $value;
										break;
									case 'name':
										$hasParticipant = true;
										$participantCells['name'] =  $value;
										$participantCells['all'][] = [
											'title' => $fieldDef['title'],
											'dataType' => $fieldDef['type'],
											'value' => $value
										];
										break;
									case 'email':
										$hasParticipant = true;
										$participantCells['email'] = $value;
										$participantCells['all'][] = [
											'title' => $fieldDef['title'],
											'dataType' => $fieldDef['type'],
											'value' => $value
										];
										break;
									case 'phone':
										$hasParticipant = true;
										$participantCells['phone'] = $value;
										$participantCells['all'][] = [
											'title' => $fieldDef['title'],
											'dataType' => $fieldDef['type'],
											'value' => $value
										];
										break;
									case 'participant-other':
										$hasParticipant = true;
										$participantCells['all'][] = [
											'title' => $fieldDef['title'],
											'dataType' => $fieldDef['type'],
											'value' => $value
										];
										break;
								}

							} else {
//								$cells[] = '';
							}
						}
						if ($hasVoucher) {
							$voucherData[] = $voucherCells;
						}
						if ($hasParticipant) {
							$participantData[] = $participantCells;
						}
//						$data[] = $cells;
					} else {
						break;
					}
				}
			}
			
			$participantFieldInfos = array_filter($fieldDefs, function($info) {
				return $info['fieldType'] != 'code' && $info['fieldType'] != 'code-other';
			});
			$res2 = $this->updateParticipantCodes($voucher, $participantData, $participantFieldInfos);

//			print_r($res2);
//			return 'ok';
			$arParticipantIds = [];
			$participantConfigs = [];
			if (isset($res2)) {
				$arParticipantIds = $res2['result']['participantIds'];
				$participantConfigs = $res2['result']['participantConfigs'];
			}
//			print_r($arParticipantIds);
//			return 'ok';
			$voucherFieldInfos = array_filter($fieldDefs, function($info) {
				return $info['fieldType'] == 'code' || $info['fieldType'] == 'code-other';
			});
			$res1 = $this->updateVoucherCodes($voucher, $voucherData, $voucherFieldInfos, $arParticipantIds);
			
			$voucher = Voucher::find($voucherId);
			$res1['result']['participantConfigs'] = json_decode($voucher->participant_configs, true);
			$res1['result']['participantCount'] = $voucher->participants()->count();
			
		}
		
		$res = [
			'status' => true,
			'result' => [
				'message' => '',
				'messageTag' => ''
			]
		];
		if (!$res1['status']) {
			$res['status'] = false;
			if (!array_key_exists('message', $res1['result'])) {
//				echo 'res1: '.PHP_EOL.PHP_EOL;
//				print_r($res1);
			} else {
				$res['result']['message'] = $res1['result']['message'];
				$res['result']['messageTag'] = $res1['result']['messageTag'];
			}
		} else if(isset($res2) && !$res2['status']) {
			$res['status'] = false;
			if (!array_key_exists('message', $res2['result'])) {
//				echo 'res2: '.PHP_EOL.PHP_EOL;
//				print_r($res2);
			} else {
				$res['result']['message'] = $res2['result']['message'];
				$res['result']['messageTag'] = $res2['result']['messageTag'];
			}
		} else {
			$res = $res1;
		}
//		$res['messageTag'] = $arParticipantIds;
		return response()->json($res);
	}
	
	public function parse2($key)
	{
		$fieldInfos = \Input::get('fieldInfos');
	
		$tempUploadFile = TempUploadFile::where('key', $key)->first();
		if (!isset($tempUploadFile)) {
			return response()->json([
				'status' => false,
				'result' => [
					'message' => 'Invalid key!',
					'messageTag' => 'invalid_key'
				]
			]);
		}
		$fullPath = storage_path('app/uploads/'.$tempUploadFile->filename);
		$voucherId = $tempUploadFile->voucher_id;
		$voucher = Voucher::find($voucherId);
		$isVoucherType = $voucher->voucher_type == 'voucher';
		$codeIndex = $this->getCodeIndex($fieldInfos);
		
		$ar = \Excel::toArray(null, $fullPath);
		if (count($ar) > 0) {
			$sheet0 = $ar[0];
			if (count($sheet0)>1) {
				for ($rowNo = 1; $rowNo <count($sheet0); $rowNo++) {
					// check first cell if empty
					if (!empty($sheet0[$rowNo][0])) {
						$codeValue = '';
						$voucherCells = [];
						$cells = [];
						for ($cellIndex = 0; $cellIndex < count($fieldInfos); $cellIndex++) {
							if ($cellIndex < count($sheet0[$rowNo])) {
								$value = $sheet0[$rowNo][$cellIndex];
								$type = getType($value);
								if (empty($value) || $type == 'null') {
									break;
								}
								if (($type == 'integer'||$type == 'double') && $value >= 36526 && $value <= 55153) {
									$type = 'date';
									$value = $this->excel2Date($value);
								}
								
								// if this cells is code
								if ($cellIndex == $codeIndex) {
									$codeValue = $value;
								} else {
									$cells[] = $value;
								}
							} else {
								$cells[] = '';
							}
						}
						array_unshift($cells, $codeValue);
						$data[] = $cells;
					} else {
						break;
					}
				}
			}
			
			if ($isVoucherType) {
				// move code title to first
				$codeFieldInfo = $fieldInfos[$codeIndex];
				array_splice($fieldInfos, $codeIndex, 1);
				array_unshift($fieldInfos, $codeFieldInfo);
				$res = $this->updateVoucherCodes($voucher, $data, $fieldInfos);
			} else {
        // move code title to first
        $codeFieldInfo = $fieldInfos[$codeIndex];
        array_splice($fieldInfos, $codeIndex, 1);
        array_unshift($fieldInfos, $codeFieldInfo);
        $res = $this->updateVoucherCodes($voucher, $data, $fieldInfos);
      }
		}
		// TempUploadFileHelper::removeUserTempFiles($this->user->id);
		
		return response()->json($res);
	}

	private function moveCodeColumnToFirst(&$fields, &$data, $codeIndex) {
	
	}

	public function updateViews($id) {
	  $status = false;
	  $voucherCode = VoucherCode::find($id);
	  if (isset($voucherCode)) {
	    $status = true;
	    $voucherCode->views++;
	    $voucherCode->save();
	    event(new VoucherCodeViewsUpdated($voucherCode));
    } else {
	    $message = 'Error: cannot update views.';
    }
    return response()->json([
      'status' => $status,
      'result' => []
    ]);
  }
	private function getCodeIndex($fieldInfos) {
		$result = 0;
		for($i = 0; $i < count($fieldInfos); $i++) {
			if ($fieldInfos[$i]['fieldType'] == 'code') {
				$result = $i;
				break;
			}
		}
		return $result;
	}
	
	private function updateParticipantCodes($voucher, $data, $fieldInfos) {
//		echo 'updateParticipantCodes: '.PHP_EOL;
		$participantIds = [];
		foreach($data as $item) {
			$formContent = implode('||', array_map(function($el) {
				return $el['value'];
			}, $item['all']));
			$participant = new VoucherParticipant([
				'name' => $item['name'],
				'email' => $item['email'],
				'phone' => $item['phone'],
				'form_content' => $formContent
			]);
			$newRow = $voucher->participants()->save($participant);
			$participantIds[] = $newRow->id;
		}
		
		$arInputObjs =[];
		foreach($fieldInfos as $fieldInfo) {
			$newInputObj = [
				'name' => $fieldInfo['title'],
				'inputType' => 'simple-text',
				'question' => '',
				'required' => 1,
				'notes1' => '',
				'notes2' => '',
				'options' => []
			];
			switch ($fieldInfo['fieldType']) {
				case 'email':
					$newInputObj['inputType'] = 'email';
					break;
				case'phone':
					$newInputObj['inputType'] = 'phone';
					break;
				case 'name':
					$newInputObj['inputType'] = 'name';
					break;
				default:
					$newInputObj['inputType'] = 'simple-text';
			}
				
				$arInputObjs[] = $newInputObj;
		}
		$participantConfigs = formConfigsToData([
			'inputObjs' => $arInputObjs
		]);
		$voucher->participant_configs = $participantConfigs;
		$voucher->save();
		
		// update participant_configs
		// {
		//    "inputObjs": [
		//      {
		//        "name": "",
		//        "inputType": "system-page",
		//        "question": "",
		//        "required": "1",
		//        "note1": "",
		//        "options": [
		//          "background-color: white;color:black;font-size:14px;max-width:640px;padding-top:60px;"
		//        ]
		//      }
		//    ]
		// {
		//    "inputObjs": [
		//      {
		//        "id": "1",
		//        "name": "",
		//        "order": "1",
		//        "inputType": "output-image",
		//        "question": "https:\/\/ticketdemo.yoov.com\/media\/image\/122",
		//        "required": "1",
		//        "options": [
		//          "",
		//          ""
		//        ],
		//        "note1": "",
		//        "note2": ""
		//      },
		//      {
		//        "id": "2",
		//        "name": "\u59d3\u540d",
		//        "order": "2",
		//        "inputType": "simple-text",
		//        "question": "\u59d3\u540d",
		//        "required": "1",
		//        "options": [
		//          ""
		//        ],
		//        "note1": "",
		//        "note2": ""
		//      },
		//      {
		//        "id": "3",
		//        "name": "\u624b\u6a5f\u865f\u78bc",
		//        "order": "3",
		//        "inputType": "phone",
		//        "question": "\u624b\u6a5f\u865f\u78bc",
		//        "required": "1",
		//        "options": [
		//          ""
		//        ],
		//        "note1": "",
		//        "note2": ""
		//      },
		//      {
		//        "id": "4",
		//        "name": "Address",
		//        "order": "4",
		//        "inputType": "simple-text",
		//        "question": "\u5730\u5740",
		//        "required": "1",
		//        "options": [
		//          ""
		//        ],
		//        "note1": "*\u53ea\u9650\u9999\u6e2f\u7528\u6236\u53c3\u52a0",
		//        "note2": ""
	  //      },
		//      {
		//        "id": "5",
		//        "name": "",
		//        "order": "5",
		//        "inputType": "output-remark",
		//        "question": "* \u8a66\u7528\u88dd\u5c07\u6703\u65bc\u767b\u8a18\u5f8c\u4e00\u661f\u671f\u5167\u4ee5\u90f5\u905e\u5bc4\u51fa",
		//        "required": "1",
		//        "options": ["",""],
		//        "note1": "",
		//        "note2": ""
		//      },
		//      {
		//        "id": "6",
		//        "name":"Q1",
		//        "order":"6",
		//        "inputType":"single-choice",
		//        "question":"\u4f60\u8a8d\u5514\u8a8d\u8b58UL\u00b7OS\u5462\u500b\u54c1\u724c\uff1f",
		//        "required":"1",
		//        "options":["\u8a8d\u8b58","\u4e0d\u8a8d\u8b58"],
		//        "note1":"",
		//        "note2":""
		//      },
		//      {
		//        "id":"7",
		//        "name":"Q2",
		//        "order":"7",
		//        "inputType":"single-choice",
		//        "question":"\u4f60\u6709\u7121\u66fe\u7d93\u7528\u904e\u6216\u8cb7\u904eUL\u00b7OS\u5605\u7522\u54c1\uff1f",
		//        "required":"1",
		//        "options":[
		//          "\u66fe\u7d93\u4f7f\u7528",
		//          "\u66fe\u7d93\u8cfc\u8cb7",
		//          "\u672a\u66fe\u4f7f\u7528",
		//          "\u672a\u66fe\u8cfc\u8cb7"
		//        ],
		//        "note1":"",
		//        "note2":""
		//      },
		//      {
		//        "id":"8",
		//        "name":"Q3",
		//        "order":"8",
		//        "inputType":"multiple-choice",
		//        "question":"\u4f60\u6709\u7121\u55ba\u4ee5\u4e0b\u5e97\u8216\u898b\u904eUL\u00b7OS\u5605\u7522\u54c1\uff1f\uff08\u53ef\u591a\u9078\uff09",
		//        "required":"1",
		//        "options":[
		//          "\u5c48\u81e3\u6c0f",
		//          "\u767e\u4f73",
		//          "\u60e0\u5eb7",
		//          "7-11",
		//          "Donki",
		//          "YATA",
		//          "AEON",
		//          "APITA",
		//          "HKTVmall",
		//          "\u5b8c\u5168\u7121\u898b\u904e"
		//        ],
		//        "note1":"",
		//        "note2":""
		//      },
		//      {
		//        "id":"9",
		//        "name":"",
		//        "order":"9",
		//        "inputType":"output-remark",
		//        "question":"* \u6bcf\u4eba\u53ea\u9650\u767b\u8a18\u4e59\u6b21\u53ca\u63db\u9818\u4e59\u4efd\uff0c\u63db\u5b8c\u5373\u6b62\u3002 | |* \u63db\u9818\u65e5\u671f\u70ba2020\u5e745\u670825\u65e5\u81f35\u670831\u65e5\uff0c\u903e\u671f\u7121\u6548\u3002",
		//        "required":"1",
		//        "options":["",""],
		//        "note1":"",
		//        "note2":""
		//      },
		//      {
		//        "id":"10",
		//        "name":"",
		//        "order":"10",
		//        "inputType":"output-image",
		//        "question":"https:\/\/ticketdemo.yoov.com\/media\/image\/114",
		//        "required":"1",
		//        "options":["",""],
		//        "note1":"",
		//        "note2":""
		//      },
		//      {
		//        "id":"11",
		//        "name":"",
		//        "order":"11",
		//        "inputType":"output-submit",
		//        "question":"\u7acb\u5373\u9818\u53d6\u8a66\u7528\u88dd",
		//        "required":"1",
		//        "options":["",""],
		//        "note1":"",
		//        "note2":""
		//      },
		//      {
		//        "id":"12",
		//        "name":"",
		//        "order":"12",
		//        "inputType":"system-page",
		//        "question":"",
		//        "required":"1",
		//        "options":[
		//          "background-color:#0D2E1D;color:White;font-size:14px;max-width:640px;padding-top:10px;selected-choice-text-color:White;selected-choice-color:#0D2E1D;",
		//          ""
		//        ],
		//        "note1":"",
		//        "note2":""
		//      },
		//      {
		//        "id":"13",
		//        "name":"",
		//        "order":"13",
		//        "inputType":"output-image",
		//        "question":"https:\/\/ticketdemo.yoov.com\/media\/image\/124",
		//        "required":"1",
		//        "options":["",""],
		//        "note1":"",
		//        "note2":""
		//      }
		//    ]
		//  }
		// }
		
		
		
		
		// $data = [
		//    ['name'=>'name1','phone'=>'phone1','email'=>'email1','all'=>[
		//      ['title'=>'title1','dataType'=>'integer','value'=>1],
		//      ['title'=>'title1','dataType'=>'integer','value'=>2],
		//      ['title'=>'title1','dataType'=>'integer','value'=>3],
		//      ['title'=>'title1','dataType'=>'integer','value'=>4],
		//      ['title'=>'title1','dataType'=>'integer','value'=>5],
		//      ['title'=>'title1','dataType'=>'integer','value'=>6],
		//    ]
		//  ]
		return [
			'status' => true,
			'result' => [
				'message' => '',
				'messageTag' => '',
				'participantIds' => $participantIds,
				'participantConfigs' => $participantConfigs
			]
		];
	}
	
	// if fieldInfos contains type of participant, all will be moved to code-other type
	private function updateVoucherCodes($voucher, $data, $fieldInfos, $arParticipantIds=[]) {
		$status = false;
		$codeFieldsStr = $this->createCodeFieldsStr($fieldInfos);
		if (isset($voucher)) {
			if (empty($voucher->code_fields) || $voucher->code_fields == $codeFieldsStr) {
				$saveResult = VoucherHelper::addNewCodes($voucher, $data, $arParticipantIds);
				// result = [
				//    new => ...
				//    updated => ...
				// ]
				if (empty($voucher->code_fields)) {
					$voucher->code_fields = $codeFieldsStr;
					$voucher->save();
				}
				$status = true;
				$result = [
					'codeFields' => $codeFieldsStr,
					'codeCount' => $saveResult['codeCount'],
					'new' => $saveResult['new'],
					'existing' => $saveResult['existing']
				];
				if ($saveResult['existing'] === 0 && $saveResult['new'] > 0 && count($fieldInfos)>0) {
					$result['code_composition'] = '{code_'.$fieldInfos[0]['title'].'}';
				}
			} else {
				$result = [
					'message' => 'Mismatched Column Headers!',
					'messageTag' => 'mismatched_column_headers'
				];
			}
		} else {
			$result = [
				'message' => 'Invalid Voucher!',
				'messageTag' => 'invalid_voucher'
			];
		}
		return [
			'status' => $status,
			'result' => $result
		];
	}
	
  public function upload()
  {
    $status = false;
    $message = '';
    $result = [];

    $fields = [];
    $data = [];
    $tempFilePath = '';
    
    if (isset($_FILES['file'])) {
	    if ($_FILES["file"]["error"] <= 0) {
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
				    if (count($sheet0) > 1) {
					    $status = true;
					    $row1 = $sheet0[1];
					    for ($cellNo = 0; $cellNo < count($fields); $cellNo++) {
						    $value = $row1[$cellNo];
						    $type = getType($value);
						    if (empty($value) || $type == 'null') {
							    break;
						    }
						    if (($type == 'integer' || $type == 'double') && $value >= 36526 && $value <= 55153) {
							    $type = 'date';
							    $value = $this->excel2Date($value);
						    }
						    $fields[$cellNo]['type'] = $type;
					    }
					    $result = [
						    'fields' => $fields
					    ];
				    } else {
					    $result = [
						    'message' => 'No data!',
						    'messageTag' => 'no_data'
					    ];
				    }
			    } else {
				    $result = [
					    'message' => 'No data!',
					    'messageTag' => 'no_data'
				    ];
			    }
		    } else {
			    $result = [
				    'message' => 'No worksheet!',
				    'messageTag' => 'no_worksheet'
			    ];
		    }
	    } else {
		    $result = [
			    'message' => 'File Error!',
			    'messageTag' => 'file_error'
		    ];
	    }
    } else {
	    $result = [
		    'message' => 'No file uploaded!',
		    'messageTag' => 'no_file_uploaded'
	    ];
    }
    if ($status) {
    	// save temp upload file
	    $voucherId = \Input::get('id', 0);
	    $key = TempUploadFileHelper::newTempFile($this->user->id, $voucherId, $tempFilePath);
	    $result = array_merge([
	    	'key' => $key
	    ], $result);
    }
//
//
//
//
//
//		    for ($rowNo = 1; $rowNo <count($sheet0); $rowNo++) {
//              // check first cell if empty
//              if (!empty($sheet0[$rowNo][0])) {
//                $cells = [];
//                for ($cellNo = 0; $cellNo < count($fields); $cellNo++) {
//                  if ($cellNo < count($sheet0[$rowNo])) {
//                    $value = $sheet0[$rowNo][$cellNo];
//                    $type = getType($value);
//                    if (empty($value) || $type == 'null') {
//                      break;
//                    }
//                    if (($type == 'integer'||$type == 'double') && $value >= 36526 && $value <= 55153) {
//                      $type = 'date';
//                      $value = $this->excel2Date($value);
//                    }
//                    $fields[$cellNo]['type'] = $type;
//                    $cells[] = $value;
//                  } else {
//                    $cells[] = '';
//                  }
//                }
//                $data[] = $cells;
//              } else {
//                break;
//              }
//            }
//          }
//          $codeFieldsStr = $this->createCodeFieldsStr($fields);
//
//          // Check codeFieldsStr and any code exists
//
//
//          $id = \Input::get('id', 0);
//          $voucher = Voucher::find($id);
//          if (isset($voucher)) {
//            if (empty($voucher->code_fields) || $voucher->code_fields == $codeFieldsStr) {
//              $saveResult = VoucherHelper::addNewCodes($voucher, $data);
//              // result = [
//              //    new => ...
//              //    updated => ...
//              // ]
//              if (empty($voucher->code_fields)) {
//                $voucher->code_fields = $codeFieldsStr;
//                $voucher->save();
//              }
//              $status = true;
//              $result = [
//                'codeFields' => $codeFieldsStr,
//                'codeCount' => $saveResult['codeCount'],
//                'new' => $saveResult['new'],
//                'existing' => $saveResult['existing']
//              ];
//              if ($saveResult['existing'] === 0 && $saveResult['new'] > 0 && count($fields)>0) {
//                $result['code_composition'] = '{code_'.$fields[0]['title'].'}';
//              }
//            } else {
//              $result = [
//                'message' => 'Mismatched Column Headers!',
//                'messageTag' => 'mismatched_column_headers'
//              ];
//            }
//          } else {
//            $result = [
//              'message' => 'Invalid Voucher!',
//              'messageTag' => 'invalid_voucher'
//            ];
//          }
//        }
//        unlink($tempFilePath);
//      }
//    }

    return response()->json([
      'status' => $status,
      'result' => $result
    ]);
  }

  private function createCodeFieldsStr($fields) {
    $ar = [];
    foreach($fields as $field) {
      $ar[] = $field['title'].':'.$field['type'];
    }
    return implode('|', $ar);
  }

  private function excel2Date($excelDateValue)
  {
    $dateTimeObject = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDateValue);
    return $dateTimeObject->format('Y-m-d');
  }
  
  public function changeStatus($id, $status) {
		$code = VoucherCode::find($id);
		if (isset($code)) {
			$oldStatus = $code->status;
			$code->status = $status;
			$code->sent_on = null;
			$code->save();
		}
		return response()->json([
			'status' => true,
			'result' => []
		]);
  }
}
