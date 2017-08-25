@extends ('layout')

@section ('content')
    <h2>
        Import a list of EXTERNAL staff.
    </h2>
    <form method="POST" action="{!! route('staff.do_import') !!}" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="form-group">
            <label for="inputFile">Upload an Excel file</label>
            <input type="file" id="inputFile" name="file" value="" required>
            <p class="help-block">
                This is ONLY for staff who do not have Glasgow GUIDs.
            </p>
            <p class="help-block">
                Spreadsheet must in of the form <pre>email|surname|forenames|institution</pre>
            </p>
        </div>
        <button type="submit" class="btn btn-primary">Import</button>
    </form>
@stop