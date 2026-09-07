@extends('layouts.master')

@section('title', '報表-學員')

@section('content')
@php
    $selectedId = $userDetail['UserID'] ?? null;
@endphp
<style>
    .alluser-layout {
        display: flex;
        gap: 20px;
        align-items: stretch;
        min-height: calc(100vh - 140px);
    }
    .alluser-list {
        flex: 0 0 48%;
        max-height: calc(100vh - 140px);
        overflow-y: auto;
        background: #fff;
        border-radius: 8px;
        padding: 8px 10px 12px;
        box-shadow: 0 1px 4px rgba(197, 156, 64, 0.18);
    }
    .alluser-list table {
        width: 100%;
        border-collapse: collapse;
    }
    .alluser-list thead th {
        position: sticky;
        top: 0;
        background: #fff;
        color: #C59C40;
        font-size: 14px;
        padding: 8px 6px;
        border-bottom: 1px solid #ead9b0;
        z-index: 1;
    }
    .alluser-row {
        cursor: pointer;
    }
    .alluser-row td {
        padding: 6px;
        vertical-align: middle;
        border-bottom: 1px solid #f3ead6;
        font-size: 14px;
    }
    .alluser-row:hover {
        background: #FFF3D1;
    }
    .alluser-row.is-selected {
        background: #F5E6C8;
    }
    .alluser-row a {
        color: #8a6a1a;
        font-weight: 600;
    }
    .alluser-edit {
        flex: 1;
        background: #fff;
        border-radius: 8px;
        padding: 20px 24px;
        box-shadow: 0 1px 4px rgba(197, 156, 64, 0.18);
        min-height: 360px;
    }
    .alluser-edit h5 {
        color: #C59C40;
        margin-bottom: 18px;
    }
    .alluser-edit table td {
        padding: 6px 0;
        vertical-align: middle;
    }
    .alluser-edit input[type="text"],
    .alluser-edit input[type="tel"],
    .alluser-edit input[type="email"] {
        width: 100%;
        max-width: 320px;
        padding: 6px 10px;
        border: 1px solid #d8c9a0;
        border-radius: 4px;
        background: #fffdf6;
    }
    .alluser-placeholder {
        color: #b49a5a;
        margin-top: 80px;
        text-align: center;
        font-size: 16px;
    }
    .alluser-status {
        color: #2e7d32;
        font-size: 14px;
        margin-bottom: 12px;
    }
    .alluser-edit .btn-update {
        background: #C59C40;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 6px 22px;
        cursor: pointer;
    }
    @media (max-width: 768px) {
        .alluser-layout {
            flex-direction: column;
        }
        .alluser-list,
        .alluser-edit {
            flex: none;
            width: 100%;
            max-height: none;
        }
        .alluser-list {
            max-height: 40vh;
        }
    }
</style>

<div class="alluser-layout">
    <div class="alluser-list">
        <table border="0">
            <thead>
                <tr>
                    <th></th>
                    <th>暱稱</th>
                    <th>姓名</th>
                    <th>email</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                @php $isSelected = $selectedId && $selectedId == $user['UserID']; @endphp
                <tr class="alluser-row {{ $isSelected ? 'is-selected' : '' }}"
                    data-href="{{ url('alluser/'.$user['UserID']) }}">
                    <td><img width="50" height="50" src="{{ $user['PictureUrl'] ?? '' }}" alt=""></td>
                    <td width="70"><a href="{{ url('alluser/'.$user['UserID']) }}">{{ $user['NickName'] }}</a></td>
                    <td width="100">{{ $user['UserName'] }}</td>
                    <td width="110">{{ $user['Email'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="alluser-edit">
        @if ($userDetail)
            <h5>編輯學員資料</h5>
            @if (session('status'))
                <div class="alluser-status">{{ session('status') }}</div>
            @endif
            <form action="{{ url()->action('LoginController@updateUser') }}" method="POST">
                @csrf
                <table border="0">
                    <tr>
                        <td width="80" height="36">暱稱</td>
                        <td><input name="NickName" type="text" value="{{ $userDetail['NickName'] ?? '' }}" required></td>
                    </tr>
                    <tr>
                        <td height="36">姓名</td>
                        <td><input name="UserName" type="text" value="{{ $userDetail['UserName'] ?? '' }}" required></td>
                    </tr>
                    <tr>
                        <td height="36">手機</td>
                        <td><input name="Mobile" type="tel" value="{{ $userDetail['Mobile'] ?? '' }}" pattern="[0-9]{10}|[0-9]{4}-[0-9]{3}-[0-9]{3}"></td>
                    </tr>
                    <tr>
                        <td height="36">地址</td>
                        <td><input name="Address" type="text" value="{{ $userDetail['Address'] ?? '' }}"></td>
                    </tr>
                    <tr>
                        <td height="36">介紹人</td>
                        <td><input name="Referrer" type="text" value="{{ $userDetail['Referrer'] ?? '' }}"></td>
                    </tr>
                    <tr>
                        <td height="36">email</td>
                        <td><input name="Email" type="email" value="{{ $userDetail['Email'] ?? '' }}"></td>
                    </tr>
                    <tr>
                        <td height="36">身分證ID</td>
                        <td><input name="PersonalID" type="text" value="{{ $userDetail['PersonalID'] ?? '' }}"></td>
                    </tr>
                    <tr>
                        <td height="36">所在</td>
                        <td><input name="Location" type="text" value="{{ $userDetail['Location'] ?? '' }}"></td>
                    </tr>
                    <tr>
                        <input name="UserID" type="hidden" value="{{ $userDetail['UserID'] }}">
                        <td colspan="2" align="right" style="padding-top: 12px;">
                            <button class="btn-update" type="submit">更新</button>
                        </td>
                    </tr>
                </table>
            </form>
        @else
            <div class="alluser-placeholder">請從左側點選學員，以編輯明細資料</div>
        @endif
    </div>
</div>

<script>
    (function() {
        document.querySelectorAll('.alluser-row').forEach(function(row) {
            row.addEventListener('click', function(event) {
                if (event.target.closest('a')) return;
                var href = row.getAttribute('data-href');
                if (href) window.location.href = href;
            });
        });
        var selected = document.querySelector('.alluser-row.is-selected');
        if (selected) {
            selected.scrollIntoView({ block: 'center' });
        }
    })();
</script>
@endsection
