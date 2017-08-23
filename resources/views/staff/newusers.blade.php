@extends('layout')

@section('content')
<div class="container">
    <h2>
        New Users
    </h2>
    <p>Here are all the new users. Are you sure you wish to send an email to them for setting their password?</p>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Send?</th>
            </tr>
        </thead>
        <tbody>
            @foreach($newUsers as $user)
                <tr class="user-{{$user->id}}">
                    <td>{{ $user->fullName() }}</td>
                    <td>{{ $user->email }}</td>
                    <td><button type="button" id="send-email" class="btn btn-info email-{{$user->id}}" value="{{ $user->id }}">Send Email</button><img class="loading-icon-{{$user->id}}" src="/img/loading.gif" alt="loading" style="width:30px;height:30px; position:absolute;" hidden></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <meta name="_token" content="{!! csrf_token() !!}" />
    <script src="/js/ajax-email.js"></script>
@stop