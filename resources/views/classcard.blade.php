@extends('layouts.master')

@section('title', '體位法課卡')

@section('content')
@php
{{
    $consumeCount = DBHelper::getConsume( $card['CardID']);
}}
@endphp

<div align="right" style="margin-right: 30px;">
    <p18>
        {{ DBHelper::getUserName($card['UserID']) }} 你好
    </p18>
</div>

<div align="center">
    <img style="margin-bottom: 24px;width:80%;" src="/images/div.png">
</div>
<div align="center" style="margin-bottom: 2px">
    <p16>課程使用期限</p16>
</div>
<div align="center" style="margin-bottom: 24px">
    <p16>{{ DBHelper::toDateString($card['Expired']) }}</p16>
</div>

<TABLE BORDER=0 align="center">

    <form>
        @for ($i = 4; $i >= 1; $i--)

        @if ($i%2==0 )
        <TR>
            @else
            @endif
            <TD align="center">
                @if ($i> $card['Points'])
                <div id="div_used">
                    @if ($consumeCount && sizeof($consumeCount)>(4-$i))
                    <p14white>
                        {{DBHelper::toDateString( $consumeCount[4-$i]['PointConsumeTime'] ) }}
                    </p14white>
                    @endif
                </div>
                @else
                <a href="{{ route('registe.classcard',  [$card['Points'], $card['CardID']]) }}">
                    <div id="div_unuse"></div>
                </a>
                @endif
            </TD>
            @if ($i%2==1 )
        </TR>
        @else
        @endif
        @endfor
    </form>
</TABLE>

@php
{{ $dt = App\Helpers\DBHelper::getMongoDateNow(); }}
@endphp
@if ($card['Points']==0)

<div align="center" style="margin-Top: 8px;">
    <a href="{{ route('buy.classcard', ['userId' => $card['UserID']] ) }}">
        <input type="image" style="width:136px;height:36px;" src="/images/classcard/buy_card.png" alt="購買新卡" />
    </a>
</div>
@else
<div align="center" style="margin-Top: 8px;width:136px;height:36px;">

</div>
@endif
<div align="center" style="margin-Top: 24px;">
    <a href="{{ route('show.classhistory', [
        'userId' => $card['UserID'], 
        'index'=>0
        ] ) }}">
        <input type="image" style="width:136px;height:36px;" src="/images/classcard/class_history.png" alt="" />
    </a>
</div>


@if ($dt>$card['Expired'] && $card['Points']>0)
<div>
    <a href="{{ route('buy.classcard', ['userId' => $card['UserID']]) }}">展期</a>
</div>
@endif

@endsection