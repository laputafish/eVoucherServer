<?php namespace App\Http\Controllers\ApiV2;

use App\Models\InputObjType;
use App\Models\InputObjAttribute;
use App\Models\InputObjGroup;

use App\Transformers\InputObjTypeTransformer;
use League\Fractal;

class InputObjsInfoController extends BaseController
{
  public function index()
  {
    $defaultPageConfigs = $this->getDefaultPageConfigs();
    $inputObjTypes = $this->getInputObjTypes();
    $attributeInfos = $this->getAttributeInfos();
    $objAttributeGroups = $this->getObjAttributeGroups();
    $iconList = $this->getIconList($inputObjTypes);

    return response()->json([
      'status' => true,
      'result' => [
        'defaultPageConfigs' => $defaultPageConfigs,
        'inputObjTypes' => $inputObjTypes,
        'attributeInfos' => $attributeInfos,
        'objAttributeGroups' => $objAttributeGroups,
        'iconList' => $iconList
      ]
    ]);
  }

  private function getIconList($inputObjTypes)
  {
//    echo 'getIconList :: inputObjTypes: ' . PHP_EOL;
//    print_r($inputObjTypes);
//    echo PHP_EOL . PHP_EOL;
    $result = [];
    foreach ($inputObjTypes as $inputObjType) {
      $result[$inputObjType['type']] = [
        'icon' => $inputObjType['icon'],
        'text' => $inputObjType['text']
      ];
    }
    return $result;
  }

  private function getObjAttributeGroups()
  {
    $inputObjGroups = InputObjGroup::orderby('order')->get();
    $result = [];
    foreach ($inputObjGroups as $inputObjGroup) {
      $type = $inputObjGroup->inputObjType->type;
      $attributeKeys = $inputObjGroup->attributes()->orderby('order')->pluck('attribute_key')->toArray();
      if (array_key_exists($type, $result)) {
        $result[$type] = [
          'caption' => $inputObjGroup->caption,
          'attributeKeys' => $attributeKeys
        ];
      } else {
        $result[$type][] = [
          'caption' => $inputObjGroup->caption,
          'attributeKeys' => $attributeKeys
        ];
      }
    }
    return $result;
  }

  private function getAttributeInfos()
  {
    $inputObjAttributes = InputObjAttribute::all();
    $result = [];
    foreach ($inputObjAttributes as $inputObjAttribute) {
      $result[$inputObjAttribute->name] = [
        'caption' => $inputObjAttribute->caption,
        'styleName' => $inputObjAttribute->style_name,
        'optionGroup' => $inputObjAttribute->option_group . 'Group'
      ];
    }
    return $result;
  }

  private function getDefaultPageConfigs()
  {
    return [
      'name' => '',
      'inputType' => 'system-page',
      'question' => '',
      'required' => true,
      'note1' => '',
      'options' => [
        'background-color:white;' .
        'color:black;' .
        'font-size:14px;' .
        'max-width:640px;' .
        'padding-top:60px;'
      ]
    ];
  }

  public function getInputObjTypes()
  {
    $inputObjTypes = InputObjType::where('enabled',1)->orderby('order')->get();
    $result = [];

    foreach ($inputObjTypes as $i => $inputObjType) {
//      echo 'i='.$i.PHP_EOL;
//      echo 'inputObjType : '.PHP_EOL;
//      print_r($inputObjType->toArray());
//      echo PHP_EOL.PHP_EOL;
      $default = [];

      $defaultStr = $inputObjType->default;
      if (!empty($defaultStr)) {
        $fields = explode('||', $defaultStr);
        foreach ($fields as $fieldInfo) {
          $keyValue = explode(':', $fieldInfo);
//          echo 'fieldInfo = '.$fieldInfo.PHP_EOL;
//          echo 'keyValue: '.PHP_EOL;
//          print_r($keyValue);
//          echo PHP_EOL.PHP_EOL;
          $key = $keyValue[0];
          $value = $keyValue[1];
          if ($key == 'options') {
            $default[$key] = explode('|', $value);
          } else {
            $default[$key] = $value;
          }
        }
      }

      $result[] = [
        'id' => (int)$inputObjType->id,
        'icon' => $inputObjType->icon,
        'newIcon' => $inputObjType->new_icon,
        'text' => $inputObjType->text,
        'label' => $inputObjType->label,
        'type' => $inputObjType->type,
        'isInput' => $inputObjType->is_input,
        'fixed' => $inputObjType->fixed,
        'default' => $default
      ];

//      echo 'i='.$i.': '.PHP_EOL;
//      echo 'result[i]: '.PHP_EOL;
//      print_r($result[$i]);
//      echo PHP_EOL.PHP_EOL;
    }
    return $result;
  }
}