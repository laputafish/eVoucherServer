<?php

use App\Models\Voucher;
use App\Models\VoucherCodeConfig;
use App\User;


//Route::get('user/verify/{verification_code}', 'AuthController@verifyUser');
//Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.request');
//Route::post('password/reset', 'Auth\ResetPasswordController@postReset')->name('password.reset');

//Route::namespace('ApiV2')->group(function() {
////  Route::get('templates/view/{key}', 'TemplateController@view');
//  Route::get('coupons/{key}', 'TemplateController@view');
//});

Route::get('/actions/test_email', function() {
  $data = [
    'name' => 'Dominic Lee',
    'body' => 'Email Body'
  ];

  \Mail::send('email.testMail', $data, function($message) {
    $message->to('yoovtest@gmail.com', 'Tutorials Point')->subject
    ('Laravel Basic Testing Mail');
    $message->from('yoovoffice@gmail.com', 'Yoov Coupon');
  });
  echo "Basic Email Sent. Check your inbox.";
});
Route::get('/actions/migrate_templates', 'ApiV2\VoucherTemplateController@migrateTemplates');
Route::get('/get_template_path', 'ApiV2\TestController@getTemplatePath');
Route::get('/media/image/{id}/{size?}', 'ApiV2\MediaController@showImage');
// sharing link testing
// $id is 'key' if timestamp not exists
Route::get('coupons/{id}/{timestamp?}', 'ApiV2\CouponController@showCoupon');
// Route::get('forms/{id}/{timestamp?}', 'ApiV2\CouponController@showForm');

// Preview question form
Route::get('q/{key}/{timestamp?}', 'ApiV2\FormQuestionController@showQuestionForm');

//Route::post('q', 'ApiV2\FormQuestionController@postQuestionForm');
Route::post('questions/submit', 'ApiV2\FormQuestionController@postQuestionForm');
//});
//Route::get('xquestions', function() {
//	return 'get q';
//});

Route::get('/get_data', function() {
//  for ($i = 0; $i < 2000; $i++) {
//    Voucher::create([
//      'description' => 'Voucher #'.$i,
//      'agent_id' => rand(1,3),
//      'activation_date' => date('Y-m-d'),
//      'expiry_date' => date('Y-m-d', strtotime('+20 days')),
//      'qr_code_size' => 160,
//      'status' => 'pending'
//    ]);
//  }
  $rows = Voucher::where('id', '>=', 7)->get();
  foreach($rows as $row) {
  	echo $row->agent_id.PHP_EOL."<Br/>";
//    $row->update([
//      'agent_id' => rand(1,3)
//    ]);
  }
//  $voucher = Voucher::find(1);
//  for ($i = 0; $i < 100; $i++) {
//    $codeInfo = new VoucherCode([
//      'order' => 0,
//      'code' => '0019999900100008000000g0rDtK'.str_pad($i, 4, '0', STR_PAD_LEFT),
//      'extra_fields' => '00092979|2019-11-14|2020-05-23',
//      'key' => ''
//    ]);
//    $voucher->codeInfos()->save($codeInfo);
//  }
  return 'get_data :: ok';
});



// Download question form configs
Route::get('d/{key}/{timestamp?}', 'ApiV2\FormQuestionController@downloadFormConfigs');

Route::get('create_roles', function() {
  $roles = [
    ['superuser', 'Super User'],
    ['member', 'Member'],
    ['admin', 'Admin'],
    ['user', 'User']
  ];
  foreach($roles as $role) {
    $name = $role[0];
    $title = $role[1];
    Bouncer::role()->firstOrCreate([
      'name' => $name,
      'title' => $title
    ]);
  }
});

Route::get('assign_roles', function() {
    $assigns = [
      'superuser' => ['yoovsuper@gmail.com'],
      'member' => ['yoovcoupon@gmail.com'],
      'admin' => ['yoovcoupon@gmail.com']
    ];
    foreach($assigns as $role=>$emails) {
      foreach($emails as $email) {
        $user = User::whereEmail($email)->first();
        if (isset($user)) {
          Bouncer::assign($role)->to($user);
        } else {
          echo 'user "'.$user->email.'" not assigned.'.PHP_EOL;
        }
      }
    }
    return 'assign_roles :: ok';
});

// clear all qr code composition
// qr_code_composition has been obsolate.
// it is placed in separate table for availability of multiple qrcode/barcode
//Route::get('fix_code_composition', function() {
//  $vouchers = Voucher::all();
//  foreach($vouchers as $voucher) {
//    if (!is_null($voucher->qr_code_composition) && !empty($voucher->qr_code_composition)) {
//      $qrcodeConfig = $voucher->codeConfigs()->where('code_group', 'qrcode')->first();
//      $qrcodeConfig->update([
//        'composition' => $voucher->qr_code_composition ?: '',
//        'width' => $voucher->qr_code_size,
//        'height' => $voucher->qr_code_size
//      ]);
//      $voucher->qr_code_composition = '';
//      $voucher->save();
//    }
//  }
//  return 'ok';
//});

//Route::get('init_code_configs', function() {
//  $vouchers = Voucher::all();
//  foreach($vouchers as $voucher) {
//    if ($voucher->codeConfigs()->count()<2) {
//      $qrCodeConfig = $voucher->codeConfigs()->where('code_group', 'qrcode')->first();
//      if (is_null($qrCodeConfig)) {
//        // if no qrcode config
//        $codeConfig = new VoucherCodeConfig([
//          'composition' => $voucher->qr_code_composition ?: '',
//          'width' => $voucher->qr_code_size,
//          'height' => $voucher->qr_code_size,
//          'code_group' => 'qrcode',
//          'code_type' => 'QRCODE'
//        ]);
//        $voucher->codeConfigs()->save($codeConfig);
//      } else {
//        // if new config exist
//        if (!empty(trim($voucher->qr_code_composition))) {
//          $qrCodeConfig->update([
//            'composition' => $voucher->qr_code_composition ?: '',
//            'width' => $voucher->qr_code_size,
//            'height' => $voucher->qr_code_size,
//          ]);
//        }
//      }
//      if (!is_null($voucher->qr_code_composition) && !empty($voucher->qr_code_composition)) {
//        $voucher->qr_code_composition = '';
//        $voucher->save();
//      }
//
//      $barcodeConfig = $voucher->codeConfigs()->where('code_group', 'barcode')->first();
//      if (is_null($barcodeConfig)) {
//        $barcodeConfig = new VoucherCodeConfig([
//          'composition' => '',
//          'width' => 3,
//          'height' => 67,
//          'code_group' => 'barcode',
//          'code_type' => 'C128'
//        ]);
//        $voucher->codeConfigs()->save($barcodeConfig);
//      }
//    }
//  }
//  return 'ok';
//});

// Testing purpose
//
//Route::get('vouchers', 'ApiV2\VoucherController@index');
//Route::get('vouchers/{id}', 'ApiV2\VoucherController@show');


