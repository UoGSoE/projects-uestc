<script src="{!! asset('vendor/datatables/js/jquery.dataTables.min.js') !!}"></script>
<script src="{!! asset('vendor/datatables/js/dataTables.bootstrap.min.js') !!}"></script>
<script>
    $(document).ready(function() {
        $('.datatable').DataTable({ paging: false });
    });
</script>
