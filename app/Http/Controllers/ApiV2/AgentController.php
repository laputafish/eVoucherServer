<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;

class AgentController extends BaseModuleController
{
  protected $modelName = 'Agent';
  protected $orderBy = 'name';

  protected $filterFields = [
    'name',
    'alias',
    'tel_no',
    'fax_no',
    'web_url',
    'email'
  ];

  protected $updateRules = [
    'name' => 'string',
    'alias' => 'nullable|string',
    'contact' => 'nullable|string',
    'tel_no' => 'nullable|string',
    'fax_no' => 'nullable|string',
    'web_url' => 'nullable|string',
    'email' => 'nullable|string',
    'remark' => 'nullable|string'
  ];

  public function getBlankRecord() {
    return [
      'id' => 0,
      'name' => '',
      'alias' => '',
      'contact' => '',
      'teL_no' => '',
      'fax_no' => '',
      'web_url' => '',
      'email' => '',
      'remark' => ''
    ];
  }

}