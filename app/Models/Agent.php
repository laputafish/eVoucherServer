<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
  protected $fillable = [
    'user_id',
    'name',
    'alias',
    'contact',
    'tel_no',
    'fax_no',
    'web_url',
    'email',
    'remark'
  ];

  public function vouchers() {
    return $this->hasMany('App\Models\Voucher');
  }

  public function getImagesAttribute() {
    $vouchers = $this->vouchers;
    $result = [];
    if (isset($vouchers)) {
      foreach($vouchers as $voucher) {
        $result = array_merge($result,
          $voucher->medias()->pluck('id')->toArray()
        );
      }
    }
    return $result;
  }

  public function smtpServers() {
    return $this->hasMany(AgentSmtpServer::class);
  }

}
