@extends('layouts.master')

@section('title', '報表-卡片明細')

@section('content')

@php
{{
        $arrIn = $paidArray;
        Illuminate\Support\Facades\Log::info('$arrIn=' . $arrIn);
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
                    <center>身分證</center>
                </th>
            </tr>
            @foreach($arrIn as $each)
            <tr height="40">
                <td width="100"> {{DBHelper::getUserName( $each['UserID']) }}</td>
                <td width="40"> {{ DBHelper::toMMDD( $each['PaymentTime']) }}</td>
                <td align="right" width="80"> {{ number_format( $each['Payment'])   }}</td>
                <td align="right" width="100"> {{ $map[ $each['UserID']  ]}}</td>
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