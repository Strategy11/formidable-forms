(function($) {

    $(".frm-page-search").autocomplete({
        delay: 500,
        classes: {
            "ui-autocomplete": "highlight"
        },
        minLength: 0,
        source: ajaxurl + '?action=' + fSettings.action + '&nonce=' + fSettings.nonce,
        select: function(event, ui) {
            $(this).val(ui.item.label);
            $(this).next('input[type="hidden"]').val(ui.item.value);

            return false;
        }
    });

})(jQuery)
