@extends('layouts.master')

@section('title', '報表-卡片明細')

@section('content')

@php
{{
        $arrIn = $paidArray;
        //Illuminate\Support\Facades\Log::info('$arrIn=' . $arrIn);
        $sumIn=0;
        foreach($arrIn as $each) {
            $sumIn += $each['Payment'];
        }
        $map = DBHelper::getPersonalIDMap();
    }}
@endphp

<center>
    <div class="text">
        <p>查詢區間{{substr($start,0,10)}}~{{substr($end,0,10)}}</p>
        身分證ID:{{ $map[ $each['Name']  ]}}
    </div>
    <div>
        <table>
            <tr height="40">
                <td COLSPAN=4> 已收(金額負數表示退款)</td>
            </tr>
            <tr height="40">
                <th>名字</th>
                <th>日期</th>
                <th>
                    <center>金額</center>
                </th>
                <th>
                    <center>種類</center>
                </th>

            </tr>
            @foreach($arrIn as $record)
            <tr height="40">
                <td width="100"> {{ $record['Name'] }}</td>
                <td width="55"> {{ DBHelper::toMMDD( $record['PaymentTime']) }}</td>
                <td align="right" width="80"> {{ number_format( $record['Payment'])   }}</td>
                <td align="center" width="100">{{ $record['Type']}}</td>
            </tr>
            @endforeach
            <tr height="40">
                <td COLSPAN=3 align="right">
                    小計 {{number_format($sumIn)}}
                </td>
            </tr>
        </table>
    </div>
    <br>
    <br>



</center>






@endsection