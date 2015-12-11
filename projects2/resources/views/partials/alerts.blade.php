
		{{-- check if we have a 'success message', if so show it at the top of the page --}}
		{{-- (usually used when saving a form) --}}
    	@if(Session::has('success_message'))
	    	@if(Session::get('success_message'))
		    	<div class="alert alert-success">
		    		{{ Session::get('success_message') }}
		    	</div>
	    	@endif
    	@endif
    	@if(count($errors) > 0)
        	<div class="alert alert-danger">
        		@foreach($errors->all() as $error)
	        		{{ $error }}
	        	@endforeach
        	</div>
    	@endif
