<?php namespace App\Helpers;

use App\Models\TemplateKey;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TemplateHelper {
  public static function processTemplate($template, $qrCodeSize, $params) {
    $qrCode = '';
    if (!empty($params['qr_code'])) {
      $qrCode = QrCode::margin(0)->size($qrCodeSize)->generate($params['qr_code']);
    }
    $template = str_replace('{qr_code}', $qrCode, $template);
    foreach($params as $key=>$value) {
      $template = str_replace( '{'.$key.'}', $value, $template);
    }

    return $template;
  }

  public static function createParams($record, $codeInfo) {
    $templateKeys = TemplateKey::all();

    $voucherParams = static::createVoucherParams($record, $templateKeys);
    $codeParams = static::createCodeParams($codeInfo, $record['code_fields'], $templateKeys);
    $basicParams = array_merge($voucherParams, $codeParams);

    $qrCodeParams = static::createQrCodeParams($basicParams, $record['qr_code_composition']);
    return array_merge($qrCodeParams, $basicParams);
  }

  public static function createQrCodeParams($params, $qrStr) {
    foreach($params as $key=>$value) {
      $qrStr = str_replace('{'.$key.'}', $value, $qrStr);
    }
    return ['qr_code' => $qrStr];
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