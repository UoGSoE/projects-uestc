$(document).ready(function(){
    var url = "/admin/staff/email/";
    $(document).on('click', '#send-email', function () {
        var user_id = $(this).val();
        $('.email-'+user_id).prop("disabled", true);
        $('.loading-icon-'+user_id).show();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        })
        $.ajax({
            type: "POST",
            url: url + user_id,
            success: function (data) {
                $('.user-'+user_id).fadeOut("slow");
            },
            error: function (data) {
                $('.loading-icon-'+user_id).hide();
                $('.email-'+user_id).removeClass("btn-info");
                $('.email-'+user_id).addClass("btn-danger");
                $('.email-'+user_id).html("Error - try again via staff page");
            }
        });
    });
});