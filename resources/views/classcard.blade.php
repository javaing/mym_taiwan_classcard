@extends('layouts.master')

@section('title', '體位法課卡')

@section('content')
@php
{{
            $arr = DBHelper::getConsume( $card['CardID']);
        }}
@endphp


<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>
    function getLocation() {
        alert(position.coords);
        if (navigator.geolocation) {
            alert(position.coords.latitude);
            navigator.geolocation.getCurrentPosition(savePosition, positionError, {
                timeout: 10000
            });
        } else {
            //Geolocation is not supported by this browser
        }
    }

    // handle the error here
    function positionError(error) {
        var errorCode = error.code;
        var message = error.message;

        alert(message);
    }

    function savePosition(position) {
        $.post("geo.php", {
            lat: position.coords.latitude,
            lng: position.coords.longitude
        });
    }
</script>
<button onclick="getLocation();">Get My Location</button>

<div id="wrapper">
    <?php
    $ip = $_SERVER['REMOTE_ADDR'];
    echo var_export(unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip)));
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