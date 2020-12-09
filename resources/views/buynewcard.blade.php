@extends('layouts.master')

@section('title', '體位法課卡')

@section('content')
<center>
    <H4>
        <a href="{{ route('buy.classcard', ['userId' => $userId,'point'=>4]) }}">買新卡</a>
    </H4>
    <br><br>
    <H4>
        <a href="{{ route('buy.classcard', ['userId' => $userId,'point'=>4]) }}">買卡但下次繳</a>
    </H4>
</center>
@endsection