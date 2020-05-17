<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentSmtpServer extends Model {
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

  public function agent() {
    return $this->belongsTo(Agent::class);
  }
}
