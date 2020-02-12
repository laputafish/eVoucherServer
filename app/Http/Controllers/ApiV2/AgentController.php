<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;

class AgentController extends BaseController
{
  protected $modelName = 'Agent';

  public function index()
  {
    $data = $this->model->orderby('name')->get();
    return response()->json([
      'status'=>true,
      'result' => $data
    ]);
  }
}