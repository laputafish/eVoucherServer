<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\Media;
use App\Models\Voucher;
use App\Models\TempUploadFile;
use App\Models\VoucherParticipant;

use App\Helpers\UploadFileHelper;
use App\Helpers\VoucherHelper;
use App\Helpers\TempUploadFileHelper;

use App\Imports\AgentCodeImport;

class HtmlFileController extends BaseController
{
  public function uploadZip(Request $request)
  {
    $status = false;

    if ($request->hasFile('file')) {
      if ($request->file('file')->isValid()) {



        $status = true;
      }
    }
    return response()->json([
      'status'=>$status,
      'result'=>[]
    ]);
  }

}