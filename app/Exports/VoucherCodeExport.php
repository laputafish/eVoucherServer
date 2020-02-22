<?php namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Illuminate\Support\Collection;

use App\Models\Voucher;
use App\Models\VoucherCode;

class VoucherCodeExport implements FromCollection, ShouldAutoSize, WithHeadings
{
  public function __construct(int $voucherId)
  {
    $this->voucherId = $voucherId;
  }

  public function headings(): array
  {
    $voucher = Voucher::find($this->voucherId);
    $this->codeFieldNames = $this->getCodeFieldNames($voucher->code_fields);
    $headingLabels = [];
    foreach($this->codeFieldNames as $fieldName) {
      $headingLabels[] = $fieldName;
    }
    $headingLabels[] = 'Key';
    $headingLabels[] = 'Link';
    $headingLabels[] = 'Status';
    $headingLabels[] = 'Remark';
    $headinglabels[] = 'Sent On';
    return $headingLabels;
  }

  private function getCodeFieldNames($codeFieldsStr) {
    $fieldInfos = explode('|', $codeFieldsStr);
    $result = [];
    foreach($fieldInfos as $fieldInfo) {
      $keyValue = explode(':', $fieldInfo);
      $result[] = $keyValue[0];
    }
    return $result;
  }
  public function collection() {
    $rows = VoucherCode::where('voucher_id', $this->voucherId)->get();
    $excelRows = [];
    foreach($rows as $row) {
      $excelCells = [$row->code];
      if (!empty(trim($row->extra_fields))) {
        $extraFields = explode('|', $row->extra_fields);
        foreach ($extraFields as $fieldValue) {
          $excelCells[] = $fieldValue;
        }
      }
      $excelCells[] = $row->key;
      $excelCells[] = \URL::to('/coupons/'.$row->key);
      $excelCells[] = $row->status;
      $excelCells[] = $row->remark;
      $excelCells[] = $row->sent_on;
      $excelRows[] = $excelCells;
    }
    return new Collection($excelRows);
  }
}
