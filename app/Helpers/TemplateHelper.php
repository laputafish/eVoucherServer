<?php namespace App\Helpers;

use App\Models\TemplateKey;
use App\Models\Voucher;

class TemplateHelper {
  private static function findCodeConfig($codeConfigs, $codeGroup) {
    $result = null;
    for($i = 0; $i < count($codeConfigs); $i++) {
      if ($codeConfigs[$i]['code_group'] == $codeGroup) {
        $result = $codeConfigs[$i];
        break;
      }
    }
    return $result;
  }

  private static function getCodeImage($codeConfigs, $params, $codeGroup) {
    $result = '';
    $codeConfig = static::findCodeConfig($codeConfigs, $codeGroup);
    $codeColor = '0,0,0';
//    if (!empty(trim($codeConfig['code_color']))) {
//    	$codeColor = trim($codeConfig['code_color']);
//    }
    $arColors = explode(',', $codeColor);
    if (isset($codeConfig) && !empty($params[$codeGroup])) {
      if ($codeGroup === 'qrcode') {
        $imgBase64 = \DNS2D::getBarcodePNG($params[$codeGroup], $codeConfig['code_type'], $codeConfig['width'], $codeConfig['width'], $arColors);
      } else {
        $imgBase64 = \DNS1D::getBarcodePNG($params[$codeGroup], $codeConfig['code_type'], $codeConfig['width'], $codeConfig['height'], $arColors);
      }
      $result = '<img src="data:image/png;base64,' . $imgBase64 . '" alt="'.$codeGroup.'" />';
    }
    return $result;
  }

  public static function processTemplate($template, $codeConfigs, $params) {

    // Fill QR Code
//    if (!empty($params['qr_code'])) {
//      $qrCode = QrCode::margin(0)->size($qrCodeSize)->generate($params['qr_code']);
//    }
//    $template = str_replace('{qr_code}', $qrCode, $template);

    // Fill Barcode
    $barcode = static::getCodeImage($codeConfigs, $params, 'barcode');
    $template = str_replace('{barcode}', $barcode, $template);

    // Fill  QR Code
    $qrCode = static::getCodeImage($codeConfigs, $params, 'qrcode');
    $template = str_replace('{qrcode}', $qrCode, $template);

    // Fill fields
    foreach($params as $key=>$value) {
      $template = str_replace( '{'.$key.'}', $value, $template);
    }
//return 'ok';
    return $template;
  }

  public static function createQrCodeParams($params, $qrStr) {
    foreach($params as $key=>$value) {
      $qrStr = str_replace('{'.$key.'}', $value, $qrStr);
    }
    return ['qr_code' => $qrStr];
  }

  public static function createImageCodeParams($params, $codeConfigs)
  {
    // barcode
    return [
      'qrcode' => static::createImageCodeCompositionValue($params, $codeConfigs, 'qrcode'),
      'barcode' => static::createImageCodeCompositionValue($params, $codeConfigs, 'barcode')
    ];
  }

  public static function createImageCodeCompositionValue($params, $codeConfigs, $codeGroup) {
//    echo 'codeGroup = '.$codeGroup.PHP_EOL;
//    echo 'count = '.$codeConfigs->count();
//    return '';

    $valueStr = '';
    $codeConfig = null;
    for($i = 0; $i < count($codeConfigs); $i++) {
      if ($codeConfigs[$i]['code_group'] == $codeGroup) {
        $codeConfig = $codeConfigs[$i];
        break;
      }
    }
    if (isset($codeConfig)) {
      $valueStr = $codeConfig['composition'];
      foreach ($params as $key => $value) {
        $valueStr = str_replace('{' . $key . '}', $value, $valueStr);
      }
    }
    return $valueStr;
  }

  public static function createParams($arVoucherFields, $codeInfo=null) {
    $templateKeys = TemplateKey::all(); // basic keys
	  // voucher_expiry_date
	  // voucher_description
	  // voucher_activation_date
	  // qrcode
	  // barcode
	  // agent_web
	  // agent_name
	  
    $voucherParams = static::createVoucherParams($arVoucherFields, $templateKeys);
    $codeParams = static::createCodeParams($codeInfo, $arVoucherFields['code_fields'], $templateKeys);
    $allVoucherCodeKeyValues = array_merge($voucherParams, $codeParams);

    $imageCodeParams = static::createImageCodeParams($allVoucherCodeKeyValues, $arVoucherFields['code_configs']);
    return array_merge($imageCodeParams, $allVoucherCodeKeyValues);
  }

  public static function createVoucherParams($arVoucherFields, $templateKeys) {
    $keyInfos = $templateKeys->filter(function($obj) {
      return in_array($obj->category, ['voucher', 'agent']);
    });

    $voucher = Voucher::find($arVoucherFields['id']);

    $result = [];
    foreach($keyInfos as $keyInfo) {
      $fieldPath = $keyInfo->field_path;
      $fieldSegs = explode('.', $fieldPath);
      $value = $voucher;
      for($i = 0; $i < count($fieldSegs); $i++) {
        $value = $value[$fieldSegs[$i]];
      }
      $result[$keyInfo->key] = $value;
    }
    return $result;
  }

  public static function createCodeParams($voucherCode, $codeFieldInfos, $templateKeys) {
    $codeParams = [];

    $codeFields = static::getCodeFields($codeFieldInfos);
    // pcc, serial_no, ...

	  $codeValues = [];
	  if (!is_null($voucherCode)) {
		  $codeValues = explode('|', $voucherCode['extra_fields']);
		  array_unshift($codeValues, $voucherCode['code']);
	  }
    
    foreach($codeFields as $i=>$field) {
		  $codeParams['code_' . $field] = empty($codeValues) ? '' : $codeValues[$i];
    }
    
    return $codeParams;
  }

  public static function getCodeFields($codeFieldInfos) {
    // codeFieldInfos:
    //   PCC:string|Serial No.:string|Start Date:date|Expiry Date:date
    //
    $fields = [];
    $fieldGroups = explode('|', $codeFieldInfos);
    foreach($fieldGroups as $fieldGroup) {
      $keyValue = explode(':', $fieldGroup);
      $fields[] = static::str2token($keyValue[0]);
    }
    return $fields;
  }

  private static function str2token($label) {
    $str = preg_replace("/[^A-Za-z0-9 ]/", '', strtolower($label));
    return str_replace(' ', '_', $str);
  }
  
  public static function createParamsFromInputObjs($plainKeyValues, $inputObjs) {
  	$values = explode('||', $plainKeyValues);
    $result = [];
    foreach($inputObjs as $i=>$inputObj) {
    	$fieldName = str_replace(' ', '_', strtolower($inputObj['name']));
    	$result[$fieldName] = $values[$i];
		}
		return $result;
	}

	public static function embedImages($htmlContent, $folder) {
  	// replace img src
		$reg = '/[\'\"]+(images\/[^\'\"]*)[\'\"]+/';
//  	$reg = '/[\'\"](images\/[^\'\"]*)[\'\"]/';
  	$matched = preg_match_all($reg, $htmlContent, $matches);
//  	echo 'matched = '.$matched.PHP_EOL;
  	
  	$result = $htmlContent;
  	if ($matched) {
  		foreach($matches[1] as $i=>$match) {
//  			echo '#'.$i.': match = '.$match.PHP_EOL;
  			$result = static::embedBase64($result, $match, $folder);
		  }
	  }
  	
    return $result;
	}
	
	public static function embedBase64($content, $imagePartialPath, $folder) {
  	$imagePath = $folder.'/'.$imagePartialPath;
  	$ext = pathinfo($imagePath, PATHINFO_EXTENSION);
  	$imageBlob = file_get_contents($imagePath);
  	$imageRaw = 'data:image/'.$ext.';base64,'.base64_encode($imageBlob);
  	return str_replace($imagePartialPath, $imageRaw, $content);
	}
	
	public static function extractContent($content, $tag) {
  	$reg = '/<'.$tag.'[^>]*>(.*?)<\/'.$tag.'>/';
  	$matched = preg_match_all($reg, $content, $matches);
  	
  	$result = '';
  	if ($matched) {
  		$result = $matches[1][0];
	  }
	  return $result;
	}
	
	public static function extractStyles($content) {
  	$reg = '/<style[^>]*>(.*?)<\/style>/';
  	$matched = preg_match_all($reg, $content, $matches);
  	
  	$result = '';
  	if ($matched) {
  		$result = '';
  		foreach($matches[0] as $match) {
  			$result .= $match;
		  }
	  }
	  return $result;
	}
	
	public static function applyTags($template, $tagValues, $codeConfigs=null) {
    if (is_null($codeConfigs)) {
    	$codeConfigs = [
    		[
    			'code_group' => 'qrcode',
			    'code_type' => 'QRCODE',
			    'code_color' => '0,0,0',
			    'width' => 7,
			    'height' => 7
		    ],
    		[
    			'code_group' => 'barcode',
			    'code_type' => 'C128',
			    'code_color' => '0,0,0',
			    'width' => 3,
			    'height' =>  67
		    ],
	    ];
    }
    
    return static::processTemplate($template, $codeConfigs, $tagValues);
	}
}