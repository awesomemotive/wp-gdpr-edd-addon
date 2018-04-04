jQuery(function ($) {
    var url = localized_object.url;
    //
    // var data = {
    //     'action': localized_object.action,
    //     'action_switch': 'edit_addon',
    //     'addon_action':localized_object.endpoint_action
    // };
    // $('.js-gf-entry-edit').on('change', function (e) {
    //     data.new_value = $(this).val();
    //     data.input_name = $(this).data('name');
    //     data.lead_id = $(this).data('lead');
    //
    //     //send ajax with changed data
    //     send_ajax_call(data);
    // });


    $('a.thickbox').on('click', function (e) {

        var button_text = $(this).text();


        var dropdown = localized_object.dropdown;


        var $form = $('.form-table');
        if ($form.find('#gdpr_dropdown').length == 0) {
            $form.find('tbody').append(
                dropdown
            );


        }

        switch (button_text) {
            case 'email':
                $form.find('#gdpr_dropdown').val('email').attr('disabled', true);
                break;
            case 'tel':
                $form.find('#gdpr_dropdown').val('phone').attr('disabled', true);
                break;
            default:
                $form.find('#gdpr_dropdown').val('empty').attr('disabled', false);
        }

    });

    $('.insert-tag').click(function (e) {

        var data = {
            'action': localized_object.action,
            'action_switch': 'edit_addon',
            'addon_action': localized_object.endpoint_action
        };

        data.post_id = $('#post_ID').val();
        data.input_name = $('.form-table:visible').find('input[name="name"]').val();
        data.dropdown_value = $('.form-table:visible').find('#gdpr_dropdown').val();

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
                console.log(data);
            }
        });
    }
});
