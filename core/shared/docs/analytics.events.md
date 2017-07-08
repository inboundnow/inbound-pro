

<!-- Start core\shared\assets\js\frontend\analytics-src\analytics.events.js -->

# Analytics Events

Events are triggered throughout the visitors journey through the site. See more on [Inbound Now][in]

Author: David Wells <david@inboundnow.com>

Version: 0.0.2 
[in]: http://www.inboundnow.com/

# Event Usage

Events are triggered throughout the visitors path through the site.
You can hook into these custom actions and filters much like WordPress Core

See below for examples

Adding Custom Actions
------------------
You can hook into custom events throughout analytics. See the full list of available [events below](#all-events)

`
_inbound.add_action( 'action_name', callback, priority );
`

```js
// example:

// Add custom function to `page_visit` event
_inbound.add_action( 'page_visit', callback, 10 );

// add custom callback to trigger when `page_visit` fires
function callback(pageData){
  var pageData =  pageData || {};
  // run callback on 'page_visit' trigger
  alert(pageData.title);
}
```

### Params:

* **string** *action_name* Name of the event trigger
* **function** *callback* function to trigger when event happens
* **int** *priority* Order to trigger the event in

Removing Custom Actions
------------------
You can hook into custom events throughout analytics. See the full list of available [events below](#all-events)

`
_inbound.remove_action( 'action_name');
`

```js
// example:

_inbound.remove_action( 'page_visit');
// all 'page_visit' actions have been deregistered
```

### Params:

* **string** *action_name* Name of the event trigger

# Event List

Events are triggered throughout the visitors journey through the site

## analytics_ready()

Triggers when analyics has finished loading

## url_parameters()

Triggers when the browser url params are parsed. You can perform custom actions
 if specific url params exist.

```js
// Usage:

// Add function to 'url_parameters' event
_inbound.add_action( 'url_parameters', url_parameters_func_example, 10);

function url_parameters_func_example(urlParams) {
    var urlParams = urlParams || {};
     for( var param in urlParams ) {
     var key = param;
     var value = urlParams[param];
     }
     // All URL Params
     alert(JSON.stringify(urlParams));

     // Check if URL parameter `utm_source` exists and matches value
     if(urlParams.utm_source === "twitter") {
       alert('This person is from twitter!');
     }
}
```

## session_start()

Triggers when session starts

```js
// Usage:

// Add function to 'session_start' event
_inbound.add_action( 'session_start', session_start_func_example, 10);

function session_start_func_example(data) {
    var data = data || {};
    // session start. Do something for new visitor
}
```

## session_end()

Triggers when visitor session goes idle for more than 30 minutes.

```js
// Usage:

// Add function to 'session_end' event
_inbound.add_action( 'session_end', session_end_func_example, 10);

function session_end_func_example(data) {
    var data = data || {};
    // Do something when session ends
    alert("Hey! It's been 30 minutes... where did you go?");
}
```

## session_active()

Triggers if active session is detected

```js
// Usage:

// Add function to 'session_active' event
_inbound.add_action( 'session_active', session_active_func_example, 10);

function session_active_func_example(data) {
    var data = data || {};
    // session active
}
```

## session_idle()

Triggers when visitor session goes idle. Idling occurs after 60 seconds of
inactivity or when the visitor switches browser tabs

```js
// Usage:

// Add function to 'session_idle' event
_inbound.add_action( 'session_idle', session_idle_func_example, 10);

function session_idle_func_example(data) {
    var data = data || {};
    // Do something when session idles
    alert('Here is a special offer for you!');
}
```

## session_resume()

Triggers when session is already active and gets resumed

```js
// Usage:

// Add function to 'session_resume' event
_inbound.add_action( 'session_resume', session_resume_func_example, 10);

function session_resume_func_example(data) {
    var data = data || {};
    // Session exists and is being resumed
}
```

## session_heartbeat()

Session emitter. Runs every 10 seconds. This is a useful function for
 pinging third party services

```js
// Usage:

// Add session_heartbeat_func_example function to 'session_heartbeat' event
_inbound.add_action( 'session_heartbeat', session_heartbeat_func_example, 10);

function session_heartbeat_func_example(data) {
    var data = data || {};
    // Do something with every 10 seconds
}
```

## page_visit()

Triggers Every Page View

```js
// Usage:

// Add function to 'page_visit' event
_inbound.add_action( 'page_visit', page_visit_func_example, 10);

function session_idle_func_example(pageData) {
    var pageData = pageData || {};
    if( pageData.view_count > 8 ){
      alert('Wow you have been to this page more than 8 times.');
    }
}
```

## page_first_visit()

Triggers If the visitor has never seen the page before

```js
// Usage:

// Add function to 'page_first_visit' event
_inbound.add_action( 'page_first_visit', page_first_visit_func_example, 10);

function page_first_visit_func_example(pageData) {
    var pageData = pageData || {};
    alert('Welcome to this page! Its the first time you have seen it')
}
```

## page_revisit()

Triggers If the visitor has seen the page before

```js
// Usage:

// Add function to 'page_revisit' event
_inbound.add_action( 'page_revisit', page_revisit_func_example, 10);

function page_revisit_func_example(pageData) {
    var pageData = pageData || {};
    alert('Welcome back to this page!');
    // Show visitor special content/offer
}
```

## tab_hidden()

`tab_hidden` is triggered when the visitor switches browser tabs

```js
// Usage:

// Adding the callback
function tab_hidden_function( data ) {
     alert('The Tab is Hidden');
};

 // Hook the function up the the `tab_hidden` event
 _inbound.add_action( 'tab_hidden', tab_hidden_function, 10 );
```

## tab_visible()

`tab_visible` is triggered when the visitor switches back to the sites tab

```js
// Usage:

// Adding the callback
function tab_visible_function( data ) {
     alert('Welcome back to this tab!');
     // trigger popup or offer special discount etc.
};

 // Hook the function up the the `tab_visible` event
 _inbound.add_action( 'tab_visible', tab_visible_function, 10 );
```

## tab_mouseout()

`tab_mouseout` is triggered when the visitor mouses out of the browser window.
 This is especially useful for exit popups

```js
// Usage:

// Adding the callback
function tab_mouseout_function( data ) {
     alert("Wait don't Go");
     // trigger popup or offer special discount etc.
};

 // Hook the function up the the `tab_mouseout` event
 _inbound.add_action( 'tab_mouseout', tab_mouseout_function, 10 );
```

## form_input_change()

`form_input_change` is triggered when tracked form inputs change
 You can use this to add additional validation or set conditional triggers

```js
// Usage:

```

## form_before_submission()

`form_before_submission` is triggered before the form is submitted to the server.
 You can filter the data here or send it to third party services

```js
// Usage:

// Adding the callback
function form_before_submission_function( data ) {
     var data = data || {};
     // filter form data
};

 // Hook the function up the the `form_before_submission` event
 _inbound.add_action( 'form_before_submission', form_before_submission_function, 10 );
```

## form_after_submission()

`form_after_submission` is triggered after the form is submitted to the server.
 You can filter the data here or send it to third party services

```js
// Usage:

// Adding the callback
function form_after_submission_function( data ) {
     var data = data || {};
     // filter form data
};

 // Hook the function up the the `form_after_submission` event
 _inbound.add_action( 'form_after_submission', form_after_submission_function, 10 );
```

## search_before_caching()

`search_before_caching` is triggered before the search is stored in the user's browser.
 If a lead ID is set, the search data will be saved to the server when the next page loads.
 You can filter the data here or send it to third party services

```js
// Usage:

// Adding the callback
function search_before_caching_function( data ) {
     var data = data || {};
     // filter search data
};

 // Hook the function up the the `search_before_caching` event
 _inbound.add_action( 'search_before_caching', search_before_caching_function, 10 );
```

button == the button that was clicked, form == the form that button belongs to, formRedirectUrl == the link that the form redirects to, if set

Get the button...

## if()

If not an iframe

If it is an iframe

## if()

If the redirect link is not set, or there is a single space in it, the form isn't supposed to redirect. So set the action for void

## if()

If not an iframe

If it is an iframe

<!-- End core\shared\assets\js\frontend\analytics-src\analytics.events.js -->

