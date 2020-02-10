<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Models\Menu;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/qrcode', function() {

  $path = storage_path('/app/temp/tmp.png');
  return QRCode::text('QR Code Generator for Laravel!')->setSize(10)->png();
/*  QRCode::text('QR Code Generator for Laravel!')->setOutfile($path);
  return response()->file($path);*/
});

Route::resource('menu', 'ApiV2\MenuController');
Route::get('make_menu_table', 'ApiV2\MenuController@makeMenu');


// Vouchers
Route::resource('vouchers', 'ApiV2\VoucherController');

// Media
Route::post('media/upload', 'ApiV2\MediaController@upload');
Route::put('media/upload', 'ApiV2\MediaController@update');
