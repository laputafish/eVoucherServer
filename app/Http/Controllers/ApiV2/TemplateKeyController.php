<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\Media;
use App\Helpers\UploadFileHelper;
use App\Imports\AgentCodeImport;

class TemplateKeyController extends BaseController
{
  protected $modelName = 'TemplateKey';

  public function index()
  {
    $rows = $this->model->all();
    return response()->json([
      'status' => true,
      'result' => $rows
    ]);
  }
}