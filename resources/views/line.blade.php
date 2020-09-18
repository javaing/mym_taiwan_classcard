@extends('layouts.master')

@section('title', '體位法課程登入')

@section('content')
<form class="form-signin .m-4 .w-full .lg:w-3/4 .lg:max-w-lg">
    <div class="text-center mb-4">
        @if($url == 'reuse')
        <a href={{ route('reuse.line') }}><img class="mb-4" src="/images/line/2x/32dp/btn_base.png"></a>
        @else
        <H4><a href="{{ $url }}">體位法課程登入</a></H4>
        <a href="{{ $url }}"><img class="mb-4" width="100" src="/images/line/2x/32dp/btn_base.png"></a>
        @endif
    </div>
</form>
@endsection