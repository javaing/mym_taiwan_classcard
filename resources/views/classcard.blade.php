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
    <form class="form-signin">
        @for ($i = 0; $i < 4; $i++) <div style="display:inline;">
            @if ($i< $used) <img style="width: 44px;margin: 5px;" src="/images/classcard/graylotus.png">
                @else
                <a href="{{ $url }}"><img style="width: 44px;margin: 5px;" src="/images/classcard/pinklotus.png"></a>
                @endif
                </div>
                @endfor

                <p class="mt-5 mb-3 text-muted text-center">MYM Taiwan &copy; 2020</p>
    </form>

</body>

</html>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>