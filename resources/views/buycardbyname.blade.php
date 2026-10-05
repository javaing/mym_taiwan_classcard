@extends('layouts.master')

@section('title', '幫學員購買新卡')

@section('content')
<style>
    .buy-card-panel {
        max-width: 520px;
        margin: 24px auto;
        padding: 24px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 1px 5px rgba(197, 156, 64, .22);
    }
    .buy-card-panel h3 { color: #C59C40; text-align: center; margin-bottom: 22px; }
    .buy-card-panel label { color: #8a6a1a; font-weight: 600; }
    .buy-card-panel .form-control { height: 48px; }
    .buy-card-panel .candidate { padding: 10px; border: 1px solid #ead9b0; border-radius: 6px; margin-bottom: 8px; }
    .buy-card-panel .btn-buy { background: #C59C40; color: #fff; width: 100%; height: 48px; }
</style>

<div class="buy-card-panel">
    <h3>幫學員購買新卡</h3>

    @if(session('warning') || isset($warning))
        <div class="alert alert-warning">{{ session('warning') ?? $warning }}</div>
    @endif

    <form method="POST" action="{{ route('buy.forUserPost') }}">
        @csrf

        <div class="form-group">
            <label for="fullName">中文全名</label>
            <input id="fullName" name="fullName" class="form-control" type="text"
                value="{{ old('fullName', $fullName ?? '') }}" placeholder="請輸入學員中文全名" required>
        </div>

        @if(isset($candidates) && count($candidates) > 1)
            <div class="form-group">
                <label>同名學員</label>
                @foreach($candidates as $candidate)
                    @php
                        $mobile = (string)($candidate['Mobile'] ?? '');
                        $mobileHint = $mobile !== '' ? '手機末四碼 ' . substr($mobile, -4) : '無手機資料';
                    @endphp
                    <label class="candidate d-block">
                        <input type="radio" name="selectedUserId" value="{{ $candidate['UserID'] }}" required>
                        {{ $candidate['NickName'] ?? '無暱稱' }}（{{ $mobileHint }}）
                    </label>
                @endforeach
            </div>
        @endif

        <div class="form-group">
            <label for="point">課卡種類</label>
            <select id="point" class="form-control" name="point" required>
                <option value="4" {{ (string)old('point', $point ?? 4) === '4' ? 'selected' : '' }}>買四堂</option>
                <option value="1" {{ (string)old('point', $point ?? 4) === '1' ? 'selected' : '' }}>買單堂</option>
            </select>
        </div>

        <div class="form-group">
            <label for="dailyPassword">今日密碼</label>
            <input id="dailyPassword" name="dailyPassword" class="form-control" type="password"
                inputmode="numeric" autocomplete="one-time-code" placeholder="請輸入今日密碼" required>
        </div>

        <button class="btn btn-buy" type="submit">確認購買</button>
    </form>
</div>
@endsection
