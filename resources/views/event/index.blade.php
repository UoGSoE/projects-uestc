@extends('layout')

@section('content')

    <h2>Activity Log</h2>
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
                        <a href="{!! route('user.show', $event->user_id) !!}" title="{{ $event->user->username }}">
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
