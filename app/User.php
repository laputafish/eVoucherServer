<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Silber\Bouncer\Database\HasRolesAndAbilities;

use App\Models\Media;
use App\Models\TempQuestionForm;
use App\Models\UserSetting;
use App\Models\Voucher;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRolesAndAbilities;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'is_verified'
    ];

    public function getJWTIdentifier() {
      return $this->getKey();
    }

    public function getJWTCustomClaims() {
      return [];
    }
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function medias() {
      return $this->hasMany(Media::class);
    }

    public function questionForms() {
    	return $this->hasMany(TempQuestionForm::class);
    }

    public function settings() {
    	return $this->hasMany(UserSetting::class);
    }
    
    public function assignedVouchers() {
    	return $this->belongsToMany(Voucher::class, 'voucher_authorization', 'user_id', 'voucher_id')
		    ->with('agent')
		    ->withPivot(['rights']);
    }
    
    public function vouchers() {
    	return $this->hasMany(Voucher::class);
    }
}
