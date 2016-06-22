jQuery(document).ready(function ($) {


    var form_move = jQuery("#entire-form-area");
    jQuery("#titlediv").after(form_move);
    jQuery("#entire-form-area").fadeIn(1000);
    jQuery("#inbound_save_form").removeClass('button').addClass('button-primary').text('Save Form');
    jQuery("#inbound-shortcodes-preview").hide().fadeIn(5000);
    jQuery("body").on('change keyup', '#title', function () {
        jQuery("#title-prompt-text").hide();
        var this_val = jQuery(this).val();
        jQuery("#inbound_shortcode_form_name").val(this_val);
    });

    var build_form = ' <span id="view-form-builder" class="button view-form-builder">Build Form</span>';
    var view_leads_list = '<span id="view-leads-list" class="button view-leads-list">View Conversions</span>';
    var view_email_response = '<span id="view-email-response" class="button">Set Email Response</span>';
    jQuery('.page-title-action').after(build_form); /* 4.3 + */
    jQuery('.add-new-h2').after(build_form); /* <4.3 */
    jQuery('#view-form-builder').after(view_leads_list);
    jQuery('#view-leads-list').after(view_email_response);

    jQuery("body").on('click', '#view-form-builder', function () {
        jQuery("#form-leads-list").hide();
        jQuery("#inbound-shortcodes-popup").show();
        jQuery('#form-leads-list, #title, #inbound-email-response,#postdivrich').hide();
    });

    jQuery("body").on('click', '#view-email-response', function () {
        jQuery('#inbound-shortcodes-popup, #form-leads-list, #title, #inbound-email-response').hide();
        jQuery('#inbound-email-response').show();
        jQuery('#postdivrich').show();
        /* if custom email selected then hide email response */
        if (jQuery('#inbound_email_send_notification_template').length > -1) {
            jQuery('#inbound_email_send_notification_template').trigger('change');
        }
    });

    jQuery("body").on('click', '#view-leads-list', function () {
        jQuery("#inbound-shortcodes-popup, #postdivrich, #form-leads-list, #inbound-email-response").hide();
        jQuery("#form-leads-list, #title").show();
    });

    jQuery("body").on('change keyup', '#inbound_shortcode_form_name', function () {
        jQuery("#title-prompt-text").hide();
        var this_val = jQuery(this).val();
        jQuery("#title").val(this_val);
    });
    jQuery("body").on('click', '#inbound_save_this_form', function () {
        var post_id = jQuery("#post_ID").val();
    });
    var post_status = jQuery("#hidden_post_status").val();
    if (post_status === 'draft') {
        jQuery("#inbound_save_form").text("Publish Form");
    }
    var post_id = jQuery("#post_ID").val();
    var post_title = jQuery("#title").val();
    //jQuery("#inbound_shortcode_form_name").val(post_title);
    var form_toggle = 'form_' + post_id;
    setTimeout(function () {
        jQuery("#inbound_shortcode_insert_default").val(form_toggle);
        InboundShortcodes.update_fields();
        fill_form_fields();
    }, 1000);

    setTimeout(function () {

        fill_form_fields();
    }, 2000);

    function fill_form_fields() {
        var SelectionData = jQuery("#cpt-form-serialize").text();
        if (SelectionData != "") {

            jQuery.each(SelectionData.split('&'), function (index, elem) {
                var vals = elem.split('=');

                var $select_val = jQuery('select[name="' + vals[0] + '"]').attr('name');
                var $select = jQuery('select[name="' + vals[0] + '"]');
                var $input = jQuery('input[name="' + vals[0] + '"]'); // input vals
                var input_type = jQuery('input[name="' + vals[0] + '"]').attr('type');
                var $checkbox = jQuery('input[name="' + vals[0] + '"]'); // input vals
                var $textarea = jQuery('textarea[name="' + vals[0] + '"]'); // input vals
                var separator = '';
                /*if ($div.html().length > 0) {
                 separator = ', ';
                 }*/
                //console.log(input_type);
                $input.val(decodeURIComponent(vals[1].replace(/\+/g, ' ')));
                if (input_type === 'checkbox' && vals[1] === 'on') {
                    $input.prop("checked", true);
                }
                if ($select_val != 'inbound_shortcode_insert_default') {
                    $select.val(decodeURIComponent(vals[1].replace(/\+/g, ' ')));
                }
                $textarea.val(decodeURIComponent(vals[1].replace(/\+/g, ' ')));
            });

        }
    }

    if (post_status === 'draft') {
        setTimeout(function () {
            jQuery("#inbound_shortcode_insert_default").val('none');
        }, 1000);
    }
    if (post_status === 'draft' && post_title != "" || post_status === 'pending' && post_title != "") {


        // run auto publish ajax
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            context: this,
            data: {
                action: 'inbound_form_auto_publish',
                post_id: post_id,
                post_title: post_title
            },

            success: function (data) {
                console.log("This Form has been auto published");
            },

            error: function (MLHttpRequest, textStatus, errorThrown) {
                alert("Ajax not enabled");
            }
        });


    }

    function inbound_forms_select_email_template() {
        var selected = jQuery('#inbound_email_send_notification_template').val();

        if ( selected != 'custom') {
            jQuery('#postdivrich').hide();
            jQuery('#inbound_confirmation_subject').hide();
            jQuery('.tokens').hide();
        } else {
            jQuery('#postdivrich').show();
            jQuery('#inbound_confirmation_subject').show();
            jQuery('.tokens').show();
        }
    }


    jQuery('.child-clone-row').first().attr('id', 'row-1');
    setTimeout(function() {
        jQuery('#inbound-shortcodes-form input:visible').first().focus();
    }, 500);

    /* Hide Options Based on Selected Template */
    jQuery('body').on('change' , '#inbound_email_send_notification_template' , function() {
        inbound_forms_select_email_template();
    });

    jQuery('body').on('change', 'select[data-field-name="map_to"]', function () {
        console.log('changed mapping');
        var values = [];
        jQuery('select[data-field-name="map_to"]').each(function(){
            var value = jQuery(this).val();
            var a = values.indexOf(value);
            if (a != -1) {
                alert('Warning You have already mapped the ' + value + ' field. You can only map a field one time.');
            }
            values.push(value);

        });

    });

    jQuery('body').on('change', 'select[data-field-name="map_to"]', function () {
        console.log('changed mapping');
        var values = [];
        jQuery('select[data-field-name="map_to"]').each(function(){
            var value = jQuery(this).val();
            var a = values.indexOf(value);
            if (a != -1) {
                alert('Warning You have already mapped the ' + value + ' field. You can only map a field one time.');
            }
            values.push(value);

        });

    });


});
