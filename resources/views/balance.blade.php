@extends('layouts.master')

@section('title', '報表-收支')

@section('content')

@php
use App\Helpers\DBHelperOnline as DBHelperOnline;
{{
        $arrIn = DBHelper::getBalanceIn($start, $end);
        //Illuminate\Support\Facades\Log::info('$arrIn=' . $arrIn);
        $arrIn2 = DBHelperOnline::getBalanceIn($start, $end);
        //$arrIn = array_merge($arrIn, $arrIn2);
        $sum1And2 = [];
        foreach($arrIn as $element) {
          array_push($sum1And2, $element);
        }
        foreach($arrIn2 as $element) {
          array_push($sum1And2, $element);
        }
        $arrIn = $sum1And2;

        $arrOut = DBHelper::getBalanceOut($start, $end);
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
                    <a href="{{ route('account.cardDetail', ['cardId' => base64_encode($purchase['CardID'])   ]) }}">{{DBHelper::getUserName( $purchase['UserID']) }}</a>

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
                        {{ DBHelper::getUserName( $consume['UserID']) }}
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
