<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
             <meta name="viewport"
                   content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                         <meta http-equiv="X-UA-Compatible" content="ie=edge">
             <title>Document</title>
  <style>
    html {
      height: 100%;
    }
    body {
      width: 100%;
      height: 100%;
      background-color:black;
      text-align: center;
      margin: 0;
    }
    body .content {
      width: 100%;
      height: 100%;
      display: flex;
    }
    body img {
      max-width: 90%;
      height: auto;
      object-fit: contain;
      margin: 0 auto;
    }
  </style>
</head>
<body>
  <div class="content" style="color: white;text-align:center;">
    <img src="{{ asset('assets/images/error_404.jpg') }}"/>
    @if(isset($version))
    {{ $version }}
    @endif
  </div>
</body>
</html>