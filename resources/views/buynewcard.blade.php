@extends('layouts.master')

@section('title', '體位法課卡')

@section('content')
<!-- <center>

    <a href="{{ route('buy.classcard', ['userId' => $userId,'point'=>4]) }}">
        <p18>買新卡</p18>
    </a>
    <br>
    <br>
    <a href="{{ route('buy.classcard', ['userId' => $userId,'point'=>1]) }}">
        <p18>買單堂 </p18>
    </a>
</center> -->
<center>
<form action="{{ route('buy.classcardPost') }}" method="POST">
@csrf
<select class="form-control" name="point">
    <option value="4">買四堂</option>
    <option value="1">買單堂</option>
</select>
<input name="buycardPass" type="text" placeholder="請輸入購卡密碼" onkeydown="return ignoreEnter(event);">
<input type="hidden" name="userId" value="{{$userId}}">
<button class="btn btn-link" type="submit">確定</button>
</form>
</center>
@endsection
