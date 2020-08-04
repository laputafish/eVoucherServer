@if(false)
	<meta name="description" content="{{$og['description']}}" />
@endif
@if(!empty($og['title']))
	<meta property="og:title" content="{{ $og['title'] }}" />
	<meta property="og:description" content="{{$og['description']}}" />
	<meta property="og:image" content="{{toHttp($og['imageSrc'])}}" />
	<meta property="og:image:secure_url" content="{{toHttps($og['imageSrc'])}}" />
	<meta property="og:url" content="{{$og['url']}}" />
	<meta property="og:site_property" content="{{ $og['title'] }}"/>
	<meta property="og:type" content="website" />
	<meta property="og:locale" content="zh_HK" />
	@if(array_key_exists('image:type', $og))
	<meta property="og:image:type" content="{{$og['image:type']}}" />
	@endif
	@if(array_key_exists('image:width', $og))
	<meta property="og:image:width" content="{{$og['image:width']}}" />
	@endif
	@if(array_key_exists('image:height', $og))
	<meta property="og:image:height" content="{{$og['image:height']}}" />
	@endif

@if(false)
	<meta content='{{ $og["title"] }}' name='og:site_name '/><meta expr:content='data:view.title.escaped' property='og:title'/>
	<meta expr:content='data:view.description.escaped' property='og:description'/>
	<b:if cond='data:view.featuredImage'>
    <meta expr:content='{{$og["imageSrc"]}}.png' property='og:image'/>
<b:elseif cond='data:widgets'/>
    <b:loop reverse='true' values='data:widgets.Blog.first.posts where (p =&gt; p.featuredImage) map (p =&gt; p.featuredImage)' var='imageUrl'>
    <meta expr:content='{{$og["imageSrc"]}}.png' property='og:image'/>
    </b:loop>
<b:elseif cond='data:blog.postImageUrl'/>
    <meta expr:content='{{$og["imageSrc"]}}.png' property='og:image'/>
</b:if>
	<meta name="description" content="{{$og['description']}}" />
	<meta property="og:url" content="{{$og['url']}}" />
	<meta property="og:title" content="{{ $og['title'] }}" />
	<meta property="og:site_property" content="{{ $og['title'] }}"/>
	<meta property="og:description" content="{{$og['description']}}" />
	<meta property="og:type" content="website" />
	<meta property="og:locale" content="zh_HK" />
	@if(array_key_exists('image:width', $og))
		<meta property="og:image:width" content="{{$og['image:width']}}" />
	@endif
	@if(array_key_exists('image:height', $og))
		<meta property="og:image:height" content="{{$og['image:height']}}" />
	@endif
@endif

	<!--  Open Graph Tags Generator for Blogger: https://bit.ly/30tJixf -->


@endif
<title>{{ $og['title'] }}</title>
