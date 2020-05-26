<?php

namespace App\Helpers;

class InputObjHelper
{
	public static function getInputObjRuleAndMessages($inputObjs) {

		$inputObjTypes = InputObjType::whereIsInput(1)->select(['type','input_field_count'])->get();
		$oneFieldInputTypes = $inputObjTypes->filter(function($item) {
			return $item->input_field_count == 1;
		});
		
		$twoFieldsInputTypes = $inputObjTypes->filter(function($item) {
			return $item->input_field_count == 2;
		});
		
//		[
//			'gender',
//			'phone',
//			'simple-text',
//			'text',
//			'number',
//			'email',
//			'single-choice',
//			'multiple-choice'
//		];
		
//		$twoFieldsInputTypes =
//			[
//			'name',
//		];
		
		$ruleList = [];
		$messages = [];
		
		for($i = 0; $i < count($inputObjs); $i++) {
			$rules = [];
			$fieldNames = [];
			
			$inputObj = $inputObjs[$i];
			$inputType = $inputObj['inputType'];

			if (in_array($inputType, $oneFieldInputTypes)) {
				$fieldNames = ['field'.$i];
				if ($inputObj['required']) {
					$rules[] = 'required';
					$messages[] = [
						$fieldNames[0] . '.required' => $inputObj['name'] . ' is necessary.'
					];
				}
			} else if (in_array($inputType, $twoFieldsInputTypes)) {
				$fieldNames = [
					'field'.$i.'_0',
					'field'.$i.'_1'
				];
				if ($inputObj['required']) {
					$rules[] = 'required';
				}
				foreach($fieldNames as $fieldName) {
					$messages[] = [
						$fieldName . '.required' => $inputObj['name'] . ' is necessary.'
					];
				}
			}
			
			for ($j = 0; $j < count($fieldNames); $j++) {
				$ruleList[$fieldNames[$j]] = implode('|', $rules);
			}
		}
		return [
			'rules' => $ruleList,
			'messages' => $messages
		];
	}
}