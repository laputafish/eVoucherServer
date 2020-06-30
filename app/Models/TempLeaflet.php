<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempLeaflet extends Model
{
  protected $primaryKey = null;
  public $incrementing = false;

  protected $fillable = [
    'key',
    'title',
    'code_configs',
//    'template',
	  'template_path',
    'params'
  ];
	
	public function getTemplateFileAttribute() {
		return 'v'.$this->id.'.tpl';
	}
	
	public function getTemplateFullPath($appFolder) {
		$result = null;
		if (!is_null($this->template_path) && !empty($this->template_path)) {
			$result = storage_path('app/'.$appFolder.'/'.
				$this->template_path.'/'.
				'v'.$this->id.'.tpl');
		}
		return $result;
	}
}
