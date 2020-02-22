<?php namespace App\Helpers;

use App\Models\AccessKey;

class AccessKeyHelper
{
  public static function create($user, $module, $command, $params) {
    $key = str_random(60);
    AccessKey::create([
      'user_id' => $user->id,
      'module' => $module,
      'command' => $command,
      'params' => $params,
      'key' => $key
    ]);
    return $key;
  }
}
