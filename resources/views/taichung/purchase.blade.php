@extends('layouts.taichung-preview')

@section('title', '台中活動購買')

@section('status-message')
    @if(session('success'))
        <div class="preview-message is-visible" role="status">{{ session('success') }}</div>
    @endif

    @if(session('warning'))
        <div class="preview-message is-visible is-warning" role="alert">{{ session('warning') }}</div>
    @endif
@endsection

@section('activity-control')
    @if(count($availableActivities) > 0)
        <form class="preview-card" method="POST" action="{{ route('taichung.authorize') }}">
            @csrf

            <img class="activity-icon" src="/images/onlineclass/point_unuse.png" alt="">

            <label for="activityType" class="activity-name" style="margin-bottom: 14px;">購活動清單</label>
            <select id="activityType" class="activity-select" name="activityType" required>
                @foreach($availableActivities as $activityType => $activity)
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
    @elseif(count($pendingCheckin) === 0)
        <div class="preview-card">
            <img class="activity-icon" src="/images/onlineclass/point_unuse.png" alt="">
            <p class="activity-name">今日活動已全部購買</p>
        </div>
    @endif

    @if(count($purchasedTypes) > 0)
        <p class="preview-note" style="margin-top: 18px;">
            今日已購：
            @foreach($purchasedTypes as $index => $activityType)
                @php
                    $purchasedLabel = isset($allActivities[$activityType])
                        ? $allActivities[$activityType]['label']
                        : $activityType;
                @endphp
                {{ $purchasedLabel }}
                @if(!in_array($activityType, $checkedTypes, true))
                    （尚未報到）
                @endif
                @if($index < count($purchasedTypes) - 1)
                    、
                @endif
            @endforeach
        </p>
    @endif

    @foreach($pendingCheckin as $activityType => $activity)
        <form
            class="preview-card"
            method="POST"
            action="{{ route('taichung.checkin', ['activityType' => $activityType]) }}"
            style="margin-top: 18px;"
        >
            @csrf
            <input type="hidden" name="from" value="purchase">
            <p class="activity-name" style="margin-bottom: 14px;">{{ $activity['label'] }} 完成報到</p>
            <button class="confirm-button" type="submit">完成報到</button>
        </form>
    @endforeach
@endsection

@section('footer-note')
請先將現金交給工作人員，選擇活動並輸入今日密碼完成預收。今日已購買的活動不可再買。
@endsection
