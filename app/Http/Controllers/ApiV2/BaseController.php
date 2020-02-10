<?php namespace App\Http\Controllers\ApiV2;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as _Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BaseController extends _Controller
{
  protected $modelName = '';

  // Generated
  protected $model = null;
//  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

  public function __construct() {
    if (!empty($this->modelName)) {
      $modelClassString = "\\App\\Models\\Voucher";
      $this->model = new $modelClassString;
    }
  }
}
