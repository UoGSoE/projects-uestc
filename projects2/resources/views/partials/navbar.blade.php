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
					@if (Auth::user()->isAdmin())
					<ul class="nav navbar-nav">
			            <!-- <li class="active"><a href="#">Home</a></li> -->
			            <li class="dropdown">
			              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Equipment <b class="caret"></b></a>
			              <ul class="dropdown-menu">
			              	<li><a href="">Hello</a></li>
			              	<li><a href="">There</a></li>
			              </ul>
			            </li>
			          </ul>
			        @endif
					<ul class="nav navbar-nav navbar-right">
						<li><a href="{{ url('/logout') }}">Log Out</a></li>
					</ul>
	        </div><!--/.nav-collapse -->
	        @endif
	      </div>
	</div> <!-- /navbar -->
