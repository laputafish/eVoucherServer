<?php namespace App\Helpers;

use App\Models\VoucherCode;
use App\Models\Voucher;

class VoucherHelper {
	public static function addNewParticipant($voucher, $participant) {
		return 'not implemented yet!';
	}
	
  public static function addNewCodes($voucher, $codeArray) {
    ini_set('max_execution_time', 300 );

    $existingCodeInfos = $voucher->codeInfos;
    $existingCodes = $existingCodeInfos->pluck('code')->toArray();

    $codeInfosToAdd = array_filter($codeArray, function($item) use ($existingCodes) {
      return !in_array($item[0], $existingCodes);
    });

    $codeInfosToUpdate = array_filter($codeArray, function($item) use ($existingCodes) {
      return in_array($item[0], $existingCodes);
    });

    // Update
//    foreach($codeInfosToUpdate as $loopCodeInfo) {
//      $keyCode = array_shift($loopCodeInfo);
//
//      $existingCodeInfo = $existingCodeInfos->where('code', $keyCode)->first();
//      $key = $existingCodeInfo->key;
//      if (empty($key)) {
//        $key = newKey();
//      }
//
//      $voucher->codeInfos()->whereCode($keyCode)->update([
//        'extra_fields' => implode('|', $loopCodeInfo),
//        'key' => $key
//      ]);
//    }
//    $codeInfos = VoucherCode::whereVoucherId($voucher->id)->orderby('order')->get();
//    foreach($codeInfos as $i=>$codeInfo) {
//      $codeInfo->update(['order' => $i+1]);
//    }

	  // update code order ensure from 1 to n
    $codeInfos = VoucherCode::whereVoucherId($voucher->id)->orderby('order')->get();
    foreach($codeInfos as $i=>$codeInfo) {
      $codeInfo->update(['order' => $i + 1]);
    }

    // Append codes
    $j = count($codeInfos);
    $batchData = [];
    $now = date('Y-m-d H:i:s');

    foreach($codeInfosToAdd as $loopCodeInfo) {
      $keyCode = array_shift($loopCodeInfo);
      $batchData[] = [
        'voucher_id' => $voucher->id,
        'code' => $keyCode,
        'order' => $j++,
        'extra_fields' => implode('|', $loopCodeInfo),
        'key' => newKey(),
        'created_at' => $now,
        'updated_at' => $now
      ];
    }
    $insertData = collect($batchData);
    $chunks = $insertData->chunk(1000);
    foreach( $chunks as $chunk) {
      \DB::table('voucher_codes')->insert($chunk->toArray());
    }
    
    // update code count
    $codeCount = VoucherCode::whereVoucherId($voucher->id)->count();
    Voucher::whereId($voucher->id)->update(['code_count'=>$codeCount]);
    
    return [
      'codeCount' => $codeCount,
	    
      'new' => count($codeInfosToAdd),
      'existing' => count($codeInfosToUpdate)
    ];
  }
}