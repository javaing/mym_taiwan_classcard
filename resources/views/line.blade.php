@extends('layouts.master_nofoot')

@section('title', '體位法課程登入')

@section('content')
<style>
    .legacy-login {
        position: relative;
        width: min(100%, 576px);
        margin: 0 auto;
    }

    .legacy-login__image {
        display: block;
        width: 100%;
        height: auto;
    }

    .legacy-login__copyright {
        position: absolute;
        right: 25%;
        bottom: 8%;
        left: 25%;
        padding: 4px 0;
        background: #fff9e5;
        color: #c99c3b;
        font-size: clamp(13px, 3.6vw, 20px);
        letter-spacing: 1px;
        text-align: center;
        white-space: nowrap;
    }
</style>

<div class="legacy-login">
    @if($url == 'reuse')
    <a href="{{ route('reuse.line') }}">
    @else
    <a href="{{ $url }}">
    @endif
        <img class="legacy-login__image" src="/images/login_bg.png" alt="Raja Yoga 課程登入">
    </a>
    <div class="legacy-login__copyright">MYM TAIWAN ©{{ date('Y') }}</div>
</div>
@endsection