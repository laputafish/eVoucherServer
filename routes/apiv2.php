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

  //********************
  // Vouchers
  //********************
  Route::resource('vouchers', 'VoucherController');
  Route::post('vouchers/{id}/status/{status}', 'VoucherController@setStatus');
	
  // voucher participants
  Route::get('vouchers/{id}/participants', 'VoucherController@getParticipants');
  Route::delete('vouchers/{id}/participants/{participantIs}', 'VoucherController@deleteParticipant');
  Route::post('vouchers/{id}/participants/export', 'VoucherController@exportParticipants');
  Route::post('vouchers/{id}/reset_all_codes_mailing_status', 'VoucherController@resetAllCodesMailingStatus');
	Route::delete('vouchers/{id}/participants', 'VoucherController@clearParticipants');
	
	Route::post('vouchers/{id}/codes/export', 'VoucherController@export');
	Route::get('vouchers/{id}/codes', 'VoucherController@getCodes');
	Route::get('vouchers/{id}/code_summary', 'VoucherController@getCodeSummary');
  Route::put('vouchers/{voucherId}/codes/{codeId}', 'VoucherController@updateCode');
  
  Route::post('vouchers/{voucherId}/codes/{codeId}/set_status', 'VoucherController@setCodeStatus');
  Route::post('vouchers/{voucherId}/codes/{codeId}/send_email', 'VoucherController@sendEmail');
	Route::post('vouchers/{id}/codes/export', 'VoucherController@exportCodes');
  Route::delete('vouchers/{id}/codes', 'VoucherController@clearCodes');
	Route::get('vouchers/{id}/mailing_summary', 'VoucherController@getMailingSummary');
	
	Route::post('vouchers/{id}/send_emails', 'VoucherController@sendEmails');
	
  //*******************
  // Agents
  //*******************
  Route::resource('agents', 'AgentController');
  Route::get('agents/{id}/smtp_servers', 'AgentController@getSmtpServers');

  // Agent Codes
//  Route::post('agent_codes/{id}/update_views', 'AgentCodeController@updateViews');
  Route::post('agent_codes/upload', 'AgentCodeController@upload');
  Route::put('agent_codes/upload', 'AgentCodeController@update');
  Route::post('agent_codes/parse/{key}', 'AgentCodeController@parse');
	Route::post('agent_codes/{id}/change_status/{status}', 'AgentCodeController@changeStatus');
	
  // Form Questions
	Route::post('form_questions/upload', 'FormQuestionController@upload');
	Route::post('form_questions/temp/create', 'FormQuestionController@saveFormConfigs');

	// Email
	Route::post('templates/create_preview', 'TemplateController@createPreview');
	
	// Html File
  Route::post('html_file/upload_zip', 'HtmlFileController@uploadZip');
		
  // Template Keys
  Route::get('template_keys', 'TemplateKeyController@index');

  // Template
  Route::post('templates/create_temp', 'TemplateController@createTemp');

  // Email Template
	Route::post('email_templates/send_test_email', 'EmailTemplateController@sendTestEmail');
	
  // Media
  Route::post('media/upload_image', 'MediaController@uploadImage');
  Route::post('media/upload', 'MediaController@upload');
//  Route::get('media/image/{id}', 'MediaController@showImage');
  Route::resource('medias', 'MediaController');

  Route::post('smtp_server/check', 'SmtpServerController@sendTestEmail');

  Route::get('input_objs_info', 'InputObjsInfoController@index');
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
	return view('errors.404', ['version' => '2.0']);
//  $DB_HOST = '192.168.1.240';
//  $DB_PORT = 3307;
//  $DB_DATABASE = 'coupon_yoov_com';
//  $DB_USERNAME = 'coupon_yoov_com';
//  $DB_PASSWORD = 'yoovYoov';
//
//  $DB_HOST = env('DB_HOST'); // '192.168.1.240';
//  $DB_PORT = env('DB_PORT'); // 3307;
//  $DB_DATABASE = env('DB_DATABASE'); //'coupon_yoov_com';
//  $DB_USERNAME = env('DB_USERNAME'); // 'coupon_yoov_com';
//  $DB_PASSWORD = env('DB_PASSWORD'); //'yoovYoov';
//
//  echo 'HOST: '.$DB_HOST.'<br/>';
//  echo 'PORT: '.$DB_PORT.'<br/>';
//  echo 'DATABASE: '.$DB_DATABASE.'<br/>';
//  echo 'USERNAME: '.$DB_USERNAME.'<br/>';
//  echo 'PASSWORD: '.$DB_PASSWORD.'<br/>';
//return 'ok';

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

Route::get('/system/configs', 'ApiV2\SystemController@getConfigs');

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