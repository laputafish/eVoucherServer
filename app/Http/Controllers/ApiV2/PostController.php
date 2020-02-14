<?php namespace App\Http\Controllers\ApiV2;

use BaseControllers;

class PostController extends BaseController {

  public function index() {
    $data = [
      ['id'=>1, 'title'=>'Post 1'],
      ['id'=>2, 'title'=>'Post 2'],
      ['id'=>3, 'title'=>'Post 3'],
      ['id'=>4, 'title'=>'Post 4'],
      ['id'=>5, 'title'=>'Post 5'],
    ];
    return response()->json($data);
  }
}