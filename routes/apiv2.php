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
  Route::post('verify', 'AuthController@verify');
  Route::post('reset_password', 'AuthController@resetPassword');
  Route::post('change_password', 'AuthController@changePassword');
});

Route::middleware(['auth:api'])->namespace('ApiV2')->group(function() {
//Route::namespace('ApiV2')->group(function() {

  Route::get('/', function () {return view('welcome');});

//  Auth::routes();

//  Route::get('/home', 'HomeController@index')->name('home');
  Route::get('/qrcode', function() {

    $path = storage_path('/app/temp/tmp.png');
    return QRCode::text('QR Code Generator for Laravel!')->setSize(4)->png();
    /*  QRCode::text('QR Code Generator for Laravel!')->setOutfile($path);
      return response()->file($path);*/
  });

  Route::resource('menu', 'MenuController');
  Route::get('make_menu_table', 'MenuController@makeMenu');

  // Vouchers
  Route::post('vouchers/{id}/codes/export', 'VoucherController@export');
  Route::resource('vouchers', 'VoucherController');

  Route::get('vouchers/{id}/participants', 'VoucherController@getParticipants');
  Route::post('vouchers/{id}/participants/export', 'VoucherController@exportParticipants');
	
	Route::get('vouchers/{id}/codes', 'VoucherController@getCodes');
  Route::put('vouchers/{voucherId}/codes/{id}', 'VoucherController@updateCode');
	Route::post('vouchers/{id}/codes/export', 'VoucherController@exportCodes');
  Route::delete('vouchers/{id}/codes', 'VoucherController@clearCodes');

  // Agents
  Route::resource('agents', 'AgentController');


  // Agent Codes
  Route::post('agent_codes/upload', 'AgentCodeController@upload');
  Route::put('agent_codes/upload', 'AgentCodeController@update');

  // Form Questions
	Route::post('form_questions/upload', 'FormQuestionController@upload');
	Route::post('form_questions/temp/create', 'FormQuestionController@saveFormConfigs');

		
  // Template Keys
  Route::get('template_keys', 'TemplateKeyController@index');

  // Template
  Route::post('templates/create_temp', 'TemplateController@createTemp');

  // Media
  Route::post('media/upload_image', 'MediaController@uploadImage');
  Route::post('media/upload', 'MediaController@upload');
//  Route::get('media/image/{id}', 'MediaController@showImage');
  Route::resource('medias', 'MediaController');
});

Route::namespace('ApiV2')->group(function() {

  // post data = {
  //    isTemp: true,
  //    key: "2345234324324234324"
  // }
  Route::post('templates',  'TemplateController@getTemplateHtml');
  Route::get('samples/download', 'SampleController@download');
  Route::get('/files/{key}', 'AccessKeyController@downloadFile');
});

Route::get('/info', function() {

  $DB_HOST = '192.168.1.240';
  $DB_PORT = 3307;
  $DB_DATABASE = 'coupon_yoov_com';
  $DB_USERNAME = 'coupon_yoov_com';
  $DB_PASSWORD = 'yoovYoov';

  $DB_HOST = env('DB_HOST'); // '192.168.1.240';
  $DB_PORT = env('DB_PORT'); // 3307;
  $DB_DATABASE = env('DB_DATABASE'); //'coupon_yoov_com';
  $DB_USERNAME = env('DB_USERNAME'); // 'coupon_yoov_com';
  $DB_PASSWORD = env('DB_PASSWORD'); //'yoovYoov';

  echo 'HOST: '.$DB_HOST.'<br/>';
  echo 'PORT: '.$DB_PORT.'<br/>';
  echo 'DATABASE: '.$DB_DATABASE.'<br/>';
  echo 'USERNAME: '.$DB_USERNAME.'<br/>';
  echo 'PASSWORD: '.$DB_PASSWORD.'<br/>';
return 'ok';

  $conn = new mysqli($DB_HOST.':'.$DB_PORT, $DB_USERNAME, $DB_PASSWORD);

  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  echo "Connected successfully".PHP_EOL;
  return 'ok';

  $result = 'API Version 2.0<br/>';
  if (!empty(\Input::all())) {
    foreach(\Input::all() as $key => $value) {
      $result .= $key.' = '.$value.'<Br/>';
    }
  }
  return $result;
});

Route::get('/system/config', 'ApiV2\SystemController@getConfig');

Route::get('/register/info2', function() {
  return 'register info2';
});

Route::get('/clear_cache', function() {
  \Artisan::call('cache:clear');
  echo 'Cache is cleared.'.PHP_EOL;

  \Artisan::call('route:clear');
  echo 'Route is cleared.'.PHP_EOL;

  \Artisan::call('config:clear');
  echo 'Config is cleared.'.PHP_EOL;

  \Artisan::call('view:clear');
  echo 'View is cleared.'.PHP_EOL;
});

Route::get('/updateapp', function()
{
  \Artisan::call('dump-autoload');
  echo 'dump-autoload complete';
});