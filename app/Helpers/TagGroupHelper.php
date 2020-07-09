<?php namespace App\Helpers;

class TagGroupHelper {
	public static function getTagValues($tagGroups=null, $voucherCode=null, $participant=null, $useDummyValues=false) {
		if (is_null($tagGroups)) {
			$tagGroups = \Config::get('constants.tagGroups');
		}
		if (is_null($voucherCode)) {
			$result = static::getDummyTagValues($tagGroups);
		} else {
			$result = static::getDataTagValues($tagGroups, $voucherCode, $participant, $useDummyValues);
//			echo 'ready to getDataTagValues result count = '.count($result)."<Br/>";
//
//			return [];

		}
		return $result;
	}
	
	public static function getDummyTagValues($tagGroups) {
		$result = [];

		$DEFAULT_MAPPING = [
			'qrcode' => '1234567890',
			'barcode' => '1234567890',
			'agent_name' => 'Yoov Internet Technology Limited',
			'agent_web' => 'www.yoov.com',
			'voucher_activation_date' => '2020-01-01',
			'voucher_expiry_date' => '2099-12-31',
			'voucher_description' => 'YOOV VOUCHER',
			'code_code' => '1234567890',
			'code_serial' => 'abcdefghij',
			'code_ticket_link' => \URL::to('/coupons/1')
		];
		
		for ($i = 0; $i < count($tagGroups); $i++) {
			switch ($tagGroups[$i]['name']) {
				case 'image_code':
				case 'voucher':
				case 'agent':
				case 'code':
					for($j = 0; $j < count($tagGroups[$i]['tags']); $j++) {
						$tag = $tagGroups[$i]['tags'][$j];
						if (array_key_exists($tag, $DEFAULT_MAPPING)) {
							$result[$tag] = $DEFAULT_MAPPING[$tag];
						} else {
							$result[$tag] = getRandomWord(10);
						}
					}
					break;
				case 'participant':
					for($j = 0; $j < count($tagGroups[$i]['tags']); $j++) {
						$tag = $tagGroups[$i]['tags'][$j];
						if (strpos($tag, 'name')!==false) {
							$result[$tag] = 'Receiver';
						} else if (strpos($tag, 'email')!==false) {
							$result[$tag] = 'receiver_email@yoov.com';
						} else if (strpos($tag, 'number')!==false) {
							$result[$tag] = '12345678';
						} else if (strpos($tag, 'date') !== false) {
							$result[$tag] = '2020-01-01';
						} else if (strpos($tag, 'deadline') !== false) {
							$result[$tag] = '2099-12-31';
						} else if (strpos($tag, 'key') !== false) {
							$result[$tag] = '12345';
						} else {
							$result[$tag] = '{' . $tag . '}';
						}
					}
					break;
			}
		}
		return $result;
	}
	
	public static function getDataTagValues($tagGroups, $voucherCode, $participant=null, $useDummyValues) {
		$result = [];
		$tagList = static::tagGroupToTagList($tagGroups);
		foreach($tagList as $tag) {
			$result[$tag] = '';
		}
		$voucher = $voucherCode->voucher;
//		echo 'isset(voucher) = '.$voucher->description."<BR/>";
		// voucher
		$result['voucher_activation_date'] = $voucher->activation_date;
		$result['voucher_expiry_date'] = $voucher->expiry_date;
		$result['voucher_description'] = $voucher->description;
		// agent
		$agent = $voucher->agent;
		if (isset($agent)) {
			$result['agent_name'] = $agent->name;
			$result['agent_web'] = $agent->web_url;
		} else {
			$result['agent_name'] = '';
			$result['agent_web'] = '';
		}

		// image_code
		$codeConfigs = $voucher->codeConfigs;

		if ($useDummyValues) {
			$result['qrcode'] = 'preview_mode';
			$result['barcode'] = '1234567890';
		} else {
			if (isset($codeConfigs)) {
				$result['qrcode'] = static::getCodeConfigOfGroup($codeConfigs, 'qrcode');
				$result['barcode'] = static::getCodeConfigOfGroup($codeConfigs, 'barcode');
			} else {
				$result['qrcode'] = '';
				$result['barcode'] = '';
			}
		}

		// code
//		$codeInfoValues = $voucherCode;
		if (isset($voucher->code_fields)) {

			$codeFields = explode('|', $voucher->code_fields);
			$hasCode = false;
			$nonCodeFields = [];
			foreach ($codeFields as $i=>$keyValueStr) {
				$keyValue = explode(':', $keyValueStr);
				$key = nameToTag($keyValue[0]);
//				if ($key != 'code') {
				if ($i > 0) {
					$nonCodeFields[] = $key;
				} else {
					$hasCode = true;
				}
			}
			if ($hasCode) {
				$result['code_code'] = $voucherCode->code;
			}
			$extraFieldValues = explode('|', $voucherCode->extra_fields);
			foreach ($nonCodeFields as $i => $fieldName) {
				if ($i < count($extraFieldValues)) {
					$result['code_'.$fieldName] = $extraFieldValues[$i];
				} else {
					$result['code_'.$fieldName] = '';
				}
			}
			$result['code_ticket_link'] = \URL::to('/coupons/'.$voucherCode->key);
		}

		// participant
		if (is_null($participant)) {
			$participant = $voucherCode->participant;
		}
		
		if (isset($participant)) {
			$participantConfigs = json_decode($voucher->participant_configs, true);
			$fieldTagNames = InputObjHelper::getFieldTagNames($participantConfigs['inputObjs']);
			$fieldValues = ParticipantHelper::getFieldValues($participant->form_content);
			foreach($fieldTagNames as $i=>$fieldTagName) {
				if ($i < count($fieldValues)) {
					$result[$fieldTagName] = $fieldValues[$i];
				}
			}
		}
		return $result;
	}
	
	private static function getCodeConfigOfGroup($codeConfigs, $codeGroup) {

		$configs = $codeConfigs->filter(function($config) use($codeGroup) {
			return $config->code_group == $codeGroup;
		});
//		print_r($configs->toArray());
//		echo 'xxcount = '.$codeConfigs->count();
//		return '';

    $result = '';
    if (isset($configs)) {
      $first = $configs->first()->toArray();
      $result = $first['composition'];
    }
		return $result;
	}
	
	public static function tagGroupToTagList($tagGroups) {
		$result = [];
		foreach($tagGroups as $tagGroup) {
			foreach($tagGroup['tags'] as $tag) {
				$result[] = $tag;
			}
		}
		return $result;
	}
}