<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;
use App\Models\Media;

class MediaController extends BaseController
{
  protected $modalName = 'Media';

  public function __construct() {
    parent::__construct();
  }
  public function upload()
  {
    $outputDir = base_path('storage/app/temp'); //"uploads/";
    // dd('FILES[file] = ' . $_FILES["file"]);
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
        $media = $this->addMedia($filename, $partialPath, 'temp');
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

        return response()->json([
          'status' => 'ok',
          'imageId' => $media->id,
          'filename' => pathinfo($originalName, PATHINFO_FILENAME),
          'fileType' => $fileType,
          'tags' => $tags
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

  public function addMedia($filename, $partialPath, $mediaType)
  {
    $media = new Media();
    $media->type = $mediaType;
    $media->path = $partialPath;
    $media->filename = $filename;
    $media->user_id = 0; // $this->user->id;
    $media->save();
    return $media;
  }


}
