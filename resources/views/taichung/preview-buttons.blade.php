@extends('layouts.taichung-preview')

@section('title', '台中活動試作－雙按鈕版')

@section('activity-control')
    <button class="activity-button" type="button" data-activity="體位法" data-price="300">
        <img class="activity-icon" src="/images/onlineclass/point_unuse.png" alt="">
        <span>
            <span class="activity-name">體位法</span>
            <span class="activity-price">300 元</span>
        </span>
    </button>

    <button class="activity-button" type="button" data-activity="梵唱" data-price="100">
        <img class="activity-icon" src="/images/onlineclass/point_unuse.png" alt="">
        <span>
            <span class="activity-name">梵唱</span>
            <span class="activity-price">100 元</span>
        </span>
    </button>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.activity-button').forEach(function (button) {
        button.addEventListener('click', function () {
            var message = document.getElementById('previewMessage');
            message.textContent = '模擬完成：' + button.dataset.activity + '，現金 ' + button.dataset.price + ' 元。';
            message.style.display = 'block';
        });
    });
</script>
@endsection
