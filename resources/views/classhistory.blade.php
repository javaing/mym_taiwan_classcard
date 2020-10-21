@extends('layouts.master')

@section('title', '體位法課卡')

@section('content')
@php
{{
    $size = sizeof(DBHelper::getUserHistory($card['UserID']));
    $consumeCount = DBHelper::getConsume( $card['CardID']);
}}
@endphp

<TABLE BORDER=0 style="margin-bottom: 12px;width:100%;">
    <TR>
        <TD COLSPAN=2 align="right">
            <p18>{{ DBHelper::getUserName($card['UserID']) }}</p18>
        </TD>
        <TD align="right">
            <p18>您好</p18>
        </TD>
    </TR>
</TABLE>

<div align="center">
    <img style="margin-bottom: 12px;width:80%;" src="/images/div.png">
</div>
<div align="center" style="margin-bottom: 2px;">

    @if ($index-1>=0 )

    <a href="{{ route('show.classhistory', [
        'userId' => $card['UserID'], 
        'index'=>$index-1,
        ] ) }}">
        <img style="width: 20px;height:20px" src="/images/arrow_left.png">
    </a>
    @else
    <img style="width: 20px;height:20px" src="/images/arrow_left.png">
    @endif

    <p16>上課紀錄</p16>

    @if ($index+1<$size ) <a href="{{ route('show.classhistory', [
        'userId' => $card['UserID'], 
        'index'=>$index+1,
        ] ) }}">
        <img style="width: 20px;height:20px" src="/images/arrow_right.png">
        </a>
        @else
        <img style="width: 20px;height:20px" src="/images/arrow_right.png">
        @endif
</div>
<div align="center" style="margin-bottom: 24px">
    <p16>{{ $index+1}}/{{ $size }}</p16>
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

<div align="center" style="margin-Top: 8px;width:136px;height:36px;">
</div>
<div align="center" style="margin-Top: 24px;">
    <a href="{{ route('reuse.line') }}">
        <input type="image" style="width:136px;height:36px;" src="/images/classcard/back_classcard.png" alt="" />
    </a>
</div>


@endsection