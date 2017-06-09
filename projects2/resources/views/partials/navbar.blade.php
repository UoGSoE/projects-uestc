	<div class="navbar navbar-default navbar-static-top navbar-fixed-top" role="navigation">
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
						@can('view_reports')
				            <li class="dropdown">
				              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Reports <b class="caret"></b></a>
				              <ul class="dropdown-menu">
			              		<li><a href="{!! route('report.projects') !!}">View All Projects</a></li>
			              		<li><a href="{!! route('report.students') !!}">View All Students</a></li>
			              		<li><a href="{!! route('report.staff') !!}">View All Staff</a></li>
			              		<li><a href="{!! route('bulkallocate.edit') !!}">Bulk Allocations</a></li>
			              		<li><a href="{!! route('bulkactive.edit') !!}">Bulk Active/Inactive</a></li>
				              </ul>
				            </li>
			            @endif
			            @if (Auth::user()->isAdmin())
				            <li class="dropdown">
				              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
				              <ul class="dropdown-menu">
				              		<li><a href="{!! route('staff.index') !!}">Staff</a></li>
				              		<li><a href="{!! route('student.index') !!}">Students</a></li>
				              		<li><a href="{!! route('course.index') !!}">Courses</a></li>
				              		<li><a href="{!! route('discipline.index') !!}">Disciplines</a></li>
				              		<li><a href="{!! route('event.index') !!}">Activity Log</a></li>
				              </ul>
				            </li>
				        @endif
			          </ul>
					<ul class="nav navbar-nav navbar-right">
						<li><a href="{{ url('/logout') }}">Log Out {{ Auth::user()->fullName() }}</a></li>
					</ul>
	        </div><!--/.nav-collapse -->
	        @endif
	      </div>
	</div> <!-- /navbar -->
