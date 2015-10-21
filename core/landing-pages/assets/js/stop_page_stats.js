<<<<<<< HEAD
/* For Iframe previews to stop saving page views */

var dont_save_page_view = _inbound.Utils.getParameterVal('dont_save', window.location.href);
if (dont_save_page_view) {
    console.log('turn off page tracking');
    window.inbound_settings.page_tracking = 'off';
=======
/* For Iframe previews to stop saving page views */

var dont_save_page_view = _inbound.Utils.getParameterVal('dont_save', window.location.href);
if (dont_save_page_view) {
    console.log('turn off page tracking');
    window.inbound_settings.page_tracking = 'off';
>>>>>>> 62d6aafcc97216033c9292f85bce6cdfbfd6455c
}