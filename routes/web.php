<?php

use App\Models\Voucher;
use App\User;


//Route::get('user/verify/{verification_code}', 'AuthController@verifyUser');
//Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.request');
//Route::post('password/reset', 'Auth\ResetPasswordController@postReset')->name('password.reset');

//Route::namespace('ApiV2')->group(function() {
////  Route::get('templates/view/{key}', 'TemplateController@view');
//  Route::get('coupons/{key}', 'TemplateController@view');
//});

Route::get('/get_data', function() {
  for ($i = 0; $i < 2000; $i++) {
    Voucher::create([
      'description' => 'Voucher #'.$i,
      'agent_id' => rand(1,3),
      'activation_date' => date('Y-m-d'),
      'expiry_date' => date('Y-m-d', strtotime('+20 days')),
      'qr_code_size' => 160,
      'status' => 'pending'
    ]);
  }
  $rows = Voucher::where('id', '>=', 7)->get();
  foreach($rows as $row) {
    $row->update([
      'agent_id' => rand(1,3)
    ]);
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
  return 'ok';
});

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
    return 'ok';
});