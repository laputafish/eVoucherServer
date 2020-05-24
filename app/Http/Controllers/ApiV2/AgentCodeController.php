<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\Media;
use App\Models\Voucher;
use App\Models\TempUploadFile;

use App\Helpers\UploadFileHelper;
use App\Helpers\VoucherHelper;
use App\Helpers\TempUploadFileHelper;

use App\Imports\AgentCodeImport;

class AgentCodeController extends BaseController
{
	public function parse($key)
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
		TempUploadFileHelper::removeUserTempFiles($this->user->id);
		
		return response()->json($res);
	}

	private function moveCodeColumnToFirst(&$fields, &$data, $codeIndex) {
	
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
	
	// if fieldInfos contains type of participant, all will be moved to code-other type
	private function updateVoucherCodes($voucher, $data, $fieldInfos) {
		$status = false;
		
		$codeFieldsStr = $this->createCodeFieldsStr($fieldInfos);
		if (isset($voucher)) {
			if (empty($voucher->code_fields) || $voucher->code_fields == $codeFieldsStr) {
				$saveResult = VoucherHelper::addNewCodes($voucher, $data);
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
}
