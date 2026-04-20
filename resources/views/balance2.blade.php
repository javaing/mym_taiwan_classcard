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
$dbFrom = DBHelper::dateShiftFrom($start);
$dbTo   = DBHelper::dateShiftTo($end);
$rowCount = count($arrIn);

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
<h4>查詢區間 {{substr($start,0,10)}} ~ {{substr($end,0,10)}}</h4>
<div style="background:#fff;border:1px solid #e5c27a;padding:6px 10px;margin:4px 0;display:inline-block;border-radius:4px;font-size:14px;">
    <div>實際查詢資料庫區間：<b>{{ $dbFrom }} ~ {{ $dbTo }}</b>（PaymentTime &gt;= from 且 &lt; to）</div>
    <div>資料筆數：<b>{{ $rowCount }}</b> 筆，小計金額：<b>{{ number_format($sumIn) }}</b></div>
    @if(request()->has('debug') && $rowCount > 0)
    <details style="margin-top:6px;">
        <summary>debug: 第一筆原始結構</summary>
        <pre style="max-width:900px;overflow:auto;background:#f5f5f5;padding:6px;">{{ json_encode($arrIn[0] ?? null, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
    </details>
    @endif
</div>
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
<div class="text-left" style="width:200px;">
    <form action="{{url()->action('Balance2Controller@balance2FreeRange')}}" method="POST">
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


<div id="myTabContent" class="tab-content">
    <div class="tab-pane show active" id="prepaid">
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

            @php
                $safe = function($v) {
                    if (is_null($v)) return '';
                    if (is_scalar($v)) return (string)$v;
                    if (is_object($v) && method_exists($v, '__toString')) return (string)$v;
                    return json_encode($v, JSON_UNESCAPED_UNICODE);
                };
                $safeDate = function($v) {
                    try {
                        if (!$v) return '';
                        if (is_object($v) && method_exists($v, 'toDateTime')) return $v->toDateTime()->format('m-d');
                        if (is_string($v)) return substr($v, 5, 5);
                        return '';
                    } catch (\Throwable $e) { return '!'; }
                };
                $safeNum = function($v) {
                    if (is_numeric($v)) return number_format((float)$v);
                    return (string)$v;
                };
            @endphp
            @forelse($arrIn as $record)
            <tr height="30">
                <td width="100">
                    <form action="{{url()->action('Balance2Controller@cardDetail2')}}" method="POST">
                        @csrf
                        <input type="hidden" name="userName" value="{{ $safe($record['Name'] ?? '') }}">
                        <input type="hidden" name="start" value="{{$start}}">
                        <input type="hidden" name="end" value="{{$end}}">
                        <a href="javascript:;" onclick="parentNode.submit();">{{ $safe($record['Name'] ?? '') }}</a>
                    </form>
                </td>
                <td width="55">{{ $safeDate($record['PaymentTime'] ?? null) }}</td>
                <td align="right" width="80">{{ $safeNum($record['Payment'] ?? 0) }}</td>
                <td align="center" width="100">{{ $safe($record['Type'] ?? '') }}</td>
                <td width="100">{{ $safe($record['Location'] ?? '') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="color:#c00;">(arrIn 沒有任何項目可以顯示)</td></tr>
            @endforelse
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
    <input type="hidden" name="start" value="{{$start}}">
    <input type="hidden" name="end" value="{{$end}}">
    <button class="btn btn-sm btn-default" type="submit">下載報表</button>
</form>

<form action="downloadByName" method="POST">
    @csrf
    <input type="hidden" name="start" value="{{$start}}">
    <input type="hidden" name="end" value="{{$end}}">
    <button class="btn btn-sm btn-default" type="submit">報表ByName</button>
</form>

<form action="downloadByKind" method="POST">
    @csrf
    <input type="hidden" name="start" value="{{$start}}">
    <input type="hidden" name="end" value="{{$end}}">
    <button class="btn btn-sm btn-default" type="submit">報表By項目</button>
</form>
@endsection
