@extends('layout')

@section('content')

    <h2>
        Projects - Bulk Inactive/Active 
    </h2>
    <form class="form-inline" method="POST" action="{!! action('ProjectController@bulkSaveActive') !!}">
    {!! csrf_field() !!}
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Active</th>
                <th>Inactive</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($projects as $project)
                <tr>
                    <td>
                        <a href="{!! action('ProjectController@show', $project->id) !!}">
                            {{ $project->title }}
                        </a>
                    </td>
                    <td>
                        <div class="radio">
                          <label>
                            <input type="radio" name="statuses[{{ $project->id }}]" value="1" @if ($project->is_active) checked @endif>
                          </label>
                        </div>
                    </td>
                    <td>
                        <div class="radio">
                          <label>
                            <input type="radio" name="statuses[{{ $project->id }}]" value="0" @if (! $project->is_active) checked @endif>
                          </label>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <button type="submit" class="btn btn-primary pull-right">Update</button>
    </form>
@stop
