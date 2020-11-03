<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <link rel="shortcut icon" href="http://taiwanyogasangha.blogspot.com/" type="/image/x-icon">

    <title>MYM Taiwan - @yield('title')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <base href="{{env('APP_URL')}}">
    <style type="text/css">
        #div_used {
            background-image: url('/images/classcard/point_used.png');
            background-repeat: no-repeat;
            background-size: contain;
            height: 100px;
            width: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4px;
        }

        #div_unuse {
            background-repeat: no-repeat;
            background-size: contain;
            height: 100px;
            width: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4px;
        }

        #div_btn_border {
            background-image: url('/images/classcard/border_normal.png');
            background-repeat: no-repeat;
            background-size: contain;
            height: 136px;
            width: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4px;
        }

        body {
            background-color: #FFF9E5;
        }

        h1 {
            color: #C59C40;
        }

        p18 {
            color: #C59C40;
            font-size: 18;
        }

        p16 {
            color: #C59C40;
            font-size: 16;
        }

        p14 {
            color: #C59C40;
            font-size: 14;
        }

        p14white {
            color: #FFFFFF;
            font-size: 14;
        }

        .display-flex {
            display: flex;
        }
    </style>
</head>

<body style="height:100%;">
    <div class="container" style="margin-top: 12px;">
        @yield('content')
    </div>


    <footer class="footer text-fades text-center py-4">
        @include('layouts.footer')
    </footer>

</body>

</html>