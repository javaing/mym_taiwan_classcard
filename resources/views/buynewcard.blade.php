@extends('layouts.master')

@section('title', '體位法課卡')

@section('content')
<center>

    <a href="{{ route('buy.classcard', ['userId' => $userId,'point'=>4]) }}">
        <p18>買新卡</p18>
    </a>
    <br>
    <br>
    <a href="{{ route('buy.classcard', ['userId' => $userId,'point'=>1]) }}">
        <p18>買單堂 </p18>
    </a>
</center>
@endsection