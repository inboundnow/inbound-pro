# igloos--inuit.css plugins

## What?

An igloo is simply a plugin for the [inuit.css](http://inuitcss.com) framework. 
They extend and add to the the core functionality.

## How?

Include inuit.css in the `<head>` using a `<link />`, 
then include igloos in the same manner after this. E.g.

    <link rel="stylesheet" href="/css/inuit.css" />
    
<link rel="stylesheet" href="/css/annotate.inuit.css" />

### HTTP requests?

I am aware of the issues here with 
several HTTP requests for CSS files, but in order to keep the core framework untouched an updatable we can't 
combine the CSS into one large document.
