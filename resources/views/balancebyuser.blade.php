@extends('layouts.master')

@section('title', '報表-收支')

@section('content')

@php
{{
        $arrIn = DBHelper::getBalanceByUserIn($userId);
        $arrOut = DBHelper::getBalanceByUserOut($userId);
        Illuminate\Support\Facades\Log::info('$arrIn=' . $arrIn);
        $sumIn=0; $sumOut=0;
        foreach($arrIn as $each) {
            $sumIn += $each['Payment'];
        }
        foreach($arrOut as $each) {
            $sumOut += $each['Cost'];
        }
    }}
@endphp

<link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script>
    (function() {
        function changeBackground() {
            $('body').css('background', '#FFF9E5');
            setTimeout(changeBackground, 100);
        }
        changeBackground();
    })();
</script>

<div align="center" style="margin-Top: 8px;">
    {{ DBHelper::getUserName($arrIn[0]['UserID']) }} 你好
</div>


<ul id="myTab" class="nav nav-tabs">
    <li class="active">
        <a href="#prepaid" data-toggle="tab">
            預收
        </a>
    </li>
    <li><a href="#used" data-toggle="tab">學員上課</a></li>
</ul>
<div id="myTabContent" class="tab-content">
    <div class="tab-pane fade in active" id="prepaid">
        <table>
            <tr height="30">
                <th>名字</th>
                <th>日期</th>
                <th>
                    <center>金額</center>
                </th>
            </tr>

            @foreach($arrIn as $purchase)
            <tr height="30">
                <td width="100">
                    <a href="{{ route('account.cardDetail', ['cardId' =>$purchase['CardID']]) }}">{{ $purchase['CardID'] }}</a>

                </td>
                <td> {{ DBHelper::toDateStringShort( $purchase['PaymentTime']) }}</td>
                <td align="right" width="80"> {{ number_format( $purchase['Payment'])   }}</td>
            </tr>
            @endforeach
            <tr>
                <td COLSPAN=4 align="right">
                    小計 {{ number_format($sumIn)}}
                </td>
            </tr>
        </table>
    </div>

    <div class="tab-pane fade" id="used">
        <table>
            <tr height="30">
                <th>名字</th>
                <th>日期</th>
                <th>
                    <center>金額</center>
                </th>
            </tr>

            @foreach($arrOut as $consume)
            <tr height="30">
                <td width="100">
                    <a href="/alluser/{{ $consume['UserID'] }}">
                        {{ $consume['CardID'] }}
                    </a>
                </td>
                <td>{{ DBHelper::toDateStringShort( $consume['PointConsumeTime']) }}</td>
                <td align="right" width="80"> {{ number_format( $consume['Cost'])  }}</td>
            </tr>
            @endforeach
            <tr>
                <td COLSPAN=4 align="right">
                    小計 {{number_format($sumOut)}}
                </td>
            </tr>
        </table>
    </div>


</div>

@endsection