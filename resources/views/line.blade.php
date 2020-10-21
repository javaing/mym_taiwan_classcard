@extends('layouts.master_nofoot')

@section('title', '體位法課程登入')

@section('content')
<form align="center">
    @if($url == 'reuse')
    <a href={{ route('reuse.line') }}><img style="height:100%" src="/images/login_bg.png"></a>
    @else
    <a href="{{ $url }}"><img style="height:100%" src="/images/login_bg.png"></a>
    @endif

</form>
@endsection