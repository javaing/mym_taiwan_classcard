@extends('layouts.master')

@section('title', '報表-卡片明細')

@section('content')

@php
{{
        $arrIn = DBHelper::getCardHistory($cardId);
        $arrOut = DBHelper::getConsumeByCard($cardId);
        Illuminate\Support\Facades\Log::info('$arrOut=' . $arrOut);
        $sumIn=0; $sumOut=0;
        foreach($arrIn as $each) {
            $sumIn += $each['Payment'];
        }
        foreach($arrOut as $each) {
            $sumOut += $each['Cost'];
        }
    }}
@endphp
<script>
    (function() {
        function changeBackground() {
            $('body').css('background', '#FFF9E5');
            setTimeout(changeBackground, 100);
        }
        changeBackground();
    })();
    $(document).ready(function() {
        $("a[id='refundbtn']").dblclick(function() {
            alert("無法重複退款");
        });
    });
</script>
<center>

    <div class="text">
        <p>
            {{ DBHelper::getUserName($arrIn[0]['UserID']) }}
            <br>
            卡號:{{ $cardId }}
        </p>
    </div>

    <div>
        <table>
            <tr>
                <td> 預付(金額負數表示退款)</td>
            </tr>
            <tr height="30">

                <th>日期</th>
                <th>
                    <center>金額</center>
                </th>
            </tr>
            @foreach($arrIn as $purchase)
            <tr height="30">
                <td> {{ DBHelper::toDateStringShort( $purchase['PaymentTime']) }}</td>
                <td align="right" width="80"> {{ number_format( $purchase['Payment'])   }}</td>
            </tr>
            @endforeach

            <tr>
                <td>&nbsp;&nbsp;</td>
            </tr>
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
    <br>

    @if($sumIn-$sumOut)

    <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <button class="btn btn-primary" data-toggle="modal" data-target="#myModal">
        退款
    </button>
    <!-- 模态框（Modal） -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">
                        返還作業
                    </h5>
                </div>
                <div class="modal-body">
                    退款{{$sumIn-$sumOut}}元
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消
                    </button>
                    <a id='refundbtn' href="{{ route('account.deposite', ['cardId' => base64_encode($cardId), 'amount'=>$sumIn-$sumOut]) }}" class="btn btn-primary">確認</a>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div>
    @else
    <a href="/account/balance">使用完畢</a>
    @endif

</center>






@endsection