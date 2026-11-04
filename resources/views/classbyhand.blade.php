@extends('layouts.master')

@section('title', '報表-收支')

@section('content')

@php
{{
    $today = DBHelper::today();
    $todayYmd = \Carbon\Carbon::now('Asia/Taipei')->format('Y-m-d');
    $arrIn = DBHelper::getLiveCards();
    $users = collect(DBHelper::getUsers())->sortBy(function ($user) {
        return ($user['UserName'] ?? '') !== '' ? $user['UserName'] : ($user['NickName'] ?? '');
    });
    $taichungActivities = config('taichung.activities');
    Illuminate\Support\Facades\Log::info('$arrIn=' . $arrIn);
}}
@endphp

<link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>

@if(session('status'))
<div class="alert alert-success">{{ session('status') }}</div>
@endif
@if(session('warning'))
<div class="alert alert-warning">{{ session('warning') }}</div>
@endif

<div class="display-flex" style="gap: 40px; flex-wrap: wrap; align-items: flex-start;">
    <div class="text-left" style="width:220px;">
        <p18>補卡作業</p18>
        <form action="{{url()->action('AccountController@registeclassByhand')}}" method="POST">
            @csrf

            <select class="form-control" name="cardId">
                <option>請選卡</option>
                @foreach ($arrIn as $purchase)
                <option value="{{ base64_encode( $purchase['CardID'] )}}">
                    {{ DBHelper::getUserName(    $purchase['UserID']) }}( {{ $purchase['CardID']   }} )
                </option>
                @endforeach
            </select>

            <input class="date form-control" type="text" name="registedate" value="{{$today}}">
            <button class="btn btn-link" type="submit">確定</button>
        </form>
    </div>

    <div class="text-left" style="width:260px;">
        <p18>台中活動補登</p18>
        <form action="{{ url('classcard/byhand/taichung') }}" method="POST">
            @csrf

            <select class="form-control" name="userId" required>
                <option value="">請選學員</option>
                @foreach ($users as $user)
                <option value="{{ $user['UserID'] }}">
                    {{ ($user['UserName'] ?? '') !== '' ? $user['UserName'] : ($user['NickName'] ?? '') }}
                    @if(!empty($user['Location']))（{{ $user['Location'] }}）@endif
                </option>
                @endforeach
            </select>

            <select class="form-control" name="activityType" required style="margin-top: 8px;">
                <option value="">請選活動</option>
                @foreach ($taichungActivities as $activityType => $activity)
                <option value="{{ $activityType }}">
                    {{ $activity['label'] }} {{ number_format($activity['amount']) }} 元
                </option>
                @endforeach
            </select>

            <input class="date form-control" type="text" name="registedate" value="{{ $todayYmd }}" style="margin-top: 8px;">
            <button class="btn btn-link" type="submit">確定</button>
        </form>
    </div>
</div>

<script type="text/javascript">
    $('.date').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        endDate: '+0d'
    });
</script>

<div id="myTabContent" class="tab-content">
    <div style="margin-top: 3px;">
        <p18>課卡一覽</p18>
    </div>
    <div class="tab-pane show active" id="prepaid">
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
