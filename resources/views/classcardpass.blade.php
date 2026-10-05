@extends('layouts.master_nofoot')

@section('title', '體位法課程購卡密碼')

@section('content')
<div align="center">
    <div style="margin-top: 80px;">
        <p18>購卡密碼是[{{$pass}}]</p18>
        <div style="margin-top:24px;">
            <a href="{{ route('buy.forUser') }}">幫學員購買新卡</a>
        </div>
    </div>

</div>
@endsection
