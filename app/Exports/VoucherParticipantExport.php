<?php namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Font;

use Illuminate\Support\Collection;

use App\Models\Voucher;
use App\Models\VoucherParticipant;

class VoucherParticipantExport implements FromCollection, ShouldAutoSize, WithHeadings, WithColumnFormatting
{
	public function __construct(int $voucherId)
	{
		$this->voucherId = $voucherId;
//		Font::setAutoSizeMethod(Font::AUTOSIZE_METHOD_EXACT);
	}
	
	public function headings(): array
	{
		$voucher = Voucher::find($this->voucherId);
		
		// key exists for form => voucher
		$haveKey = $voucher->action_type_before_goal === 'form_voucher';
		$keyFromCode = $voucher->goal_type === 'codes';
		
		$columnHeaders = $voucher->column_headers;
		
		$headingLabels = ['#'];
		if ($haveKey) {
			$headingLabels[] = 'Key';
		}
		foreach($columnHeaders as $columnHeader) {
			$headingLabels[] = str_replace('|', ' '.chr(13), $columnHeader);
		}
		$headingLabels[] = 'Created At';
		return $headingLabels;
	}
	
	public function collection() {
		$excelRows = [];
		$voucher = Voucher::find($this->voucherId);
		
		// key exists for form => voucher
		$haveKey = $voucher->action_type_before_goal === 'form_voucher';
		$keyFromCode = $voucher->goal_type === 'codes';

		if (isset($voucher)) {
			$inputObjs = $voucher->input_objs;
			$participants = $voucher->participants;
//			echo 'count = '.$participants->count();
			foreach($participants as $i=>$participant) {
				$excelCells = [$i + 1];
				if ($haveKey) {
					if ($keyFromCode) {
						$excelCells[] = $participant->code->key;
					} else {
						$excelCells[] = $participant->participant_key;
					}
				}
				$formContent = $participant->form_content;
				
				if (!empty($formContent)) {
//					echo 'formContentStr not empty';
//					$formContent = json_decode($formContentStr, true);
//					echo 'formContent: '."<br/>";
//					print_r($formContent);
					$fieldValues = explode('||', $formContent);
					$fieldValueCount = count($fieldValues);
//					echo 'fieldValueCount = '. $fieldValueCount."<br/>";
					foreach($inputObjs as $j=>$inputObj) {
//						echo 'inputObj.name = '.$inputObj['name']."<br/>";
//						echo 'j = '.$j."<br/>";
						if ($j < $fieldValueCount) {
							$fieldValue = $fieldValues[$j];
							switch ($inputObj['inputType']) {
								case 'simple-text':
								case 'number':
								case 'phone':
								case 'email':
								case 'text':
									$excelCells[] = $fieldValue;
									break;
								case 'single-choice':
								case 'gender':
									$index = (int) $fieldValue;
									$excelCells[] = $inputObj['options'][$index];
									break;
								case 'multiple-choice':
									$indexStrs = explode('|', $fieldValue);
									$indices = [];
									foreach($indexStrs as $str) {
										$val = (int) trim($str);
										$indices[] = $val;
									}
									for ($k = 0; $k < count($inputObj['options']); $k++) {
										$excelCells[] = in_array($k, $indices);
									}
									break;
								case 'name':
//								case 'phone':
									$fieldValueSegs = explode('|', $fieldValue);
									$fieldValueSegsCount = count($fieldValueSegs);
									$excelCells[] = $fieldValueSegsCount > 0 ? $fieldValueSegs[0] : '';
									$excelCells[] = $fieldValueSegsCount > 1 ? $fieldValueSegs[1] : '';
									break;
							}
						}
					}
				} else {
					echo 'form_content is empty';
				}
				$excelCells[] = $participant->created_at;
				$excelRows[] = $excelCells;
			}
			
		}
//		dd('ok');
		
		return new Collection($excelRows);
	}

	public function columnFormats(): array
	{
		$result = [];
//		foreach ($this->codeFields as $i=>$fieldValue) {
//			$fieldType = $this->codeFields[$i]['fieldType'];
//			if ($fieldType == 'date') {
//				$result[chr(65 + $i)] = NumberFormat::FORMAT_DATE_YYYYMMDD;
//			}
//		}
		return $result;
	}
	
}
