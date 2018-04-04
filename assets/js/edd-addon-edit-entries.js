jQuery(function ($) {
    var url = localized_object_wc.url;

    var data = {
        'action': localized_object_wc.action,
        'action_switch': 'edit_addon',
        'addon_action':localized_object_wc.endpoint_action
    };
    $( '.js-wc-entry-edit').change('change', function (e) {

        data.new_value = $(this).val();
        data.input_name = $(this).data('name');
        data.lead_id = $(this).data('lead');

        //send ajax with changed data
        send_ajax_call(data);
    });

    function send_ajax_call(data) {
        /**
         * ajax call to controller-search-form.php
         * ajax registered in php as: flight_endpoint
         */
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function (data) {
                $('.js-update-message').html(data);
            }
        });
    }
});
