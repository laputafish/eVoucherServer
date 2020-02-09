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