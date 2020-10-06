@extends('layouts.master')

@section('title', '報表-收支')
<style>
    body {
        font-family: Arial;
    }

    /* Style the tab */
    .tab {
        overflow: hidden;
        border: 1px solid #ccc;
        background-color: #f1f1f1;
    }

    /* Style the buttons inside the tab */
    .tab button {
        background-color: inherit;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        transition: 0.3s;
        font-size: 17px;
    }

    /* Change background color of buttons on hover */
    .tab button:hover {
        background-color: #ddd;
    }

    /* Create an active/current tablink class */
    .tab button.active {
        background-color: #ccc;
    }

    /* Style the tab content */
    .tabcontent {
        display: none;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-top: none;
    }
</style>
@section('content')

@php
{{
        $arrIn = DBHelper::getBalanceIn($start, $end);
        $arrOut = DBHelper::getBalanceOut($start, $end);
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

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>
<div class="text-left" style="width:200px;">
    <h4>查詢區間</h4>
    <form action="{{url()->action('AccountController@balance')}}" method="POST">
        @csrf
        <input class="date form-control" type="text" name="start" value="{{$start}}">
        <input class="date form-control" type="text" name="end" value="{{$end}}">
        <script type="text/javascript">
            $('.date').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });
        </script>
        <button class="btn btn-link" type="submit">查詢</button>
    </form>
</div>

<div class="tab">
    <button class="tablinks" onclick="openCity(event, 'London')">預收</button>
    <button class="tablinks" onclick="openCity(event, 'Paris')">學員上課</button>
</div>

<div id="London" style="display: block;" class="tabcontent">
    <table>
        <tr>
            <th>
                <h4>預收</h4>
            </th>
        </tr>
        <tr>
            <th>卡號</th>
            <th>日期</th>
            <th>
                <center>金額</center>
            </th>
        </tr>

        @foreach($arrIn as $purchase)
        <tr>
            <td width="100"> {{$purchase['CardID'] }} <br> ({{ DBHelper::getUserName(    $purchase['UserID']) }})</td>
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

<div id="Paris" class="tabcontent">
    <table>
        <tr>
            <th>
                <h4>學員上課</h4>
            </th>
        </tr>
        <tr>
            <th>卡號</th>
            <th>日期</th>
            <th>
                <center>金額</center>
            </th>
        </tr>

        @foreach($arrOut as $consume)
        <tr>
            <td width="100">{{$consume['CardID'] }}</td>
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


<script>
    function openCity(evt, cityName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(cityName).style.display = "block";
        evt.currentTarget.className += " active";
    }
</script>


@endsection