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
            <td width="100"> {{ $user['NickName'] }}</td>
            <td width="50"> {{ $user['Mobile'] }}</td>
            <td width="110"> {{ $user['Email'] }}</td>
        </tr> @endforeach
    </table>
</div>


@endsection