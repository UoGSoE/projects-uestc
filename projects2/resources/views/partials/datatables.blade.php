<script src="{!! asset('vendor/datatables/js/jquery.dataTables.min.js') !!}"></script>
<script src="{!! asset('vendor/datatables/js/dataTables.bootstrap.min.js') !!}"></script>
<script>
    $(document).ready(function() {
        $('.datatable').DataTable({ "pageLength": 100, "order": [] });
    });
</script>
