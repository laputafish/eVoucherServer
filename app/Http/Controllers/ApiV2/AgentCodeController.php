<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\Media;
use App\Helpers\UploadFileHelper;
use App\Imports\AgentCodeImport;

class AgentCodeController extends BaseController
{
  public function upload()
  {
    $status = false;
    $fields = [];
    $data = [];
    if (isset($_FILES['file'])) {
      if ($_FILES["file"]["error"] > 0) {
        echo "Error: " . $_FILES["file"]["error"] . "		";
      } else {
        $status = true;

        $tempFilePath = UploadFileHelper::saveTempFile($_FILES['file']);

        $ar = \Excel::toArray(null, $tempFilePath);

        if (count($ar) > 0) {
          $sheet0 = $ar[0];
          if (count($sheet0)>0) {
            $row0 = $sheet0[0];
            if (count($row0)>0) {
              // iterate on each cell
              $cells = [];
              foreach($row0 as $loopCell) {
                 $cells[] = [
                   'title' => $loopCell,
                   'type' => 'string'
                 ];
              }
              $fields = $cells;
            }
            for ($i = 1; $i <count($sheet0); $i++) {
              // check first cell if empty
              if (!empty($sheet0[$i][0])) {
                $cells = [];
                for ($j = 0; $j < count($fields); $j++) {
                  if ($j < count($sheet0[$i])) {
                    $value = $sheet0[$i][$j];
                    $type = getType($value);

//                    if ($type == 'integer') {
//                      $value = $this->excel2Date($value).': '.$value;
//                    }
                    if ($type == 'integer' && $value >= 36526 && $value <= 55153) {
                      $type = 'date';
                      $value = $this->excel2Date($value);
                    }
                    $fields[$j]['type'] = $type;
                    $cells[] = $value;
                  } else {
                    $cells[] = '';
                  }
                }
                $data[] = $cells;
//                [
//                  $sheet0[$i][0],
//                  $sheet0[$i][1],
//
//                  getType($sheet0[$i][2]).': '.$sheet0[$i][2],
//                  getType($sheet0[$i][3]).': '.$sheet0[$i][3]
////                  $this->excel2Date($sheet0[$i][2]),
////                  $this->excel2Date($sheet0[$i][3])
//                ];
              } else {
                break;
              }
            }
          }
        }
      }
    }
    return response()->json([
      'status' => $status,
      'result' => [
        'fields' => $fields,
        'data' => $data
      ]
    ]);
  }

  private function excel2Date($excelDateValue)
  {
    $dateTimeObject = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDateValue);
    return $dateTimeObject->format('Y-m-d');
  }
}
