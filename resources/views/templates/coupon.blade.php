<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-100">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if(isset($og))
        @include('templates.og', ['og'=>$og])
    @else
        <title>Yoov Ticket</title>
    @endif

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
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
    </style>
    <!-- Styles -->
</head>
<body class="{{ empty($template) ? 'h-100 d-flex flex-column justify-content-center align-items-center' : '' }}">
    @if(empty($template))
        <img class="yoov-logo" src="{!! URL::asset('/images/yoov_ticket_logo.png') !!}"/>
        <h3>Voucher leaflet not defined!</h3>
    @else

        @if(!is_null($redemptionMethod) && !empty($redemptionMethod) && $redemptionMethod!=='none')
            <div style="margin-bottom: 120px;">
                {!! $template !!}
            </div>
            <div class="py-2 position-fixed w-100" style="bottom:0;background-color:rgba(0,0,0,.2);">
                <div style="max-width:90%;width:480px;border-radius:1rem;border:lightgray 5px solid;background-color:rgba(0,176,240,.7);"
                     class="p-4 mx-auto">
                    @if(empty($redeemedOn))
                        <form method="POST" action="{!! url('/coupons/'.$key.'/redeem') !!}">
                            {{ csrf_field() }}
                            @if (Session::has('message'))
                                <h3 class="m-0 text-center">{{ Session::get('message') }}</h3>
                            @endif
                            <div class="d-flex flex-row align-items-center">
                                <input class="form-control" type="password" name="redemptionCode" id="redemptionCode"/>
                                <button type="submit" class="ml-1 input-group-append btn btn-primary">Redeem</button>
                            </div>
                        </form>
                    @else
                        <div class="text-center">
                          <h4 class="text-white m-0"
                            style="text-shadow:2px 2px black;">
                            Redeemed {{ $redeemedOn }}
                          </h4>
                        </div>
                    @endif
                </div>
            </div>
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
        function toggleRedemptionPassword() {
          var objPassword = document.getElementById('redemptionCode');
          var inputType = objPassword.getAttribute('type')
          if (inputType==='text') {
            objPassword.setAttribute('type', 'password')
            document.getElementById('hidingPassword').style.display = 'block';
            document.getElementById('showingPassword').style.display = 'none';
          } else {
            objPassword.setAttribute('type', 'text')
            document.getElementById('hidingPassword').style.display = 'none';
            document.getElementById('showingPassword').style.display = 'block';
          }
        }
    </script>
</body>
</html>
