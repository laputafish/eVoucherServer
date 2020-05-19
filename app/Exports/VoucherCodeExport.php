<?php namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use Illuminate\Support\Collection;

use App\Models\Voucher;
use App\Models\VoucherCode;

class VoucherCodeExport implements FromCollection, ShouldAutoSize, WithHeadings, WithColumnFormatting
{
  public function __construct(int $voucherId)
  {
    $this->voucherId = $voucherId;
  }
  
  public function headings(): array
  {
    $voucher = Voucher::find($this->voucherId);
    
	  // key exists for form => voucher
	  $isFormType = $voucher->voucher_type === 'form';
	  $haveKey = $voucher->action_type_before_goal === 'form_voucher';
	  
	  $keyFromCode = $voucher->goal_type === 'codes';
    $this->codeFields = $this->getCodeFields($voucher->code_fields);
    
    


    
    $headingLabels = [];
    foreach($this->codeFields as $codeField) {
      $headingLabels[] = $codeField['fieldName'];
    }
    $headingLabels[] = '';
    $headingLabels[] = 'Key';
    $headingLabels[] = 'Link';
    $headingLabels[] = 'Status';
    $headingLabels[] = 'Remark';
    $headingLabels[] = 'Sent On';
    
    if ($isFormType && $haveKey && $keyFromCode) {
    	$headingLabels[] = 'Participant';
    }
    return $headingLabels;
  }

  private function getCodeFields($codeFieldsStr) {
    $fieldInfos = explode('|', $codeFieldsStr);
    $result = [];
    foreach($fieldInfos as $fieldInfo) {
      $keyValue = explode(':', $fieldInfo);
      $result[] = [
        'fieldName' => $keyValue[0],
        'fieldType' => $keyValue[1]
      ];
    }
    return $result;
  }
  public function collection() {
    $rows = VoucherCode::where('voucher_id', $this->voucherId)->get();
    $excelRows = [];
	
	  $voucher = Voucher::find($this->voucherId);
	  // key exists for form => voucher
	  $isFormType = $voucher->voucher_type === 'form';
	  $haveKey = $voucher->action_type_before_goal === 'form_voucher';
	  $keyFromCode = $voucher->goal_type === 'codes';
	  
    foreach($rows as $row) {
      $excelCells = [$row->code];
      if (!empty(trim($row->extra_fields))) {
        $extraFields = explode('|', $row->extra_fields);
        foreach ($extraFields as $i=>$fieldValue) {
        	if (count($this->codeFields)>$i+1) {
		        $fieldType = $this->codeFields[$i + 1]['fieldType'];
		        if ($fieldType == 'date') {
			        $dt = date_create_from_format('Y-m-d', $fieldValue);
			        //            $dt = strtotime($fieldValue);
			        //            $excelCells[] = 25569 + ($dt / 86400);
			        //            $excelCells[] = PHPExcel_Shared_Date::PHPToExcel($dt);
			        $excelCells[] = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dt);
		        } else {
			        $excelCells[] = $fieldValue;
		        }
	        } else {
        		break;
	        }
        }
      }
      $excelCells[] = '';
      $excelCells[] = $row->key;
      $excelCells[] = \URL::to('/coupons/'.$row->key);
      $excelCells[] = $row->status;
      $excelCells[] = $row->remark;
      $excelCells[] = $row->sent_on;
      
      if ($haveKey && $keyFromCode) {
      	if (isset($row->participant)) {
		      $excelCells[] = $row->participant->name . ' (' . $row->participant->email . ')';
	      } else {
      		$excelCells[] = '';
	      }
      }
      
      $excelRows[] = $excelCells;
    }
    return new Collection($excelRows);
  }

  public function columnFormats(): array
  {
    $result = [];
    foreach ($this->codeFields as $i=>$fieldValue) {
      $fieldType = $this->codeFields[$i]['fieldType'];
      if ($fieldType == 'date') {
        $result[chr(65 + $i)] = NumberFormat::FORMAT_DATE_YYYYMMDD;
      }
    }
    return $result;
  }

}
