@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in!

	<h1>Laravel 5.7 - QR Code Generator Example</h1>

                        {!! QrCode::size(250)->generate('ItSolutionStuff.com'); !!}

                        <p>example by ItSolutionStuf.com.</p>
                    {{--<img src="/qrcode"/>--}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
