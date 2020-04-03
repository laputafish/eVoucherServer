<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
  protected $fillable = [
    'description',
    'notes',
    'agent_id',
    'activation_date',
    'expiry_date',
    'template',
    'code_fields',
    'qr_code_composition',
    'qr_code_size',
	  'sharing_title',
	  'sharing_description',
	  'sharing_image_id',
    'status'
  ];

  protected static function boot() {
    parent::boot();

    static::deleting(function($voucher) {
      $voucher->codeInfos()->delete();
      $voucher->emails()->delete();
      return true;
    });
  }

  public function codeInfos() {
    return $this->hasMany('App\Models\VoucherCode');
  }

  public function emails() {
    return $this->hasMany('App\Models\VoucherEmail');
  }

  public function agent() {
    return $this->belongsTo('App\Models\Agent');
  }

  public function codeConfigs() {
    return $this->hasMany('App\Models\VoucherCodeConfig');
  }
  
  public function sharingMedia() {
  	return $this->belongsTo(Media::class, 'sharing_image_id');
  }
}
