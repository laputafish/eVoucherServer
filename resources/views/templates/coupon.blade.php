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

	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
	        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
	        crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
          integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
          crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

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
		font-size:16px;
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
			bottom: 10px;
			cursor: pointer;
		}

		#mainbody {
			position: fixed;
			left: 0;
			right: 0;
			bottom: 0;
			top: 0;
			background-color:rgba(0,0,0,.6);
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

		#qr-canvas {
			display: none;
		}
	</style>
</head>
<body class="clean-body">
@if(empty($template))
	<img class="yoov-logo" src="{!! URL::asset('/images/yoov_ticket_logo.png') !!}"/>
	<h3>Voucher leaflet not defined!</h3>
@else
	@if(!is_null($redemptionMethod) && !empty($redemptionMethod) && $redemptionMethod!=='none')
		<div style="margin-bottom: 120px;">
        {!! $template !!}
    </div>
		@if($redemptionMethod==='password')
			<div class="redeem-row">
	        <form method="POST" action="{!! url('/coupons/'.$key.'/redeem') !!}" class="w-100 h-100">
	                {{ csrf_field() }}
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
			<button type="button" id="redeemButton" class="redeem-button text-center">
				兌換<br/>
				Redeem
			</button>
			<form method="POST" action="{!! url('coupons/'.$key.'/qr_redeem') !!}" class="w-100 h-100">
				{{ csrf_field() }}
				<input type="hidden" id="redemptionQrcode" name="redemptionQrcode" value=""/>
			</form>
			<div id="mainbody" class="d-none flex-column align-items-center py-5 px-3">
				<div class="d-flex flex-grow-1 flex-column justify-content-between align-items-center w-100"
				     style="max-width:99%;margin:0 auto;">
					<div id="outdiv" class="flex-grow-1 mb-2 w-100"></div>
					<h3 id="result">&nbsp;</h3>
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
			  function isValid(code) {
          var codesStr = document.getElementById('redemptionQrcodes').getAttribute('data');
          var result = false;
          if (codesStr.length > 0) {
            var codes = codesStr.split('|');
            for (var i = 0; i < codes.length; i++) {
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

				function go() {
          document.getElementById('mainbody').classList.remove('d-none');
          document.getElementById('mainbody').classList.add('d-flex');
				  if (typeof startScan === 'function') {
				    startScan()
				  } else {
				    console.log('startScan is not function')
				  }
				}

				function closeCamera() {
				  document.getElementById('mainbody').classList.remove('d-flex')
				  document.getElementById('mainbody').classList.add('d-none')
				}
				document.getElementById('redeemButton').onclick = go;
				document.getElementById('cancelButton').onclick = closeCamera
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
</html>
