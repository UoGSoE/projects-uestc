@extends ('layout')

@section ('content')

    <h2>
        Import Students on <a href="{!! action('CourseController@show', $course->id) !!}">{{ $course->title }}</a>
    </h2>
    <form method="POST" action="{!! action('CourseController@updateStudents', $course->id) !!}" enctype="multipart/form-data">
        {{ csrf_field() }}
        <input type="hidden" name="_method" value="PATCH">
        <div class="form-group">
            <label for="inputFile">Upload an Excel file</label>
            <input type="file" id="inputFile" name="file" value="" required>
            <p class="help-block">
                Spreadsheet must in of the form <pre>matric|surname|forenames</pre>
            </p>
        </div>
        <button type="submit" class="btn btn-primary">Import</button>
    </form>
@stop