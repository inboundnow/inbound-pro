/*
URL param action
 */
// Add to page
_inbound.add_action('url_parameters', URL_Param_Function, 10);
// callback function
function URL_Param_Function(urlParams) {

    //urlParams = _inbound.apply_filters( 'urlParamFilter', urlParams);

    for (var param in urlParams) {
        var key = param;
        var value = urlParams[param];
    }

    //alert(JSON.stringify(urlParams));

    /* Check if URL parameter exists and matches value */
    if (urlParams.test === "true") {
        alert('url param true is true');
    }
}

/* Applying filters to your actions */
_inbound.add_filter('filter_url_parameters', URL_Param_Filter, 10);

function URL_Param_Filter(urlParams) {

    var params = urlParams || {};
    /* check for item in object */
    if (params.utm_source !== "undefined") {
        //alert('its here');
    }
    /* delete item from object */
    delete params.utm_source;

    return params;

}

/* Applying filters to your actions */
_inbound.add_filter('filter_inbound_analytics_loaded', event_filter_data_example, 10);

function event_filter_data_example(data) {

    var data = data || {};

    /* Add property to data */
    data.add_this = 'additional data';

    /* check for item in object */
    if (data.opt1 === true) {
        alert('options.opt1 = true');
    }

    /* Add or modifiy option to event */
    data.new_options = 'new option';

    /* delete item from data */
    delete data.utm_source;

    return data;

}

_inbound.add_action('tab_hidden', Tab_Hidden_Function, 10);

function Tab_Hidden_Function(data) {
    //alert('NOPE! LOOK AT ME!!!!');
}

_inbound.add_action('tab_visible', tab_visible_function, 9);

function tab_visible_function(data) {
    //alert('Welcome back to the tab');
}

_inbound.add_action('tab_mouseout', tab_mouseout_function, 10);

function tab_mouseout_function(data) {
    //alert('You moused out of the tab');
    document.body.style.background = 'red';
}

_inbound.add_action('page_first_visit', Tab_vis_Function, 10);

function Tab_vis_Function(data) {
    //alert('Welcome back bro 2');
}

_inbound.add_action('page_revisit', page_revisit_Function, 10);

function page_revisit_Function(data) {
    console.log('Welcome page_revisit');
}

window.addEventListener("page_revisit", page_seen_function, false);

function page_seen_function(e) {
    var view_count = e.detail.count;
    console.log("This page has been seen " + e.detail.count + " times");
    if (view_count > 10) {
        console.log("Page has been viewed more than 10 times");
    }
}

_inbound.add_action('session_start', session_start_func, 10);

function session_start_func(data) {
    //alert('Session starting Now');
}

_inbound.add_action('session_resume', session_resume_func, 10);

function session_resume_func(data) {
    //alert('Session Resume');
}



_inbound.add_action('session_init', session_end_func, 10);

function session_end_func(data) {
    //alert('Session session_end');
}


_inbound.add_action('session_end', session_end_func, 10);

function session_end_func(data) {
    //alert('Session session_end');
}

_inbound.add_action('analytics_ready', analytics_ready_func, 10);

function analytics_ready_func(data) {
    //alert('analytics_ready');
}

_inbound.add_action('form_input_change', form_input_change_func, 10);

function form_input_change_func(inputData) {
    var inputData = inputData || {};
    console.log(inputData); // View input data object
    console.log(inputData.node + '[name="' + inputData.name + '"]');
    /*jQuery(inputData.node + '[name="'+inputData.name+'"]')
	.animate({
	    opacity: 0.50,
	    left: "+=50",
	  }, 1000, function() {
	    jQuery(this).css('color', 'green');
	});*/
}

_inbound.add_action('form_after_submission', form_after_submission_func, 10);

function form_after_submission_func(data) {
    console.log('do this');
    // alert(JSON.stringify(data));
}

/* Jquery Examples */

_inbound.add_action('form_before_submission', alert_form_data, 10);

function alert_form_data(data) {
    console.log(JSON.stringify(data));
}
//_inbound.remove_action( 'inbound_form_form_before_submission');
/* raw_js_trigger event trigger */
window.addEventListener("form_before_submission", raw_js_trigger, false);

function raw_js_trigger(e) {
    var data = e.detail;
    console.log('Pure Javascript form_before_submission action fire');
    //alert(JSON.stringify(data.raw_params));
}

if (window.jQuery) {
    jQuery(document).on('form_before_submission', function(event, data) {

        console.log('Run jQuery form_before_submission trigger');

    });
}