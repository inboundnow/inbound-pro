

<!-- Start shared/assets/js/frontend/analytics-src/analytics.events.js -->

# Analytics Events

Events are triggered throughout the visitors journey through the site. See more on [Inbound Now][in]

Author: David Wells <david@inboundnow.com>

Version: 0.0.1 
[in]: http://www.inboundnow.com/

# Event Usage

Adding Custom Actions
------------------
You can hook into custom events throughout analytics. See the full list of available [events below](#all-events)

`
_inbound.add_action( 'action_name', callback, priority );
`

```js
// example:

_inbound.add_action( 'page_visit', callback, 10 );

// add custom callback
function callback(data){
  // run callback on 'page_visit' trigger
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

## analytics_loaded()

Triggers when the browser url params are parsed. You can perform custom actions
if specific url params exist.

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

// Add session_start_func_example function to 'session_start' event
_inbound.add_action( 'session_start', session_start_func_example, 10);

function session_start_func_example(data) {
    var data = data || {};
    // session active
}
```

## session_active()

Triggers when session is already active

```js
// Usage:

// Add session_heartbeat_func_example function to 'session_heartbeat' event
_inbound.add_action( 'session_heartbeat', session_heartbeat_func_example, 10);

function session_heartbeat_func_example(data) {
    var data = data || {};
    // Do something with every 10 seconds
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

Page Visit Events

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

## before_form_submission()

`before_form_submission` is triggered before the form is submitted to the server.
 You can filter the data here or send it to third party services

```js
// Usage:

// Adding the callback
function before_form_submission_function( data ) {
     var data = data || {};
     // filter form data
};

 // Hook the function up the the `before_form_submission` event
 _inbound.add_action( 'before_form_submission', before_form_submission_function, 10 );
```

<!-- End shared/assets/js/frontend/analytics-src/analytics.events.js -->




<!-- Start shared/assets/js/frontend/analytics-src/analytics.examples.js -->

URL param action

Check if URL parameter exists and matches value

Applying filters to your actions

check for item in object

delete item from object

Applying filters to your actions

## add_this

Add property to data

check for item in object

## new_options

Add or modifiy option to event

delete item from data

<!-- End shared/assets/js/frontend/analytics-src/analytics.examples.js -->




<!-- Start shared/assets/js/frontend/analytics-src/analytics.forms.js -->

# Inbound Forms

This file contains all of the form functions of the main _inbound object.
Filters and actions are described below

Author: David Wells <david@inboundnow.com>

Version: 0.0.1

## InboundForms

Launches form class

Adding values here maps them

## runFieldMappingFilters()

This triggers the forms.field_map filter on the mapping array.
This will allow you to add or remore Items from the mapping lookup

### Example inbound.form_map_before filter

This is an example of how form mapping can be filtered and
additional fields can be mapped via javascript

```js
 // Adding the filter function
 function Inbound_Add_Filter_Example( FieldMapArray ) {
   var map = FieldMapArray || [];
   map.push('new lookup value');

   return map;
 };

 // Adding the filter on dom ready
 _inbound.hooks.addFilter( 'inbound.form_map_before', Inbound_Add_Filter_Example, 10 );
```

### Return:

* **[type]** [description]

attach form listener

## loopClassSelectors()

Loop through include/exclude items for tracking

## initFormMapping()

Map field fields on load

Map form fields

Remember visible inputs

Fill visible inputs

## formListener()

prevent default submission temporarily

## attachFormSubmitEvent()

attach form listeners

## Timeout

fallback if submit name="submit"

if (formInput.id) { inputsObject[inputName]['id'] = formInput.id; }
                  if ('classList' in document.documentElement)  {
                      if (formInput.classList) { inputsObject[inputName]['class'] = formInput.classList; }
                  }

inputsObject[inputName].push(multiple ? values.join(',') : encodeURIComponent(value));

Add custom hook here to look for additional values

## email

Check Use form Email or Cookie

Get Variation ID

Filter here for raw

Old data model
              var return_data = {
                        "action": 'inbound_store_lead',
                        "emailTo": data['email'],
                        "first_name": data['first_name'],
                        "last_name": data['last_name'],
                        "phone": data['phone'],
                        "address": data['address'],
                        "company_name": data['company'],
                        "page_views": data['page_views'],
                        "form_input_values": all_form_fields,
                        "Mapped_Data": mapped_form_data,
                        "Search_Data": data['search_data']
              };

Action Example

Set Lead cookie ID

Resume normal form functionality

Check for input type

Set Field Input Cookies

Push to 'unsubmitted form object'

## mapField()

Maps data attributes to fields on page load

Loop through all match possiblities

look for name attribute match

look for id match

Check siblings for label

Check closest li for label

Map the field

## getInputValue()

Get correct input values

## addDataAttr()

Add data-map-form-field attr to input

## removeArrayItem()

Optimize FieldMapArray array for fewer lookups

## siblingsIsLabel()

Look for siblings that are form labels

if only 1 label

## CheckParentForLabel()

Check parent elements inside form for labels

<!-- End shared/assets/js/frontend/analytics-src/analytics.forms.js -->




<!-- Start shared/assets/js/frontend/analytics-src/analytics.hooks.js -->

## _inboundHooks

# Hooks & Filters

This file contains all of the form functions of the main _inbound object.
Filters and actions are described below

Author: David Wells <david@inboundnow.com>

Version: 0.0.1

# EventManager

Actions and filters List
addAction( 'namespace.identifier', callback, priority )
addFilter( 'namespace.identifier', callback, priority )
removeAction( 'namespace.identifier' )
removeFilter( 'namespace.identifier' )
doAction( 'namespace.identifier', arg1, arg2, moreArgs, finalArg )
applyFilters( 'namespace.identifier', content )

### Return:

* **[type]** [description]

## EventManager()

Handles managing all events for whatever you plug it into. Priorities for hooks are based on lowest to highest in
that, lowest priority hooks are fired first.

## MethodsAvailable

Maintain a reference to the object scope so our public methods never get confusing.

## STORAGE

Contains the hooks that get registered with this EventManager. The array for storage utilizes a "flat"
object literal such that looking up the hook utilizes the native object literal hash.

## addAction(Must, Must, Used, Supply)

Adds an action to the event manager.

### Params:

* **action** *Must* contain namespace.identifier
* **callback** *Must* be a valid callback function before this action is added
* **[priority=10]** *Used* to control when the function is executed in relation to other callbacks bound to the same hook
* **[context]** *Supply* a value to be used for this

## doAction()

Performs an action if it exists. You can pass as many arguments as you want to this function; the only rule is
that the first argument must always be the action.

action, arg1, arg2, ...

## removeAction(The, Callback)

Removes the specified action if it contains a namespace.identifier & exists.

### Params:

* **action** *The* action to remove
* **[callback]** *Callback* function to remove

## addFilter(Must, Must, Used, Supply)

Adds a filter to the event manager.

### Params:

* **filter** *Must* contain namespace.identifier
* **callback** *Must* be a valid callback function before this action is added
* **[priority=10]** *Used* to control when the function is executed in relation to other callbacks bound to the same hook
* **[context]** *Supply* a value to be used for this

## applyFilters()

Performs a filter if it exists. You should only ever pass 1 argument to be filtered. The only rule is that
the first argument must always be the filter.

filter, filtered arg, arg2, ...

## removeFilter(The, Callback)

Removes the specified filter if it contains a namespace.identifier & exists.

### Params:

* **filter** *The* action to remove
* **[callback]** *Callback* function to remove

## _removeHook(Type, The)

Removes the specified hook by resetting the value of it.

### Params:

* **type** *Type* of hook, either 'actions' or 'filters'
* **hook** *The* hook (namespace.identifier) to remove

## _addHook('actions', The, The, The, A)

Adds the hook to the appropriate storage container

### Params:

* **type** *'actions'* or 'filters'
* **hook** *The* hook (namespace.identifier) to add to our event manager
* **callback** *The* function that will be called when the hook is executed.
* **priority** *The* priority of this hook. Must be an integer.
* **[context]** *A* value to be used for this

## _hookInsertSort(The)

Use an insert sort for keeping our hooks organized based on priority. This function is ridiculously faster
than bubble sort, etc: http://jsperf.com/javascript-sort

### Params:

* **hooks** *The* custom array containing all of the appropriate hooks to perform an insert sort on.

## _runHook('actions', The, Arguments)

Runs the specified hook. If it is an action, the value is not modified but if it is a filter, it is.

### Params:

* **type** *'actions'* or 'filters'
* **hook** *The* hook ( namespace.identifier ) to be ran.
* **args** *Arguments* to pass to the action/filter. If it's a filter, args is actually a single parameter.

Event Hooks and Filters public methods

## add_action()

add_action

 This function uses _inbound.hooks to mimics WP add_action

 ```js
  function Inbound_Add_Action_Example(data) {
      // Do stuff here.
  };
  // Add action to the hook
  _inbound.add_action( 'name_of_action', Inbound_Add_Action_Example, 10 );
  ```

## remove_action()

remove_action

 This function uses _inbound.hooks to mimics WP remove_action

 ```js
  // Add remove action 'name_of_action'
  _inbound.remove_action( 'name_of_action');
 ```

## do_action()

do_action

 This function uses _inbound.hooks to mimics WP do_action
 This is used if you want to allow for third party JS plugins to act on your functions

## add_filter()

add_filter

 This function uses _inbound.hooks to mimics WP add_filter

 ```js
  _inbound.add_filter( 'urlParamFilter', URL_Param_Filter, 10 );
  function URL_Param_Filter(urlParams) {

  var params = urlParams || {};
  // check for item in object
  if(params.utm_source !== "undefined"){
    //alert('url param "utm_source" is here');
  }

  // delete item from object
  delete params.utm_source;

  return params;

  }
  ```

## remove_filter()

remove_filter

 This function uses _inbound.hooks to mimics WP remove_filter

  ```js
  // Add remove filter 'urlParamFilter'
  _inbound.remove_action( 'urlParamFilter');
  ```

## apply_filters()

apply_filters

 This function uses _inbound.hooks to mimics WP apply_filters

<!-- End shared/assets/js/frontend/analytics-src/analytics.hooks.js -->




<!-- Start shared/assets/js/frontend/analytics-src/analytics.init.js -->

## inbound_data

# _inbound

This main the _inbound class

Author: David Wells <david@inboundnow.com>

Version: 0.0.1

## _gaq

Ensure global _gaq Google Analytics queue has been initialized.

## url

load dummy data for testing

## defaults

Constants

## init()

Initialize individual modules

run form mapping

set URL params

## Timeout

run form mapping for dynamically generated forms

## extend(defaults, options)

Merge script defaults with user options

### Params:

* **Object** *defaults* Default settings
* **Object** *options* User options

### Return:

* **Object** Merged values of defaults and options

## debug()

Debugger Function toggled by var debugMode

## Settings

Set globals

<!-- End shared/assets/js/frontend/analytics-src/analytics.init.js -->




<!-- Start shared/assets/js/frontend/analytics-src/analytics.lead.js -->

## _inboundLeadsAPI

Leads API functions

### Params:

* **Object** *_inbound* - Main JS object

### Return:

* **Object** - include event triggers

## d

Set 3 day timeout for checking DB for new lead data for Lead_Global var

<!-- End shared/assets/js/frontend/analytics-src/analytics.lead.js -->




<!-- Start shared/assets/js/frontend/analytics-src/analytics.page.js -->

# Page View Tracking

Page view tracking

Author: David Wells <david@inboundnow.com>

Version: 0.0.1

## _inboundPageTracking

Launches view tracking

Start Session on page load

## startSession()

This start only runs once

Todo session start here

## pingSession()

Ping Session to keep active

## getPageViews()

Returns the pages viewed by the site visitor

```js
 var pageViews = _inbound.PageTracking.getPageViews();
 // returns page view object
```

### Return:

* **object** page view object with page ID as key and timestamp

Page Revisit Trigger

Page First Seen Trigger

Default

<!-- End shared/assets/js/frontend/analytics-src/analytics.page.js -->




<!-- Start shared/assets/js/frontend/analytics-src/analytics.start.js -->

# Start

Runs init functions and runs the domReady functions

Author: David Wells <david@inboundnow.com>

Version: 0.0.1

raw_js_trigger event trigger

Filter Example

On Load Analytics Events

Action Example

Get InboundLeadData

Lead list check

Set Session Timeout

<!-- End shared/assets/js/frontend/analytics-src/analytics.start.js -->




<!-- Start shared/assets/js/frontend/analytics-src/analytics.storage.js -->

## InboundTotalStorage

LocalStorage Component

## totalStorage()

Make the methods public

<!-- End shared/assets/js/frontend/analytics-src/analytics.storage.js -->




<!-- Start shared/assets/js/frontend/analytics-src/analytics.utils.js -->

## _inboundUtils

# _inbound UTILS

This file contains all of the utility functions used by analytics

Author: David Wells <david@inboundnow.com>

Version: 0.0.1

## polyFills()

Polyfills for missing browser functionality

Console.log fix for old browsers

Event trigger polyfill for IE9 and 10
            (function() {
                function CustomEvent(event, params) {
                    params = params || {
                        bubbles: false,
                        cancelable: false,
                        detail: undefined
                    };
                    var evt = document.createEvent('CustomEvent');
                    evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
                    return evt;
                }

                CustomEvent.prototype = window.Event.prototype;

                window.CustomEvent = CustomEvent;
            })();

custom event for ie8+ https://gist.github.com/WebReflection/6693661

querySelectorAll polyfill for ie7+

Innertext shim for firefox https://github.com/duckinator/innerText-polyfill/blob/master/innertext.js

## createCookie(name, value, days)

Create cookie

```js
// Creates cookie for 10 days
_inbound.utils.createCookie( 'cookie_name', 'value', 10 );
```

### Params:

* **string** *name* Name of cookie
* **string** *value* Value of cookie
* **string** *days* Length of storage

## readCookie(name)

Read cookie value

```js
var cookie = _inbound.utils.readCookie( 'cookie_name' );
console.log(cookie); // cookie value
```

### Params:

* **string** *name* name of cookie

### Return:

* **string** value of cookie

## eraseCookie(name)

Erase cookie

```js
// usage:
_inbound.utils.eraseCookie( 'cookie_name' );
// deletes 'cookie_name' value
```

### Params:

* **string** *name* name of cookie

### Return:

* **string** value of cookie

## getAllCookies()

Get All Cookies

## setUrlParams()

Grab URL params and save

Set Param Cookies

Set Param LocalStorage

## getParameterVal()

Get url param

http://spin.atomicobject.com/2013/01/23/ios-private-browsing-localstorage/
            var hasStorage;
            hasStorage = function() {
              var mod, result;
              try {
                mod = new Date;
                localStorage.setItem(mod, mod.toString());
                result = localStorage.getItem(mod) === mod.toString();
                localStorage.removeItem(mod);
                return result;
              } catch (_error) {}
            };

## addDays()

Add days to datetime

## SetSessionTimeout()

Set Expiration Date of Session Logging

Set Lead UID

## countProperties()

Count number of session visits

IE Polyfill

## addListener()

Cross-browser event listening

## throttle()

Throttle function borrowed from:
Underscore.js 1.5.2
http://underscorejs.org
(c) 2009-2013 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
Underscore may be freely distributed under the MIT license.

## checkTypeofGA()

Determine which version of GA is being used
"ga", "_gaq", and "dataLayer" are the possible globals

<!-- End shared/assets/js/frontend/analytics-src/analytics.utils.js -->

