<?php
	function get($var, $keyName, $default=null) {
		return array_key_exists($keyName, $var) ? $var[$keyName] : $default;
	}
	function getOptions($options, $default) {
		$result = $default;
		for($i = 0; $i < count($result); $i++) {
			if (count($options) > $i) {
				$result[$i] = $options[$i];
			}
		}
		return $result;
	}
	function getKeyPairs($objOptions) {
		$options = [];
		if (!empty($objOptions)) {
			$options = keyPairStrToAssocArray($objOptions[0]);
		}
		return $options;
	}
	function keyPairStrToAssocArray($str, $separator=';') {
		$segs = explode($separator, $str);
		$result = [];
		foreach($segs as $seg) {
			$keyPair = explode('=', $seg);
			$result[$keyPair[0]] = count($keyPair) > 1 ? $keyPair[1] : '';
		}
		return $result;
	}
  $paddingTop = 0;
  $bgColor = 'white';
  $color = 'white';
  $pageTitle = 'YOOV';
  $maxWidth = '640px';
  $fontSize = '14px';
  $selectedChoiceColor = 'blue';
  $selectedChoiceTextColor = 'white';

  $inputObjs = [];
  if (isset($formConfigs)) {
  	if (isset($formConfigs['pageConfig'])) {
  		$pageConfig = $formConfigs['pageConfig'];

  		$bgColor = get($pageConfig, 'bgColor', $bgColor);
  		$paddingTop = get($pageConfig, 'paddingTop', $paddingTop);
  		$pageTitle = get($pageConfig, 'pageTitle', $pageTitle);
  		$maxWidth = get($pageConfig, 'maxWidtdh', $maxWidth);
  		$fontSize = get($pageConfig, 'fontSize', $fontSize);
	  }
	  $inputObjs = get($formConfigs, 'inputObjs', $inputObjs);
  }

?><!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
      class="h-100">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	@if(isset($og)) {
		@include('templates.og', ['og'=>$og])
	@else
		<title>{{ $pageTitle }}</title>
	@endif

	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <style>
      body img.yoov-logo {
        width: 480px;
        max-width: 90%;
      }
      body p {
        margin: 15px 0;
      }

      .question-form img {
				width: 100%;
	      height: auto;
	      object-fit: contain;
      }

      .question-form .question-label {
	      line-height: 1.2;
	    }

	    .question-form .user-answer {
				line-height: 1;
	    }

	    .question-form .container > .row {
		    margin-bottom: 5px;
	    }

	    .question-form .btn-vgroup.radio-toggle .btn:first-child {
		    border-top-left-radius: 0.5rem;
		    border-top-right-radius: 0.5rem;
	    }
	    .question-form .btn-vgroup.radio-toggle .btn:last-child {
		    border-bottom-left-radius: 0.5rem;
		    border-bottom-right-radius: 0.5rem;
	    }
	    .question-form .btn-vgroup.checkbox-toggle .btn {
		    margin-bottom: 2px;
	    }
	    .question-form .btn-vgroup .btn {
		    width: 100%;
		    border-radius: 0;
		    margin-bottom: 1px;
	    }

      .question-form .btn-vgroup .btn.selected {
	      background-color: {{$selectedChoiceColor}};
	      color: {{$selectedChoiceTextColor}};
	    }
    </style>
	<!-- Styles -->
</head>
<body class="h-100 d-flex flex-column align-items-center question-form"
      style="background-color:{{ $formConfigs['pageConfig']['bgColor'] }};padding-top:{{ $paddingTop }};color:{{$color}};font-size:{{$fontSize}}">
	<form method="post">
		<input type="hidden" name="key" value="{{ $key }}"/>
	<div class="container" style="max-width:{{ $maxWidth }}">
		@foreach($inputObjs as $i=>$inputObj)
			<div class="row mt-4">
				@if($inputObj['inputType']=='simple-text')
					<div class="col-sm-5 question-label">
						@include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
					</div>
					<div class="col-sm-7 user-answer">
						<input type="text" {{$inputObj['required'] ? 'required' : ''}} class="form-control" name="field{{$i}}" id="field{{$i}}"/>
						@if(!empty($inputObj['notes']))<small>{{$inputObj['notes']}}</small>@endif
					</div>

				@elseif($inputObj['inputType']=='number')
					<div class="col-sm-5 question-label">
						@include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
					</div>
					<div class="col-sm-7 user-answer">
						<input type="number" {{$inputObj['required'] ? 'required' : ''}} class="form-control" name="field{{$i}}" id="field{{$i}}"/>
						@if(!empty($inputObj['notes']))<small>{{$inputObj['notes']}}</small>@endif
					</div>

				@elseif($inputObj['inputType']=='email')
					<div class="col-sm-5 question-label">
						@include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
					</div>
					<div class="col-sm-7 user-answer">
						<input type="email" {{$inputObj['required'] ? 'required' : ''}} class="form-control" name="field{{$i}}" id="field{{$i}}"/>
						@if(!empty($inputObj['notes']))<small>{{$inputObj['notes']}}</small>@endif
					</div>

				@elseif($inputObj['inputType']=='text')
					<div class="col-sm-5 question-label">
						@include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
					</div>
					<div class="col-sm-7 user-answer">
						<textarea {{$inputObj['required'] ? 'required' : ''}} rows="5" class="form-control" name="field{{$i}}" id="field{{$i}}"/>
						@if(!empty($inputObj['notes']))<small>{{$inputObj['notes']}}</small>@endif
					</div>

				@elseif($inputObj['inputType']=='name')
					<?php
//						$arNotes = ['', ''];
//						if (!empty($inputObj['notes'])) {
//							$notes = explode('|', $inputObj['notes']);
//							foreach($notes as $i=>$note) {
//								$arNotes[$i] = $note;
//							}
//						}
					?>
					<div class="col-sm-5 question-label">
						@include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
					</div>
					<div class="col-sm-7 user-answer">
						<div class="row">
							<div class="col-sm-6 pr-sm-1">
								<input {{$inputObj['required'] ? 'required' : ''}} type="text"
								       class="form-control" name="field{{$i}}_1" id="field{{$i}}_1"/>
								<small>First Name</small>
							</div>
							<div class="col-sm-6 pl-sm-0">
								<input {{$inputObj['required'] ? 'required' : ''}} type="text"
								       class="form-control" name="field{{$i}}_2" id="field{{$i}}_2"/>
								<small>Last Name</small>
							</div>
						</div>
					</div>
				@elseif($inputObj['inputType']=='phone')
					<?php
//					$arNotes = ['', ''];
//					if (!empty($inputObj['notes'])) {
//						$notes = explode('|', $inputObj['notes']);
//						foreach($notes as $i=>$note) {
//							$arNotes[$i] = $note;
//						}
//					}
					?>
					<div class="col-sm-5 question-label">
						@include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
					</div>
					<div class="col-sm-7 user-answer">
						<div class="row">
							<div class="col-sm-6 pr-sm-1">
								<input {{$inputObj['required'] ? 'required' : ''}} type="text"
								       class="form-control" name="field{{$i}}_1" id="field{{$i}}_1"/>
								<small>Region Code</small>
							</div>
							<div class="col-sm-6 pl-sm-0">
								<input {{$inputObj['required'] ? 'required' : ''}} type="text"
								       class="form-control" name="field{{$i}}_2" id="field{{$i}}_2"/>
								<small>Phone No.</small>
							</div>
						</div>
					</div>

				@elseif($inputObj['inputType']=='single-choice')
					<div class="col-sm-5 question-label">
						@include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
					</div>
					<div class="col-sm-7 user-answer radio-toggle btn-vgroup">
						@foreach($inputObj['options'] as $option)
						<button type="button" class="d-block btn btn-light">{{ $option }}</button>
						@endforeach
					</div>
				@elseif($inputObj['inputType']=='multiple-choice')
					<div class="col-sm-5 question-label">
						@include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
					</div>
					<div class="col-sm-7 user-answer checkbox-toggle btn-vgroup">
						@foreach($inputObj['options'] as $option)
							<button type="button" class="btn btn-light">{{ $option }}</button>
						@endforeach
					</div>
				@elseif($inputObj['inputType']=='image')
					<img src="{{$inputObj['question']}}"/>
				@elseif($inputObj['inputType']=='remark')
					<?php
						$options = getKeyPairs($inputObj['options'], []);
						$paddingTop = get($options, 'paddingTop', '10px');
						$paddingBottom = get($options, 'paddingBottom', '10px');
						$fontSize = get($options, 'fontSize', '18px');
					?>
					<div class="col-sm-12">
						<div style="padding-top:{{ $paddingTop }};padding-bottom:{{ $paddingBottom }};font-size:{{ $fontSize }}";">
							{{ $inputObj['question'] }}
						</div>
					</div>
				@elseif($inputObj['inputType']=='submit')
					<?php
						$option1 = 'background-color:orange;color:white;width:200px;font-size:18px;';
						$option2 = 'padding-top:10px;padding-bottom:10px;';
						$styleStrs = getOptions($inputObj['options'], [$option1, $option2]);
						$question = empty($inputObj['question']) ? 'Submit' : $inputObj['question'];
					?>
					<div class="col-sm-12 text-center">
						<div style="{{ $styleStrs[1] }}">
							<button type="button"
							        class="btn"
							        style="{{ $styleStrs[0] }}">
								 {{ $question }}
							</button>
						</div>
					</div>
				@endif
			</div>
		@endforeach
	</div>
	</form>
<script>
	$(document).ready(function() {
	  $('body').on('click', '.radio-toggle .btn', function() {
	    $(this).toggleClass('selected', true);
	    $(this).siblings().toggleClass('selected', false);
	  });
	  $('body').on('click', '.checkbox-toggle .btn', function() {
	    $(this).toggleClass('selected')
	  });
	})
</script>
</body>
</html>
