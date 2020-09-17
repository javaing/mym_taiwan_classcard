<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>MYM Taiwan ClassCard</title>

    <!-- Bootstrap core CSS -->
    <link href="https://bootstrap.hexschool.com/docs/4.2/dist/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="https://bootstrap.hexschool.com/docs/4.2/examples/floating-labels/floating-labels.css" rel="stylesheet">

    <base href="{{env('APP_URL')}}">
</head>

<body>
    @php
    {{
        $dateFrom = Carbon\Carbon::createFromDate(2020, 8, 1);
        $dateTo = Carbon\Carbon::createFromDate(2020, 10, 1);
        $arrIn = DBHelper::getBalanceIn($dateFrom, $dateTo);
        $arrOut = DBHelper::getBalanceOut($dateFrom, $dateTo);
        Illuminate\Support\Facades\Log::info('$arrIn=' . $arrIn);
        $sumIn=0; $sumOut=0;
        foreach($arrIn as $each) {
            $sumIn += $each['Payment'];
        }
        foreach($arrOut as $each) {
            $sumOut += $each['Cost'];
        }
    }}
    @endphp
    <table>
        <tr>
            <th>
                <h4>預收</h4>
            </th>
        </tr>
        <tr>
            <th>用戶</th>
            <th>卡號</th>
            <th>日期</th>
            <th>
                <center>金額</center>
            </th>
        </tr>

        @foreach($arrIn as $purchase)
        <tr>
            <td width="100"> {{ DBHelper::getUserName(    $purchase['UserID']) }}</td>
            <td> {{$purchase['CardID'] }}</td>
            <td> {{ DBHelper::toDateString( $purchase['PaymentTime']) }}</td>
            <td align="right" width="100"> {{ number_format( $purchase['Payment'])   }}</td>
        </tr>
        @endforeach
        <tr>
            <td COLSPAN=4 align="right">
                小計 {{ number_format($sumIn)}}
            </td>
        </tr>
    </table>



    <table style="margin-left : 100px;">
        <tr>
            <th>
                <h4>學員上課</h4>
            </th>
        </tr>
        <tr>
            <th>卡號</th>
            <th>日期</th>
            <th>
                <center>金額</center>
            </th>
        </tr>

        @foreach($arrOut as $consume)
        <tr>
            <td>{{$consume['CardID'] }}</td>
            <td>{{ DBHelper::toDateString( $consume['PointConsumeTime']) }}</td>
            <td align="right" width="100"> {{ number_format( $consume['Cost'])  }}</td>
        </tr>
        @endforeach
        <tr>
            <td COLSPAN=4 align="right">
                小計 {{number_format($sumOut)}}
            </td>
        </tr>
    </table>

</body>

</html>
<!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script> -->