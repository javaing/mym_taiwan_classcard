@extends('layouts.master')

@section('title', '報表-學員')

@section('content')
<div>
    <table border="0">
        <tr>
            <th></th>
            <th>學員</th>
            <th>手機</th>
            <th>email</th>
        </tr>
        @foreach($users as $user)
        <tr>
            <td> <img width="50" height="50" src="{{ $user['PictureUrl']   }}"></td>
            <td width="100"> <a href="/alluser/{{ $user['UserID'] }}">{{ $user['NickName'] }}</a> </td>
            <td width="100"> {{ $user['Mobile'] }}</td>
            <td width="110"> {{ $user['Email'] }}</td>
        </tr> @endforeach
    </table>
</div>

@if ($userDetail)
<form action="{{url()->action('LoginController@updateUser')}}" method="POST">
    @csrf
    <table border="0" style="margin-top: 36;">
        <tr>
            <td width="60" height="36">暱稱</td>
            <td><input name="NickName" type="text" value="{{$userDetail['NickName']}}"> </td>
        </tr>
        <tr>
            <td width="60" height="36">姓名</td>
            <td> <input name="UserName" type="text" value="{{$userDetail['UserName']}}"> </td>
        </tr>
        <tr>
            <td width="60" height="36">手機</td>
            <td><input name="Mobile" type="text" value="{{$userDetail['Mobile']}}"> </td>
        </tr>
        <tr>
            <td width="60" height="36">地址</td>
            <td><input name="Address" type="text" value="{{$userDetail['Address']}}"> </td>
        </tr>
        <tr>
            <td width="60" height="36">介紹人</td>
            <td><input name="Referrer" type="text" value="{{$userDetail['Referrer']}}"></td>
        </tr>
        <tr>
            <td width="60" height="36">email</td>
            <td><input name="Email" type="text" value="{{$userDetail['Email']}}"> </td>
        </tr>
        <tr>
            <input name="UserID" type="hidden" value="{{$userDetail['UserID']}}">
            <td COLSPAN=2 align="right"><button class="btn btn-link" type="submit">更新</button>
        </tr>
    </table>

</form>
@endif


@endsection