<?php namespace App\Helpers;

use LaravelQRCode\Facades\QRCode;

use App\Models\Media;

class MediaHelper {
  public static function checkMediaFolder($folder='app/images') {
    $mediaPath = static::getMediaFolder($folder);
    if (!file_exists($mediaPath)) {
      mkdir($mediaPath, 755, true);
    }
    return $mediaPath;
  }

  public static function getMediaFolder($folder='app/images') {
    return storage_path($folder);
  }
	
	public static function deleteMedia($param)
	{
		$media = is_numeric($param) ? Media::find($param) : $param;
		if (!is_null($media)) {
			if ($media->type == 'temp') {
				self::deleteMediaFiles('temp/' . $media->path, $media->filename);
			} else {
				self::deleteMediaFiles('image/' . $media->path, $media->filename);
				self::deleteMediaFiles('image_xs/' . $media->path, $media->filename);
				self::deleteMediaFiles('image_sm/' . $media->path, $media->filename);
			}
			$media->delete();
		}
	}
	
	
	public static function deleteMediaFiles($mediaPath, $filename)
	{
		$path = storage_path('app/' . $mediaPath);
		$filePath = $path . '/' . $filename;
		if (file_exists($filePath)) {
			unlink($filePath);
		}
		self::removeMediaPathIfEmpty($path);
	}
	
	private static function removeMediaPathIfEmpty($path)
	{
		if (file_exists($path)) {
			if (is_dir_empty($path)) {
				rmdir($path);
			}
		}
		$parentPath = dirname($path);
		if (file_exists($parentPath)) {
			if (is_dir_empty($parentPath)) {
				rmdir($parentPath);
			}
		}
	}
	
	public static function isImage($mediaObj)
	{
		$ext = strtolower(self::getMediaExt($mediaObj));
		return in_array($ext, ['png', 'jpg', 'jpeg', 'gif']);
	}
	
	public static function getMediaExt($mediaObj)
	{
		$media = is_int($mediaObj) ?
			Media::find($mediaObj) :
			$mediaObj;
		
		$result = '';
		if (isset($media)) {
			$filename = $media->filename;
			$result = pathinfo($filename, PATHINFO_EXTENSION);
		}
		return $result;
	}
	
	public static function changeMediaType($param, $type)
	{
		$media = is_numeric($param) ? Media::find($param) : $param;
		if (!is_null($media)) {
			$oldMediaType = $media->type;
			if ($oldMediaType != $type) {
				self::moveFile($media, $type);
				$media->type = $type;
				$media->save();
				if ($media->is_image) {
					self::createThumbnail($media, 'image_sm');
					self::createThumbnail($media, 'image_xs');
				}
			}
		}
	}
	
	public static function changeImageResolution($param, $width=0, $height=0) {
    if ($width==0 && $height==0) {return;}
    
    if ($width==0) {$width = $height;}
    if ($height==0) {$height = $width;}
    
		$media = is_numeric($param) ? Media::find($param) : $param;
    $imagePath = $media->filePath;
    if (file_exists($imagePath)) {
      $imageDetails = getimagesize($imagePath);
      $originalWidth = $imageDetails[0];
      $originalHeight = $imageDetails[1];
      
      $tempPath = storage_path('app/temp/'.$media->filename);
      $wPercentChanged = 0;
      $hPercentChanged = 0;
//      echo 'originalWidth = '.$originalWidth.PHP_EOL;
//      echo 'originalHeight = '.$originalHeight.PHP_EOL;
//      echo 'width = '.$width.PHP_EOL;
//      echo 'height = '.$height.PHP_EOL;
      
      if ($originalWidth > $width) {
      	$wPercentChanged = ($originalWidth - $width) / $width;
      }
      if ($originalHeight > $height) {
      	$hPercentChanged = ($originalHeight - $height) / $height;
      }

//      echo 'wPercentChanged = '.$wPercentChanged.PHP_EOL;
//      echo 'hPercentChanged = '.$hPercentChanged.PHP_EOL;
      
      if ($wPercentChanged != 0 || $hPercentChanged != 0) {
	      if ($wPercentChanged > $hPercentChanged) {
		      $newWidth = $width;
		      $newHeight = intVal($originalHeight * $newWidth / $originalWidth);
	      } else {
		      $newHeight = $height;
		      $newWidth = intVal($originalWidth * $newHeight / $originalHeight);
	      }
	
	      if ($imageDetails[2] == IMAGETYPE_GIF) {
		      $imgt = "imagegif";
		      $imgcreatefrom = "ImageCreateFromGIF";
	      }
	      if ($imageDetails[2] == IMAGETYPE_JPEG) {
		      $imgt = "imagejpeg";
		      $imgcreatefrom = "ImageCreateFromJPEG";
	      }
	      if ($imageDetails[2] == IMAGETYPE_PNG) {
		      $imgt = "imagepng";
		      $imgcreatefrom = "ImageCreateFromPNG";
	      }
	
	      if ($imgt) {
		      rename($imagePath, $tempPath);
		
		      $oldImage = $imgcreatefrom($tempPath);
		      $newImage = imagecreatetruecolor($newWidth, $newHeight);
		      imagecopyresized($newImage, $oldImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
		      $imgt($newImage, $imagePath);
		      unlink($tempPath);
	      }
      }
    }
	}
	
	public static function createThumbnail($media, $mediaPath)
	{
		$ext = strtolower(pathinfo($media->filename, PATHINFO_EXTENSION));
		if (in_array($ext, ['jpg', 'gif', 'png', 'jpeg'])) {
			$sizeW = SystemSettingHelper::get($mediaPath . '_size_w', 320);
			$sizeH = SystemSettingHelper::get($mediaPath . '_size_h', 320);
			$targetFolder = storage_path('app/' . $mediaPath . '/' . $media->path);
			
			if (!file_exists($targetFolder)) {
				mkdir($targetFolder, 0755, true);
			}
			$targetPath = $targetFolder . '/' . $media->filename;
			if (!file_exists($targetPath)) {
				$imagePath = storage_path('app/image/' . $media->path . '/' . $media->filename);
				if (file_exists($imagePath)) {
					$imageDetails = getimagesize($imagePath);
					$originalWidth = $imageDetails[0];
					$originalHeight = $imageDetails[1];
					
					if ($originalWidth > $originalHeight) {
						$newWidth = $sizeW;
						$newHeight = intval($originalHeight * $newWidth / $originalWidth);
					} else {
						$newHeight = $sizeH;
						$newWidth = intval($originalWidth * $newHeight / $originalHeight);
					}
					
					if ($imageDetails[2] == IMAGETYPE_GIF) {
						$imgt = "imagegif";
						$imgcreatefrom = "ImageCreateFromGIF";
					}
					if ($imageDetails[2] == IMAGETYPE_JPEG) {
						$imgt = "imagejpeg";
						$imgcreatefrom = "ImageCreateFromJPEG";
					}
					if ($imageDetails[2] == IMAGETYPE_PNG) {
						$imgt = "imagepng";
						$imgcreatefrom = "ImageCreateFromPNG";
					}
					
					if ($imgt) {
						$oldImage = $imgcreatefrom($imagePath);
						$newImage = imagecreatetruecolor($newWidth, $newHeight);
						imagecopyresized($newImage, $oldImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
						$imgt($newImage, $targetPath);
					}
				}
			}
			return true;
		} else
			return false;
	}
	
	private static function moveFile($param, $type)
	{
		$media = is_numeric($param) ? Media::find($param) : $param;
		
		$prefixPath = $media->type == 'temp' ?
			'temp' :
			'images';
		$newPrefixPath = $type == 'temp' ?
			'temp' :
			'images';
		
		$oldFolder = base_path('storage/app/' . $prefixPath . '/' . $media->path);
		$newFolder = base_path('storage/app/' . $newPrefixPath . '/' . $media->path);
		$oldPath = platformSlashes($oldFolder . '/' . $media->filename);
		$newPath = platformSlashes($newFolder . '/' . $media->filename);
		
		if (is_writable($oldPath)) {
			if (!file_exists(dirname($newPath))) {
				
				mkdir(dirname($newPath), 0755, true);
			}
			rename($oldPath, $newPath);
		}
		cascadePurgeFolders($oldFolder, base_path('storage/app/' . $prefixPath));
	}
	
	
	
}
