@extends('layouts.master')

@section('title', '線上課程-一覽及購買')

@section('content')

@php
use App\Helpers\DBHelper as DBHelper;
use App\Helpers\DBHelperOnline as DBHelperOnline;
{{
    $today = DBHelper::today();
    $oncardUsers = DBHelperOnline::getUsersNoOnlineCard();
    $boughtList = DBHelperOnline::getOnlineCardList();
    //Illuminate\Support\Facades\Log::info('$oncardUsers=' . $oncardUsers);

    }}
@endphp

<link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>
<div class="text-left" style="width:200px;">
    <p18>Online課卡購買作業</p18>
    <form action="{{url()->action('OnlineClassController@buy')}}" method="POST">
        @csrf

        <select class="form-control" name="userId">

            <option>請選購卡人</option>

            @foreach ($oncardUsers as $purchase)
            <option value="{{ base64_encode( $purchase['UserID'] )}}">
                {{ DBHelper::getUserName(    $purchase['UserID']) }}
            </option>
            @endforeach
        </select>

        <input class="date form-control" type="text" name="buydate" value="{{$today}}">
        <script type="text/javascript">
            $('.date').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                endDate: '+0d'
            });
        </script>

        <select class="form-control" name="point">
            <option value="4">買四堂</option>
            <option value="1">買單堂</option>
        </select>

        <button class="btn btn-link" type="submit">確定</button>
    </form>
</div>


<div id="myTabContent" class="tab-content">
    <div style="margin-top: 3px;">
        <p18>Online課卡一覽(卡片明細可退款)</p18>
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

            @foreach($boughtList as $purchase)
            <tr height="30">
                <td width="100">
                    <a href="{{ route('onlineclass.cardDetail', ['cardId' => base64_encode($purchase['CardID']) ]) }}">{{$purchase['CardID'] }}</a>

                    <br> ({{ DBHelper::getUserName(    $purchase['UserID']) }})</td>
                <td> {{ DBHelper::toDateStringJS( $purchase['PaymentTime']) }}</td>
                <td align="right" width="80"> {{ number_format( $purchase['Payment'])   }}</td>
                <td align="right" width="40">{{ $purchase['Points'] }}</td>
            </tr>
            @endforeach

        </table>
    </div>






</div>


@endsection
