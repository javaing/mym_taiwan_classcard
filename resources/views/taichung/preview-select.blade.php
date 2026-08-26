@extends('layouts.taichung-preview')

@section('title', '台中活動試作－下拉選單版')

@section('activity-control')
    <div class="preview-card">
        <img class="activity-icon" src="/images/onlineclass/point_unuse.png" alt="">

        <label for="activitySelect" class="activity-name" style="margin-bottom: 14px;">請選擇活動</label>
        <select id="activitySelect" class="activity-select">
            <option value="體位法" data-price="300">體位法 300 元</option>
            <option value="梵唱" data-price="100">梵唱 100 元</option>
        </select>

        <button id="confirmButton" class="confirm-button" type="button">確定</button>
    </div>
@endsection

@section('scripts')
<script>
    document.getElementById('confirmButton').addEventListener('click', function () {
        var select = document.getElementById('activitySelect');
        var option = select.options[select.selectedIndex];
        var message = document.getElementById('previewMessage');
        message.textContent = '模擬完成：' + option.value + '，現金 ' + option.dataset.price + ' 元。';
        message.style.display = 'block';
    });
</script>
@endsection
