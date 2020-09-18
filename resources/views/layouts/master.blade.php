<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <link rel="shortcut icon" href="http://taiwanyogasangha.blogspot.com/" type="/image/x-icon">

    <title>MYM Taiwan - @yield('title')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <base href="{{env('APP_URL')}}">
</head>

<body>
    <div class="container">
        @yield('content')
    </div>


</body>
<footer class="footer text-fades text-center py-5">
    @include('layouts.footer')
</footer>

</html>