@extends('layouts.master')

@section('title', '報表-卡片明細')

@section('content')

@php
{{
        $purchase = DBHelper::getCard($cardId);
        $arrOut = DBHelper::getConsumeByCard($cardId);
        Illuminate\Support\Facades\Log::info('$arrOut=' . $arrOut);
        $sumOut=0;
        foreach($arrOut as $each) {
            $sumOut += $each['Cost'];
        }
    }}
@endphp
<center>
    <p>
        卡號:{{ $cardId }}
    </p>

    <div>
        <table>
            <tr>
                <td> 預付</td>
            </tr>
            <tr height="30">

                <th>日期</th>
                <th>
                    <center>金額</center>
                </th>
            </tr>
            <tr height="30">

                <td> {{ DBHelper::toDateStringShort( $purchase['PaymentTime']) }}</td>
                <td align="right" width="80"> {{ number_format( $purchase['Payment'])   }}</td>
            </tr>

        </table>
    </div>
    <br>
    <div>
        <table>
            <tr>
                <td>已用</td>
            </tr>
            <tr height="30">

                <th>日期</th>
                <th>
                    <center>金額</center>
                </th>
            </tr>

            @foreach($arrOut as $consume)
            <tr height="30">

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
    <br>

    @if($purchase['Payment']>$sumOut)
    <a href="">退款</a>
    @else
    使用完畢
    @endif

</center>






@endsection