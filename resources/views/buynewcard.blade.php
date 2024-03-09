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
<div align="center">
    <div style="margin-top: 3px;">
        <p18>Hello!</p18>
    </div>
    <div style="margin-top: 3px;margin-bottom: 6px;">
        <p18>{{ DBHelper::getUserName($userId) }}</p18>
    </div>
</div>


<div align="center">
    <img style="margin-bottom: 12px;width:80%;" src="/images/div.png">
</div>

<center>
  <br>
<form action="{{ route('buy.classcardPost') }}" method="POST">
@csrf
<select class="form-control" name="point" style="height:50px">
    <option value="4">買四堂</option>
    <option value="1">買單堂</option>
</select>
<br>
<input name="buycardPass" style="height:50px" type="text" placeholder="請輸入購卡密碼" onkeydown="return ignoreEnter(event);">
<input type="hidden" name="userId" value="{{$userId}}">
<br>
<button class="btn btn-link" style="height:50px; margin-top:16px" type="submit">確定</button>
</form>
</center>
@endsection
