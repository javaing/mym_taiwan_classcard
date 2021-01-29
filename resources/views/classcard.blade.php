@extends('layouts.master')

@section('title', '體位法課卡')

<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prettify/r298/run_prettify.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/css/bootstrap-dialog.min.css" rel="stylesheet" type="text/css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/js/bootstrap-dialog.min.js"></script>


@section('content')
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
    $expiredDate = DBHelper::toDateString($card['Expired']);
    if($expiredDate=='') {$expiredDate = "無期限";}
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

<script type="text/javascript">
    $(function() {
        $("#div_unuse img").click(function() {
            var str = document.getElementById('abcId').value;
            var todayDate = new Date().toISOString().slice(0, 10);
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
                $('#registeLink')[0].click();
            }

        });
    });
</script>


<!-- Modal -->
<div class="modal" id="expiredHint" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                抱歉，已逾期須補差額
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<TABLE BORDER=0 align="center">
    <form>
        <input type=hidden id="abcId" value="{{implode( ", ", $listDate )}}" />


        @for ($i = $oneOrFourClass; $i >= 1; $i--)

        @if ($i%2==0 )
        <TR>
            @else
            @endif
            <TD align="center">
                @if ($i> $card['Points'])

                @if ($registArray && sizeof($registArray)>($oneOrFourClass-$i))
                <div id="div_used">
                    <p16white>{{DBHelper::toDateString( $registArray[$oneOrFourClass-$i]['PointConsumeTime'] ) }}</p16white>
                </div>
                @else
                <div id="div_used">
                    <p16white>N/A</p16white>
                </div>
                @endif

                @else

                @if (DBHelper::isExpired($card))
                <div id="div_unuse" data-toggle="modal" data-target="#expiredHint">
                    @else
                    <div id="div_unuse">
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
                    <a id="registeLink" href="{{ route('registe.classcard',  [$card['Points'], $cardId] ) }}" />

                    @endif
            </TD>
            @if ($i%2==1 )
        </TR>
        @else
        @endif
        @endfor
    </form>
</TABLE>

<script>
    $(function() {
        $("#buycard1").click(function() {
            $('#buyNewLink1')[0].click();
        });
        $("#buycard4").click(function() {
            $('#buyNewLink4')[0].click();
        });
    });
</script>

@if ($card['Points']==0)
<div align="center" style="margin-Top: 8px;">

    <!-- <img class="img-responsive center-block" data-toggle="modal" data-target="#exampleModalCentered" type="image" style="width:106px;height:28px;" src="/images/classcard/buy_card.png" /> -->
    <a href="{{ route('buy.newcard', ['userId' => $card['UserID'] ] ) }}">
        <input type="image" style="height:28px;" src="/images/classcard/buy_card.png" alt="" />
    </a>
</div>

<!-- 單堂或四堂按鈕 -->
<div class="modal" id="exampleModalCentered" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenteredLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <button id="buycard1" class="btn btn-primary btn-lg btn-block border-none" style="color:#c59c40;background-color: #FFf9e5;border-color:#FFFFFF">單堂 500元</button>
                <button id="buycard4" class="btn btn-primary btn-lg btn-block border-none" style="color:#c59c40;background-color: #FFf9e5;border-color:#FFFFFF">四堂 1800元</button>
                <a id="buyNewLink1" href="{{ route('buy.classcard', ['userId' => $card['UserID'], 'point'=>1] ) }}" />
                <a id="buyNewLink4" href="{{ route('buy.classcard', ['userId' => $card['UserID'], 'point'=>4] ) }}" />
            </div>
        </div>
    </div>
</div>

@else

@endif

@if ( DBHelper::isExpired($card) )
<div align="center" style="margin-Top: 8px;">
    <a href="{{ route('extend.classcard', ['userId' => $card['UserID'], 'cardId' => $cardId ]) }}">
        <input type="image" style="height:28px;" src="/images/classcard/class_extend.png" alt="" />
    </a>
</div>
@endif


@endsection
