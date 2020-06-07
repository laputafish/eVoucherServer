<?php
// INPUT
//
// $content
// $emailKey
//
use App\Helpers\FileHelper;

	//echo 'htmlEmail.blade.php'.PHP_EOL;
//	function extractImageFiles(&$content, &$message, $emailKey) {
	  echo 'extractImageFiles begins'.PHP_EOL;
		$result = [];

	//	$folder = storage_path('app/temp/email_sending/'.$emailKey);
	//	FileHelper::checkCreateFolder($folder);

		$reg = '#[\'\"]?data:image\/(\w+);base64,([^\'\"]*)[\'\"]?#';
	//	$reg = '#[\'\"]?data:image\/(\w+);base64,([^\'\"]*)[\'\"]?#';

		$matched = preg_match_all($reg, $content, $matches);
	//		print_r($matches[1]);
		if ($matched !== false) {
			foreach($matches[1] as $i=>$ext) {
				$fileName = 'image_'.$i.'.'.$ext;
	//			$filePath = $folder.'/'.$fileName;
				$imageData = base64_decode($matches[2][$i]);
	//			file_put_contents($filePath, $imageData);
//				$result[] = $filePath;

				$replacement = $message->embedData($imageData, $fileName);
				$content = preg_replace($reg, $replacement , $content, 1);
			}
		}
	//  echo 'extractImageFiles ends'.PHP_EOL;
//		return $result;
//	}

//extractImageFiles($content, $message, $emailKey);
//for($i = 0; $i < count($imageFileNames); $i++) {
//	$content = str_replace('{image_'.$i.'}', $message->embed($imageFileNames[$i]), $content);
//
//}
?><html>
<body>
<?php
//for($i = 0; $i < count($imageFileNames); $i++) {
//	echo $imageFileNames[$i].PHP_EOL."<br/>";
//	echo $message->embed($imageFileNames[$i]).PHP_EOL."<br/>";
//}
?>
{!! $content !!}
</body>
</html>
