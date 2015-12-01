@extends('layout')

@section('content')

    <h2>System Log</h2>
    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Date</th>
                <th>By</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($events as $event)
                <tr>
                    <td>{{ $event->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{!! action('UserController@show', $event->user_id) !!}">
                            {{ $event->user->fullName() }}
                        </a>
                    </td>
                    <td>{{ $event->message }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @include('partials.datatables')
@stop
