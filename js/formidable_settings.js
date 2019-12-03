(function($) {

    $("#page-search").autocomplete({
        delay: 500,
        classes: {
            "ui-autocomplete": "highlight"
        },
        minLength: 2,
        source: ajaxurl + '?action=' + fSettings.action + '&nonce=' + fSettings.nonce,
        select: function(event, ui) {
            $valueholder = $('input[name="' + $("#page-search").data("valueholder_field") + '"]');

            $("#page-search").val(ui.item.label);
            $valueholder.val(ui.item.value);

            return false;
        }
    });

})(jQuery)
