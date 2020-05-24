<?php namespace App\Transformers;

use App\Models\InputObjAttribute;
use League\Fractal;

class InputObjAttributeTransformer extends Fractal\TransformerAbstract
{
  public function transform(InputObjAttribute $inputObjAttribute)
  {
    return [
      'caption' => $inputObjAttribute->caption,
      'styleName' => $inputObjAttribute->style_name,
      'optionGroup' => $inputObjAttribute->option_group.'Group'
    ];
  }
}