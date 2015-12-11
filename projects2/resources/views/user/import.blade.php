@extends ('layout')

@section ('content')

    <h2>
        Import a list of staff.
    </h2>
    <form method="POST" action="{!! action('UserController@updateStaff') !!}" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="form-group">
            <label for="inputFile">Upload an Excel file</label>
            <input type="file" id="inputFile" name="file" value="" required>
            <p class="help-block">
                Spreadsheet must in of the form <pre>email|surname|forenames</pre>
            </p>
        </div>
        <button type="submit" class="btn btn-primary">Import</button>
    </form>
@stop