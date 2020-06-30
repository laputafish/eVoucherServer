<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
  protected $fillable = [
  	'id',
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

  public function getImageIdsAttribute() {
    return array_map(function($image) {return $image->id;}, $this->images);
  }
  public function getImagesAttribute() {
    $vouchers = $this->vouchers;
    $result = [];
    if (isset($vouchers)) {
      foreach($vouchers as $voucher) {
        $result = array_merge($result,
          $voucher->medias->toArray()
        );
      }
    }
    return $result;
  }

  public function smtpServers() {
    return $this->belongsToMany(SmtpServer::class, 'agent_smtp_servers', 'agent_id', 'smtp_server_id');
  }

}
