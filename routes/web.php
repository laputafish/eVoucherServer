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

// Agent Codes
Route::post('agent_codes/upload', 'ApiV2\AgentCodeController@upload');
Route::put('agent_codes/upload', 'ApiV2\AgentCodeController@update');

// Template Keys
Route::get('template_keys', 'ApiV2\TemplateKeyController@index');

// Agents
Route::get('agents', 'ApiV2\AgentController@index');
