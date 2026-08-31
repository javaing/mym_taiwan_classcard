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
            onsubmit="return confirm('確定完成{{ $activity['label'] }}報到？');"
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

    <a class="secondary-link" href="{{ route('taichung.activities') }}">重新選擇活動</a>
@endsection

@section('footer-note')
點選已購活動完成報到；同一活動每日限一次。
@endsection
