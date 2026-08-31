@extends('layouts.taichung-preview')

@section('title', '台中活動報到')

@section('status-message')
    @if(session('success'))
        <div class="preview-message is-visible" role="status">{{ session('success') }}</div>
    @endif

    @if(session('warning'))
        <div class="preview-message is-visible is-warning" role="alert">{{ session('warning') }}</div>
    @endif
@endsection

@section('activity-control')
    @foreach($activities as $activityType => $activity)
        @php
            $checked = in_array($activityType, $checkedActivityTypes, true);
        @endphp

        <form
            class="activity-form"
            method="POST"
            action="{{ route('taichung.checkin', ['activityType' => $activityType]) }}"
            onsubmit="return confirm('請確認已將現金交給工作人員，再完成{{ $activity['label'] }}報到。');"
        >
            @csrf
            <button class="activity-button" type="submit" @if($checked) disabled @endif>
                <img class="activity-icon" src="/images/onlineclass/point_unuse.png" alt="">
                <span>
                    <span class="activity-name">{{ $activity['label'] }}</span>
                    <span class="activity-price">
                        @if($checked)
                            今日已完成
                        @else
                            {{ number_format($activity['amount']) }} 元
                        @endif
                    </span>
                </span>
            </button>
        </form>
    @endforeach
@endsection

@section('footer-note')
請先將現金交給工作人員，再點選活動完成報到。每項活動每日限一次。
@endsection
