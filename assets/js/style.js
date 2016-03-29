jQuery(function () {
    jQuery('input[type=radio]').change(function () {
        var selected = jQuery('input[name=first]:checked').val();
        if (selected == 'msg') {
            jQuery('textarea[name=msg]').show();
            jQuery('input[name=redirect]').hide();
        }
        if (selected == 'redirect') {
            jQuery('input[name=redirect]').show();
            jQuery('textarea[name=msg]').hide();
        }
    });
});