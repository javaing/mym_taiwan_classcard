@extends('layouts.master')

@section('title', '體位法課卡')

<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prettify/r298/run_prettify.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/css/bootstrap-dialog.min.css" rel="stylesheet" type="text/css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/js/bootstrap-dialog.min.js"></script>


@section('content')
@if(session('status'))
<div class="alert alert-success text-center">{{ session('status') }}</div>
@endif
@if(session('warning'))
<div class="alert alert-warning text-center">{{ session('warning') }}</div>
@endif
@php
{{
    $registArray = DBHelper::getConsume( $card['CardID']);

    $listDate = array();
    for ($i = 0; $i < sizeof($registArray); $i++) {
        array_push($listDate, DBHelper::toDateStringJS( $registArray[$i]['PointConsumeTime']));
    }
    //array_push($listDate, "2020/12/16");
    //array_push($listDate, "2020/12/15");

    $oneOrFourClass = 4;
    if($card['Payment']==500) {$oneOrFourClass=1;}
    $cardId = base64_encode( $card['CardID'] );
    $effectiveExpiration = DBHelper::getEffectiveExpiration($card);
    $expiredDate = DBHelper::toDateString($effectiveExpiration);
    if($expiredDate=='') {$expiredDate = "無使用期限";}
    $consumeCount = min(sizeof($registArray), $oneOrFourClass);
    $cardIsUsable = !DBHelper::isExpired($card)
        && $card['Points'] > 0
        && $consumeCount < $oneOrFourClass;
}}
@endphp

<div align="center">
    <div style="margin-top: 3px;">
        <p18>Hello!</p18>
    </div>
    <div style="margin-top: 3px;margin-bottom: 6px;">
        <p18>{{ DBHelper::getUserName($card['UserID']) }}</p18>
    </div>
</div>


<div align="center">
    <img style="margin-bottom: 12px;width:80%;" src="/images/div.png">
</div>
<div align="center" style="margin-bottom: 2px">
    <p16>課程使用期限</p16>
</div>
<div align="center" style="margin-bottom: 24px">
    <p16>{{ $expiredDate }}</p16>
</div>
@if(isset($debugConsumeCount))
<div align="center" style="margin-bottom: 8px; font-size: 12px; color: #666;">
    [除錯] Consume={{ $debugConsumeCount }}
    / Points={{ $debugCardPoints }}
    / total={{ $debugTotalClasses }}
</div>
@endif

<script type="text/javascript">
    $(function() {
        var isSubmitting = false;
        $(".div_unuse img").click(function() {
            var str = document.getElementById('abcId').value;
            var now = new Date();
            var todayDate = now.getFullYear() + '-'
                + String(now.getMonth() + 1).padStart(2, '0') + '-'
                + String(now.getDate()).padStart(2, '0');
            //alert(str);
            //alert(todayDate);
            //alert(str.includes(todayDate));
            //今日蓋過不可再蓋
            if (str.includes(todayDate)) {
                var N = $(this).attr("id").substr(2);
                $("#SS" + N).attr("src", "/images/classcard/point_today.png");
                window.setTimeout(function() {
                    $("#SS" + N).attr("src", "/images/classcard/point_" + N + ".png");
                }, 2000);
            } else {
                if (isSubmitting) return;
                if (!document.getElementById('registeForm')) return;
                isSubmitting = true;
                showStampProgress();
                $('#registeForm').submit();
            }

        });

        //蓋章送出後顯示前景progress，避免重按；逾時則停止等待並提示，免得網路不通時一直轉
        var STAMP_TIMEOUT_MS = 15000;
        var stampTimer = null;

        function showStampProgress() {
            $('#stampTimeoutMsg').hide();
            $('#stampSpinner, #stampLoadingMsg').show();
            $('#stampOverlay').css('display', 'flex');
            stampTimer = window.setTimeout(onStampTimeout, STAMP_TIMEOUT_MS);
        }

        function onStampTimeout() {
            if (window.stop) window.stop();
            $('#stampSpinner, #stampLoadingMsg').hide();
            $('#stampTimeoutMsg').show();
        }

        function hideStampProgress() {
            window.clearTimeout(stampTimer);
            $('#stampOverlay').hide();
            isSubmitting = false;
        }

        $('#stampReload').click(function() {
            window.location.reload();
        });
        $('#stampClose').click(hideStampProgress);

        //從上一頁返回(bfcache)時，把遮罩收掉
        $(window).on('pageshow', function(e) {
            if (e.originalEvent && e.originalEvent.persisted) hideStampProgress();
        });
    });
</script>

<style>
    #stampOverlay {
        display: none;
        position: fixed;
        top: 0; right: 0; bottom: 0; left: 0;
        z-index: 2000;
        background: rgba(0, 0, 0, 0.35);
        align-items: center;
        justify-content: center;
    }
    #stampOverlay .stamp-box {
        background: #fff9e8;
        border-radius: 12px;
        padding: 24px 28px;
        width: 260px;
        text-align: center;
        color: #b8963e;
        font-size: 16px;
    }
    #stampSpinner {
        width: 40px;
        height: 40px;
        margin: 0 auto 12px;
        border: 4px solid #f0e2bd;
        border-top-color: #c4a043;
        border-radius: 50%;
        animation: stamp-spin 0.8s linear infinite;
    }
    @keyframes stamp-spin { to { transform: rotate(360deg); } }
    #stampTimeoutMsg { display: none; }
    #stampTimeoutMsg button {
        margin: 12px 4px 0;
        padding: 6px 14px;
        border: 1px solid #c4a043;
        border-radius: 16px;
        background: #fff;
        color: #b8963e;
    }
    #stampTimeoutMsg button.primary { background: #c4a043; color: #fff; }
</style>

<div id="stampOverlay" role="alertdialog" aria-live="assertive">
    <div class="stamp-box">
        <div id="stampSpinner"></div>
        <div id="stampLoadingMsg">蓋章中，請稍候…</div>
        <div id="stampTimeoutMsg">
            <div>網路回應逾時</div>
            <div style="font-size: 14px; margin-top: 6px;">可能已蓋章成功，請重新整理確認，避免重複蓋章</div>
            <button type="button" id="stampClose">關閉</button>
            <button type="button" id="stampReload" class="primary">重新整理</button>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal" id="expiredHint" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                @if (DBHelper::isExpired($card))
                    抱歉，此課卡已逾期，須補差額後才能使用
                @else
                    此課卡目前無法繼續蓋章
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


@if ($cardIsUsable)
<form id="registeForm" method="POST" action="{{ route('registe.classcard', [$card['Points'], $cardId]) }}">
    @csrf
@endif
<input type="hidden" id="abcId" value="{{ implode(', ', $listDate) }}" />
<TABLE BORDER=0 align="center">
        @for ($i = $oneOrFourClass; $i >= 1; $i--)

        @if ($i%2==0 )
        <TR>
            @else
            @endif
            <TD align="center">
                @php($slotIndex = $oneOrFourClass - $i)
                @if ($slotIndex < $consumeCount)

                @if ($registArray && sizeof($registArray)>$slotIndex)
                <div id="div_used">
                    <p16white>{{DBHelper::toDateString( $registArray[$slotIndex]['PointConsumeTime'] ) }}</p16white>
                </div>
                @else
                <div id="div_used">
                    <p16white>N/A</p16white>
                </div>
                @endif

                @else

                @if (!$cardIsUsable)
                <div class="div_unuse" data-toggle="modal" data-target="#expiredHint">
                    @else
                    <div class="div_unuse">
                        @endif


                        @if($oneOrFourClass==1)
                        <img src="/images/classcard/point_unuse.png" style="width:100%;height:100%" id="SS1">
                        @else
                        @switch($oneOrFourClass-$i+1)
                        @case(1)
                        <img src="/images/classcard/point_1.png" style="width:100%;height:100%" id="SS1">
                        @break

                        @case(2)
                        <img src="/images/classcard/point_2.png" style="width:100%;height:100%" id="SS2">
                        @break

                        @case(3)
                        <img src="/images/classcard/point_3.png" style="width:100%;height:100%" id="SS3">
                        @break

                        @case(4)
                        <img src="/images/classcard/point_4.png" style="width:100%;height:100%" id="SS4">
                        @break
                        @endswitch
                        @endif

                    </div>
                    @endif
            </TD>
            @if ($i%2==1 )
        </TR>
        @else
        @endif
        @endfor
</TABLE>
@if ($cardIsUsable)
</form>
@endif

@if ($card['Points']==0)
<div align="center" style="margin-Top: 8px;">
    <a href="{{ route('buy.newcard', ['userId' => $card['UserID'] ] ) }}">
        <input type="image" style="height:28px;" src="/images/classcard/buy_card.png" alt="" />
    </a>
</div>
@endif

@if ( DBHelper::isExpired($card) )
<div align="center" style="margin-Top: 8px;">
    <a href="{{ route('extend.classcard', ['userId' => $card['UserID'], 'cardId' => $cardId ]) }}">
        <input type="image" style="height:28px;" src="/images/classcard/class_extend.png" alt="" />
    </a>
    <div >
      <p14>限一年內有效</p14>

    </div>
</div>
@endif


@endsection
