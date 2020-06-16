<title>{{ $og['title'] }}</title>
<meta name="description" content="{{$og['description']}}">
@if(!empty($og['title']))
	<meta property="og:title" content="{{ $og['title'] }}" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="{{$og['url']}}" />
	<meta property="og:image" content="{{$og['imageSrc']}}" />
	@if(array_key_exists('image:width', $og))
	<meta property="og:image:width" content="{{$og['image:width']}}" />
	@endif
	@if(array_key_exists('image:height', $og))
	<meta property="og:image:height" content="{{$og['image:height']}}" />
	@endif
	<meta property="og:description" content="{{$og['description']}}" />
@endif

