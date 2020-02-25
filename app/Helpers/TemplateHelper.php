<?php namespace App\Helpers;

use App\Models\TemplateKey;
use App\Models\Voucher;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
    if (isset($codeConfig) && !empty($params[$codeGroup])) {
      if ($codeGroup === 'qrcode') {
        $imgBase64 = \DNS2D::getBarcodePNG($params[$codeGroup], $codeConfig['code_type'], $codeConfig['width'], $codeConfig['width']);
      } else {
        $imgBase64 = \DNS1D::getBarcodePNG($params[$codeGroup], $codeConfig['code_type'], $codeConfig['width'], $codeConfig['height']);
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

  public static function createParams($arRecord, $codeInfo) {
    $templateKeys = TemplateKey::all();
    $voucherParams = static::createVoucherParams($arRecord, $templateKeys);
    $codeParams = static::createCodeParams($codeInfo, $arRecord['code_fields'], $templateKeys);
    $basicParams = array_merge($voucherParams, $codeParams);

    $imageCodeParams = static::createImageCodeParams($basicParams, $arRecord['code_configs']);
//    $qrCodeParams = static::createQrCodeParams($basicParams, $record['qr_code_composition']);
    return array_merge($imageCodeParams, $basicParams);
  }

  public static function createVoucherParams($arRecord, $templateKeys) {
    $keyInfos = $templateKeys->filter(function($obj) {
      return in_array($obj->category, ['voucher', 'agent']);
    });

    $voucher = Voucher::find($arRecord['id']);

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

  public static function createCodeParams($row, $codeFieldInfos, $templateKeys) {
    $codeParams = [];

    $codeFields = static::getCodeFields($codeFieldInfos);
    // pcc, serial_no, ...

    $codeValues = explode('|', $row['extra_fields']);
    array_unshift($codeValues, $row['code']);

    foreach($codeFields as $i=>$field) {
      $codeParams['code_'.$field] = $codeValues[$i];
    }
    return $codeParams;
  }

  public static function getCodeFields($codeFieldInfos) {
    // codeFieldInfos:
    //    PCC:string|Serial No.:string|Start Date:date|Expiry Date:date
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

}