/**
 * # Inbound Forms
 *
 * This file contains all of the form functions of the main _inbound object.
 * Filters and actions are described below
 *
 * @author David Wells <david@inboundnow.com>
 * @author Hudson Atwell <hudson@inboundnow.com>
 * @version 0.0.2
 */
/* Finish Exclusions for CC */

/* Launches form class */
var InboundForms = (function(_inbound) {

    var debugMode = false,
        utils = _inbound.Utils,
        no_match = [],
        rawParams = [],
        mappedParams = [],
        callTracker = {},
        settings = _inbound.Settings;

    var FieldMapArray = [
        "first name",
        "last name",
        "name",
        "email",
        "e-mail",
        "phone",
        "website",
        "job title",
        "your favorite food",
        "company",
        "tele",
        "address",
        "comment"
        /* Adding values here maps them */
    ];

    _inbound.Forms = {

        // Init Form functions
        init: function() {
            _inbound.Forms.runFieldMappingFilters();
            _inbound.Forms.formTrackInit();
            _inbound.Forms.searchTrackInit();
        },
        /**
         * This triggers the forms.field_map filter on the mapping array.
         * This will allow you to add or remore Items from the mapping lookup
         *
         * ### Example inbound.form_map_before filter
         *
         * This is an example of how form mapping can be filtered and
         * additional fields can be mapped via javascript
         *
         * ```js
         *  // Adding the filter function
         *  function Inbound_Add_Filter_Example( FieldMapArray ) {
         *    var map = FieldMapArray || [];
         *    map.push('new lookup value');
         *
         *    return map;
         *  };
         *
         *  // Adding the filter on dom ready
         *  _inbound.hooks.addFilter( 'inbound.form_map_before', Inbound_Add_Filter_Example, 10 );
         * ```
         *
         * @return {[type]} [description]
         */
        runFieldMappingFilters: function() {
            FieldMapArray = _inbound.hooks.applyFilters('forms.field_map', FieldMapArray);
            //alert(FieldMapArray);
        },
        debug: function(msg, callback) {
            //if app not in debug mode, exit immediately
            if (!debugMode || !console) {
                return;
            }

            var msg = msg || false;
            //console.log the message
            if (msg && (typeof msg === 'string')) {
                console.log(msg);
            }

            //execute the callback if one was passed-in
            if (callback && (callback instanceof Function)) {
                callback();
            }
        },
        formTrackInit: function() {

            for (var i = 0; i < window.document.forms.length; i++) {
                var trackForm = false;
                var form = window.document.forms[i];
                /* process forms only once */
                if (!form.dataset.formProcessed) {
                    form.dataset.formProcessed = true;
                    trackForm = this.checkTrackStatus(form);
                    if (trackForm) {
                        this.attachFormSubmitEvent(form); /* attach form listener */
                        this.initFormMapping(form);
                    }
                }
            }
        },
        searchTrackInit: function(){
            
            /* exit if searches aren't supposed to be tracked, or this function has already been called */
            if(inbound_settings.search_tracking == 'off' || callTracker['searchTrackInit']){
                return;
            }

            for (var i = 0; i < window.document.forms.length; i++) {
                var trackForm = false;
                var form = window.document.forms[i];
                /* process forms only once */
                if (!form.dataset.searchChecked) {
                    form.dataset.searchChecked = true;
                    trackForm = this.checkSearchTrackStatus(form);
                    if (trackForm) {
                        this.attachSearchFormSubmitEvent(form); /* attach form listener */
                    }
                }
            }

            /* store the search data on init */
            utils.storeSearchData();
            
            /* log that this function has been called */
            callTracker['searchTrackInit'] = true;
        },
        checkTrackStatus: function(form) {
            var ClassIs = form.getAttribute('class');
            if (ClassIs !== "" && ClassIs !== null) {
                if (ClassIs.toLowerCase().indexOf("wpl-track-me") > -1) {
                    return true;
                } else if (ClassIs.toLowerCase().indexOf("inbound-track") > -1) {
                    return true;
                } else {
                    cb = function() { console.log(form); };
                    _inbound.deBugger('forms', "This form not tracked. Please assign on in settings...", cb);
                    return false;
                }
            }
        },
        checkSearchTrackStatus: function(form) {
            var ClassIs = form.getAttribute('class'),
                IdIs = form.getAttribute('id');
            if (ClassIs !== "" && ClassIs !== null) {
                if (ClassIs.toLowerCase().indexOf("search") > -1) {
                    return true;
                }
            }
            if (IdIs !== "" && IdIs !== null) {
                if (IdIs.toLowerCase().indexOf("search") > -1) {
                    return true;
                }
            }else{
                cb = function() { console.log(form); };
                _inbound.deBugger('searches', "This search form is not tracked. Please assign on in settings...", cb);
                return false;
            }
        },
        /* Loop through include/exclude items for tracking */
        loopClassSelectors: function(selectors, action) {
            for (var i = selectors.length - 1; i >= 0; i--) {

                var selector = utils.trim(selectors[i])
                if (selector.indexOf("#") === -1 && selector.indexOf(".") === -1) {
                    // assign ID as default
                    selector = "#" + selector;
                }
                //if(selectors[i] match . or # )
                selector = document.querySelector(selector);
                //console.log("SELECTOR", selector);
                if (selector) {
                    if (action === 'add') {
                        _inbound.Utils.addClass('wpl-track-me', selector);
                        _inbound.Utils.addClass('inbound-track', selector);
                    } else {
                        _inbound.Utils.removeClass('wpl-track-me', selector);
                        _inbound.Utils.removeClass('inbound-track', selector);
                    }
                }
            }
        },
        /* Map field fields on load */
        initFormMapping: function(form) {
            var hiddenInputs = [];

            for (var i = 0; i < form.elements.length; i++) {
                formInput = form.elements[i];

                if (formInput.type === 'hidden') {
                    hiddenInputs.push(formInput);
                    continue;
                }

                //this.ignoreFields(formInput);
                /* Map form fields */
                this.mapField(formInput);
                /* Remember visible inputs */
                this.rememberInputValues(formInput);
                /* Fill visible inputs */
                if (settings.formAutoPopulation && !_inbound.Utils.hasClass( "nopopulate", form ) ) {
                    this.fillInputValues(formInput);
                }

            }

            /* loop hidden inputs */
            for (var n = hiddenInputs.length - 1; n >= 0; n--) {
                formInput = hiddenInputs[n];
                this.mapField(formInput);
            }

            //console.log('mapping on load completed');
        },
                /* Maps data attributes to fields on page load */
        mapField: function(input) {

            var input_id = input.id || false;
            var input_name = input.name || false;
            var label = this.getInputLabel(input);

            if(label){
                //console.log(label[0].innerText);
                var ignoreField = this.ignoreFieldByLabel(label[0].innerText);
                if(ignoreField){
                    input.dataset.ignoreFormField = true;
                    return false;
                }
            }
                 
            /* Loop through all match possiblities */
            for (i = 0; i < FieldMapArray.length; i++) {
                //for (var i = FieldMapArray.length - 1; i >= 0; i--) {
                var found = false;
                var match = FieldMapArray[i];
                var lookingFor = utils.trim(match);
                var nice_name = lookingFor.replace(/ /g, '_');


                //console.log("NICE NAME", nice_name);
                //console.log('looking for match on ' + lookingFor);
                //_inbound.deBugger('forms', 'looking for match on ' + lookingFor + " nice_name= " + nice_name);

                // Check if input has an attached lable using for= tag
                //var $laxbel = $("label[for='" + $element.attr('id') + "']").text();
                //var labxel = 'label[for="' + input_id + '"]';

                /* look for name attribute match */
                if (input_name && input_name.toLowerCase().indexOf(lookingFor) > -1) {
                    found = true;
                    _inbound.deBugger('forms', 'Found matching name attribute for -> ' + lookingFor);

                    /* look for id match */
                } else if (input_id && input_id.toLowerCase().indexOf(lookingFor) > -1) {

                    found = true;
                     _inbound.deBugger('forms', 'Found matching ID attribute for ->' + lookingFor);

                    /* Check siblings for label */
                } else if (label) {
                    //var label = (label.length > 1 ? label[0] : label);
                    //console.log('label', label);
                    if (label[0].innerText.toLowerCase().indexOf(lookingFor) > -1) {

                    found = true;
                    _inbound.deBugger('forms', 'Found matching sibling label for -> ' + lookingFor);

                    }

                } else {
                    /* no match found */
                    //_inbound.deBugger('forms', 'NO Match on ' + lookingFor + " in " + input_name);
                    no_match.push(lookingFor);

                }

                /* Map the field */
                if (found) {
                    this.addDataAttr(input, nice_name);
                    this.removeArrayItem(FieldMapArray, lookingFor);
                    i--; //decrement count
                }

            }
            
            return inbound_data;

        },
        /* prevent default submission temporarily */
        formListener: function(event) {
            //console.log(event);
            event.preventDefault();
            _inbound.Forms.saveFormData(event.target);
            document.body.style.cursor = "wait";
        },
        /* prevent default submission temporarily */
        searchFormListener: function(event) {
            //console.log(event);
            event.preventDefault();
            _inbound.Forms.saveSearchData(event.target);
            //document.body.style.cursor = "wait";
        },
        /* attach form listeners */
        attachFormSubmitEvent: function(form) {
            utils.addListener(form, 'submit', this.formListener);
            var email_input = document.querySelector('.inbound-email');
            /* utils.addListener(email_input, 'blur', this.mailCheck); */
        },
        /* attach search form listener */
        attachSearchFormSubmitEvent: function(form) {
            utils.addListener(form, 'submit', this.searchFormListener);
        },
        /* Ignore CC data */
        ignoreFieldByLabel: function(label) {
            var ignore_field = false;

            if(!label){ return false; }

            // Ignore any fields with labels that indicate a credit card field
            if (label.toLowerCase().indexOf('credit card') != -1 || label.toLowerCase().indexOf('card number') != -1) {
                ignore_field = true;
            }

            if (label.toLowerCase().indexOf('expiration') != -1 || label.toLowerCase().indexOf('expiry') != -1) {
                ignore_field = true;
            }

            if (label.toLowerCase() == 'month' || label.toLowerCase() == 'mm' || label.toLowerCase() == 'yy' || label.toLowerCase() == 'yyyy' || label.toLowerCase() == 'year') {
                ignore_field = true;
            }

            if (label.toLowerCase().indexOf('cvv') != -1 || label.toLowerCase().indexOf('cvc') != -1 || label.toLowerCase().indexOf('secure code') != -1 || label.toLowerCase().indexOf('security code') != -1) {
                ignore_field = true;
            }

            if(ignore_field){
                _inbound.deBugger('forms', 'ignore ' + label);
            }

            return ignore_field;

        },
        /* not implemented yet */
        ignoreFieldByValue: function(value){
            var ignore_field = false;

            if(!value){ return false; }

            if (value.toLowerCase() == 'visa' || value.toLowerCase() == 'mastercard' || value.toLowerCase() == 'american express' || value.toLowerCase() == 'amex' || value.toLowerCase() == 'discover') {
                ignore_field = true;
            }

            // Check if value has integers, strip out spaces, then ignore anything with a credit card length (>16) or an expiration/cvv length (<5)
            var int_regex = new RegExp("/^[0-9]+$/");
            if (int_regex.test(value)) {
                var value_no_spaces = value.replace(' ', '');

                if (this.isInt(value_no_spaces) && value_no_spaces.length >= 16) {
                    ignore_field = true;
                }

            }

            return ignore_field;

        },
        isInt: function(n) {
            return typeof n == "number" && isFinite(n) && n % 1 === 0;
        },
        releaseFormSubmit: function(form) {
            //console.log('remove form listener event');
            document.body.style.cursor = "default";
            utils.removeClass('wpl-track-me', form);
            utils.removeListener(form, 'submit', this.formListener);
            var formClass = form.getAttribute('class');
            if (formClass !== "" && formClass !== null) {
                /* If contact form 7 do this */
                if (formClass.toLowerCase().indexOf("wpcf7-form") != -1) {
                    //alert('release')
                    setTimeout(function() {
                       document.body.style.cursor = "default";
                    }, 300);
                    return true;
                }
            }

            form.submit();
            /* fallback if submit name="submit" */
            setTimeout(function() {
                for (var i = 0; i < form.elements.length; i++) {
                    formInput = form.elements[i];
                    type = formInput.type || false;
                    if (type === "submit" && formInput.name === "submit") {
                        form.elements[i].click();
                    }
                }
            }, 2000);

        },
        saveFormData: function(form) {
            var inputsObject = inputsObject || {};
            for (var i = 0; i < form.elements.length; i++) {

                // console.log(inputsObject);

                formInput = form.elements[i];
                multiple = false;

                if (formInput.name) {

                    if (formInput.dataset.ignoreFormField) {
                        _inbound.deBugger('forms', 'ignore ' + formInput.name);
                        continue;
                    }

                    inputName = formInput.name.replace(/\[([^\[]*)\]/g, "%5B%5D$1");
                    //inputName = inputName.replace(/-/g, "_");
                    if (!inputsObject[inputName]) {
                        inputsObject[inputName] = {};
                    }
                    if (formInput.type) {
                        inputsObject[inputName]['type'] = formInput.type;
                    }
                    if (!inputsObject[inputName]['name']) {
                        inputsObject[inputName]['name'] = formInput.name;
                    }
                    if (formInput.dataset.mapFormField) {
                        inputsObject[inputName]['map'] = formInput.dataset.mapFormField;
                    }


                    switch (formInput.nodeName) {

                        case 'INPUT':
                            value = this.getInputValue(formInput);


                            if (value === false) {
                                continue;
                            }
                            break;

                        case 'TEXTAREA':
                            value = formInput.value;
                            break;

                        case 'SELECT':
                            if (formInput.multiple) {
                                values = [];
                                multiple = true;

                                for (var j = 0; j < formInput.length; j++) {
                                    if (formInput[j].selected) {
                                        values.push(encodeURIComponent(formInput[j].value));
                                    }
                                }

                            } else {
                                value = (formInput.value);
                            }

                            break;
                    }

                    _inbound.deBugger('forms', 'Input Value = ' + value);


                    if (value) {
                        /* inputsObject[inputName].push(multiple ? values.join(',') : encodeURIComponent(value)); */
                        if (!inputsObject[inputName]['value']) {
                            inputsObject[inputName]['value'] = [];
                        }
                        inputsObject[inputName]['value'].push(multiple ? values.join(',') : encodeURIComponent(value));
                        var value = multiple ? values.join(',') : encodeURIComponent(value);

                    }

                }
            }
            _inbound.deBugger('forms', inputsObject);

            //console.log('These are the raw values', inputsObject);
            //_inbound.totalStorage('the_key', inputsObject);
            //var inputsObject = sortInputs(inputsObject);

            var matchCommon = /name|first name|last name|email|e-mail|phone|website|job title|company|tele|address|comment/;

            for (var input in inputsObject) {
                //console.log(input);

                var inputValue = inputsObject[input]['value'];
                var inputMappedField = inputsObject[input]['map'];
                //if (matchCommon.test(input) !== false) {
                //console.log(input + " Matches Regex run mapping test");
                //var map = inputsObject[input];
                //console.log("MAPP", map);
                //mappedParams.push( input + '=' + inputsObject[input]['value'].join(',') );
                //}

                /* Add custom hook here to look for additional values */
                if (typeof(inputValue) != "undefined" && inputValue != null && inputValue != "") {
                    rawParams.push(input + '=' + inputsObject[input]['value'].join(','));
                }

                if (typeof(inputMappedField) != "undefined" && inputMappedField != null && inputsObject[input]['value']) {
                    //console.log('Data ATTR', formInput.dataset.mapFormField);
                    mappedParams.push(inputMappedField + "=" + inputsObject[input]['value'].join(','));
                    if (input === 'email') {
                        var email = inputsObject[input]['value'].join(',');
                        //alert(email);

                    }
                }
            }

            var raw_params = rawParams.join('&');
            _inbound.deBugger('forms', "Stringified Raw Form PARAMS: " + raw_params);

            var mapped_params = mappedParams.join('&');
             _inbound.deBugger('forms', "Stringified Mapped PARAMS" + mapped_params);

            /* Check Use form Email or Cookie */
            var email = utils.getParameterVal('email', mapped_params) || utils.readCookie('wp_lead_email');

            /* Legacy Email map */
            if (!email) {
                email = utils.getParameterVal('wpleads_email_address', mapped_params);
            }

            var fullName = utils.getParameterVal('name', mapped_params);
            var fName = utils.getParameterVal('first_name', mapped_params);
            var lName = utils.getParameterVal('last_name', mapped_params);

            // Fallbacks for empty values
            if (!lName && fName) {
                var parts = decodeURI(fName).split(" ");
                if (parts.length > 0) {
                    fName = parts[0];
                    lName = parts[1];
                }
            }

            if (fullName && !lName && !fName) {
                var parts = decodeURI(fullName).split(" ");
                if (parts.length > 0) {
                    fName = parts[0];
                    lName = parts[1];
                }
            }

            fullName = (fName && lName) ? fName + " " + lName : fullName;

            if(!fName) { fName = ""; }
            if(!lName) { lName = ""; }

            _inbound.deBugger('forms', "fName = " + fName);
            _inbound.deBugger('forms', "lName = " + lName);
            _inbound.deBugger('forms', "fullName = " + fullName);

            //return false;
            var page_views = _inbound.totalStorage('page_views') || {};
            var urlParams = _inbound.totalStorage('inbound_url_params') || {};

            /* check if redirect url is empty */
            var formRedirectUrl = form.querySelectorAll('input[value][type="hidden"][name="inbound_furl"]:not([value=""])');
            var inbound_form_is_ajax = false;
            if(formRedirectUrl.length == 0 || formRedirectUrl[0]['value'] == 'IA=='){
                var inbound_form_is_ajax = true;
            }

            /* get form id */
            var inbound_form_id = form.querySelectorAll('input[value][type="hidden"][name="inbound_form_id"]');
            if(inbound_form_id.length > 0 ){
                inbound_form_id = inbound_form_id[0]['value'];
            } else {
                inbound_form_id = 0;
            }

            var inboundDATA = {
                'email': email
            };

            /* Get Variation ID */
            if (typeof(landing_path_info) != "undefined") {
                var variation = landing_path_info.variation;
            } else if (typeof(cta_path_info) != "undefined") {
                var variation = cta_path_info.variation;
            } else {
                var variation = inbound_settings.variation_id;
            }
            var post_type = inbound_settings.post_type || 'page';
            var page_id = inbound_settings.post_id || 0;
            // data['wp_lead_uid'] = jQuery.cookie("wp_lead_uid") || null;
            // data['search_data'] = JSON.stringify(jQuery.totalStorage('inbound_search')) || {};
            search_data = {};
            /* Filter here for raw */
            formData = {
                'action': 'inbound_lead_store',
                'email': email,
                "full_name": fullName,
                "first_name": fName,
                "last_name": lName,
                'raw_params': raw_params,
                'mapped_params': mapped_params,
                'url_params': JSON.stringify(urlParams),
                'search_data': 'test',
                'page_views': JSON.stringify(page_views),
                'post_type': post_type,
                'page_id': page_id,
                'variation': variation,
                'source': utils.readCookie("inbound_referral_site"),
                'inbound_submitted': inbound_form_is_ajax,
                'inbound_form_id': inbound_form_id,
                'inbound_nonce': inbound_settings.ajax_nonce,
                'event': form
            };

            callback = function(leadID) {
                /* Action Example */

                _inbound.deBugger('forms', 'Lead Created with ID: ' + leadID);
                leadID = parseInt(leadID, 10);
                formData.leadID = leadID;
                /* Set Lead cookie ID */
                if (leadID) {
                    utils.createCookie("wp_lead_id", leadID);
                    _inbound.totalStorage.deleteItem('page_views'); // remove pageviews
                    _inbound.totalStorage.deleteItem('tracking_events'); // remove events
                }

                _inbound.trigger('form_after_submission', formData);

                /* Resume normal form functionality */
                _inbound.Forms.releaseFormSubmit(form);

            }

            _inbound.trigger('form_before_submission', formData);

            utils.ajaxPost(inbound_settings.admin_url, formData, callback);
        },
        saveSearchData: function(form) {
            var inputsObject = inputsObject || {};
            for (var i = 0; i < form.elements.length; i++) {

                //console.log(inputsObject);

                formInput = form.elements[i];
                multiple = false;

                if (formInput.name) {

                    if (formInput.dataset.ignoreFormField) {
                        _inbound.deBugger('searches', 'ignore ' + formInput.name);
                        continue;
                    }

                    inputName = formInput.name.replace(/\[([^\[]*)\]/g, "%5B%5D$1");
                    //inputName = inputName.replace(/-/g, "_");
                    if (!inputsObject[inputName]) {
                        inputsObject[inputName] = {};
                    }
                    if (formInput.type) {
                        inputsObject[inputName]['type'] = formInput.type;
                        
                    }
                    if (!inputsObject[inputName]['name']) {
                        inputsObject[inputName]['name'] = formInput.name;
                    }
                    if (formInput.dataset.mapFormField) {
                        inputsObject[inputName]['map'] = formInput.dataset.mapFormField;
                    }


                    switch (formInput.nodeName) {

                        case 'INPUT':
                            value = this.getInputValue(formInput);


                            if (value === false) {
                                continue;
                            }
                            break;

                        case 'TEXTAREA':
                            value = formInput.value;
                            break;

                        case 'SELECT':
                            if (formInput.multiple) {
                                values = [];
                                multiple = true;

                                for (var j = 0; j < formInput.length; j++) {
                                    if (formInput[j].selected) {
                                        values.push(encodeURIComponent(formInput[j].value));
                                    }
                                }

                            } else {
                                value = (formInput.value);
                            }

                            break;
                    }

                    _inbound.deBugger('searches', 'Input Value = ' + value);


                    if (value) {
                        /* inputsObject[inputName].push(multiple ? values.join(',') : encodeURIComponent(value)); */
                        if (!inputsObject[inputName]['value']) {
                            inputsObject[inputName]['value'] = [];
                        }
                        inputsObject[inputName]['value'].push(multiple ? values.join(',') : encodeURIComponent(value));
                        var value = multiple ? values.join(',') : encodeURIComponent(value);

                    }

                }
            }

            _inbound.deBugger('searches', inputsObject);

            /* create an array of search fields //(not fully implemented) at the moment, it only maps the text in the "search" input types*/
            var searchQuery = [];
            for (var input in inputsObject) {
                var inputValue = inputsObject[input]['value'];
                var inputType = inputsObject[input]['type'];

                /* Add custom hook here to look for additional values */
                if (typeof(inputValue) != "undefined" && inputValue != null && inputValue != "") {
                    // This is for mapping all fields of a search form. The resulting string is processed 
                    // in inbound-pro\classes\admin\report-templates\report.lead-searches-and-comments.php
                    // In the function print_action_popup()
                    // searchQuery.push(input + '|value|' + inputsObject[input]['value'].join(','));
                    
                    // get the search input value
                    if(inputType == 'search'){
                        searchQuery.push('search_text' + '|value|' + inputsObject[input]['value']);
                    }
                }
            }
            /* exit if there isn't a search query */
            if(!searchQuery[0]){
                return;
            }

            var searchString = searchQuery.join('|field|');
            _inbound.deBugger('searches', "Stringified Search Form PARAMS: " + searchString);

            /* Get Variation ID */
            if (typeof(landing_path_info) != "undefined") {
                var variation = landing_path_info.variation;
            } else if (typeof(cta_path_info) != "undefined") {
                var variation = cta_path_info.variation;
            } else {
                var variation = inbound_settings.variation_id;
            }
            var post_type = inbound_settings.post_type || 'page';
            var page_id = inbound_settings.post_id || 0;
            
            var user_UID = utils.readCookie("wp_lead_uid");
            
            /* get the user's email address if possible */
            if(inbound_settings.wp_lead_data.lead_email){
                email = inbound_settings.wp_lead_data.lead_email;
            }else if(utils.readCookie('inbound_wpleads_email_address')){
                email = utils.readCookie('inbound_wpleads_email_address');
            }else{
                email = '';
            }

            /* Filter here for raw */
            searchData = {
                'email': email,
                'search_data': searchString,
                'user_UID': user_UID,
                'post_type': post_type,
                'page_id': page_id,
                'variation': variation,
                'source': utils.readCookie("inbound_referral_site"),
                'ip_address': inbound_settings.ip_address,
                'timestamp': Math.floor((new Date).getTime()/1000),
            };

            /* filter data before caching it in the user's browser */
            _inbound.trigger('search_before_caching', searchData);

            /* cache search data */
            if(inbound_settings.wp_lead_data.lead_id){
                searchData['lead_id'] = inbound_settings.wp_lead_data.lead_id;
                utils.cacheSearchData(searchData, form)
            }else{
                utils.cacheSearchData(searchData, form);
            }
            

        },
        rememberInputValues: function(input) {
            var name = (input.name) ? "inbound_" + input.name : '';
            var type = (input.type) ? input.type : 'text';
            if (type === 'submit' || type === 'hidden' || type === 'file' || type === "password" || input.dataset.ignoreFormField) {
                return false;
            }

            utils.addListener(input, 'change', function(e) {
                if (e.target.name) {
                    /* Check for input type */
                    if (type !== "checkbox") {
                        var value = e.target.value;
                    } else {
                        var values = [];
                        var checkboxes = document.querySelectorAll('input[name="' + e.target.name + '"]');
                        for (var i = 0; i < checkboxes.length; i++) {
                            var checked = checkboxes[i].checked;
                            if (checked) {
                                values.push(checkboxes[i].value);
                            }
                            value = values.join(',');
                        };
                    }
                    //console.log(e.target.nodeName);
                    //console.log('change ' + e.target.name + " " + encodeURIComponent(value));

                    inputData = {
                        name: e.target.name,
                        node: e.target.nodeName.toLowerCase(),
                        type: type,
                        value: value,
                        mapping: e.target.dataset.mapFormField
                    };

                    _inbound.trigger('form_input_change', inputData);
                    /* Set Field Input Cookies */
                    utils.createCookie("inbound_" + e.target.name, encodeURIComponent(value));
                    // _inbound.totalStorage('the_key', FormStore);
                    /* Push to 'unsubmitted form object' */
                }

            });
        },
        fillInputValues: function(input) {
            var name = (input.name) ? "inbound_" + input.name : '';
            var type = (input.type) ? input.type : 'text';
            if (type === 'submit' || type === 'hidden' || type === 'file' || type === "password") {
                return false;
            }
            if (utils.readCookie(name) && name != 'comment') {

                value = decodeURIComponent(utils.readCookie(name));
                if (type === 'checkbox' || type === 'radio') {
                    var checkbox_vals = value.split(',');
                    for (var i = 0; i < checkbox_vals.length; i++) {
                        if (input.value.indexOf(checkbox_vals[i]) > -1) {
                            input.checked = true;
                        }
                    }
                } else {
                    if (value !== "undefined") {
                        input.value = value;
                    }
                }
            }
        },
        getInputLabel: function(input){
            var label;
            if(label = this.siblingsIsLabel(input)){
               return label;
            } else if (label = this.CheckParentForLabel(input)) {
               return label;
            } else {
               //console.log("no label nf", input);
               return false;
            }
        },
        /* Get correct input values */
        getInputValue: function(input) {
            var value = false;

            switch (input.type) {
                case 'radio':
                case 'checkbox':
                    if (input.checked) {
                        value = input.value;
                        //console.log("CHECKBOX VAL", value)
                    }
                    break;

                case 'text':
                case 'hidden':
                default:
                    value = input.value;
                    break;

            }

            return value;
        },
        /* Add data-map-form-field attr to input */
        addDataAttr: function(formInput, match) {

            var getAllInputs = document.getElementsByName(formInput.name);
            for (var i = getAllInputs.length - 1; i >= 0; i--) {
                if (!formInput.dataset.mapFormField) {
                    getAllInputs[i].dataset.mapFormField = match;
                }
            };
        },
        /* Optimize FieldMapArray array for fewer lookups */
        removeArrayItem: function(array, item) {
            if (array.indexOf) {
                index = array.indexOf(item);
            } else {
                for (index = array.length - 1; index >= 0; --index) {
                    if (array[index] === item) {
                        break;
                    }
                }
            }
            if (index >= 0) {
                array.splice(index, 1);
            }
            //_inbound.deBugger('forms', 'removed ' + item + " from array");
            //console.log('removed ' + item + " from array");
            return;
        },
        /* Look for siblings that are form labels */
        siblingsIsLabel: function(input) {
            var siblings = this.getSiblings(input);
            var labels = [];
            for (var i = siblings.length - 1; i >= 0; i--) {
                if (siblings[i].nodeName.toLowerCase() === 'label') {
                    labels.push(siblings[i]);
                }
            };
            /* if only 1 label */
            if (labels.length > 0 && labels.length < 2) {
                return labels;
            }

            return false;
        },
        getChildren: function(n, skipMe) {
            var r = [];
            var elem = null;
            for (; n; n = n.nextSibling)
                if (n.nodeType == 1 && n != skipMe)
                    r.push(n);
            return r;
        },
        getSiblings: function(n) {
            return this.getChildren(n.parentNode.firstChild, n);
        },
        /* Check parent elements inside form for labels */
        CheckParentForLabel: function(element) {
            if (element.nodeName === 'FORM') {
                return null;
            }
            do {
                var labels = element.getElementsByTagName("label");
                if (labels.length > 0 && labels.length < 2) {
                    return element.getElementsByTagName("label");
                }

            } while (element = element.parentNode);

            return null;
        },
        /* Validate Common Email addresses */
        mailCheck: function() {
            var email_input = document.querySelector('.inbound-email');
            if (email_input) {
                //
                utils.addListener(email_input, 'blur', this.mailCheck);

                Mailcheck.run({
                    email: document.querySelector('.inbound-email').value,
                    suggested: function(suggestion) {
                        // callback code

                        var suggest = document.querySelector('.email_suggestion');
                        if (suggest) {
                            utils.removeElement(suggest);
                        }
                        var el = document.createElement("span");
                        el.innerHTML = "<span class=\"email_suggestion\">Did youu mean <b><i id='email_correction' style='cursor: pointer;' title=\"click to update\">" + suggestion.full + "</b></i>?</span>";
                        email_input.parentNode.insertBefore(el, email_input.nextSibling);
                        var update = document.getElementById('email_correction');
                        utils.addListener(update, 'click', function() {
                            email_input.value = update.innerHTML;
                            update.parentNode.parentNode.innerHTML = "Fixed!";
                        });
                    },
                    empty: function() {
                        //$(".email_suggestion").html("No Suggestions :(");
                    }
                });
            }
        }

    };
    /* Mailcheck */
    if (typeof Mailcheck === "undefined") {
        var Mailcheck = {
            domainThreshold: 1,
            topLevelThreshold: 3,

            defaultDomains: ["yahoo.com", "google.com", "hotmail.com", "gmail.com", "me.com", "aol.com", "mac.com",
                "live.com", "comcast.net", "googlemail.com", "msn.com", "hotmail.co.uk", "yahoo.co.uk",
                "facebook.com", "verizon.net", "sbcglobal.net", "att.net", "gmx.com", "mail.com", "outlook.com", "icloud.com"
            ],

            defaultTopLevelDomains: ["co.jp", "co.uk", "com", "net", "org", "info", "edu", "gov", "mil", "ca", "de"],

            run: function(opts) {
                opts.domains = opts.domains || Mailcheck.defaultDomains;
                opts.topLevelDomains = opts.topLevelDomains || Mailcheck.defaultTopLevelDomains;
                opts.distanceFunction = opts.distanceFunction || Mailcheck.sift3Distance;

                var defaultCallback = function(result) {
                    return result;
                };
                var suggestedCallback = opts.suggested || defaultCallback;
                var emptyCallback = opts.empty || defaultCallback;

                var result = Mailcheck.suggest(Mailcheck.encodeEmail(opts.email), opts.domains, opts.topLevelDomains, opts.distanceFunction);

                return result ? suggestedCallback(result) : emptyCallback();
            },

            suggest: function(email, domains, topLevelDomains, distanceFunction) {
                email = email.toLowerCase();

                var emailParts = this.splitEmail(email);

                var closestDomain = this.findClosestDomain(emailParts.domain, domains, distanceFunction, this.domainThreshold);

                if (closestDomain) {
                    if (closestDomain != emailParts.domain) {
                        // The email address closely matches one of the supplied domains; return a suggestion
                        return {
                            address: emailParts.address,
                            domain: closestDomain,
                            full: emailParts.address + "@" + closestDomain
                        };
                    }
                } else {
                    // The email address does not closely match one of the supplied domains
                    var closestTopLevelDomain = this.findClosestDomain(emailParts.topLevelDomain, topLevelDomains, distanceFunction, this.topLevelThreshold);
                    if (emailParts.domain && closestTopLevelDomain && closestTopLevelDomain != emailParts.topLevelDomain) {
                        // The email address may have a mispelled top-level domain; return a suggestion
                        var domain = emailParts.domain;
                        closestDomain = domain.substring(0, domain.lastIndexOf(emailParts.topLevelDomain)) + closestTopLevelDomain;
                        return {
                            address: emailParts.address,
                            domain: closestDomain,
                            full: emailParts.address + "@" + closestDomain
                        };
                    }
                }
                /* The email address exactly matches one of the supplied domains, does not closely
                 * match any domain and does not appear to simply have a mispelled top-level domain,
                 * or is an invalid email address; do not return a suggestion.
                 */
                return false;
            },

            findClosestDomain: function(domain, domains, distanceFunction, threshold) {
                threshold = threshold || this.topLevelThreshold;
                var dist;
                var minDist = 99;
                var closestDomain = null;

                if (!domain || !domains) {
                    return false;
                }
                if (!distanceFunction) {
                    distanceFunction = this.sift3Distance;
                }

                for (var i = 0; i < domains.length; i++) {
                    if (domain === domains[i]) {
                        return domain;
                    }
                    dist = distanceFunction(domain, domains[i]);
                    if (dist < minDist) {
                        minDist = dist;
                        closestDomain = domains[i];
                    }
                }

                if (minDist <= threshold && closestDomain !== null) {
                    return closestDomain;
                } else {
                    return false;
                }
            },

            sift3Distance: function(s1, s2) {
                // sift3: http://siderite.blogspot.com/2007/04/super-fast-and-accurate-string-distance.html
                if (s1 === null || s1.length === 0) {
                    if (s2 === null || s2.length === 0) {
                        return 0;
                    } else {
                        return s2.length;
                    }
                }

                if (s2 === null || s2.length === 0) {
                    return s1.length;
                }

                var c = 0;
                var offset1 = 0;
                var offset2 = 0;
                var lcs = 0;
                var maxOffset = 5;

                while ((c + offset1 < s1.length) && (c + offset2 < s2.length)) {
                    if (s1.charAt(c + offset1) == s2.charAt(c + offset2)) {
                        lcs++;
                    } else {
                        offset1 = 0;
                        offset2 = 0;
                        for (var i = 0; i < maxOffset; i++) {
                            if ((c + i < s1.length) && (s1.charAt(c + i) == s2.charAt(c))) {
                                offset1 = i;
                                break;
                            }
                            if ((c + i < s2.length) && (s1.charAt(c) == s2.charAt(c + i))) {
                                offset2 = i;
                                break;
                            }
                        }
                    }
                    c++;
                }
                return (s1.length + s2.length) / 2 - lcs;
            },

            splitEmail: function(email) {
                var parts = email.trim().split("@");

                if (parts.length < 2) {
                    return false;
                }

                for (var i = 0; i < parts.length; i++) {
                    if (parts[i] === "") {
                        return false;
                    }
                }

                var domain = parts.pop();
                var domainParts = domain.split(".");
                var tld = "";

                if (domainParts.length === 0) {
                    // The address does not have a top-level domain
                    return false;
                } else if (domainParts.length == 1) {
                    // The address has only a top-level domain (valid under RFC)
                    tld = domainParts[0];
                } else {
                    // The address has a domain and a top-level domain
                    for (var i = 1; i < domainParts.length; i++) {
                        tld += domainParts[i] + ".";
                    }
                    if (domainParts.length >= 2) {
                        tld = tld.substring(0, tld.length - 1);
                    }
                }

                return {
                    topLevelDomain: tld,
                    domain: domain,
                    address: parts.join("@")
                };
            },

            // Encode the email address to prevent XSS but leave in valid
            // characters, following this official spec:
            // http://en.wikipedia.org/wiki/Email_address#Syntax
            encodeEmail: function(email) {
                var result = encodeURI(email);
                result = result.replace("%20", " ").replace("%25", "%").replace("%5E", "^")
                    .replace("%60", "`").replace("%7B", "{").replace("%7C", "|")
                    .replace("%7D", "}");
                return result;
            }
        };
    } // End Mailcheck


    return _inbound;

})(_inbound || {});
