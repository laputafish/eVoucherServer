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
	  'voucher_type',
	
	  'template',
    'has_template',
	
	  'has_custom_link',
	  'custom_link_key',
	
	  'entrance_page_type',
	  'entrance_page_id',
	  'entrance_page_type_after_quota',
	  'entrance_page_id_after_quota',
	  
    'questionnaire',
    'questionnaire_fields',
    'questionnaire_configs',
    'thankyou_configs',
    'sorry_configs',
	  
	  'goal_type', // fixed, codes, none
	  'goal_count',

    'action_type_before_goal', // form_voucher, form_custom, custom
    'custom_form_key_before_goal',

	  'action_type_after_goal', // form_custom, custom, none
	  'custom_form_key_after_goal',
	  
    'code_fields',
    'code_count',
	  
    'qr_code_composition',
    'qr_code_size',
	  
	  'sharing_title',
	  'sharing_description',
	  'sharing_image_id',

    'form_sharing_title',
    'form_sharing_description',
    'form_sharing_image_id',

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
  
  public function customForms() {
  	return $this->hasMany(VoucherCustomForm::class);
  }
}
