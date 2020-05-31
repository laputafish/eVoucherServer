<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class xxxxAgentSmtpServer extends Model {
  protected $fillable = [
    'agent_id',
    'description',
    'mail_driver',
    'mail_host',
    'mail_port',
    'mail_username',
    'mail_password',
    'mail_encryption',
    'mail_from_address',
    'mail_from_name'
  ];

  protected $appends = ['voucher_count'];
  
  public function agent() {
    return $this->belongsTo(Agent::class);
  }
  
  public function vouchers() {
  	return $this->hasMany(Voucher::class);
  }
  
  public function getVoucherCountAttribute() {
  	return $this->vouchers()->count();
  }
}
