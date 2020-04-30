<?php namespace App\Helpers;

use App\Models\TemplateKey;
use App\Models\Voucher;

class QuestionnaireHelper
{
	
	public static $DEFAULT_PAGE_CONFIG = [
		'bgColor' => '',
		'color' => '',
		'fontSize' => '',
		'maxWidth' => '',
		'paddingTop' => '',
		'style' => ''
	];
	
	public static $BLANK_INPUT_OBJ = [
		'name' => '',
		'inputType' => '',
		'question' => '',
		'required' => true,
		'options' => [],
		'notes' => '',
	];
	
	public static function getBlankFormConfigs()
	{
		return [
			'pageConfig' => [
				'bgColor' => 'white',
				'color' => 'black',
				'fontSize' => '14px',
				'maxWidth' => '640px',
				'paddingTop' => '60px'
			],
			'inputObjs' => []
		];
	}
	
	public static function getFormConfigsFromInput($formConfigs)
	{
		return self::parseConfigs($formConfigs);
	}
	
	private static function parseConfigs(&$formConfigs)
	{
		$pageConfig = $formConfigs['pageConfig'];
		$styleArray = self::combineAllPageStyles($pageConfig); //parseStyles($styleStr);
		$pageStyleOptions = keyValueArrayToStr($styleArray);
		
		self::setPageStyleInputObjOptions($formConfigs, $pageStyleOptions);
//		return json_encode($formConfigs['inputObjs']);
//		echo 'parseConfigs: ';
//		print_r($formConfigs);
		
		// finally, page config will be placed in inputObjs with tyep='page'
		//
		// formConfigs = [
		//    'inputObjs' => [
		//    ]
		// ]
		//
		unset($formConfigs['pageConfig']);
//		print_r($formConfigs);

    array_walk_recursive($formConfigs,function(&$formConfigs){$formConfigs=strval($formConfigs);});
//    echo 'QuestionnaireHelper :: parseConfigs: '.PHP_EOL;
//    print_r($formConfigs);
    $jsonFormConfigs = json_encode($formConfigs);
//    print_r($jsonFormConfigs);
		return $jsonFormConfigs;
	}
	
	private static function combineAllPageStyles($pageConfig)
	{
		$arKeyValues = [];
		$styleLine = array_key_exists('style', $pageConfig) ? $pageConfig['style'] : '';
		
		if (!empty($styleLine)) {
			$arKeyValues = self::parseStyles($styleLine);
		}
		
		self::updateStyleKeyValue($arKeyValues, 'background-color', $pageConfig, 'bgColor');
		self::updateStyleKeyValue($arKeyValues, 'color', $pageConfig, 'color');
		self::updateStyleKeyValue($arKeyValues, 'font-size', $pageConfig, 'fontSize');
		self::updateStyleKeyValue($arKeyValues, 'max-width', $pageConfig, 'maxWidth');
		self::updateStyleKeyValue($arKeyValues, 'padding-top', $pageConfig, 'paddingTop');
		self::updateStyleKeyValue($arKeyValues, 'selectedChoiceColor', $pageConfig, 'selectedChoiceColor');
		self::updateStyleKeyValue($arKeyValues, 'selectedChoiceTextColor', $pageConfig, 'selectedChoiceTextColor');
		
		return $arKeyValues;
	}
	
	private static function updateStyleKeyValue(&$ar, $styleName, $pageConfig, $configName)
	{
//		echo 'updateStyleKeyValue: '.PHP_EOL;
		if (array_key_exists($configName, $pageConfig) && !empty($pageConfig[$configName])) {
			$ar[$styleName] = $pageConfig[$configName];
//			print_r($ar);
		}
	}
	
	private static function setPageStyleInputObjOptions(&$formConfigs, $options)
	{
//		echo 'setPageStyleInputObjOptions: ';
//		print_r($options);
		
		$inputObjs = $formConfigs['inputObjs'];
		
		$result = self::$BLANK_INPUT_OBJ;
		$result['inputType'] = 'page';
		
		// Assing id:
		$inputObjCount = count($inputObjs);
		for ($i = 0; $i < $inputObjCount; $i++) {
			$inputObjs[$i]['id'] = $i;
		}
		
		// Find 'page' input obj
		$objIndex = -1;
		foreach ($inputObjs as $i => $inputObj) {
			if ($inputObj['inputType'] == 'page') {
				$objIndex = $i;
				break;
			}
		}
		
		// if no the page style input obj found,
		if ($objIndex == -1) {
//			echo 'no page style input obj found. '.PHP_EOL;
			$pageStyleInputObj = self::$BLANK_INPUT_OBJ;
			$pageStyleInputObj['id'] = $inputObjCount + 1;
			$pageStyleInputObj['inputType'] = 'page';
			$pageStyleInputObj['options'] = [$options];
			$formConfigs['inputObjs'][] = $pageStyleInputObj;
		} else {
//			echo 'page style input obj found: '.PHP_EOL;
			$formConfigs['inputObjs'][$objIndex]['options'] = [$options];
		}
	}
	
	private static function parseStyles($styleStr)
	{
		$result = [];
		$styleItems = explode(';', $styleStr);
		foreach ($styleItems as $styleItem) {
			if (!empty($styleItem)) {
				$keyValue = explode(':', $styleItem);
				if (count($keyValue) > 1) {
					$result[$keyValue[0]] = $keyValue[1];
				}
			}
		}
		return $result;
	}
	
	public static function getUserPageConfigFromInputObj(&$formConfigs)
	{
		$inputObjs = array_key_exists('inputObjs', $formConfigs) ?
			$formConfigs['inputObjs'] :
			[];
		
		if (!array_key_exists('pageConfig', $formConfigs)) {
			$pageConfig = self::$DEFAULT_PAGE_CONFIG;
			
			$pageStyleOptions = [];
			foreach ($inputObjs as $objIndex => $inputObj) {
				if ($inputObj['inputType'] == 'page') {
					$pageStyleOptions = $inputObj['options'];
					array_splice($inputObjs, $objIndex, 1);
					$formConfigs['inputObjs'] = $inputObjs;
					break;
				}
			}
			if (count($pageStyleOptions) > 0) {
				$styleKeyValueArray = self::parseStyles($pageStyleOptions[0]);
				
				$userStyles = [
					'background-color' => 'bgColor',
					'color' => 'color',
					'font-size' => 'fontSize',
					'max-width' => 'maxWidth',
					'padding-top' => 'paddingTop',
					'selectedChoiceColor' => 'selectedChoiceColor',
					'selectedChoiceTextColor' => 'selectedChoiceTextColor'
				];
				$keys = array_keys($styleKeyValueArray);
				
				foreach ($userStyles as $styleName => $attrName) {
					if (in_array($styleName, $keys)) {
						$pageConfig[$attrName] = $styleKeyValueArray[$styleName];
						unset($styleKeyValueArray[$styleName]);
					}
				}
				$pageConfig['style'] = keyValueArrayToStr($styleKeyValueArray);
			}
//	    $pageConfig['style'] = 'x'; // keyValueArrayToStr($styleKeyValueArray);
			$formConfigs['pageConfig'] = $pageConfig;
		}
	}
}