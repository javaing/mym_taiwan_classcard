@extends('layouts.master')

@section('title', '報表-收支')

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

<div class="d-flex flex-column" style="margin-top: 30px;">
    <div class="p-2">
        <table>
            <tr>
                <th>
                    <h4>預收</h4>
                </th>
            </tr>
            <tr>
                <th>用戶</th>
                <th>卡號</th>
                <th>日期</th>
                <th>
                    <center>金額</center>
                </th>
            </tr>

            @foreach($arrIn as $purchase)
            <tr>
                <td width="100"> {{ DBHelper::getUserName(    $purchase['UserID']) }}</td>
                <td> {{$purchase['CardID'] }}</td>
                <td> {{ DBHelper::toDateString( $purchase['PaymentTime']) }}</td>
                <td align="right" width="100"> {{ number_format( $purchase['Payment'])   }}</td>
            </tr>
            @endforeach
            <tr>
                <td COLSPAN=4 align="right">
                    小計 {{ number_format($sumIn)}}
                </td>
            </tr>
        </table>
    </div>
    <div class="p-2">
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
                <td>{{$consume['CardID'] }}</td>
                <td>{{ DBHelper::toDateString( $consume['PointConsumeTime']) }}</td>
                <td align="right" width="100"> {{ number_format( $consume['Cost'])  }}</td>
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