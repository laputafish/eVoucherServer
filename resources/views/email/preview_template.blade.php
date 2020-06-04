<?php
$isPreview = substr($key, 0, 1)=='_';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
<!--[if gte mso 9]><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
<meta content="width=device-width" name="viewport"/>
	<!--[if !mso]><!-->
<meta content="IE=edge" http-equiv="X-UA-Compatible"/>
	<!--<![endif]-->
<title>Email Preview</title>
	<!--[if !mso]><!-->
	<!--<![endif]-->
</head>
<body class="clean-body" style="margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #FFFFFF;">
@if($isPreview)
	<img src="{{ URL::asset('/assets/images/preview_mark.png') }}"
	     style="display:inline-block;position:fixed;right:0;top:0;width:200px;height:auto;"></img>
@endif
{!! $content !!}
</body>
</html>
