<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpServer extends Model {
  protected $fillable = [
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

  public function agent() {
    return $this->belongsToMany(Agent::class, 'agent_smtp_servers', 'smtp_server_id', 'agent_id');
  }

  public function voucher() {
    return $this->belongsToMany(Voucher::class, 'voucher_smtp_servers', 'smtp_server_id', 'voucher_id');
  }

}
