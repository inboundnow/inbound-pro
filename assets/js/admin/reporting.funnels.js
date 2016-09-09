var FunnelsPathView = (function () {

    var funnel;


    var construct = {
        /**
         *  Initialize JS Class
         */
        init: function () {
            this.addListeners();
        },
        /**
         *  Add UI Listeners
         */
        addListeners: function () {
            /* Add listener to delete funnel */
            jQuery('body').on('click', '#delete_funnel', function () {
                /* set static var */
                FunnelsPathView.funnel = jQuery(this);
                FunnelsPathView.confirmDelete();
            });

            jQuery('body').on('click', '.confirm_delete', function () {
                /* set static var */
                FunnelsPathView.deleteFunnel();;
            });
        },
        /**
         *
         */
        confirmDelete: function () {
           jQuery('#delete_funnel').text('Are you sure?');
           jQuery('#delete_funnel').addClass('confirm_delete');
        },
        deleteEvent: function () {

            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                context: FunnelsPathView.input,
                data: {
                    action: 'inbound_pro_update_setting',
                    input: serialized
                },
                dataType: 'html',
                timeout: 20000,
                success: function (response) {
                    FunnelsPathView.endProcessing();

                },
                error: function (request, status, err) {
                    FunnelsPathView.input.parent().append("<span class='update-text' >"+status+"</span>");
                    setTimeout(function () {
                        jQuery('.update-text').fadeOut(2000, function () {
                            jQuery('.update-text').remove();
                        });
                    }, 500);
                }
            });
        },

        startProcessing: function(event) {
            FunnelsPathView.input.parent().append("<span class='processing' ><img src='" + inboundSettingsLocalVars.inboundProURL + "assets/images/spinner_60.gif' style=''></span>");

            /* temporarily add disabled class */
            jQuery(".inbound-field input, .inbound-field textarea, .inbound-field select").each(function() {
                var isDisabled = jQuery(this).is(':disabled');
                if (!isDisabled) {
                    jQuery(this).addClass('disabled');
                    jQuery(this).prop('disabled', true);
                }
            });
        },
        endProcessing: function() {
            jQuery('.processing').remove();

            FunnelsPathView.input.parent().append("<span class='update-text' >Updated</span>");
            setTimeout(function () {
                jQuery('.update-text').fadeOut(2000, function () {
                    jQuery('.update-text').remove();
                });
            }, 500);

            /* temporarily add disabled class */
            jQuery(".disabled").each(function() {
                jQuery(this).removeClass('disabled');
                jQuery(this).prop('disabled', false);

            });
        },
        /**
         *  Save Custom Fields
         */
        updateCustomFields: function () {
            setTimeout(function () {
                var form = jQuery('#custom-fields-form').clone();
                var originalSelects = jQuery('#custom-fields-form').find('select');

                /* removed disabled attribute from cloned object */
                form.find(':input:disabled').val().replace(/ /g, '_').toLowerCase();

                /* make sure the select values are correct */
                form.find('select').each(function (index, item) {
                    jQuery(this).val(originalSelects.eq(index).val());
                });


                if ('inbound_pro_update_custom_fields' in FunnelsPathView.running_ajax) {
                    FunnelsPathView.running_ajax['inbound_pro_update_custom_fields'].abort();
                }

                FunnelsPathView.running_ajax['inbound_pro_update_custom_fields'] = jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'inbound_pro_update_custom_fields',
                        input: form.serialize()
                    },
                    dataType: 'html',
                    timeout: 20000,
                    success: function (response) {
                        delete FunnelsPathView.running_ajax['inbound_pro_update_custom_fields'];
                    },
                    error: function (request, status, err) {
                        console.log(status);
                    }
                });
            }, 500);
        },
        /**
         *  Save Custom Fields
         */
        updateLeadStatuses: function () {
            setTimeout(function () {
                var form = jQuery('#lead-statuses-form').clone();

                if ('inbound_pro_update_lead_statuses' in FunnelsPathView.running_ajax) {
                    FunnelsPathView.running_ajax['inbound_pro_update_lead_statuses'].abort();
                } else {
                    jQuery('#add-lead-status').prepend('<img id="lead-status-processing" src="' + inboundSettingsLocalVars.inboundProURL + 'assets/images/processing.gif" /> ');
                }

                FunnelsPathView.running_ajax['inbound_pro_update_lead_statuses'] = jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'inbound_pro_update_lead_statuses',
                        input: form.serialize()
                    },
                    dataType: 'html',
                    timeout: 20000,
                    success: function (response) {
                        jQuery('#lead-status-processing').remove();
                        delete FunnelsPathView.running_ajax['inbound_pro_update_lead_statuses'];
                    },
                    error: function (request, status, err) {
                        console.log(status);
                    }
                });
            }, 500);
        },
        /**
         *  Save IP Addresses
         */
        updateIPAddresses: function () {
            setTimeout(function () {
                var form = jQuery('#ip-addresses-form').clone();

                if ('inbound_pro_update_ip_addresses' in FunnelsPathView.running_ajax) {
                    FunnelsPathView.running_ajax['inbound_pro_update_ip_addresses'].abort();
                }

                FunnelsPathView.running_ajax['inbound_pro_update_ip_addresses'] = jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'inbound_pro_update_ip_addresses',
                        input: form.serialize()
                    },
                    dataType: 'html',
                    timeout: 20000,
                    success: function (response) {
                        delete FunnelsPathView.running_ajax['inbound_pro_update_ip_addresses'];
                    },
                    error: function (request, status, err) {
                        console.log(status);
                    }
                });
            }, 500);
        },
        /**
         *  Rebuilds priority for custom fields
         */
        updateCustomFieldPriority: function () {
            var i = 0;
            setTimeout(function () {
                jQuery.each(jQuery('.map-row'), function () {
                    jQuery(this).attr('data-priority', i);
                    jQuery(this).find('.field-priority').val(i);
                    i++;
                });
            }, 200);
        },
        /**
         *  Rebuilds priority for lead statuses
         */
        updateLeadStatusesPriority: function () {
            var i = 0;
            setTimeout(function () {
                jQuery.each(jQuery('.status-row'), function () {
                    jQuery(this).attr('status-priority', i);
                    jQuery(this).find('.status-priority').val(i);
                    i++;
                });
            }, 200);
        },
        /**
         *  Deauthorize oauth
         */
        deauthorizeOauth: function () {
            /* serialize input data */
            var serialized = this.prepareSettingData();

            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: 'revoke_oauth_tokens',
                    input: serialized
                },
                dataType: 'html',
                timeout: 20000,
                success: function (response) {
                    var group = FunnelsPathView.input.data('field-group');
                    var button = jQuery('.oauth[data-field-group="' + group + '"]');
                    button.removeClass('hidden');
                    FunnelsPathView.input.addClass('hidden');
                },
                error: function (request, status, err) {
                    console.log(status);
                }
            });
        },
        /**
         *  Mark as authorized
         */
        setAuthorized: function () {
            var group = FunnelsPathView.input.data('field-group');

            var button = jQuery('.unauth[data-field-group="' + group + '"]');
            button.removeClass('hidden');

            FunnelsPathView.input.addClass('hidden');
        },
        /**
         * Prepare a serialized format of settings data
         */
        prepareSettingData: function () {
            var dataarr = new Array();

            /* get data attributes */
            for (var i in FunnelsPathView.input.data()) {
                var subarr = new Array();
                subarr['name'] = i;
                subarr['value'] = FunnelsPathView.input.data()[i];
                dataarr.push(subarr);
            }

            /* get value */
            switch (FunnelsPathView.input.data('field-type')) {
                case 'checkbox':
                    var value = jQuery('#' + FunnelsPathView.input.attr("id") + ' input:checkbox:checked').map(function () {
                        return jQuery(this).val();
                    }).get();
                    break;
                case 'select2':
                    var value = jQuery('#' + FunnelsPathView.input.attr('id') + ' option:selected').map(function () {
                        return jQuery(this).val();
                    }).get();
                    break;
                default:
                    var value = FunnelsPathView.input.val();
                    break;
            }

            /* set value */
            var subarr = new Array();
            subarr['name'] = 'value';
            subarr['value'] = value;
            dataarr.push(subarr);


            /* get name attr */
            var subarr = new Array();
            subarr['name'] = 'name';
            subarr['value'] = FunnelsPathView.input.attr('name');
            dataarr.push(subarr);

            return jQuery.param(FunnelsPathView.input.serializeArray().concat(dataarr));
        },
        /**
         *  Validate API Key
         */
        validateAPIKey: function () {

            if (jQuery('.api').length === 0) {
                return;
            }

            if (typeof jQuery('.api') != 'undefined') {
                FunnelsPathView.input = jQuery('.api');
            } else {
                alert('notifiy developer of this message');
            }

            FunnelsPathView.markKeyProcessing();

            if ('inbound_validate_api_key' in FunnelsPathView.running_ajax) {
                FunnelsPathView.running_ajax['inbound_validate_api_key'].abort();
            }

            FunnelsPathView.running_ajax['inbound_validate_api_key'] = jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'inbound_validate_api_key',
                    api_key: FunnelsPathView.input.val(),
                    site: inboundSettingsLocalVars.siteURL
                },
                dataType: "json",
                timeout: 20000,
                success: function (response) {
                    if (typeof response.customer != 'undefined') {
                        FunnelsPathView.markKeyValid();
                        console.log('is_pro=' + response.customer.is_pro);
                    } else {
                        FunnelsPathView.markKeyInvalid(response.message);
                    }
                    delete FunnelsPathView.running_ajax['inbound_validate_api_key'];
                },
                error: function (request, status, err) {
                    console.log(request.responseText);
                    console.log(status);
                    console.log(err);

                    if (err != 'abort') {
                        FunnelsPathView.markKeyInvalid('There was an error connecting to Inbound Now');
                    }
                }
            });

        },
        /**
         * mark key as being processed
         */
        markKeyProcessing: function () {
            FunnelsPathView.input.removeClass('valid');
            FunnelsPathView.input.removeClass('invalid');
            jQuery('.valid-icon').remove();
            jQuery('.invalid-icon').remove();
            jQuery('.processing-icon').remove();
            jQuery('<i>', {
                class: "fa fa-spinner processing-icon inbound-tooltip",
                title: "Checking Key"
            }).appendTo('.api-key');
        },
        /**
         * Mark key invalid
         */
        markKeyInvalid: function (message) {
            FunnelsPathView.input.removeClass('valid');
            FunnelsPathView.input.addClass('invalid');
            jQuery('.valid-icon').remove();
            jQuery('.invalid-icon').remove();
            jQuery('.processing-icon').remove();
            jQuery('<i>', {
                class: "fa fa-times-circle invalid-icon inbound-tooltip",
                title: message
            }).appendTo('.api-key').tooltip({
                animated: 'fade',
                placement: 'right',
                container: 'body',
                show: true
            }).tooltip("show");


        },
        /**
         * mark key valid
         */
        markKeyValid: function () {
            FunnelsPathView.input.removeClass('invalid');
            FunnelsPathView.input.addClass('valid');
            jQuery('.invalid-icon').remove();
            jQuery('.valid-icon').remove();
            jQuery('.processing-icon').remove();
            jQuery('.tooltip').hide();
            jQuery('<i>', {
                class: "fa fa-check valid-icon inbound-tooltip",
                title: "API Key Is Valid"
            }).appendTo('.api-key');
        },
        /**
         *  Get URL Param
         */
        getUrlParam: function (sParam) {
            var sPageURL = window.location.search.substring(1);
            var sURLVariables = sPageURL.split('&');
            for (var i = 0; i < sURLVariables.length; i++) {
                var sParameterName = sURLVariables[i].split('=');
                if (sParameterName[0] == sParam) {
                    return sParameterName[1];
                }
            }
        },
        /**
         *  Adds custom lead fields to field map
         */
        addCustomLeadField: function () {
            /* create a new li and append to list */
            var clone = jQuery(".custom-fields-row:last").clone();

            /* discover last priority and next priority in line */
            var priority = clone.data('priority');
            var name = clone.find('input.field-key').val();
            var new_name = jQuery('.map-row-addnew #new-key').val().replace(/ /g, '_').toLowerCase();
            var next_priority = parseInt(priority) + 1;

            /* update cloned object's priority */
            clone.attr('data-priority', next_priority);
            clone.find('.field-priority').val(next_priority);

            /* change values to custom values */
            clone.find('input.field-key').val(new_name);
            clone.find('input.field-label').val(jQuery('.map-row-addnew #new-label').val());
            clone.find('select.field-type').val(jQuery('.map-row-addnew #new-type').val());

            /* update name spaces to show priority placement */
            clone.find('input,select').each(function () {
                this.name = this.name.replace(name, new_name);
            });

            /* unhide delete button */
            clone.find('.delete-custom-field').removeClass('hidden');

            /* removed disabled attribute from mapped key */
            clone.find('input.field-key').removeAttr('disabled');

            /* empty add new container */
            jQuery('.map-row-addnew #new-key').val('');
            jQuery('.map-row-addnew #new-label').val('');

            clone.appendTo(".field-map");

            /* run update */
            FunnelsPathView.updateCustomFields();

        },
        /**
         *  Adds custom lead fields to field map
         */
        addLeadStatus: function () {
            /* create a new li and append to list */
            var clone = jQuery(".status-row:last").clone();

            /* discover last priority and next priority in line */
            var priority = clone.data('priority');
            var next_priority = parseInt(priority) + 1;

            var label = clone.find('input.status-label').val();
            var key = jQuery('#new-status-label').val().replace(/ /g, '-').toLowerCase();
            var old_key = clone.find('input.status-key').val();

            /* update cloned object's priority */
            clone.attr('data-priority', next_priority);
            clone.find('.status-priority').val(next_priority);

            /* change values to custom values */
            clone.find('input.status-key').val(key);
            clone.find('input.status-label').val(jQuery('#add-new-lead-status-form #new-status-label').val());
            clone.find('input.status-colorpicker').val(jQuery('#add-new-lead-status-form #new-status-color').val());
            clone.find('.minicolors-swatch-color').css('background-color', jQuery('#add-new-lead-status-form #new-status-color').val());

            /* update name spaces to show priority placement */
            clone.find('input').each(function () {
                this.name = this.name.replace(old_key, key);
            });

            /* unhide delete button */
            clone.find('.delete-lead-status').removeClass('hidden');

            /* removed disabled attribute from mapped key */
            clone.find('input.status-key').removeAttr('disabled');

            /* empty add new container */
            jQuery('.map-row-addnew #new-key').val('');
            jQuery('.map-row-addnew #new-label').val('');

            clone.appendTo(".lead-statuses");

            /* run update */
            FunnelsPathView.updateLeadStatuses();
            FunnelsPathView.initColorPicker();

        },
        /**
         *  Adds ip address to list of ignored ip addresses
         */
        addIPAddress: function () {
            /* create a new li and append to list */
            var clone = jQuery(".ip-address-row:last").clone();

            /* remove hidden class if present */
            clone.removeClass('hidden');

            /* change values to custom values */
            clone.find('input.field-ip-address').val(jQuery('.ip-address-row-addnew #new-ip-address').val());

            /* unhide delete button */
            clone.find('.delete-custom-field').removeClass('hidden');

            /* empty add new container */
            jQuery('.ip-address-row-addnew #new-ip-address').val('');

            clone.appendTo(".field-ip-addresses");

            /* run update */
            FunnelsPathView.updateIPAddresses();

        },
        /**
         *  Prompt remove custom field confirmation
         */
        removeCustomFieldConfirm: function () {
            FunnelsPathView.input.addClass('hidden');
            FunnelsPathView.input = FunnelsPathView.input.closest('.map-row');
            FunnelsPathView.input.find('.delete-custom-field-confirm').removeClass('hidden');
        },
        /**
         *  Remove custom field
         */
        removeCustomField: function () {
            FunnelsPathView.input = FunnelsPathView.input.closest('.map-row');
            FunnelsPathView.input.remove();
            /* run update */
            FunnelsPathView.updateCustomFields();
        },
        /**
         *  Prompt remove custom field confirmation
         */
        removeLeadStatusConfirm: function () {
            FunnelsPathView.input.addClass('hidden');
            FunnelsPathView.input = FunnelsPathView.input.closest('.status-row');
            FunnelsPathView.input.find('.delete-lead-status-confirm').removeClass('hidden');
        },
        /**
         *  Remove custom field
         */
        removeLeadStatus: function () {
            FunnelsPathView.input = FunnelsPathView.input.closest('.status-row');
            FunnelsPathView.input.remove();
            /* run update */
            FunnelsPathView.updateLeadStatuses();
        },
        /**
         *  Prompt remove custom field confirmation
         */
        removeIPAddressConfirm: function () {
            FunnelsPathView.input.addClass('hidden');
            FunnelsPathView.input = FunnelsPathView.input.closest('.ip-address-row');
            FunnelsPathView.input.find('.delete-ip-address-confirm').removeClass('hidden');
        },
        /**
         *  Remove custom field
         */
        removeIPAddress: function () {
            if (jQuery('.ip-address-row').length > 1) {
                FunnelsPathView.input = FunnelsPathView.input.closest('.ip-address-row');
                FunnelsPathView.input.remove();
                /* run update */
                FunnelsPathView.updateIPAddresses();
            } else {
                jQuery('.ip-address-row').addClass('hidden');
                jQuery('.ip-address-row').find('input').val('');
                FunnelsPathView.updateIPAddresses();
            }
        }
    }


    return construct;

})();


/**
 *  Once dom has been loaded load listeners and initialize components
 */
jQuery(document).ready(function () {

    FunnelsPathView.init();

});