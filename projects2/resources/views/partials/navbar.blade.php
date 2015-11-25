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
					@can('basic_admin')
					<ul class="nav navbar-nav">
			            <!-- <li class="active"><a href="#">Home</a></li> -->
			            <li class="dropdown">
			              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
			              <ul class="dropdown-menu">
			              	@can('see_reports')
			              		<li><a href="">View All Projects</a></li>
			              		<li><a href="">View All Students</a></li>
			              		<li><a href="">View All Staff</a></li>
			              		<li><a href="">View All Allocations</a></li>
			              	@endcan
			              	@can('edit_user_roles')
			              		<li><a href="">Edit User Permissions</a></li>
			              	@endcan
			              	@can('edit_users')
			              		<li><a href="{!! action('UserController@index') !!}">Create/Edit Users</a></li>
			              	@endcan
			              	@can('edit_courses')
			              		<li><a href="">Edit Courses</a></li>
			              		<li><a href="">Edit Programmes</a></li>
			              	@endcan
			              	@can('edit_site_permissions')
			              		<li><a href="">Edit Site Permissions</a></li>
							@endcan
			              </ul>
			            </li>
			          </ul>
			        @endcan
					<ul class="nav navbar-nav navbar-right">
						<li><a href="{{ url('/logout') }}">Log Out</a></li>
					</ul>
	        </div><!--/.nav-collapse -->
	        @endif
	      </div>
	</div> <!-- /navbar -->
