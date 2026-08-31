@extends('layouts.master_nofoot')

@section('title', '課程登入')

@section('content')
<style>
    .location-login {
        position: relative;
        width: min(100%, 576px);
        margin: 0 auto;
    }

    .location-login__background {
        display: block;
        width: 100%;
        height: auto;
    }

    .location-login__options {
        position: absolute;
        top: 66%;
        right: 17%;
        left: 17%;
        height: 17%;
        background: #fff9e5;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: clamp(12px, 5vw, 30px);
    }

    .location-login__button {
        width: clamp(78px, 22vw, 112px);
        height: clamp(78px, 22vw, 112px);
        border: 1.5px solid #c99c3b;
        border-radius: 50%;
        color: #c99c3b;
        background: #fff9e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(20px, 5vw, 28px);
        font-weight: 500;
        text-decoration: none;
    }

    .location-login__button:hover,
    .location-login__button:focus {
        color: #a87b20;
        border-color: #a87b20;
        text-decoration: none;
    }

    .location-login__copyright {
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

<div class="location-login">
    <img
        class="location-login__background"
        src="/images/login_bg.png"
        alt="Raja Yoga 體位法與冥想"
    >

    <nav class="location-login__options" aria-label="選擇課程地區">
        <a class="location-login__button" href="{{ $taipeiUrl }}">台北</a>
        <a class="location-login__button" href="{{ $taichungUrl }}">台中</a>
    </nav>

    <div class="location-login__copyright">MYM TAIWAN ©{{ date('Y') }}</div>
</div>
@endsection
