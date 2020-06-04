<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempUploadFile extends Model {
	protected $table = 'temp_upload_files';

	protected $primaryKey = null;
	public $incrementing = false;
	
	protected $fillable = [
		'user_id',
		'key',
		'filename',
		'voucher_id',
		'type'
	];
	
}
