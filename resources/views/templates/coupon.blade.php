<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-100">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if(!empty($ogDescription))
    <meta name="description" content="{{$ogDescription}}">
    <meta name="og:description" content="{{$ogDescription}}">
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

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <style>
        body img.yoov-logo {
            width: 480px;
            max-width: 90%;
        }
        body p {
            margin: 15px 0;
        }
    </style>
    <!-- Styles -->
</head>
<body class="{{ empty($template) ? 'h-100 d-flex flex-column justify-content-center align-items-center' : '' }}">
    @if(empty($template))
        <img class="yoov-logo" src="{!! URL::asset('/images/yoov_ticket_logo.png') !!}"/>
    @else
        {!! $template !!}
    @endif
    <div class="text-center">
        <div style="width:90%;max-width:640px;margin:0 auto;">
            <script>(function(t,e,s,n){var o,a,c;t.SMCX=t.SMCX||[],e.getElementById(n)||(o=e.getElementsByTagName(s),a=o[o.length-1],c=e.createElement(s),c.type="text/javascript",c.async=!0,c.id=n,c.src=["https:"===location.protocol?"https://":"http://","widget.surveymonkey.com/collect/website/js/tRaiETqnLgj758hTBazgd_2BE466S0KQp0ZmamlSu3N0w_2B9o1pArcvC_2Bu80qOg7ihA.js"].join(""),a.parentNode.insertBefore(c,a))})(window,document,"script","smcx-sdk");</script><a style="font: 12px Helvetica, sans-serif; color: #999; text-decoration: none;" href=https://www.surveymonkey.com> Create your own user feedback survey</a>
        </div>
    </div>
</body>
</html>
