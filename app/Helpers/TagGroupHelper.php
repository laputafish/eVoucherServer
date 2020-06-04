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
		// image_code
		// agent
		// voucher
		//
	}
	
	public static function getTagValues($tagGroups, $voucherCode) {
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