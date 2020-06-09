<?php
// INPUT
//
// $content
// $emailKey
//
use App\Helpers\FileHelper;

$result = [];

//$reg = '#[\'\"]?data:image\/(\w+);base64,([^\'\"]*)[\'\"]?#';
$reg = '#data:image\/(\w+);base64,([^\'\"]*)#';

$matched = preg_match_all($reg, $content, $matches);
if ($matched !== false) {
	foreach($matches[1] as $i=>$ext) {
//		echo 'ext = '.$ext.PHP_EOL;
		$fileName = 'image_'.$i.'.'.$ext;
		//			$filePath = $folder.'/'.$fileName;
		$imageData = base64_decode($matches[2][$i]);
		//			file_put_contents($filePath, $imageData);
//				$result[] = $filePath;

		$replacement = $message->embedData($imageData, $fileName);
		$content = str_replace_first($matches[0][$i], $replacement, $content);
//		 $content = preg_replace($reg, $replacement , $content, 1);
	}
}

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
