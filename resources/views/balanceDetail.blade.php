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
            @foreach($arrIn as $purchase)
            <tr height="30">
                <td> {{ DBHelper::toDateStringShort( $purchase['PaymentTime']) }}</td>
                <td align="right" width="80"> {{ number_format( $purchase['Payment'])   }}</td>
            </tr>
            @endforeach

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
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭
                    </button>
                    <a href="{{ route('account.deposite', ['cardId' =>$cardId, 'amount'=>$sumIn-$sumOut]) }}" class="btn btn-primary">確認</a>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div>
    @else
    使用完畢
    @endif

</center>






@endsection