<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\Media;
use App\Models\Voucher;
use App\Models\Agent;

use App\Helpers\MediaHelper;

use Illuminate\Http\Request;

class MediaController extends BaseController
{
  protected $modelName = 'Media';

  public function __construct() {
    parent::__construct();
  }

  public function getMediaIds() {
    $query = $this->user->medias();
    if (\Input::has('scope')) {
      $scope = \Input::get('scope');
      switch($scope) {
        case 'local':
          $voucherId = \Input::get('voucherId');
          $voucher = Voucher::find($voucherId);
          $result = $voucher->medias;
          break;
        case 'voucher':
          $result = Voucher::select('id', 'description')->whereUserId($this->user->id)->with('medias')->get();
          $result = $result->map(function ($row) {
            $row->images = $row->medias;
            unset($row->medias);
            return $row;
          });
          break;
        case 'agent':
          $rows = Agent::whereUserId($this->user->id)
            ->select('id', 'name')->get();
          $result = [];
          foreach($rows as $row) {
            $result[] = [
              'id' => $row->id,
              'description' => $row->name,
              'images' => $row->images
            ];
          }
//          $result = $rows->toArray();
//          foreach($result as $record) {
//            unset($record['vouchers']);
//          }
          break;
        case 'all':
          $result = Media::whereUserId($this->user->id)->select('id')->get();
      }
    } else {
      $result = $query->get();
    }
    return response()->json([
      'status' => true,
      'result' => $result
    ]);
  }

  public function index() {
    $query = $this->user->medias();
    if (\Input::has('scope')) {
      $scope = \Input::get('scope');
      switch($scope) {
        case 'local':
          $voucherId = \Input::get('voucherId');
          $voucher = Voucher::find($voucherId);
          $result = $voucher->medias;
          break;
        case 'voucher':
          $result = Voucher::select('id', 'description')->whereUserId($this->user->id)->with('medias')->get();
          $result = $result->map(function ($row) {
            $row->images = $row->medias;
            unset($row->medias);
            return $row;
          });
          break;
        case 'agent':
          $rows = Agent::whereUserId($this->user->id)->get();
          $result = [];
          foreach($rows as $row) {
            $result[] = [
              'id' => $row->id,
              'description' => $row->name,
              'images' => $row->images
            ];
          }
//          $result = $rows->toArray();
//          foreach($result as $record) {
//            unset($record['vouchers']);
//          }
          break;
        case 'all':
          $result = Media::whereUserId($this->user->id)->select('id')->get();
      }
    } else {
      $result = $query->get();
    }
    return response()->json([
      'status' => true,
      'result' => $result
    ]);
  }

  public function uploadImage(Request $request) {
	  $outputDir = MediaHelper::checkMediaFolder();
	  if ($request->hasFile('file')) {
		  if ($request->file('file')->isValid()) {
			  $originalName = $request->file->getClientOriginalName();
			  $filename = $this->createFilename($originalName);
			  $partialPath = $this->createPartialPath($filename);
			  $outputPath = $outputDir . '/' . $partialPath . '/' . $filename; //$_FILES["file"]["name"];
			  mkdir($outputDir . '/' . $partialPath, 0777, true);
			  move_uploaded_file($_FILES["file"]["tmp_name"], $outputPath);
			
			  $scope = \Input::get('scope', 'general');
			  switch ($scope) {
          case 'general':
          case 'tinymce':
            $media = $this->addMedia($filename, $partialPath, 'image', $scope);
            break;
          case 'all':
          case 'voucher':
          case 'agent':
          default:
            $voucherId = \Input::get('voucherId');
            $media = $this->addMedia($filename, $partialPath, 'image', 'general');
            $voucher = Voucher::find($voucherId);
            $voucher->medias()->save($media);
        }
			  $fileType = pathinfo($originalName, PATHINFO_EXTENSION);
			
			  return response()->json([
				  'status' => 'ok',
				  'result' => [
					  'imageId' => $media->id,
					  'filename' => pathinfo($originalName, PATHINFO_FILENAME),
					  'fileType' => $fileType,
					  'imageUrl' => url('/media/image/'.$media->id)
				  ]
			  ]);
		  } else {
		  	return 'no';
		  }
	  } else {
		  return 'no';
	  }
  }
  
  public function uploadImage2()
  {
    $outputDir = MediaHelper::checkMediaFolder();
    if (isset($_FILES["file"])) {
      if ($_FILES["file"]["error"] > 0) {
        echo "Error: " . $_FILES["file"]["error"] . "		";
      } else {
        $originalName = $_FILES['file']['name'];
        $filename = $this->createFilename($originalName);
        $partialPath = $this->createPartialPath($filename);
        $outputPath = $outputDir . '/' . $partialPath . '/' . $filename; //$_FILES["file"]["name"];
        mkdir($outputDir . '/' . $partialPath, 0777, true);
        move_uploaded_file($_FILES["file"]["tmp_name"], $outputPath);

        $scope = \Input::get('scope', 'general');
        $media = $this->addMedia($filename, $partialPath, 'image', $scope);
        $fileType = pathinfo($originalName, PATHINFO_EXTENSION);

        return response()->json([
          'status' => 'ok',
          'result' => [
            'imageId' => $media->id,
            'filename' => pathinfo($originalName, PATHINFO_FILENAME),
            'fileType' => $fileType,
            'imageUrl' => url('/media/image/'.$media->id)
          ]
        ]);
      }
    } else {
      return 'no';
    }
  }
  public function upload()
  {
    $outputDir = MediaHelper::checkMediaFolder('app/temp'); //"uploads/";
    if (isset($_FILES["file"])) {
      //Filter the file types , if you want.
      if ($_FILES["file"]["error"] > 0) {
        echo "Error: " . $_FILES["file"]["error"] . "		";
      } else {
        $originalName = $_FILES['file']['name'];
        $filename = $this->createFilename($originalName);
        $partialPath = $this->createPartialPath($filename);
        $outputPath = $outputDir . '/' . $partialPath . '/' . $filename; //$_FILES["file"]["name"];
        mkdir($outputDir . '/' . $partialPath, 0777, true);
        move_uploaded_file($_FILES["file"]["tmp_name"], $outputPath);
        $scope = \Input::get('scope', 'general');
        $media = $this->addMedia($filename, $partialPath, 'temp', $scope);
        $fileType = pathinfo($originalName, PATHINFO_EXTENSION);

        $tags = [];
        try {
          if ($fileType == 'docx') {
            $systemTagNames = OfferDocumentHelper::getDynamicTags();
            $tagNames = [];
            $tagNames = ConversionHelper::getTags(
              MediaHelper::getMediaPath(
                $media->id));
            foreach ($tagNames as $tagName) {
              $tags[] = [
                'id' => 0,
                'name' => $tagName,
                'default' => '',
                'placeholder' => in_array($tagName, $systemTagNames) ? '(Auto)' : ''
              ];
            }
          }
        } catch( \Exception $e ) {
          abort(500);
          return;
        }
        
        $newWidth = \Input::get('width', 0);
        $newHeight = \Input::get('height', 0);
        if ($newWidth != 0 || $newHeight != 0) {
					MediaHelper::changeImageResolution($media->id, $newWidth, $newHeight);
					
	      }
	      
	      
        return response()->json([
          'status' => 'ok',
          'result' => [
            'imageId' => $media->id,
            'filename' => pathinfo($originalName, PATHINFO_FILENAME),
            'fileType' => $fileType,
            'tags' => $tags
          ]
        ]);
      }
    } else {
      return 'no';
    }
  }

  public function createFilename($filename)
  {
    // dd($filename);
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $result = date('Ymd_His') . '_' . substr((string)microtime(), 2, 8);
    return $result . '.' . $ext;
    // return 'aa' . date('Ymd His u') . '.' . $ext;
  }

  public function createPartialPath($filename)
  {
    $md5 = md5($filename);
    return substr($md5, 0, 2) . '/' . substr($md5, 2, 2);
  }

  public function addMedia($filename, $partialPath, $mediaType, $scope='general')
  {
    $media = new Media();
    $media->type = $mediaType;
    $media->path = $partialPath;
    $media->filename = $filename;
    $media->user_id = $this->user->id;
    $media->scope = $scope;
    $media->save();
    return $media;
  }

  public function getImageFileInfo($id, $size=null) {
	  $defaultImageFolder = 'images';
	  $media = Media::find($id);
	
	  $defaultImagePath = storage_path('images/blank.png');
	  $imageFileExt = 'png';
	
	  if (!is_null($media)) {
		  $ext = pathinfo($media->filename, PATHINFO_EXTENSION);
		  $pathPrefix = $media->type == 'temp' ? 'temp' : $defaultImageFolder;
		  switch (strtolower($ext)) {
			  case 'jpg':
			  case 'png':
			  case 'gif':
			  case 'jpeg':
				  if ($media->type == 'image') {
					  if (!is_null($size) && is_file(storage_path('app/images_' . $size . '/' . $media->path . '/' . $media->filename))) {
						  $pathPrefix = 'images_' . $size;
					  }
				  }
				  $filePath = storage_path('app/' . $pathPrefix . '/' . $media->path . '/' . $media->filename);
				
				  if (file_exists($filePath)) {
					  $fileContent = \Storage::get($pathPrefix . '/' . $media->path . '/' . $media->filename);
					  $imageFileExt = $ext;
				  } else {
					  $fileContent = file_get_contents($defaultImagePath);
				  }
				  break;
			  default:
				  $fileContent = file_get_content($defaultImagePath);
		  }
	  } else {
		  $fileContent = file_get_contents($defaultImagePath);
	  }
		return [
			'fileContent' => $fileContent,
			'fileExt' => $imageFileExt
		];
  }
	
	public function downloadImage($id, $size=null) {
		$imageFileInfo = $this->getImageFileInfo($id, $size);
		$headers = [
			'Content-type' => 'image/'.$imageFileInfo['fileExt'],
			'Content-Disposition' => 'attachment; filename="image.'.$imageFileInfo['fileExt'].'"',
		];
		return Response($imageFileInfo['fileContent'], 200, $headers);
	}
	
  public function showImage($id, $size=null) {
  	$imageFileInfo = $this->getImageFileInfo($id, $size);
		return Response($imageFileInfo['fileContent'], 200)->header('Content-Type', 'image/'.$imageFileInfo['fileExt']);
  }
  
  public function update($id) {
  	$media = $this->model->find($id);
  	if (!is_null($media)) {
  		$input = \Input::all();
  		$media->update($input);
	  }
	  return response()->json([
	  	'status'=>true,
		  'result' => [
		  	'message' => 'Updated Successfully.'
		  ]
	  ]);
  }
  public function destroy($id) {
		MediaHelper::deleteMedia($id);
		return response()->json([
			'status' => true,
			'result' => [
				'message' => 'Deleted Successfully.'
			]
		]);
  }

  public function purge() {
    $res = MediaHelper::purge();
    return response()->json([
      'status' => true,
      'result' => [
        'message' => $res['count'] . ' record(s) without any files is found and removed.',
        'items' => $res['items']
      ]
    ]);
  }

  public function purgeTest() {
    $test = true;
    $res = MediaHelper::purge($test);
    return response()->json([
      'status' => true,
      'result' => [
        'message' => $res['count'] . ' record(s) without any files is found and removed.',
        'items' => $res['items']
      ]
    ]);
  }
}
