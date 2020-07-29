<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" class="h-100">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:v="urn:schemas-microsoft-com:vml">
<head>
<!--[if gte mso 9]>
	<xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
	<![endif]-->
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>

	@if(isset($og))
		@include('templates.og', ['og'=>$og])
	@else
		<title>Yoov Ticket</title>
	@endif

	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
	{{--<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"--}}
	{{--integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"--}}
	{{--crossorigin="anonymous"></script>--}}
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
	        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
	        crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
	      integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
	        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
	        crossorigin="anonymous"></script>

	<style>
    body {
	    height: 100%;
	    margin: 0;
	    padding: 0;
	    -webkit-text-size-adjust: 100%;
	    background-color: #FFFFFF;
    "
    }

    body p {
	    margin: 15px 0;
    }
  </style>

	<!--[if !mso]><!-->
<meta content="IE=edge" http-equiv="X-UA-Compatible"/>
	<!--<![endif]-->

	<!--[if !mso]><!-->
	<!--<![endif]-->
	<style id="media-query" type="text/css">
		@media (max-width: 520px) {

			.block-grid,
			.col {
				min-width: 320px !important;
				max-width: 100% !important;
				display: block !important;
			}

			.block-grid {
				width: 100% !important;
			}

			.col {
				width: 100% !important;
			}

			.col > div {
				margin: 0 auto;
			}

			img.fullwidth,
			img.fullwidthOnMobile {
				max-width: 100% !important;
			}

			.no-stack .col {
				min-width: 0 !important;
				display: table-cell !important;
			}

			.no-stack.two-up .col {
				width: 50% !important;
			}

			.no-stack .col.num4 {
				width: 33% !important;
			}

			.no-stack .col.num8 {
				width: 66% !important;
			}

			.no-stack .col.num4 {
				width: 33% !important;
			}

			.no-stack .col.num3 {
				width: 25% !important;
			}

			.no-stack .col.num6 {
				width: 50% !important;
			}

			.no-stack .col.num9 {
				width: 75% !important;
			}

			.video-block {
				max-width: none !important;
			}

			.mobile_hide {
				min-height: 0px;
				max-height: 0px;
				max-width: 0px;
				display: none;
				overflow: hidden;
				font-size: 0px;
			}

			.desktop_hide {
				display: block !important;
				max-height: none !important;
			}
		}

	</style>

	<style>
	.redeem-row {
		bottom: 0;
		background-color: rgba(0, 0, 0, .2);
		padding-top: 0.5rem;
		padding-bottom: 0.5rem;
		position: fixed;
		width: 100%;
		height: 110px;
	}

	.redeem-row .redeem-block {
		max-width: 95%;
		width: 480px;
		border-radius: 1rem;
		border: lightgray 5px solid;
		background-color: rgba(0, 176, 240, .7);
		height: 100%;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		margin: 0 auto;
	}

	.redeemed-error-message {
		margin: 0 0 2px 0;
		color: red;
		text-align: center;
		font-size: 16px;
		line-height: 1;
	}

	/*.redeemed-error-message_cht {*/
	/*margin: 0;*/
	/*color: red;*/
	/*text-align: center;*/
	/*font-size: 14px;*/
	/*}*/

	.redeemed-message {
		text-shadow: 1px 1px darkgray;
		margin: 0 0 2px 0;
		color: red;
	}

	.redeemed-message .redeemed-date {
		margin-left: 0.5rem;
		margin-right: 0.5rem;
		display: inline-block;
		white-space: nowrap;
	}

	.redeem-input {
		display: flex;
		flex-direction: row;
		align-items: center;
		width: 80%;
	}

	/* using qr code */

	#redeemButton {
		border-top-left-radius: 1rem;
		border-bottom-left-radius: 1rem;
		background-color: rgba(0, 176, 240, .7);
		padding: 0.5rem 1rem;
		line-height: 1.2;
		border: 1px solid rgba(0, 176, 240, .7);
		position: fixed;
		right: 0;
		/*top: 2rem;*/
		bottom: 2rem;
		cursor: pointer;
	}

	#mainbody {
		position: fixed;
		left: 0;
		right: 0;
		bottom: 0;
		top: 0;
		background-color: rgba(0, 0, 0, .8);
	}

	#outdiv {
		background-color: black;
	}

	#result {
		color: white;
		width: 100%;
		padding: 0.5rem;
		line-height: 1;
		text-align: center;
		border: white 2px solid;
		border-radius: 0.5rem;
	}

	#v {
		width: 100%;
		height: 100%;
	}

	#qr-canvas {
		display: none;
	}

	#switchCamera {
		position: absolute;
		left: 5px;
		top: 5px;
		margin: 0 auto;
		display: inline-block;
		padding: 2px 10px;
		border: 1px solid white;
		background-color: rgba(0, 0, 0, .5);
		color: white;
		outline: 0;
		border-radius: 0.5rem;
		line-height: 1;
	}


	</style>
</head>
<body class="clean-body">
<script>
	var scanning = false
  var intervalVar = null;

  function startJob () {
    startDrawingScanningLine();
    intervalVar = setInterval(startDrawingScanningLine, 2000);
  }

  function stopJob () {
    clearInterval(intervalVar);
    intervalVar = null;
  }

  function startDrawingScanningLine () {
    console.log('startScan :: scanning = ' + (scanning ? 'yes' : 'no'));
    $('#scanningLine').animate({top: 'toggle'}, 2000);
    $('#scanningLine').animate({top: 'toggle'}, 2000);
  }
</script>

@if(empty($template))
	<img class="yoov-logo" src="{!! URL::asset('/images/yoov_ticket_logo.png') !!}"/>
	<h3>Voucher leaflet not defined!</h3>
@else
	@if(!is_null($redemptionMethod) && !empty($redemptionMethod) && $redemptionMethod!=='none')
		<div style="margin-bottom: 120px;">
        {!! $template !!}
    </div>

		<div id="redeemCodeInvalid"
		     class="mb-2 position-absolute d-flex flex-column justify-content-center w-100"
		     style="display:none;line-height:1.2;padding:5px;z-index:2001;background-color:lightskyblue;">
			<div style="width:400px;max-width:98%;" class="mx-auto">
				兌換確認碼無效！<br/>
				Invalid edemption Confirmation Code!
			</div>
			<div class="mx-auto">
				<button id="closeButton" type="button" class="ml-1 btn btn-primary min-width-80">Close</button>
			</div>
		</div>

		<div id="redeemCodeExpired"
		     class="mb-2 position-absolute d-flex flex-column justify-content-center w-100"
		     style="display:none;line-height:1.2;padding:5px;z-index:2001;background-color:lightskyblue;">
			<div style="width:400px;max-width:98%;" class="mx-auto">
				兌換確認碼已逾時，請重新掃描。<br/>
				Redemption Confirmation Code expired. Please scan again.
			</div>
			<div class="mx-auto">
				<button id="closeButton" type="button" class="ml-1 btn btn-primary min-width-80">Close</button>
			</div>
		</div>

		<div id="confirmRedemption"
		     class="mb-2 position-absolute d-flex flex-column justify-content-center w-100"
		     style="display:none;line-height:1.2;padding:5px;z-index:2001;background-color:lightskyblue;">
			<div style="width:400px;max-width:98%;" class="mx-auto">
				你的電子優惠券已被核對.
				Your voucher has been verified.
			</div>
			<div class="mx-auto">
				<button id="confirmButton" type="button" class="mr-1 btn btn-primary min-width-80">Confirm</button>
				<button id="cancelButton" type="button" class="ml-1 btn btn-primary min-width-80">Cancel</button>
			</div>
		</div>

		@if($redemptionMethod==='password')
			<div class="redeem-row">
	        <form method="POST" action="{!! url('/coupons/'.$key.'/redeem') !!}" class="w-100 h-100">
	                {{ csrf_field() }}
		        <input type="hidden" name="redemptionMethod" value="{{$redemptionMethod}}"/>

		        <div class="redeem-block">
		            @if(empty($redeemedOn))
				        @if (Session::has('message'))
					        <div class="redeemed-error-message">{{ Session::get('message') }}</div>
					        <div class="redeemed-error-message">{{ Session::get('message_cht') }}</div>
				        @endif
				        <div class="redeem-input">
		                        <input class="form-control" type="password" name="redemptionCode" id="redemptionCode"/>
		                        <button type="submit" style="line-height:1;"
		                                class="ml-1 py-1 input-group-append btn btn-primary">兌換<br/>Redeem</button>
		                    </div>
			        @else
				        <div class="text-center">
		                  <h4 class="redeemed-message">
			                  <span class="text-danger flex flex-row align-items-center">
				                  <span class="font-weight-bold">已兌換</span> Redeemed</span>
			                  <div class="redeemed-date text-white">{{ $redeemedOn }}</div>
		                  </h4>
		                </div>
			        @endif
		        </div>
	        </form>
	    </div>
		@else
			<div id="redemptionQrcodes" data="{{$redemptionQrcodes}}" class="d-none"></div>
			<div id="redemptionPasswords" data="{{$redemptionPasswords}}" class="d-none"></div>
			<button type="button" id="redeemButton" class="redeem-button text-center">
				兌換<br/>
				Redeem
			</button>
			<form id="qrcodeRedemptionForm"
			      method="POST"
			      action="{!! url('coupons/'.$key.'/qr_redeem') !!}"
			      class="w-100 h-100">

				{{ csrf_field() }}
				<input type="hidden" name="redemptionMethod" value="{{$redemptionMethod}}"/>
				<input type="hidden" id="redemptionQrcode" name="redemptionQrcode" value=""/>
			</form>
			<div id="mainbody" class="d-none flex-column align-items-center py-4 px-3">
				<div class="d-flex flex-grow-1 flex-column justify-content-between align-items-center w-100"
				     style="max-width:99%;margin:0 auto;">
					<div class="flex-grow-1 w-100 position-relative d-flex flex-column">
						<div id="outdiv" class="flex-grow-1 w-100"></div>

						<!-- square -->
						<div class="position-absolute d-flex flex-column justify-content-center align-items-center"
						     style="left:0;top:0;width:100%;height:100%;background-color:transparent">
							<div style="width:200px;height:200px;border:2px solid rgba(255,255,255,.5);background-color:transparent;"
							     class="position-relative">
								<div id="scanningLine" style="margin:-2px;top:200px;width:200px;height:2px;background-color:white;"
								     class="position-absolute"></div>
							</div>
						</div>

						<button id="switchCamera" type="button" style="z-index:1000;">
							Switch Camera<br/>
							轉換鏡頭</button>

						<div id="messageBoard"
						     class="mb-2 position-absolute d-flex flex-column justify-content-start w-100 h-100"
						     style="display:none;line-height:1.2;padding:5px;z-index:2000;background-color:lightskyblue">
							<h5 class="text-center">
								未能接駁手機鏡頭<br/>
								Cannot connect camera</h5>
							<ol style="padding-left:1em;margin:0;">

								<li class="pb-2 px-0">沒有鏡頭, 或<br/>
									no camera, or
								</li>

								<li class="pb-2 px-0">你的瀏灠器未能支援手機鏡頭<br/>
								Camera not supported in your browser!<br/>
									可嘗試 Try：<br/>

										Android:<br/>
									<!-- Chrome -->
										<a href="https://play.google.com/store/apps/details?id=com.android.chrome&hl=en">
											<img src="https://img.icons8.com/fluent/48/000000/chrome.png"/>
										</a>
									<!-- Firefox -->
										<a href="https://play.google.com/store/apps/details?id=org.mozilla.firefox&hl=en">
											<img src="https://img.icons8.com/color/48/000000/firefox.png"/>
										</a>
									<!-- Opera -->
										<a href="https://play.google.com/store/apps/details?id=com.opera.browser&hl=en">
											<img src="https://img.icons8.com/color/48/000000/opera--v1.png"/>
										</a>

										<br/>

										iOS<br/>
									<!-- Firebox -->
										<a href="https://apps.apple.com/us/app/firefox-private-safe-browser/id989804926">
											<img src="https://img.icons8.com/color/48/000000/firefox.png"/>
										</a>
									<!-- Chrome -->
										<a href="https://apps.apple.com/us/app/google-chrome/id535886823">
											<img src="https://img.icons8.com/fluent/48/000000/chrome.png"/>
										</a>
									<!-- Opera -->
										<a href="https://apps.apple.com/app/id1411869974">
											<img src="https://img.icons8.com/color/48/000000/opera--v1.png"/>
										</a>

								</li>
								<li>用手機apps掃描QR code打開確認碼網頁進行兌換.<br/>
									Use Scanner Apps to scan QR Code, open acknowlegement page to redeem.
								</li>
							</ol>
							{{--<div class="flex-grow-1 d-flex flex-column justify-content-center align-items-center">--}}
							{{--<h3 class="m-0" id="remainingTime"></h3>--}}
							{{--</div>--}}
						</div>
						{{--<div class="d-none position-absolute text-white" style="left:5px;top:60px;">--}}
						{{--<div>Device Count: <span id="deviceCount"></span></div>--}}
						{{--<div>Device ID: <span id="deviceId"></span></div>--}}
						{{--<div>Device Label: <span id="deviceLabel"></span></div>--}}
						{{--<div>Device Kind: <span id="deviceKind"></span></div>--}}
						{{--</div>--}}

					</div>

					<h3 class="mt-2" id="result">&nbsp;</h3>
					<canvas id="qr-canvas" width="800" height="600" style="width: 800px; height: 600px;"></canvas>
					<button id="cancelButton"
					        class="btn btn-danger btn-lg w-100">
						取消 Cancel
					</button>
				</div>
			</div>
			<div id="canvasErrorMessage"
			     class="position-absolute w-100 px-3"
			     style="top:30%;"></div>
			<script type="text/javascript" src="{{ url('/webqr/llqrcode.js') }}"></script>
			<script type="text/javascript" src="{{ url('/webqr/webqr.js') }}"></script>
			<script>
				var value = 120;
        var timer = null;

        function showTimer () {
          // timer = setInterval(() => {
          //  console.log('timer running ...');
          //  var valueStr = '';
          //  if (value === 0) {
          //    valueStr = '請重新刷新網頁. Please refresh this page.';
          //  } else {
          //    valueStr = value + ' 秒 sec.';
          //  }
          //  document.getElementById('remainingTime').innerText = valueStr
          // if (value === 0) {
          //    clearInterval(timer)
          // }
          //   value--;
          // }, 1000);
        }

        function isValid (code) {
          let codesStr = document.getElementById('redemptionQrcodes').getAttribute('data');
          let result = false;
          let i = 0;
          if (codesStr.length > 0) {
            let codes = codesStr.split('||');
            for (i = 0; i < codes.length; i++) {
              if (codes[i].trim() === code.trim()) {
                document.getElementById('redemptionQrcode').value = code.trim();
                result = true;
                break;
              }
            }
          }
          else {
            document.getElementById('result').innerHtml('No redemption code!');
          }
          return result;
        }

        function hideDeviceError () {
          showDeviceError(false);
        }

        function showDeviceError (state) {
          if (state) {
            document.getElementById('result').style.display = 'none';
            document.getElementById('messageBoard').style.display = 'block';
          } else {
            document.getElementById('result').style.display = 'block';
            document.getElementById('messageBoard').style.display = 'none';
          }
          showTimer();
        }

        function go () {
          document.getElementById('mainbody').classList.remove('d-none');
          document.getElementById('mainbody').classList.add('d-flex');
          if (typeof startScan === 'function') {
            const result = startScan();
            if (result !== 0) {
              showDeviceError(true);
            }
          } else {
            console.log('startScan is not function')
          }
        }

        function closeCamera () {
          document.getElementById('mainbody').classList.remove('d-flex')
          document.getElementById('mainbody').classList.add('d-none')
        }

        function switchCamera () {
          console.log('switchCamera')
          if (typeof switchCam === 'function') {
            console.log('switchCameria => switchCam')
            switchCam()
          }
        }

        document.getElementById('redeemButton').onclick = go;
        document.getElementById('cancelButton').onclick = closeCamera
        if (document.getElementById('switchCamera')) {
          document.getElementById('switchCamera').onclick = switchCamera
        }
			</script>
		@endif
	@else
		{!! $template !!}
	@endif
@endif
@if(!empty($script))
	<div class="text-center">
        <div style="width:90%;max-width:640px;margin:0 auto;">
            {!! $script !!}
        </div>
    </div>
@endif
<script>
	function toDateTime (d) {
    const result =
      d.getFullYear() + '-' +
      ('0' + (d.getMonth() + 1)).slice(-2) + '-' +
      ('0' + d.getDate()).slice(-2) + ' ' +
      ('0' + d.getHours()).slice(-2) + ':' +
      ('0' + d.getMinutes()).slice(-2) + ':' +
      ('0' + d.getSeconds()).slice(-2);
    return result;
  }

  function showDialog(dialogId) {

		const objDialog = document.getElementById(dialogId);
		objDialog.style.display = 'block';
  }

  function getCodeArray(objId) {
	  const obj = document.getElementById(objId);
	  const codeStr = obj.getAttribute('data');
	  return codeStr === '' ? [] : codeStr.split('||');
  }

  function submitForm(formId) {
	  const objForm = document.getElementById(formId);
	  objForm.submit();
  }
  function isCorrectRedeemCode(redeemCode) {
		// redeemCode in base64 format
		const decoded = atob(redeemCode);
		const redemptionCodes = getCodeArray('redemptionQrcodes');
		const result = redemptionCodes.indexOf(decoded) >= 0
	  return result
  }

  $(document).ready(function () {
    const redeemCode = localStorage.getItem('redeem_code')

	  $('body').on('click', '#closeButton', () => {
	    document.getElementById('redeemCodeExpired').style.display = 'none';
	  });

    $('body').on('click', '#cancelButton', () => {
      document.getElementById('confirmRedemption').style.display = 'none';
    });

	  $('body').on('click', '#confirmButton', () => {
	    document.getElementById('qrcodeRedemptionForm').submit();
	  });

    if (redeemCode) {
      var now = new Date();
      var nowStr = toDateTime(now);
      const redeemCodeExpiryStr = localStorage.getItem('redeem_code_expiry');

      // if correct redeem code
	   if (isCorrectRedeemCode(redeemCode)) {
       if (nowStr < redeemCodeExpiryStr) {
         showDialog('confirmRedemption');
       } else {
         showDialog('redeemCodeExpired');
       }
     } else {
	     showDialog('redeemCodeInvalid');
	   }

    } else {
      var d = new Date();
      var expiry = new Date();
      const VALID_PERIOD_IN_MIN = 2;
      const datetimeString = toDateTime(d);
      expiry.setTime(expiry.getTime() + VALID_PERIOD_IN_MIN * 60000);

      const redemptionQrcodes = document.getElementById('redemptionQrcodes').getAttribute('data');
      const redemptionPasswords = document.getElementById('redemptionPasswords').getAttribute('data');

      localStorage.clear();
      localStorage.setItem('voucher_code', '{{ $key }}');
      localStorage.setItem('voucher_code_time', datetimeString);
      localStorage.setItem('voucher_code_expiry', toDateTime(expiry));
      localStorage.setItem('voucher_redemption_qrcodes', redemptionQrcodes);
      localStorage.setItem('voucher_redemption_passwords', redemptionPasswords);
    }
  })
</script>
</body>
</html>
