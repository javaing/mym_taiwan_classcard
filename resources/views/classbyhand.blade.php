@extends('layouts.master')

@section('title', '報表-收支')

@section('content')

@php
{{
    $today = DBHelper::today();
    $arrIn = DBHelper::getLiveCards();
    Illuminate\Support\Facades\Log::info('$arrIn=' . $arrIn);

    }}
@endphp

<link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>
<div class="text-left" style="width:200px;">
    <p18>補卡作業</p18>
    <form action="{{url()->action('AccountController@registeclassByhand')}}" method="POST">
        @csrf

        <select class="form-control" name="cardId">

            <option>請選卡</option>

            @foreach ($arrIn as $purchase)
            <option value="{{ $purchase['CardID'] }}">
                {{ DBHelper::getUserName(    $purchase['UserID']) }}( {{ $purchase['CardID']   }} )
            </option>
            @endforeach
        </select>

        <input class="date form-control" type="text" name="registedate" value="{{$today}}">
        <script type="text/javascript">
            $('.date').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                endDate: '+0d'
            });
        </script>

        <button class="btn btn-link" type="submit">確定</button>
    </form>
</div>


<div id="myTabContent" class="tab-content">
    <div style="margin-top: 3px;">
        <p18>課卡一覽</p18>
    </div>
    <div class="tab-pane fade in active" id="prepaid">
        <table>
            <tr height="30">
                <th>卡號</th>
                <th>購卡日期</th>
                <th>
                    <center>金額</center>
                </th>
                <th>剩餘次數</th>
            </tr>

            @foreach($arrIn as $purchase)
            <tr height="30">
                <td width="100">
                    <a href="{{ route('account.cardDetail', ['cardId' => base64_encode($purchase['CardID']) ]) }}">{{$purchase['CardID'] }}</a>

                    <br> ({{ DBHelper::getUserName(    $purchase['UserID']) }})</td>
                <td> {{ DBHelper::toDateStringShort( $purchase['PaymentTime']) }}</td>
                <td align="right" width="80"> {{ number_format( $purchase['Payment'])   }}</td>
                <td align="right" width="40">{{ $purchase['Points'] }}</td>
            </tr>
            @endforeach

        </table>
    </div>






</div>


@endsection