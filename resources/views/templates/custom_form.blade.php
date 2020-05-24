<?php
//$KEY_TO_STYLE_NAMES = [
//  'bgColor' => 'background-color',
//  'color' => 'color',
//  'fontSize' => 'font-size',
//  'maxWidth' => 'max-width',
//  'paddingTop' => 'padding-top',
//];

//************
// Routines
//************
function fillArray($ar, $count, $default)
{
  $result = [];
  for ($i = 0; $i < $count; $i++) {
    $result[] = $default;
  }
  for ($i = 0; $i < count($ar); $i++) {
    $result[$i] = $ar[$i];
  }
  return $result;
}

function getInputOptions($inputOptions)
{
  $options = ['', ''];
  foreach ($inputOptions as $i => $option) {
    $options[$i] = $option;
  }
  return $options;
}

function strToKeyValues($str, $separator = ';')
{
  $result = [];
  if (isset($str)) {
    $segs = explode($separator, $str);
    foreach ($segs as $seg) {
      if (!empty($seg)) {
        $keyPair = explode(':', $seg);
        if (count($keyPair) > 1) {
          $result[$keyPair[0]] = $keyPair[1];
        }
      }
    }
  }
  return $result;
}

function keyValuesToStr($keyValues)
{
  $result = '';
  foreach ($keyValues as $key => $value) {
    $result .= $key . ':' . $value . ';';
  }
  return $result;
}
function style_merge($defaultStr, $userStyleStr)
{
  $defaultKeyValues = strToKeyValues($defaultStr);
  $userKeyValues = strToKeyValues($userStyleStr);

  $updatedKeyValues = array_merge($defaultKeyValues, $userKeyValues);
  return keyValuesToStr($updatedKeyValues);
}

function get($keyValues, $keyName, $default = null)
{
  return array_key_exists($keyName, $keyValues) ? $keyValues[$keyName] : $default;
}
function getOptions($options, $default)
{
  $result = $default;
  for ($i = 0; $i < count($result); $i++) {
    if (count($options) > $i) {
      $result[$i] = $options[$i];
    }
  }
  return $result;
}
function getKeyPairs($objOptions)
{
  $options = [];
  if (!empty($objOptions)) {
    $options = strToKeyValues($objOptions[0]);
  }
  return $options;
}

function getPageStyleStr($formConfigs)
{
  $result = '';
  if (isset($formConfigs) && isset($formConfigs['inputObjs'])) {
    $inputObjs = $formConfigs['inputObjs'];
    $index = array_search('system-page', array_column($inputObjs, 'inputType'));

    if ($index !== false) {
      $pageInputObj = $inputObjs[$index];
      $result = $pageInputObj['options'][0];
    }
  }
  return $result;
}
// *** end of routines

$isDemo = isset($isDemo) ? ($isDemo && $formType=='question') : false;
$isPreview = isset($isTemp) ? $isTemp : false;
$pageTitle = 'YOOV';
$selectedChoiceColor = 'blue';
$selectedChoiceTextColor = 'white';
$inputRegionMaxWidth = '640px';

$maxWidth = '640px';
$bodyStyleKeyValues = [
  'padding-top' => 0,
  'background-color' => 'white',
  'color' => 'white',
  'font-size' => '14px'
];

$inputObjs = [];
$pageStyleStr = '';
if (isset($formConfigs) && isset($formConfigs['inputObjs'])) {
  $inputObjs = $formConfigs['inputObjs'];
  $index = array_search('system-page', array_column($inputObjs, 'inputType'));
  if ($index !== false) {
    $pageInputObj = $inputObjs[$index];
    $pageStyleStr = $pageInputObj['options'][0];
    $inputObjs = array_filter($inputObjs, function ($inputObj) {
      return $inputObj['inputType'] != 'system-page';
    });
  }

}
$pageStyleStr = getPageStyleStr($formConfigs);
$pageKeyValues = strToKeyValues($pageStyleStr);

foreach ($bodyStyleKeyValues as $styleKey => $default) {
  if (array_key_exists($styleKey, $pageKeyValues)) {
    $bodyStyleKeyValues[$styleKey] = $pageKeyValues[$styleKey];
    unset($pageKeyValues[$styleKey]);
  }
}
$maxWidth = get($pageKeyValues, 'max-width', $maxWidth);
unset($pageKeyValues['max-width']);
$selectedChoiceColor = get($pageKeyValues, 'selected-choice-color', $selectedChoiceColor);
unset($pageKeyValues['selected-choice-color']);
$selectedChoiceTextColor = get($pageKeyValues, 'selected-choice-text-color', $selectedChoiceTextColor);
unset($pageKeyValues['selected-choice-text-color']);
$inputRegionMaxWidth = get($pageKeyValues, 'input-region-max-width', $inputRegionMaxWidth);
unset($pageKeyValues['input-region-max-width']);

$bodyStyleKeyValues = array_merge($bodyStyleKeyValues, $pageKeyValues);
$bodyStyleStr = keyValuesToStr($bodyStyleKeyValues);

$rules = [];
$messages = [];
$i = 0;
foreach($inputObjs as $inputObj) {
	$inputType = $inputObj['inputType'];
	if ($inputType=='output-remark' ||
    $inputType=='output-image' ||
    $inputType=='output-submit' ||
    $inputType=='system-page') {
		continue;
  }
    $fieldName = 'field'.$i;
    $fieldName0 = 'field'.$i.'_0';
    $fieldName1 = 'field'.$i.'_1';
    $ruleTags = [];

    switch($inputObj['inputType']) {
	    case 'email':
		    $ruleTags[] = 'email';
      case 'simple-text':
      case 'text':
      case 'number':
      case 'gender':
	    case 'phone':
	    case 'single-choice':
      case 'multiple-choice':
      	$ruleTags[] = 'required';
      	$fieldRules = [];
      	$fieldMessages = [];
      	foreach($ruleTags as $ruleTag) {
      		$fieldRules[$ruleTag] = true;
      		$fieldMessages[$ruleTag] = '';
        }
        $rules[$fieldName] = $fieldRules;
      	$messages[$fieldName] = $fieldMessages;
      	break;
	    case 'name':
	    	if ($inputObj['required']) {
	    		$rules[$fieldName0] = ['required' => true];
	    		$rules[$fieldName1] = ['required' => true];

	    		$messages[$fieldName0] = ['required' => ''];
	    		$messages[$fieldName1] = ['required' => ''];
        }
	    break;
    }
    $i++;
}

?><!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
      class="h-100">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if(isset($og))
    @include('templates.og', ['og'=>$og])
    @else
        <title>{{ $pageTitle }}</title>
    @endif

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
            integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
    <script src="{{ asset('assets/js/popbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
            integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
            crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="{{ asset('assets/css/popbox.css') }}">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
            integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
            crossorigin="anonymous"></script>
    <style>
        body {
            height: 100%;
        }

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

        .question-form .input-region {
            width: 100%;
            margin-left: auto;
            margin-right: auto;
            max-width: {{ $inputRegionMaxWidth }};
        }

        .question-form .question-label {
            line-height: 1.2;
            margin-bottom: 5px;
        }

        .question-form .user-answer {
            line-height: 1;
        }

        .question-form .container > .row {
            margin-bottom: 5px;
        }

        .question-form .input-obj-hidden {
            width: 1px;
            left: 50%;
            position: absolute;
            height: 1px;
            z-index: -100;
            color: transparent;
            border-color: transparent;
            background-color: transparent;
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

        .question-form input:not([type=hidden]) {
            border-width: 2px;
        }
        .question-form input:not([type=hidden]).has-error {
            border-color: red;
        }
        .question-form .radio-toggle .button-wrapper {
            border: 2px solid transparent;
            border-radius: 0.7rem;
        }

        .question-form .output-link:hover {
            cursor: pointer;
        }

        .question-form .radio-toggle.has-error .button-wrapper {
            border-color: red;
        }

        .question-form .checkbox-toggle .button-wrapper {
            border: 2px solid transparent;
            border-radius: 0rem;
        }

        .question-form .checkbox-toggle.has-error .button-wrapper {
            border-color: red;
        }

        /*.error-message {*/
            /*width: 99%;*/
            /*max-width: 640px;*/
            /*background-color: white;*/
            /*border: 5px solid black;*/
            /*font-size: 24px;*/
        /*}*/


    </style>
    <!-- Styles -->
</head>
<body class="h-100 d-flex flex-column align-items-stretch question-form"
      style="{{ $bodyStyleStr }}">
@if($isPreview)
    <img src="{{ URL::asset('/assets/images/preview_mark.png') }}"
         class="d-inline-block position-fixed"
         style="right:0;top:0;width:200px;height:auto;></img>
@endif
@if($isDemo)
    <button class="btn btn-primary" style="z-index:9999;width:200px;position:fixed;right:10px;top:10px;" onclick="useDemoData()">
        Demo Data
    </button>
@endif
{{--<body class="h-100 d-flex flex-column align-items-center question-form"--}}
{{--style="background-color:{{ $formConfigs['pageConfig']['bgColor'] }};padding-top:{{ $paddingTop }};color:{{$color}};font-size:{{$fontSize}}">--}}
<form novalidate id="questionForm" method="post" action="{{ url('/questions/submit') }}">
    {{ csrf_field()  }}
    <input type="hidden" name="formKey" value="{{ $formKey }}"/>
    <div class="container-fluid" style="max-width:{{ $maxWidth }}">
        @php($i=0)
        @foreach($inputObjs as $inputObj)
            <?php
                $regionClass = 'input-region';
                switch($inputObj['inputType']) {
                  case 'output-image':
                  case 'output-remark':
                  case 'output-submit':
                  	$regionClass = '';
                  	break;
                }
            ?>
            <div class="{{ $regionClass }} row {{ $regionClass==='' ? 'mt-0' : 'mt-4' }}">
                <!-- *********** -->
                <!-- simple-text -->
                <!-- *********** -->
                @if($inputObj['inputType']=='simple-text')
                    <div class="col-sm-5 question-label">
                        @include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
                    </div>
                    <div class="col-sm-7 user-answer">
                        <input type="text" {{$inputObj['required'] ? 'required' : ''}} class="form-control"
                               value="{{ old('field'.$i) }}"
                               name="field{{$i}}" id="field{{$i}}"/>
                        @if(!empty($inputObj['note1']))
                            <small>{{$inputObj['note1']}}</small>
                        @endif
                    </div>
                    @php($i++)
                <!-- *********** -->
                <!-- number -->
                <!-- *********** -->
                @elseif($inputObj['inputType']=='number')

                    <div class="col-sm-5 question-label">
                        @include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
                    </div>
                    <div class="col-sm-7 user-answer">
                        <input type="number" {{$inputObj['required'] ? 'required' : ''}} class="form-control"
                               value="{{ old('field'.$i) }}"
                               name="field{{$i}}" id="field{{$i}}"/>
                        @if(!empty($inputObj['note1']))
                            <small>{{$inputObj['note1']}}</small>@endif
                    </div>
                    @php($i++)
                @elseif($inputObj['inputType']=='email')
                    <div class="col-sm-5 question-label">
                        @include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
                    </div>
                    <div class="col-sm-7 user-answer">
                        <input type="email" {{$inputObj['required'] ? 'required' : ''}} class="form-control"
                               value="{{ old('field'.$i) }}"
                               name="field{{$i}}" id="field{{$i}}"/>
                        @if(!empty($inputObj['note1']))
                            <small>{{$inputObj['note1']}}</small>@endif
                    </div>
                    @php($i++)
                @elseif($inputObj['inputType']=='text')
                    <div class="col-sm-5 question-label">
                        @include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
                    </div>
                    <div class="col-sm-7 user-answer">
                        <textarea {{$inputObj['required'] ? 'required' : ''}} rows="5" class="form-control"
                                  name="field{{$i}}" id="field{{$i}}">
	                        {{ old('field'.$i) }}
                        </textarea>
                        @if(!empty($inputObj['note1']))
                            <small>{{$inputObj['note1']}}</small>@endif
                    </div>
                    @php($i++)
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
                                       value="{{ old('field'.$i.'_0') }}"
                                       class="form-control" name="field{{$i}}_0" id="field{{$i}}_0"/>
                                <small>{{ $inputObj['note1'] }}</small>
                            </div>
                            <div class="col-sm-6 pl-sm-0">
                                <input {{$inputObj['required'] ? 'required' : ''}} type="text"
                                       value="{{ old('field'.$i.'_1') }}"
                                       class="form-control" name="field{{$i}}_1" id="field{{$i}}_1"/>
                                <small>{{ $inputObj['note2'] }}</small>
                            </div>
                        </div>
                    </div>
                    @php($i++)
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
                            <div class="col-sm-12">
                                <input {{$inputObj['required'] ? 'required' : ''}} type="text"
                                       value="{{ old('field'.$i) }}"
                                       data-obj-type="phone"
                                       class="form-control" name="field{{$i}}" id="field{{$i}}"/>
                                <small>{{ $inputObj['note1'] }}</small>
                            </div>
                            {{--<div class="col-sm-6 pl-sm-0">--}}
                                {{--<input {{$inputObj['required'] ? 'required' : ''}} type="text"--}}
                                       {{--value="{{ old('field'.$i.'_1') }}"--}}
                                       {{--data-obj-type="phone"--}}
                                       {{--class="form-control" name="field{{$i}}_1" id="field{{$i}}_1"/>--}}
                                {{--<small>{{ $inputObj['note2'] }}</small>--}}
                            {{--</div>--}}
                        </div>
                    </div>
                    @php($i++)
                <!-- SINGLE CHOICE -->
                @elseif($inputObj['inputType']=='single-choice' || $inputObj['inputType']=='gender')
                <?php
                $choice = -1;
                $oldValue = old('field' . $i);
                if (isset($oldValue) && $oldValue !== '') {
                  $choice = (int)$oldValue;
                }
                ?>
                    <div class="col-sm-5 question-label">
                        @include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
                    </div>
                    <div class="col-sm-7 user-answer radio-toggle btn-vgroup" {{$inputObj['required'] ? 'required' : ''}}>
                        <input type="text"
                               name="field{{$i}}"
                               class="input-obj-hidden"
                               value="{{ $oldValue }}"
                               data-obj-type="single-choice"/>
                        <div class="button-wrapper">
                            @foreach($inputObj['options'] as $idx=>$option)
                                <button type="button" data-buttonidx="{{$idx}}"
                                        class="d-block btn btn-light {{ $choice==$idx ? 'selected' : '' }}">{{ $option }}</button>
                            @endforeach
                        </div>
                    </div>
                    @php($i++)
                <!-- MULTIPLE CHOICE -->
                @elseif($inputObj['inputType']=='multiple-choice')
                <?php
                $choices = [];
                $oldValue = old('field' . $i);
                if (isset($oldValue) && !empty($oldValue)) {
                  $choices = explode(',', $oldValue);
                }
                ?>
                    <div class="col-sm-5 question-label">
                        @include('templates.question', ['question'=>$inputObj['question'],'required'=>$inputObj['required']])
                    </div>
                    <div class="col-sm-7 user-answer checkbox-toggle btn-vgroup" {{$inputObj['required'] ? 'required' : ''}}>
                        <input type="text"
                               name="field{{$i}}"
                               class="input-obj-hidden"
                               value="{{ $oldValue }}"
                               data-obj-type="multiple-choice"/>
                        <div class="button-wrapper">
                            @foreach($inputObj['options'] as $idx=>$option)
                                <button type="button" data-buttonidx="{{$idx}}"
                                        class="btn btn-light {{ in_array((string)$idx, $choices) ? 'selected' : ''}}">{{ $option }}</button>
                            @endforeach
                        </div>
                    </div>
                    @php($i++)

                @elseif($inputObj['inputType']=='output-image')
                <?php
                $options = fillArray($inputObj['options'], 2, '');
                $keyValuesElement = strToKeyValues($options[0]);
                $keyValuesContainer = strToKeyValues($options[1]);

                $styleElement = keyValuesToStr($keyValuesElement);
                $styleContainer = keyValuesToStr($keyValuesContainer);
                ?>
                    <div class="col-sm-12" style="{{$styleContainer}}">
                        @if($inputObj['note1']=='')
                        <img src="{{$inputObj['question']}}" style="{{$styleElement}}"/>
                        @else
                        <img class="output-link"
                             data-link="{{ $inputObj['note1'] }}"
                             src="{{$inputObj['question']}}" style="{{$styleElement}}"/>
                        @endif
                    </div>
                @elseif($inputObj['inputType']=='output-remark')
                <?php
                $default = 'padding-top:10px;padding-bottom:10px;font-size:18px;';
                $keyValuesDefault = strToKeyValues($default);

                $options = fillArray($inputObj['options'], 2, '');
                $keyValuesElement = strToKeyValues($options[0]);
                $keyValuesContainer = strToKeyValues($options[1]);

                $styleElement = keyValuesToStr($keyValuesElement);
                $styleContainer = keyValuesToStr($keyValuesContainer);

                $outputRemark = str_replace('|', '<br/>', $inputObj['question']);
                //                  $paddingTop = get($options, 'paddingTop', '10px');
                //                  $paddingBottom = get($options, 'paddingBottom', '10px');
                //                  $fontSize = get($options, 'fontSize', '18px');
                ?>
                    <div class="col-sm-12" style="{{$styleContainer}}">
                        @if($inputObj['note1']=='')
                        <div style="{{$styleElement}}">
                            {!! $outputRemark !!}
                        </div>
                        @else
                        <div style="{{$styleElement}}" class="output-link" data-link="{{ $inputObj['note1'] }}">
                            {!! $outputRemark !!}
                        </div>
                        @endif

                    </div>
                @elseif($inputObj['inputType']=='output-submit')
                <?php
                $defaultOption1 = 'background-color:orange;color:white;width:200px;font-size:18px;';
                $defaultOption2 = 'padding-top:10px;padding-bottom:10px;';
                $inputOptions = getInputOptions($inputObj['options']);

                $buttonStyleStr = style_merge($defaultOption1, $inputOptions[0]);
                $containerStyleStr = style_merge($defaultOption2, $inputOptions[1]);
                $question = empty($inputObj['question']) ? 'Submit' : $inputObj['question'];
                ?>
                    {{--<h4>options[0]: {{ $inputObj['options'][0] }}</h4>--}}
                    {{--<h4>options[1]: {{ $inputObj['options'][1] }}</h4>--}}

                    <div class="col-sm-12 text-center">
                        <div style="{{ $containerStyleStr }}">
                            <button type="submit"
                                    class="btn"
                                    style="{{ $buttonStyleStr }}">
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
  // function getValues(final, obj) {
  //   const index = $(obj).data('index');
  //   if (final === '') {
  //     return index
  //   } else {
  //     return final + ',' + index
  //   }
  // }

  function updateValue (containerObj) {
    const inputObj = $(containerObj).find('input');
    var result = [];
    const selectedObjs = $(containerObj).find('.btn.selected');
    for (var i = 0; i < selectedObjs.length; i++) {
      result.push($(selectedObjs).eq(i).data('buttonidx'))
    }
    const finalValue = result.join(',');
    $(inputObj).val(finalValue);
  }

  $(document).ready(function () {
    $('body').on('click', '.radio-toggle .btn', function () {
      $(this).toggleClass('selected', true);
      $(this).siblings().toggleClass('selected', false);
      const parentObj = $(this).closest('.radio-toggle');
      updateValue(parentObj);

      var hasSubmitButton = $('button[type=submit]').length > 0
      checkToggleSelected('.radio-toggle', hasSubmitButton)
    });

    $('body').on('click', '.checkbox-toggle .btn', function () {
      $(this).toggleClass('selected')
      const parentObj = $(this).closest('.checkbox-toggle');
      updateValue(parentObj);

      var hasSubmitButton = $('button[type=submit]').length > 0
      checkToggleSelected('.checkbox-toggle', hasSubmitButton)
    });

    $('body').on('click', '.output-link', function() {
        var link = $(this).data('link');
        window.location = link;
    });

    $(window).scrollTop(0);
    $(":input:not(:hidden)").each(function (i) {
      $(this).attr('tabindex', i + 1);
    });
    $('input').not(':hidden').eq(0).focus();

    var errors = {!! $errors !!};

    // console.log('errors: ', errors);
    // if (errors['token']) {
    //   alert(errors['token']);
    // }
    // $('#errorDialog').dialog({
    //   autoOpen: false,
    //   modal: true,
    //   show: 'blind',
    //   hide: 'blind'
    // });
    if (Object.keys(errors).length > 0) {
      var errorMsg = errors[Object.keys(errors)[0]]
      // $.modal('<div class="error-message">' + errorMsg + '</div>');
      // $('#errorMessage').text(errorMsg);

      var popbox = new Popbox({
        blur: true,
        overlay: true
      });
      $('#errorMessage').text(errorMsg)
      popbox.open('errorDialog');
      // $('#errorMessage').modal();
    }
  })

  function checkToggleSelected (classType, result) {
    $(classType).each((index, element) => {
      if ($(element).attr('required')) {
        var btns = $(element).find('.btn.selected');
        var error = btns.length === 0
        console.log('$(this): ', $(element))
        console.log('error = ', error)
        console.log('$(this).attr(class) = ' + $(element).attr('class'))
        $(element).toggleClass('has-error', error);
        if (result && error) {
          result = false
        }
      }
    })
    return result
  }

  $('#questionForm').submit(function () {
    var result = $('button[type=submit]').length > 0
    if (result) {
      result = checkToggleSelected('.radio-toggle', result)
      result = checkToggleSelected('.checkbox-toggle', result)
    }
    return result;
  })

  function useDemoData () {
    const inputObjs = $('input');
    $('input[name=field0_0]').val('John');
    $('input[name=field0_1]').val('Chan');
    $('input[name=field1]').val('johnchan@gmail.com');
    $('input[name=field2]').val('98765432');
    $('input[name=field3]').val('Room 1, 1/F., First Bldg');
    $('input[name=field4]').val('First Street, Kwun Tong.');
    $('input[name=field5]').val('0');
    $('input[name=field6]').val('1');
    $('input[name=field7]').val('1,3,5');
    $('.radio-toggle').eq(0).find('button').eq(0).addClass('selected');
    $('.radio-toggle').eq(1).find('button').eq(1).addClass('selected');
    $('.checkbox-toggle').find('button').eq(1).addClass('selected');
    $('.checkbox-toggle').find('button').eq(3).addClass('selected');
    $('.checkbox-toggle').find('button').eq(5).addClass('selected');
  }

  // Define custom rules

  var rules = {!! json_encode($rules) !!};
  var messages = {!! json_encode($messages) !!};
  console.log('rules: ', rules)
  $('#questionForm').validate({
    errorPlacement: function( error, element) {
      error.remove();
    },
    errorClass: 'has-error',
    rules: rules,
    messages: messages
  });

  console.log('messages: ', messages)
</script>
 <div data-popbox-id="errorDialog" class="popbox">
   <div class="popbox_container">
    <div id="errorMessage"></div>
    <button class="close-button" data-popbox-close="errorDialog">Close</button>
   </div>
 </div>
</body>
</html>
