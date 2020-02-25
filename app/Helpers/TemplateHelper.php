<?php namespace App\Helpers;

use App\Models\TemplateKey;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TemplateHelper {
  public static function processTemplate($template, $codeConfigs, $params) {

    // Fill QR Code
//    if (!empty($params['qr_code'])) {
//      $qrCode = QrCode::margin(0)->size($qrCodeSize)->generate($params['qr_code']);
//    }
//    $template = str_replace('{qr_code}', $qrCode, $template);

    // Fill Barcode
    $barcode = '';
    $codeConfig = $codeConfigs->where('code_group', 'barcode')->first();
//    echo 'barcode width = '.$codeConfig->width.PHP_EOL;
//    echo 'barcode height = '.$codeConfig->height.PHP_EOL;
    if (isset($codeConfig) && !empty($params['barcode'])) {
      $imgBase64 = \DNS1D::getBarcodePNG($params['barcode'], $codeConfig->code_type, $codeConfig->width, $codeConfig->height);
      $barcode = '<img src="data:image/png;base64,' . $imgBase64 . '" alt="barcode" />';
    }
    $template = str_replace('{barcode}', $barcode, $template);

    // Fill  QR Code
    $qrCode = '';
    $codeConfig = $codeConfigs->where('code_group', 'qrcode')->first();
//    echo 'qrcode width = '.$codeConfig->width.PHP_EOL;
//    echo 'qrcode height = '.$codeConfig->height.PHP_EOL;
    if (isset($codeConfig) && !empty($params['qrcode'])) {
      $imgBase64 = \DNS2D::getBarcodePNG($params['qrcode'], $codeConfig->code_type, 20,20); // $codeConfig->width, $codeConfig->height);
      $qrCode = '<img src="data:image/png;base64,'.$imgBase64.'" alt="qrcode" />';
    }
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
    $codeConfig = $codeConfigs->where('code_group', $codeGroup)->first();
    if (isset($codeConfig)) {
      $valueStr = $codeConfig->composition;
      foreach ($params as $key => $value) {
        $valueStr = str_replace('{' . $key . '}', $value, $valueStr);
      }
    }
    return $valueStr;
  }

  public static function createParams($record, $codeInfo) {
    $templateKeys = TemplateKey::all();
    $voucherParams = static::createVoucherParams($record, $templateKeys);
    $codeParams = static::createCodeParams($codeInfo, $record->code_fields, $templateKeys);
    $basicParams = array_merge($voucherParams, $codeParams);

    $imageCodeParams = static::createImageCodeParams($basicParams, $record->codeConfigs);
//    $qrCodeParams = static::createQrCodeParams($basicParams, $record['qr_code_composition']);
    return array_merge($imageCodeParams, $basicParams);
  }

  public static function createVoucherParams($record, $templateKeys) {
    $keyInfos = $templateKeys->filter(function($obj) {
      return in_array($obj->category, ['voucher', 'agent']);
    });

    $result = [];
    foreach($keyInfos as $keyInfo) {
      $fieldPath = $keyInfo->field_path;
      $fieldSegs = explode('.', $fieldPath);
      $value = $record;
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