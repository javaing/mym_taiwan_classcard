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
            $arr = DBHelper::getConsume( $card['CardID']);
        }}
    @endphp

    <TABLE BORDER=0 CELLPADDING="4">
        <TR>
            <TD COLSPAN=3>
                <form>
                    @for ($i = 4; $i >= 1; $i--) <div style="display:inline;">
                        @if ($i> $card['Points']) <img style="width: 20%;margin: 5px;" src="/images/classcard/graylotus.png">
                        @if ($arr && sizeof($arr)> 0)
                        {{DBHelper::toDateString( $arr[4-$i]['PointConsumeTime'] ) }}
                        @endif
                        @else
                        <a href="{{ route('registe.classcard',  [$card['Points'], $card['CardID']]) }}"><img style="width:20%;margin: 5px;" src="/images/classcard/pinklotus.png"></a>
                        @endif
                    </div>
                    @endfor


                </form>
            </TD>
        </TR>

        <TR>
            <TD COLSPAN=3 align="right">期限: {{ DBHelper::toDateString($card['Expired']) }}</TD>
        </TR>
        @php
        {{ $dt = App\Helpers\DBHelper::getMongoDateNow(); }}
        @endphp
        <TR>
            <TD align="center">@if ($card['Points']==0) <H4>
                    <a href="{{ route('buy.classcard', ['userId' => $card['UserID']] ) }}">買新卡2</a>
                </H4>
                @endif</TD>
            <TD> @if ($dt>$card['Expired'] && $card['Points']>0) <H4>
                    <a href="{{ route('buy.classcard', ['userId' => $card['UserID']]) }}">展期2</a>
                </H4>
                @endif</TD>
        </TR>
        <TR>
            <TD COLSPAN=3>
                <p class="mt-5 mb-3 text-muted text-center">MYM Taiwan &copy; 2020</p>
            </TD>
        </TR>
    </TABLE>

</body>

</html>
<!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script> -->