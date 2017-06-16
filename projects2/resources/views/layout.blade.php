<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Student Projects - {{{ $page_title or '' }}}</title>
    <link rel="stylesheet" href="{!! asset('vendor/bootstrap/css/bootstrap.min.css') !!}" media="print,screen" />
    <link rel="stylesheet" href="{!! asset('vendor/select2/dist/css/select2.min.css') !!}" media="screen" />
    <link rel="stylesheet" href="{!! asset('vendor/datatables/css/jquery.dataTables.min.css') !!}" media="screen" />
    <link rel="stylesheet" href="{!! asset('vendor/datatables/css/dataTables.bootstrap.css') !!}" media="screen" />
    <link rel="stylesheet" href="/css/projects2.css" media="screen" />
    <script src="{!! asset('vendor/jquery.min.js') !!}"></script>
    <script src="/js/es6-promise.auto.min.js"></script>
    <style>
        .fake-link {
            cursor: pointer;
        }
        .fake-link:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
	@include ('partials.navbar')

	<div class="container">

		<noscript>
			<div class="alert alert-danger"><strong>Warning:</strong> This site will not work correctly without javascript</div>
		</noscript>

		@include ('partials.alerts')

        @yield('content')
	</div><!-- container -->

	<br />
	<div id="footer">
	  <hr />
      <div class="container">
        <p class="text-muted pull-right">&copy; School of Engineering - University of Glasgow {!! date('Y') !!}</p>
      </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{!! asset('vendor/select2/dist/js/select2.full.min.js') !!}"></script>
    <script>
    	$( document ).ready(function() {
			$('a[data-confirm]').click(function(ev) {
				var href = $(this).attr('action-href');
				if (!$('#dataConfirmModal').length) {
					$('body').append('<div id="dataConfirmModal" class="modal fade" aria-labelledby="dataConfirmLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button><a class="btn btn-primary" id="dataConfirmOK">OK</a></div></div></div>');
				}
				$('#dataConfirmModal').find('.modal-body').text($(this).attr('data-confirm'));
				$('#dataConfirmOK').attr('href', href);
				$('#dataConfirmModal').modal({show:true});
				return false;
			});
		});
    </script>
</body>
</html>
