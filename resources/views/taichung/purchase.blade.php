@extends('layouts.taichung-preview')

@section('title', '台中活動購買')

@section('status-message')
    @if(session('warning'))
        <div class="preview-message is-visible is-warning" role="alert">{{ session('warning') }}</div>
    @endif
@endsection

@section('activity-control')
    <form class="preview-card" method="POST" action="{{ route('taichung.authorize') }}">
        @csrf

        <img class="activity-icon" src="/images/onlineclass/point_unuse.png" alt="">

        <label for="activityType" class="activity-name" style="margin-bottom: 14px;">購活動清單</label>
        <select id="activityType" class="activity-select" name="activityType" required>
            @foreach($activities as $activityType => $activity)
                <option
                    value="{{ $activityType }}"
                    @if(old('activityType') === $activityType) selected @endif
                >
                    {{ $activity['label'] }} {{ number_format($activity['amount']) }} 元
                </option>
            @endforeach
        </select>

        <input
            class="activity-input"
            type="password"
            name="buyActivityPass"
            placeholder="請輸入今日密碼"
            inputmode="numeric"
            pattern="[0-9]{4}"
            maxlength="4"
            autocomplete="off"
            required
        >

        <button class="confirm-button" type="submit">確定</button>
    </form>
@endsection

@section('footer-note')
請先將現金交給工作人員，選擇活動並輸入今日密碼完成預收。
@endsection
