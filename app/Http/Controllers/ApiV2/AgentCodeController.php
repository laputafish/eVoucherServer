<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\Media;
use App\Models\Voucher;

use App\Helpers\UploadFileHelper;
use App\Helpers\VoucherHelper;

use App\Imports\AgentCodeImport;

class AgentCodeController extends BaseController
{
  public function upload()
  {
    $status = false;
    $message = '';
    $result = [];

    $fields = [];
    $data = [];
    if (isset($_FILES['file'])) {
      if ($_FILES["file"]["error"] > 0) {
        echo "Error: " . $_FILES["file"]["error"] . "		";
      } else {

        $tempFilePath = UploadFileHelper::saveTempFile($_FILES['file']);

        $ar = \Excel::toArray(null, $tempFilePath);

        if (count($ar) > 0) {
          $sheet0 = $ar[0];
          if (count($sheet0)>0) {
            $row0 = $sheet0[0];
            // Cells of first row is heading/field names
            if (count($row0)>0) {
              // iterate on each cell
              $cells = [];
              foreach($row0 as $i=>$loopCell) {
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
            for ($rowNo = 1; $rowNo <count($sheet0); $rowNo++) {
              // check first cell if empty
              if (!empty($sheet0[$rowNo][0])) {
                $cells = [];
                for ($cellNo = 0; $cellNo < count($fields); $cellNo++) {
                  if ($cellNo < count($sheet0[$rowNo])) {
                    $value = $sheet0[$rowNo][$cellNo];
                    $type = getType($value);
                    if (empty($value) || $type == 'null') {
                      break;
                    }
                    if (($type == 'integer'||$type == 'double') && $value >= 36526 && $value <= 55153) {
                      $type = 'date';
                      $value = $this->excel2Date($value);
                    }
                    $fields[$cellNo]['type'] = $type;
                    $cells[] = $value;
                  } else {
                    $cells[] = '';
                  }
                }
                $data[] = $cells;
              } else {
                break;
              }
            }
          }
          $codeFieldsStr = $this->createCodeFieldsStr($fields);

          // Check codeFieldsStr and any code exists


          $id = \Input::get('id', 0);
          $voucher = Voucher::find($id);
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
              if ($saveResult['existing'] === 0 && $saveResult['new'] > 0 && count($fields)>0) {
                $result['code_composition'] = '{code_'.$fields[0]['title'].'}';
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
        }
        unlink($tempFilePath);
      }
    }

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
