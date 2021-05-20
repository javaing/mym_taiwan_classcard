@extends('layouts.master')

@section('title', '報表-收支')

@section('content')


@php

$arrIn = DBHelper::getBalanceInJoin('ALL', $start, $end);
$sumIn=0;
foreach($arrIn as $each) {
$sumIn += $each['Payment'];
}
$range = $range ?? '';

@endphp

<link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>
<script>
    (function() {
        function changeBackground() {
            $('body').css('background', '#FFF9E5');
            setTimeout(changeBackground, 100);
        }
        changeBackground();
    })();
</script>
<h4>查詢區間{{substr($start,0,10)}}~{{substr($end,0,10)}}</h4>
<div class="text-left" style="width:200px;">

    <form action="{{url()->action('Balance2Controller@balance2')}}" method="POST">
        @csrf
        <Select name="range" onchange="javascript:submit()">
            <Option Value=""></Option>
            <Option @if($range==1) selected="" @endif Value="1">一二月</Option>
            <Option @if($range==2) selected="" @endif Value="2">三四月</Option>
            <Option @if($range==3) selected="" @endif Value="3">五六月</Option>
            <Option @if($range==4) selected="" @endif Value="4">七八月</Option>
            <Option @if($range==5) selected="" @endif Value="5">九十月</Option>
            <Option @if($range==6) selected="" @endif Value="6">十一十二月</Option>
            <Option @if($range==7) selected="" @endif Value="7">去年十一十二月</Option>
        </Select>
    </form>
</div>


<div id="myTabContent" class="tab-content">
    <div class="tab-pane fade in active" id="prepaid">
        <table>
            <tr height="30">
                <th>名字</th>
                <th>日期</th>
                <th>
                    <center>金額</center>
                </th>
                <th>
                    <center>種類</center>
                </th>
                <th>地區</th>
            </tr>

            @foreach($arrIn as $record)
            <tr height="30">
                <td width="100">
                    <form action="{{url()->action('Balance2Controller@cardDetail2')}}" method="POST">
                        @csrf
                        <input type="hidden" name="userName" value="{{$record['Name']}}">
                        <input type="hidden" name="start" value="{{$start}}">
                        <input type="hidden" name="end" value="{{$end}}">
                        <a href="javascript:;" onclick="parentNode.submit();">{{ $record['Name'] }}</a>
                    </form>

                </td>
                <td width="55"> {{ DBHelper::toMMDD( $record['PaymentTime']) }}</td>
                <td align="right" width="80"> {{ number_format( $record['Payment'])   }}</td>
                <td align="center" width="100">{{ $record['Type']}}</td>
                <td width="100"> {{ $record['Location'] }}</td>
            </tr>
            @endforeach
            <tr>
                <td COLSPAN=4 align="right">
                    小計 {{ number_format($sumIn)}}
                </td>
            </tr>
        </table>
    </div>



</div>

<form action="download" method="POST">
    @csrf
    <input type="hidden" name="filename" value="{{substr($start,0,10)}}_{{substr($end,0,10)}}">
    <input type="hidden" name="start" value="{{$start}}">
    <input type="hidden" name="end" value="{{$end}}">
    <button class="btn btn-sm btn-default" type="submit">下載報表</button>
</form>

<form action="downloadByName" method="POST">
    @csrf
    <input type="hidden" name="filename" value="{{substr($start,0,10)}}_{{substr($end,0,10)}}">
    <input type="hidden" name="start" value="{{$start}}">
    <input type="hidden" name="end" value="{{$end}}">
    <button class="btn btn-sm btn-default" type="submit">報表ByName</button>
</form>

<form action="downloadByKind" method="POST">
    @csrf
    <input type="hidden" name="filename" value="{{substr($start,0,10)}}_{{substr($end,0,10)}}">
    <input type="hidden" name="start" value="{{$start}}">
    <input type="hidden" name="end" value="{{$end}}">
    <button class="btn btn-sm btn-default" type="submit">報表By項目</button>
</form>
@endsection
