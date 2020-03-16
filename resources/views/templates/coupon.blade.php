<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if(!empty($ogDescription))
    <meta name="description" content="{{$ogDescription}}">
    @endif

    @if(!empty($ogUrl))
    <meta name="og:url" content="{{$ogUrl}}">
    @endif

    @if(!empty($ogImageSrc))
    <meta name="og:image" content="{{$ogImageSrc}}">
    @endif

    @if(!empty($ogTitle))
    <meta name="og:title" content="{{$ogTitle}}">
    <title>{{ $ogTitle }}</title>
    @else
    <title>Yoov Ticket</title>
    @endif

    <!-- Styles -->
</head>
<body>
    {!! $template !!}
</body>
</html>
