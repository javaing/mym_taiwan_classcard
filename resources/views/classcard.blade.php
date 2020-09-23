@extends('layouts.master')

@section('title', '體位法課卡')

@section('content')
@php
{{
    //25.028  121.547 公司
    //25.022  121.520 心悅
    //25.039 121.552 AA
            $arr = DBHelper::getConsume( $card['CardID']);
            $allow_locations = array(
                array('lat' => '25.028','lng'=>'121.547'),
                array('lat' => '25.022','lng'=>'121.520'),
                array('lat' => '25.039','lng'=>'121.552')
            );
        }}
@endphp
<div id="wrapper">
    <?php

    $ip = $_SERVER['REMOTE_ADDR'];
    $geo = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip));
    $lat = $geo["geoplugin_latitude"];
    $lng = $geo["geoplugin_longitude"];
    $lat = "25.0478";
    $lng = "121.5318";
    $isLocationAllow = false;
    if ($lat && $lng) {
        foreach ($allow_locations as $each) {
            if (substr($lat, 0, 6) == $each['lat']) {
                if (substr($lng, 0, 7) == $each['lng']) {
                    echo 'bingo';
                    $isLocationAllow = true;
                    break;
                }
            }
        }
        echo 'not allow register location';
    } else {
        echo 'empty lat,lng';
    }


    ?>
</div>

<TABLE BORDER=0 CELLPADDING="4">
    <TR>
        <TD COLSPAN=4>{{ DBHelper::getUserName($card['UserID']) }} 你好</TD>
    </TR>
    <TR>
        <form>
            @for ($i = 4; $i >= 1; $i--) <TD>
                <div style="display:inline;">
                    @if ($i> $card['Points']) <img style="width: 75;" src="/images/classcard/graylotus.png">
                    @if ($arr && sizeof($arr)> 0)
                    <br>
                    <center>
                        <font size="1">
                            {{DBHelper::toDateString( $arr[4-$i]['PointConsumeTime'] ) }}
                        </font>
                    </center>
                    @endif
                    @else
                    <a href="{{ route('registe.classcard',  [$card['Points'], $card['CardID']]) }}"><img style="width: 75;" src="/images/classcard/pinklotus.png"></a>
                    @endif
                </div>
            </TD>
            @endfor

        </form>
    </TR>
    <TR>
        <TD COLSPAN=4 align="right">期限: {{ DBHelper::toDateString($card['Expired']) }}</TD>
    </TR>
    @php
    {{ $dt = App\Helpers\DBHelper::getMongoDateNow(); }}
    @endphp
    <TR>
        <TD align="center" COLSPAN=2>@if ($card['Points']==0) <H4>
                <a href="{{ route('buy.classcard', ['userId' => $card['UserID']] ) }}">買新卡</a>
            </H4>
            @endif</TD>
        <TD COLSPAN=2> @if ($dt>$card['Expired'] && $card['Points']>0) <H4>
                <a href="{{ route('buy.classcard', ['userId' => $card['UserID']]) }}">展期</a>
            </H4>
            @endif</TD>
    </TR>
</TABLE>
@endsection
