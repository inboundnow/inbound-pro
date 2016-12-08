var InboundSettings = (function () {

    var api_url;
    var search_container;
    /* element that wraps all settings and is shuffle js ready */
    var target;
    /* target setting if available */
    var input;
    var datatype;
    var value;
    var timer;
    var running_ajax;

    var construct = {
        /**
         *  Initialize JS Class
         */
        init: function () {
            this.addListeners();
            this.setVars();
            this.setupSearching();
            this.initShuffleJs();
            this.initColorPicker();
            this.initShuffle();
            this.initBootStrapToolTips();
            setTimeout(function () {
                if (typeof InboundSettings.getUrlParam('setting') == 'undefined' ) {
                    InboundSettings.validateAPIKey();
                }
            }, 1000 * 0)
        },
        /**
         *  Sets static vars
         */
        setVars: function () {
            this.search_container = jQuery('#grid');
            this.target = InboundSettings.getUrlParam('setting');
            if (typeof this.target != 'undefined' ) {
                this.target = this.target.replace('+' , ' ')
            }
            this.running_ajax = new Object();
        },
        /**
         *  Sets up setting searching
         */
        setupSearching: function () {
            // Advanced filtering
            jQuery('body').on('keyup change', '.filter-search', function () {
                var val = this.value.toLowerCase().trim();
                InboundSettings.search_container.shuffle('shuffle', function ($el, shuffle) {
                    var text = $el.data('keywords').toLowerCase();
                    return text.indexOf(val) !== -1;
                });
            });
        },
        /**
         *  Shuffle if there is a 'setting' param
         */
        initShuffleJs: function () {
            if (this.target) {
                jQuery('.filter-search').val(InboundSettings.target);
                jQuery('.filter-search').trigger('change');
                jQuery('.filter-search').trigger('keyup');
            }
        },
        /**
         *  Shuffle if there is a 'setting' param
         */
        initColorPicker: function () {
            jQuery('.status-colorpicker').minicolors();
        },
        /**
         *  Shuffle if there is a 'setting' param
         */
        initShuffle: function () {

            jQuery(".field-map").sortable({
                stop: function () {
                    InboundSettings.updateCustomFieldPriority();
                    InboundSettings.updateCustomFields();
                }
            });

            jQuery(".lead-statuses").sortable({
                stop: function () {
                    InboundSettings.updateLeadStatusesPriority();
                    InboundSettings.updateLeadStatuses();
                }
            });

        },
        /**
         *  Add UI Listeners
         */
        addListeners: function () {

            InboundSettings.addInputListeners();
            InboundSettings.addCustomLeadFieldListeners();
            InboundSettings.addLeadStatusListeners();
            InboundSettings.addIPAddressListeners();
            InboundSettings.addAPIKeyListeners();
            InboundSettings.addOauthListeners();


        },
        /**
         * Initialized BootStrap Tooltips
         */
        initBootStrapToolTips: function () {
            jQuery('.inbound-tooltip').tooltip({
                animated: 'fade',
                placement: 'right',
                container: 'body'
            });
        },
        /**
         *  Adds listeners that support non repeater setting updates
         */
        addInputListeners: function () {
            /* add listeners for non array data changes */
            jQuery(document).on('change unfocus propertychange paste', 'input[data-special-handler!="true"],select[data-special-handler!="true"],radio[data-special-handler!="true"]', function (event) {

                /* set static var */
                InboundSettings.input = jQuery(this);

                if (typeof InboundSettings.input.attr('type') != 'undefined' && InboundSettings.input.attr('type') == 'search' ) {
                    return;
                }

                /* Save Data on Change */
                switch (event.type) {
                    case 'paste':
                        setTimeout(function () {
                            InboundSettings.updateSetting(event);
                        }, 250);
                        break;
                    default:
                        InboundSettings.updateSetting(event);
                        break;
                }

                /* add listener for input hide/reveal selectors */

                var group_name = InboundSettings.input.context.dataset.fieldGroup;
                var value = InboundSettings.input.attr('value');

                jQuery('#'+group_name+' div[data-reveal-selector="#' + InboundSettings.input.attr('id') + '"][data-reveal-value="' + value + '"]').each(function () {
                        jQuery(this).removeClass('hidden');
                });

                 jQuery('#'+group_name+' div[data-reveal-selector="#' + InboundSettings.input.attr('id') + '"][data-reveal-value!="'+value+'"]').each(function () {
                    jQuery(this).addClass('hidden');

                });


            });


        },
        /**
         *  Add listeners that support custom lead fields
         */
        addCustomLeadFieldListeners: function () {


            /* add listeners for custom field changes */
            jQuery(document).on('change unfocus propertychange keyup', 'input[data-field-type="mapped-field"],select[data-field-type="mapped-field"]', function (event) {

                /* format field key */
                if (jQuery(this).hasClass('field-key')) {
                    jQuery(this).val(jQuery(this).val().replace(/ /g, '_').toLowerCase());
                }

                if (InboundSettings.timer == true && event.type != 'propertychange') {
                    return;
                }

                InboundSettings.timer = true;

                setTimeout(function () {
                    InboundSettings.updateCustomFields();
                    InboundSettings.timer = false;
                }, 500);

            });

            /* Add listener to delete custom field */
            jQuery('body').on('click', '#show-hide-disabled-custom-fields', function () {
                InboundSettings.input = jQuery(this);
                if (InboundSettings.input.is(':checked')) {
                    jQuery('.field-map .readonly').css('display', 'flex');
                } else {
                    jQuery('.field-map .readonly').css('display', 'none');
                }
            });

            /* Add listener to delete custom field */
            jQuery('body').on('click', '.toggle-lead-field', function () {
                InboundSettings.input = jQuery(this);
                InboundSettings.toggleCustomField();
            });

            /* Add listener to delete custom field */
            jQuery('body').on('click', '.delete-custom-field', function () {
                InboundSettings.input = jQuery(this);
                InboundSettings.removeCustomFieldConfirm();
            });

            /* Add listener to delete custom field */
            jQuery('body').on('click', '.delete-custom-field-confirm', function () {
                /* set static var */
                InboundSettings.input = jQuery(this);
                InboundSettings.removeCustomField();
            });

            /* Add listeners for add custom field save buttons */
            jQuery(document).on('submit', '#add-new-custom-field-form', function (e) {

                /* prevent the form from doing a submit */
                e.preventDefault();

                InboundSettings.addCustomLeadField();
                return false;
            });
        },
        /**
         *  Add listeners that support custom lead fields
         */
        addLeadStatusListeners: function () {

            /* add listeners for custom field changes */
            jQuery(document).on('change unfocus propertychange keyup', 'input[data-field-type="lead-status-field"]', function () {
                /* format field key */
                if (jQuery(this).hasClass('status-key')) {
                    jQuery(this).val(jQuery(this).val().replace(/ /g, '_').toLowerCase());
                }

                if (InboundSettings.timer == true && event.type != 'propertychange') {
                    return;
                }

                InboundSettings.timer = true;

                setTimeout(function () {
                    InboundSettings.updateLeadStatuses();
                    InboundSettings.timer = false;
                }, 500);

            });

            /* Add listener to delete lead status */
            jQuery('body').on('click', '.delete-lead-status', function () {
                /* set static var */
                InboundSettings.input = jQuery(this);
                InboundSettings.removeLeadStatusConfirm();
            });

            /* Add listener to delete lead-status */
            jQuery('body').on('click', '.delete-lead-status-confirm', function () {
                /* set static var */
                InboundSettings.input = jQuery(this);
                InboundSettings.removeLeadStatus();
            });

            /* Add listeners for add custom field save buttons */
            jQuery(document).on('submit', '#add-new-lead-status-form', function (e) {
                /* prevent the form from doing a submit */
                e.preventDefault();
                InboundSettings.addLeadStatus();
                return false;
            });

        },
        /**
         *  Add listeners to support Analytics do not track IP Addresses
         */
        addIPAddressListeners: function () {

            /* add listeners for IP Address rule changes */
            jQuery(document).on('change unfocus propertychange keyup', 'input[data-field-type="ip-address"]', function (event) {

                if (InboundSettings.timer == true && event.type != 'propertychange') {
                    return;
                }

                InboundSettings.timer = true;

                setTimeout(function () {
                    InboundSettings.updateIPAddresses();
                    InboundSettings.timer = false;
                }, 500);

            });

            /* Add listener to delete custom field */
            jQuery('body').on('click', '.delete-ip-address', function () {
                /* set static var */
                InboundSettings.input = jQuery(this);
                InboundSettings.removeIPAddressConfirm();
            });

            /* Add listener to delete custom field */
            jQuery('body').on('click', '.delete-ip-address-confirm', function () {
                /* set static var */
                InboundSettings.input = jQuery(this);
                InboundSettings.removeIPAddress();
            });

            /* Add listeners for oauth unauthorize buttons */
            jQuery(document).on('submit', '#add-new-ip-address-form', function (e) {
                /* prevent the form from doing a submit */
                e.preventDefault();

                InboundSettings.addIPAddress();
                return false;
            });


        },
        /**
         *  Adds license key input listeners
         */
        addAPIKeyListeners: function () {
            /* add listenrs for license key validation */
            jQuery(document).on('keyup', '.api', function () {
                /* set static var */
                InboundSettings.input = jQuery(this);

                /* dont do squat if the api key does not reach a certain length */
                if (InboundSettings.input.val().length < 10) {
                    return;
                }

                /* Validate api Key */
                if (!this.target) {
                    InboundSettings.validateAPIKey( false );
                }

            });

            /* add listenrs for license key validation */
            jQuery('#reauthorize-api-key').click( function () {
                /* set static var */
                InboundSettings.input = jQuery('.api');

                /* dont do squat if the api key does not reach a certain length */
                if (InboundSettings.input.val().length < 10) {
                    return;
                }

                /* Validate api Key */
                if (!this.target) {
                    InboundSettings.validateAPIKey( true );
                }

            });
        },
        /**
         *  Add oauth workflow listeners
         */
        addOauthListeners: function () {

            /* add listeners for 'add new custom fields  */
            jQuery('body').on('click', '.unauth', function () {
                /* set static var */
                InboundSettings.input = jQuery(this);
                InboundSettings.deauthorizeOauth();
            });


            /* Add listeners for oauth authorize button */
            jQuery('body').on('click', '.oauth', function () {
                /* set static var */
                InboundSettings.input = jQuery(this);
            });

            /* Add listeners for export settings button */
            jQuery('body').on('click', '#export-settings', function () {
                InboundSettings.exportSettings();
            });


            /* Add listeners for oauth close button */
            jQuery(document).on('tb_unload', '#TB_window', function (e) {
                /* listen for success */
                var success = jQuery('#TB_iframeContent').contents().find('.success');

                if (success.length) {
                    InboundSettings.setAuthorized();
                }
            });

        },
        /**
         *  Save Input Data
         */
        updateSetting: function (event) {
            /* serialize input data */
            var serialized = this.prepareSettingData();


            InboundSettings.startProcessing(event);

            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                context: InboundSettings.input,
                data: {
                    action: 'inbound_pro_update_setting',
                    input: serialized
                },
                dataType: 'html',
                timeout: 20000,
                success: function (response) {
                    InboundSettings.endProcessing();

                },
                error: function (request, status, err) {
                    InboundSettings.input.parent().append("<span class='update-text' >"+status+"</span>");
                    setTimeout(function () {
                        jQuery('.update-text').fadeOut(2000, function () {
                            jQuery('.update-text').remove();
                        });
                    }, 500);
                }
            });
        },

        startProcessing: function(event) {
            InboundSettings.input.parent().append("<span class='processing' ><img src='" + inboundSettingsLocalVars.inboundProURL + "assets/images/spinner_60.gif' style=''></span>");

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

            InboundSettings.input.parent().append("<span class='update-text' >Updated</span>");
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


                if ('inbound_pro_update_custom_fields' in InboundSettings.running_ajax) {
                    InboundSettings.running_ajax['inbound_pro_update_custom_fields'].abort();
                }

                InboundSettings.running_ajax['inbound_pro_update_custom_fields'] = jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'inbound_pro_update_custom_fields',
                        input: form.serialize()
                    },
                    dataType: 'html',
                    timeout: 20000,
                    success: function (response) {
                        delete InboundSettings.running_ajax['inbound_pro_update_custom_fields'];
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

                if ('inbound_pro_update_lead_statuses' in InboundSettings.running_ajax) {
                    InboundSettings.running_ajax['inbound_pro_update_lead_statuses'].abort();
                } else {
                    jQuery('#add-lead-status').prepend('<img id="lead-status-processing" src="' + inboundSettingsLocalVars.inboundProURL + 'assets/images/processing.gif" /> ');
                }

                InboundSettings.running_ajax['inbound_pro_update_lead_statuses'] = jQuery.ajax({
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
                        delete InboundSettings.running_ajax['inbound_pro_update_lead_statuses'];
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

                if ('inbound_pro_update_ip_addresses' in InboundSettings.running_ajax) {
                    InboundSettings.running_ajax['inbound_pro_update_ip_addresses'].abort();
                }

                InboundSettings.running_ajax['inbound_pro_update_ip_addresses'] = jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'inbound_pro_update_ip_addresses',
                        input: form.serialize()
                    },
                    dataType: 'html',
                    timeout: 20000,
                    success: function (response) {
                        delete InboundSettings.running_ajax['inbound_pro_update_ip_addresses'];
                    },
                    error: function (request, status, err) {
                        console.log(status);
                    }
                });
            }, 500);
        },

        /**
         *  Export Settings
         */
        exportSettings: function (event) {
            var spin = jQuery('<i>').addClass('fa fa-spinner fa-spin')
            jQuery('#export-settings').text('');
            spin.appendTo('#export-settings');

            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: 'inbound_pro_export_settings',
                },
                dataType: 'html',
                timeout: 20000,
                success: function (response) {
                    jQuery('#export-settings').text('Creating File in /uploads/inbound-pro/settings/');
                    var link = jQuery('<a>').attr('href' , response).text(response).attr('title','Open and save to your computer.').addClass('exported-json-link').css( 'display' , 'list-item').attr('target' , '_blank');
                    link.insertAfter('#export-settings');
                    jQuery('#export-settings').remove();
                },
                error: function (request, status, err) {

                }
            });
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
                    var group = InboundSettings.input.data('field-group');
                    var button = jQuery('.oauth[data-field-group="' + group + '"]');
                    button.removeClass('hidden');
                    InboundSettings.input.addClass('hidden');
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
            var group = InboundSettings.input.data('field-group');

            var button = jQuery('.unauth[data-field-group="' + group + '"]');
            button.removeClass('hidden');

            InboundSettings.input.addClass('hidden');
        },
        /**
         * Prepare a serialized format of settings data
         */
        prepareSettingData: function () {
            var dataarr = new Array();

            /* get data attributes */
            for (var i in InboundSettings.input.data()) {
                var subarr = new Array();
                subarr['name'] = i;
                subarr['value'] = InboundSettings.input.data()[i];
                dataarr.push(subarr);
            }

            /* get value */
            switch (InboundSettings.input.data('field-type')) {
                case 'checkbox':
                    var value = jQuery('#' + InboundSettings.input.attr("id") + ' input:checkbox:checked').map(function () {
                        return jQuery(this).val();
                    }).get();
                    break;
                case 'select2':
                    var value = jQuery('#' + InboundSettings.input.attr('id') + ' option:selected').map(function () {
                        return jQuery(this).val();
                    }).get();
                    break;
                default:
                    var value = InboundSettings.input.val();
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
            subarr['value'] = InboundSettings.input.attr('name');
            dataarr.push(subarr);

            return jQuery.param(InboundSettings.input.serializeArray().concat(dataarr));
        },
        /**
         *  Validate API Key
         */
        validateAPIKey: function ( clear_cache ) {

            if (jQuery('.api').length === 0) {
                return;
            }

            if (typeof jQuery('.api') != 'undefined') {
                InboundSettings.input = jQuery('.api');
            } else {
                alert('notifiy developer of this message');
            }

            InboundSettings.markKeyProcessing();

            if ('inbound_validate_api_key' in InboundSettings.running_ajax) {
                InboundSettings.running_ajax['inbound_validate_api_key'].abort();
            }

            InboundSettings.running_ajax['inbound_validate_api_key'] = jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'inbound_validate_api_key',
                    api_key: InboundSettings.input.val(),
                    site: inboundSettingsLocalVars.siteURL,
                    clear_cache: clear_cache
                },
                dataType: "json",
                timeout: 20000,
                success: function (response) {
                    if (typeof response.customer != 'undefined') {
                        InboundSettings.markKeyValid(response.customer.is_pro);
                        console.log('is_pro=' + response.customer.is_pro);
                    } else {
                        InboundSettings.markKeyInvalid(response.message);
                    }
                    delete InboundSettings.running_ajax['inbound_validate_api_key'];
                },
                error: function (request, status, err) {
                    console.log(request.responseText);
                    console.log(status);
                    console.log(err);

                    if (err != 'abort') {
                        InboundSettings.markKeyInvalid('There was an error connecting to Inbound Now');
                    }
                }
            });

        },
        /**
         * mark key as being processed
         */
        markKeyProcessing: function () {
            InboundSettings.input.removeClass('valid');
            InboundSettings.input.removeClass('invalid');
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
            InboundSettings.input.removeClass('valid');
            InboundSettings.input.addClass('invalid');
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
        markKeyValid: function ( is_pro ) {
            InboundSettings.input.removeClass('invalid');
            InboundSettings.input.addClass('valid');
            jQuery('.invalid-icon').remove();
            jQuery('.valid-icon').remove();
            jQuery('.processing-icon').remove();
            jQuery('.tooltip').hide();
            jQuery('<i>', {
                class: "fa fa-check valid-icon inbound-tooltip",
                title: "Welcome Subscriber - privledge level " + is_pro
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
            var clone = jQuery(".custom-fields-row:not('.readonly'):last").clone();

            /* discover last priority and next priority in line */
            var priority = clone.attr('status-priority');

            var name = clone.find('input.field-key').val();
            var new_name = jQuery('.map-row-addnew #new-key').val().replace(/ /g, '_').toLowerCase();
            var next_priority = parseInt(priority) + 1;

            /* update cloned object's priority */
            clone.attr('status-priority', next_priority);
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
            InboundSettings.updateCustomFields();

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
            InboundSettings.updateLeadStatuses();
            InboundSettings.initColorPicker();

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
            InboundSettings.updateIPAddresses();

        },
        /**
         *  Prompt remove custom field confirmation
         */
        toggleCustomField: function () {
            if (InboundSettings.input.is(':checked')) {
                InboundSettings.input = InboundSettings.input.parent().parent();
                InboundSettings.input.find('input[type!="checkbox"]').prop('readonly' , false);
                InboundSettings.input.find('select').prop('disabled' , false);
                InboundSettings.input.find('select').prop('required' , true);
            } else {
                InboundSettings.input = InboundSettings.input.parent().parent();
                InboundSettings.input.find('input[type!="checkbox"],select').prop('readonly', true);
                InboundSettings.input.find('select').prop('disabled' , true);
                InboundSettings.input.find('select').prop('required' , true);
            }
        },
        /**
         *  Prompt remove custom field confirmation
         */
        removeCustomFieldConfirm: function () {
            InboundSettings.input.addClass('hidden');
            InboundSettings.input = InboundSettings.input.closest('.map-row');
            InboundSettings.input.find('.delete-custom-field-confirm').removeClass('hidden');
        },
        /**
         *  Remove custom field
         */
        removeCustomField: function () {
            InboundSettings.input = InboundSettings.input.closest('.map-row');
            InboundSettings.input.remove();
            /* run update */
            InboundSettings.updateCustomFields();
        },
        /**
         *  Prompt remove custom field confirmation
         */
        removeLeadStatusConfirm: function () {
            InboundSettings.input.addClass('hidden');
            InboundSettings.input = InboundSettings.input.closest('.status-row');
            InboundSettings.input.find('.delete-lead-status-confirm').removeClass('hidden');
        },
        /**
         *  Remove custom field
         */
        removeLeadStatus: function () {
            InboundSettings.input = InboundSettings.input.closest('.status-row');
            InboundSettings.input.remove();
            /* run update */
            InboundSettings.updateLeadStatuses();
        },
        /**
         *  Prompt remove custom field confirmation
         */
        removeIPAddressConfirm: function () {
            InboundSettings.input.addClass('hidden');
            InboundSettings.input = InboundSettings.input.closest('.ip-address-row');
            InboundSettings.input.find('.delete-ip-address-confirm').removeClass('hidden');
        },
        /**
         *  Remove custom field
         */
        removeIPAddress: function () {
            if (jQuery('.ip-address-row').length > 1) {
                InboundSettings.input = InboundSettings.input.closest('.ip-address-row');
                InboundSettings.input.remove();
                /* run update */
                InboundSettings.updateIPAddresses();
            } else {
                jQuery('.ip-address-row').addClass('hidden');
                jQuery('.ip-address-row').find('input').val('');
                InboundSettings.updateIPAddresses();
            }
        }
    }


    return construct;

})();


/**
 *  Once dom has been loaded load listeners and initialize components
 */
jQuery(document).ready(function () {

    InboundSettings.init();

});