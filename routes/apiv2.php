<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//
//// Default
//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
//
//Route::middleware('auth:api')->get('/posts', function() {
//  return 'ok';
//});

//Route::post('register', 'AuthController@register');
//Route::post('login', 'AuthController@login');
//Route::post('recover', 'AuthController@recover');
//
//Route::group(['middleware' => ['jwt.auth']], function() {
//  Route::get('logout', 'AuthController@logout');
//
//  Route::get('test', function(){
//    return response()->json(['foo'=>'bar']);
//  });
//});

Route::group(['prefix' => 'auth'], function ($router) {
  Route::post('login', 'AuthController@login');
  Route::post('logout', 'AuthController@logout');
  Route::post('refresh', 'AuthController@refresh');
  Route::post('me', 'AuthController@me');
  Route::post('register', 'AuthController@register');
});

//Route::middleware(['auth:api'])->namespace('ApiV2')->group(function() {
Route::namespace('ApiV2')->group(function() {

  Route::get('/', function () {return view('welcome');});

//  Auth::routes();

  Route::get('/home', 'HomeController@index')->name('home');
  Route::get('/qrcode', function() {

    $path = storage_path('/app/temp/tmp.png');
    return QRCode::text('QR Code Generator for Laravel!')->setSize(4)->png();
    /*  QRCode::text('QR Code Generator for Laravel!')->setOutfile($path);
      return response()->file($path);*/
  });

  Route::resource('menu', 'MenuController');
  Route::get('make_menu_table', 'MenuController@makeMenu');


  // Vouchers
  Route::resource('vouchers', 'VoucherController');

  // Agent Codes
  Route::post('agent_codes/upload', 'AgentCodeController@upload');
  Route::put('agent_codes/upload', 'AgentCodeController@update');

  // Template Keys
  Route::get('template_keys', 'TemplateKeyController@index');

  // Agents
  Route::get('agents', 'AgentController@index');

  // Template
  Route::post('templates/create_temp', 'TemplateController@createTemp');
  Route::get('templates/view/{key}', 'TemplateController@view');

});