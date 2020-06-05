<?php namespace App\Helpers;

class TagGroupHelper {
	public static function getTagValues($tagGroups, $voucherCode=null) {
		$result = [];
		if (is_null($voucherCode)) {
			$result = static::getDummyTagValues($tagGroups);
		} else {
			$result = static::getDataTagValues($tagGroups, $voucherCode);
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
			'code_serial' => 'abcdefghij'
		];
		
		for ($i = 0; $i < count($tagGroups); $i++) {
			switch ($tagGroups[$i]['name']) {
				case 'image_code':
				case 'voucher':
				case 'agent':
					for($j = 0; $j < count($tagGroups[$i]['tags']); $j++) {
						$tag = $tagGroups[$i]['tags'][$j];
						$result[$tag] = $DEFAULT_MAPPING[$tag];
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
						} else {
							$result[$tag] = '{' . $tag . '}';
						}
					}
					break;
			}
		}
		return $result;
	}
	
	public static function getDataTagValues($tagGroups, $voucherCode) {
		$tagList = static::tagGroupToTagList($tagGroups);
		$voucher = $voucherCode->voucher;
		$participant = $voucherCode->participant;
		$result = [];
		foreach($tagList as $tag) {
			$result[$tag] = '';
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