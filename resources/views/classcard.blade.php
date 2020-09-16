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

    <TABLE BORDER=0>
        <TR>
            <TD COLSPAN=3>
                <form class="form-signin" style="width: 100%;">
                    @for ($i = 4; $i >= 1; $i--) <div style="display:inline;">
                        @if ($i> $point) <img style="width: 20%;margin: 5px;" src="/images/classcard/graylotus.png">
                        @else
                        <a href="{{ route('registe.classcard',  [$point, $cardId]) }}"><img style="width:20%;margin: 5px;" src="/images/classcard/pinklotus.png"></a>
                        @endif
                    </div>
                    @endfor


                </form>
            </TD>
        </TR>
        <TR>
            <TD>@if ($point==0) <H4>
                    <a href="{{ route('buy.classcard', $userId, 1800) }}">買新卡</a>
                </H4>
                @endif</TD>
            <TD> @if ($point==0) <H4>
                    <a href="{{ route('buy.classcard', $userId, 1800) }}">展期</a>
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