<?php namespace App\Transformers;

use App\Models\InputObjType;
use League\Fractal;

class InputObjTypeTransformer extends Fractal\TransformerAbstract
{
  public function transform(InputObjType $inputObjType)
  {
    $defaultStr = $inputObjType->default;
    $fields = explode('||', $defaultStr);
    $default = [];

    foreach($fields as $fieldInfo) {
      $keyValue = explode(':', $fieldInfo);
      $key = $fieldInfo[0];
      $value = $fieldInfo[1];
      if ($key == 'options') {
        $default[$key] = explode('|', $value);
      } else {
        $default[$key] = $value;
      }
    }

    return [
      'id' => (int) $inputObjType->id,
      'icon' => $inputObjType->icon,
      'newIcon' => $inputObjType->new_icon,
      'text' => $inputObjType->text,
      'label' => $inputObjType->label,
      'type' => $inputObjType->type,
      'isInput' => $inputObjType->is_input,
      'fixed' => $inputObjType->fixed,
      'default' => $default
    ];
  }
}
