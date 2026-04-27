@extends('layouts.master')

@section('title', '報表-收支')

@section('content')

@php
use App\Helpers\DBHelperOnline as DBHelperOnline;
use App\Helpers\Tools as Tools;
{{
        $arrIn = DBHelper::getBalanceIn($start, $end);
        //Illuminate\Support\Facades\Log::info('$arrIn=' . $arrIn);
        $arrIn2 = DBHelperOnline::getBalanceIn($start, $end);
        $arrIn = Tools::merge($arrIn, $arrIn2);

        $arrOut = DBHelper::getBalanceOut($start, $end);
        $arrOut2 = DBHelperOnline::getBalanceOut($start, $end);
        $arrOut = Tools::merge($arrOut, $arrOut2);

        $sumIn=0; $sumOut=0;
        foreach($arrIn as $each) {
            $sumIn += $each['Payment'];
        }
        foreach($arrOut as $each) {
            $sumOut += $each['Cost'];
        }
        Illuminate\Support\Facades\Log::info('account.balance.prepaid.data', [
            'start' => (string) $start,
            'end' => (string) $end,
            'prepaid_count' => count($arrIn),
            'used_count' => count($arrOut),
            'sum_in' => $sumIn,
            'sum_out' => $sumOut,
        ]);
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

    $(function() {
        $('.js-balance-tab').on('click', function(event) {
            event.preventDefault();

            var target = $(this).data('target');
            $('.js-balance-tab').parent().removeClass('active');
            $(this).parent().addClass('active');
            $('.js-balance-pane').removeClass('active show').hide();
            $(target).addClass('active show').show();

            console.log('[balance] display tab clicked', {
                target: target,
                text: $(this).text().trim()
            });
        });
        $('.js-prepaid-card-link').on('click', function(event) {
            console.log('[balance] prepaid card link clicked', {
                href: this.href,
                cardId: $(this).data('card-id'),
                payment: $(this).data('payment'),
                name: $(this).text().trim()
            });
        });
    });
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
        <a class="js-balance-tab" href="#prepaid" data-target="#prepaid">
            預收
        </a>
    </li>
    <li><a class="js-balance-tab" href="#used" data-target="#used">學員上課</a></li>
</ul>
<div id="myTabContent" class="tab-content">
    <div class="tab-pane js-balance-pane show active" id="prepaid">
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

                    @if ($purchase['Payment']==300|| $purchase['Payment']==1200)
                      <a class="js-prepaid-card-link" data-card-id="{{ $purchase['CardID'] }}" data-payment="{{ $purchase['Payment'] }}" href="{{ route('onlineclass.cardDetail', ['cardId' => base64_encode($purchase['CardID'])]) }}">{{DBHelper::getUserName( $purchase['UserID']) }}</a>
                    @else
                      <a class="js-prepaid-card-link" data-card-id="{{ $purchase['CardID'] }}" data-payment="{{ $purchase['Payment'] }}" href="{{ route('account.cardDetail', ['cardId' => base64_encode($purchase['CardID'])]) }}">{{DBHelper::getUserName( $purchase['UserID']) }}</a>
                    @endif

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

    <div class="tab-pane js-balance-pane" id="used" style="display:none;">
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
