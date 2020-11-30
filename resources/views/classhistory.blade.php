@extends('layouts.master')

@section('title', '體位法課卡')

@section('content')
@php
{{
    $cardId = $card['CardID'];
    $userId = $card['UserID'];
    $size = sizeof(DBHelper::getUserHistory($userId));
    $registArray = DBHelper::getConsume( $cardId);
    $thisYearCount = DBHelper::thisYearCount($userId);
    $thisMonthCount = DBHelper::thisMonthCount( $userId);
    $stampCount = 4;
    if(DBHelper::isSingleClassCard($cardId)) $stampCount = 1;
}}
@endphp

<div align="center">
    <div style="margin-top: 3px;">
        <p18>Hello!</p18>
    </div>
    <div style="margin-top: 3px;margin-bottom: 6px;">
        <p18>{{ DBHelper::getUserName($userId) }}</p18>
    </div>
</div>

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
<div align="center" style="margin-bottom: 20px">
    <p16>{{ $index+1}}/{{ $size }}</p16><br>
    <p14>(卡號 {{$cardId}})</p14>
</div>

<TABLE BORDER=0 align="center">

    @for ($i = $stampCount; $i >= 1; $i--)
    @if ($i%2==0 )
    <TR>
        @else
        @endif
        <TD align="center">
            @if ($i> $card['Points'])

            @if ($registArray && sizeof($registArray)>($stampCount-$i))
            <div id="div_used">
                <p16white>{{DBHelper::toDateString( $registArray[$stampCount-$i]['PointConsumeTime'] ) }}</p16white>
            </div>
            @else
            <div id="div_used">
                <p16white>n/a</p16white>
            </div>
            @endif

            @else
            <div id="div_used">
                <p16white>尚未使用</p16white>
            </div>
            @endif
        </TD>
        @if ($i%2==1 )
    </TR>
    @else
    @endif
    @endfor

    <tr>
        <td style="width:50%;text-align:center;">
            <p18>今年/{{$thisYearCount}}堂</p18>
        </td>
        <td style="width:50%;text-align:center;">
            <p18>本月/{{$thisMonthCount}}堂</p18>
        </td>
    </tr>
</TABLE>


<div align="center" style="margin-Top: 12px;">
    <a href="{{ route('reuse.line') }}">
        <input type="image" style="width:106px;height:28px;" src="/images/classcard/back_classcard.png" alt="" />
    </a>
</div>


@endsection