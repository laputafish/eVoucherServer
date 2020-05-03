<?php namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use Illuminate\Support\Collection;

use App\Models\Voucher;
use App\Models\VoucherCode;

class FormConfigsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithColumnFormatting
{
  public function __construct($formConfigs)
  {
    $this->formConfigs = $formConfigs;
  }

//  private function getInputObjTypes($inputType, $inputObjTypes) {
//    $result = null;
//    foreach($inputObjTypes as $inputObjType) {
//      if ($inputObjType['type'] == $inputType) {
//        $result = $inputObjType;
//        break;
//      }
//    }
//    return $result;
//  }

  public function headings(): array
  {
    return [
      'type',
      'description',
      'required',
      'question/remark/image link',
      'note1',
      'note2',
      'options'
    ];
  }

  public function collection() {
    $arInputObjs = $this->formConfigs['inputObjs'];
    $excelRows = [];
    foreach($arInputObjs as $inputObj) {
//      echo '*****************'.PHP_EOL;
//
//      print_r($inputObj);
//      echo PHP_EOL.PHP_EOL;
//      continue;
      // input type
      $excelCells = [$inputObj['inputType']];
      // description (name)
      $excelCells[] = $inputObj['name'];
      // required
      $excelCells[] = $inputObj['required'];
      // question/remark/image link'
      $excelCells[] = $inputObj['question'];

      if (array_key_exists('notes', $inputObj)) {
        $excelCells[] = $inputObj['notes'];
        $excelCells[] = '';
      } else {
        // note1
        $excelCells[] = $inputObj['note1'];
        // note2
        $excelCells[] = $inputObj['note2'];
      }

      // options
      $options = $inputObj['options'];
      foreach($options as $option) {
        $excelCells[] = $option;
      }
      $excelRows[] = $excelCells;
    }
    return new Collection($excelRows);
  }

  public function columnFormats(): array
  {
    $result = [];
//    foreach ($this->codeFields as $i=>$fieldValue) {
//      $fieldType = $this->codeFields[$i]['fieldType'];
//      if ($fieldType == 'date') {
//        $result[chr(65 + $i)] = NumberFormat::FORMAT_DATE_YYYYMMDD;
//      }
//    }
    return $result;
  }

}
