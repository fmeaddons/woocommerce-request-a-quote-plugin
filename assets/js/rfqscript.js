function form_up(div_id) { 
    jQuery(function ($) {
       var data = {
           'action': 'get_form',
       };
       $.post(rfqAjax.ajaxurl, data, function(response) {
           $.fancybox.showLoading();
           $.fancybox(response);
           //$(div_id).fancybox({ content: response });
           //$(div_id).html(response);
           //$.fancybox.update();
       }); 
    });
}

jQuery(document).ready(function ($) {
    $("#tip5").fancybox({
        "scrolling": "no",
        "titleShow": false,
        "onClosed": function () {
            $("#rfq_error").hide();
        }
    });

    $("#rfq_form").bind("submit", function () {
        console.log($("#rfq_form").serialize());
        if ($("#email").val().length < 1) {
            $("#rfq_error").show();
            $.fancybox.update();
            return false;
        }

        $.fancybox.showLoading();

        $.ajax({
            type: "GET",
            url: rfqAjax.ajaxurl,
            action: "save_rfq",
            data: $("#rfq_form").serialize(),
            success: function (data) {
                $.fancybox(data);
            }
        });
        return false;

    });

    return false;
});