@extends('layouts.master')

@section('title', '體位法課卡')

@section('content')
@php
{{
    $registArray = DBHelper::getConsume( $card['CardID']);
    $today = DBHelper::todaySlash();
    $isTodayDone = false;
    for ($i = 0; $i < sizeof($registArray); $i++) {
        if($today==DBHelper::toDateString( $registArray[$i]['PointConsumeTime'])) {
            $isTodayDone = true;
            break;
        }
    }
    $oneOrFourClass = 4;
    if($card['Payment']==500) {$oneOrFourClass=1;}
}}
@endphp

<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prettify/r298/run_prettify.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/css/bootstrap-dialog.min.css" rel="stylesheet" type="text/css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/js/bootstrap-dialog.min.js"></script>
<div class="display-flex">
    <div class="div-size" style="width:80%;text-align:center">
        <p18>{{ DBHelper::getUserName($card['UserID']) }}</p18>
    </div>
    <div class="div-size">
        <p18>您好</p18>
    </div>
</div>


<div align="center">
    <img style="margin-bottom: 12px;width:80%;" src="/images/div.png">
</div>
<div align="center" style="margin-bottom: 2px">
    <p16>課程使用期限</p16>
</div>
<div align="center" style="margin-bottom: 24px">
    <p16>{{ DBHelper::toDateString($card['Expired']) }}</p16>
</div>

@if ($isTodayDone)
<script type="text/javascript">
    $(function() {
        $("#div_unuse img").click(function() {
            var N = $(this).attr("id").substr(2);

            $("#SS" + N).attr("src", "/images/classcard/point_today.png");
            window.setTimeout(function() {
                $("#SS" + N).attr("src", "/images/classcard/point_" + N + ".png");
            }, 2000);
        });
    });
</script>
@else
<script type="text/javascript">
    $(function() {
        $("#div_unuse img").click(function() {
            $('#registeLink')[0].click();
        });
    });
</script>
@endif


<TABLE BORDER=0 align="center">
    <form>
        @for ($i = $oneOrFourClass; $i >= 1; $i--)

        @if ($i%2==0 )
        <TR>
            @else
            @endif
            <TD align="center">
                @if ($i> $card['Points'])

                @if ($registArray && sizeof($registArray)>($oneOrFourClass-$i))
                <div id="div_used">
                    <p14white>{{DBHelper::toDateString( $registArray[$oneOrFourClass-$i]['PointConsumeTime'] ) }}</p14white>
                </div>
                @else
                <div id="div_used" />
                @endif

                @else

                <div id="div_unuse">
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
                </div>
                <a id="registeLink" href="{{ route('registe.classcard',  [$card['Points'], $card['CardID']]) }}" />

                @endif
            </TD>
            @if ($i%2==1 )
        </TR>
        @else
        @endif
        @endfor
    </form>
</TABLE>


<!-- CSS -->
<style>
    .ui-widget-header,
    .ui-state-default,
    ui-button {
        background: #b9cd6d;
        border: 1px solid #b9cd6d;
        color: #FFFFFF;
        font-weight: bold;
    }
</style>
<script>
    $(function() {

        $("#buyNewBtn").click(function() {
            //alert('buy new card');
            var dialog = new BootstrapDialog({
                message: function(dialogRef) {
                    var $message = $('<div>購買課卡</div>');
                    var $button = $('<button class="btn btn-primary btn-lg btn-block">單堂 500元</button>');
                    $button.on('click', {
                        dialogRef: dialogRef
                    }, function(event) {
                        $('#buyNewLink1')[0].click();
                        event.data.dialogRef.close();
                    });
                    $message.append($button);

                    var $button2 = $('<button class="btn btn-primary btn-lg btn-block">四堂 1800元</button>');

                    $button2.on('click', {
                        dialogRef: dialogRef
                    }, function(event) {
                        $('#buyNewLink4')[0].click();
                        event.data.dialogRef.close();
                    });
                    $message.append($button2);

                    return $message;
                },
                closable: false
            });
            dialog.realize();
            dialog.getModalHeader().hide();
            dialog.getModalFooter().hide();
            dialog.open();

        });

    });
</script>

@if ($card['Points']==0)
<div align="center" style="margin-Top: 8px;">
    <input id="buyNewBtn" type="image" style="width:106px;height:28px;" src="/images/classcard/buy_card.png" alt="購買新卡" />
    <a id="buyNewLink1" href="{{ route('buy.classcard', ['userId' => $card['UserID'], 'point'=>1] ) }}" />
    <a id="buyNewLink4" href="{{ route('buy.classcard', ['userId' => $card['UserID'], 'point'=>4] ) }}" />
</div>


@else
<div align="center" style="margin-Top: 8px;width:106px;height:28px;">
</div>
@endif

<div align="center" style="margin-Top: 8px;">
    <a href="{{ route('show.classhistory', [
        'userId' => $card['UserID'], 
        'index'=>0
        ] ) }}">
        <input type="image" style="width:106px;height:28px;" src="/images/classcard/class_history.png" alt="" />
    </a>
</div>

@php
{{ $dt = App\Helpers\DBHelper::getMongoDateNow(); }}
@endphp
@if ($dt>$card['Expired'] && $card['Points']>0)
<div>
    <a href="{{ route('buy.classcard', ['userId' => $card['UserID']]) }}">展期</a>
</div>
@endif


@endsection