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
		html {
			height: 100%;
		}

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
<body class="clean-body" style="background-color:lightskyblue;">
	<div class="w-100 h-100 d-flex flex-column justify-content-center align-items-center">
		<div
				style="width:480px;max-width:90%;padding:20px;text-align: center;border:5px solid white;border-radius:2rem;line-height: 1.2;">
			<div class="mb-3">
				你已成功取得兌換確認碼，可以開啟你的電子優惠券進行兌換.<br/>
			</div>
			<div class="mb-3">
				Successfully got Redeem Code,<br/>
				you can open your voucher to redeem.
			</div>
			<a href="#" id="openLastVoucher" type="button"
			   style="display:none;"
			   class="btn min-width-100 btn-primary">
				開啟你的電子優惠券<br/>
				Open Last Voucher</a>
		</div>
	</div>
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

      $(document).ready(function () {
        const voucherCode = localStorage.getItem('voucher_code');
        const VALID_PERIOD_IN_MIN = 20;

        var d = new Date();
        var expiry = new Date();
        const datetimeString = toDateTime(d);

        expiry.setTime(expiry.getTime() + VALID_PERIOD_IN_MIN*60000);

        localStorage.setItem('redeem_code', '{{ $redeemCode }}');
        localStorage.setItem('redeem_code_time', datetimeString);
        localStorage.setItem('redeem_code_expiry', toDateTime(expiry));

        if (voucherCode !== '') {
          console.log('document.ready :: voucherCode not blank');
          const url = '{{ url("/coupons") }}' + '/' + voucherCode;
          console.log('document.ready :: url = ' + url);

          const objButton = document.getElementById('openLastVoucher');
          objButton.setAttribute('href', url);
          objButton.style.display = 'inline-block';
        }
      });
	</script>
</body>
</html>
