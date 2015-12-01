	<div class="navbar navbar-default navbar-static-top" role="navigation">
	      <div class="container">
	        <div class="navbar-header">
	          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
	            <span class="sr-only">Toggle navigation</span>
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	          </button>
	          <a class="navbar-brand" href="{{ url('/') }}">Student Projects</a>
	        </div>
            @if (Auth::check())
		        <div class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
			            <!-- <li class="active"><a href="#">Home</a></li> -->
						@can('see_reports')
			            <li class="dropdown">
			              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Reports <b class="caret"></b></a>
			              <ul class="dropdown-menu">
			              	@can('see_reports')
			              		<li><a href="{!! action('ReportController@allProjects') !!}">View All Projects</a></li>
			              		<li><a href="{!! action('ReportController@allStudents') !!}">View All Students</a></li>
			              		<li><a href="{!! action('ReportController@allStaff') !!}">View All Staff</a></li>
			              		<li><a href="">View All Allocations</a></li>
			              	@endcan
			              </ul>
			            </li>
			            @endcan
			            @can('basic_admin')
			            <li class="dropdown">
			              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
			              <ul class="dropdown-menu">
			              	@can('edit_users')
			              		<li><a href="{!! action('UserController@indexStaff') !!}">Staff</a></li>
			              		<li><a href="{!! action('UserController@indexStudents') !!}">Students</a></li>
			              	@endcan
			              	@can('edit_courses')
			              		<li><a href="{!! action('CourseController@index') !!}">Courses</a></li>
			              		<li><a href="{!! action('ProgrammeController@index') !!}">Programmes</a></li>
			              		<li><a href="{!! action('ProjectTypeController@index') !!}">Project Types</a></li>
			              		<li><a href="{!! action('LocationController@index') !!}">Locations</a></li>
			              	@endcan
			              	@can('view_eventlog')
			              		<li><a href="{!! action('EventLogController@index') !!}">Activity Log</a></li>
			              	@endcan
			              	@can('edit_site_permissions')
			              		<li><a href="{!! action('PermissionController@index') !!}">Edit Site Permissions</a></li>
			              		<li><a href="{!! action('RoleController@index') !!}">Edit Site Roles</a></li>
							@endcan
			              </ul>
			            </li>
				        @endcan
			          </ul>
					<ul class="nav navbar-nav navbar-right">
						<li><a href="{{ url('/logout') }}">Log Out</a></li>
					</ul>
	        </div><!--/.nav-collapse -->
	        @endif
	      </div>
	</div> <!-- /navbar -->
