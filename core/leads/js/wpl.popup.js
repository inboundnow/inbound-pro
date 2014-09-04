// Loads for lightbox popup screen
jQuery(document).ready(function($) {
	var link = window.location.href.replace('&small_lead_preview=true&', '');
	var link = link.replace('&small_lead_preview=true', '');
	var full_link = "<a id='view-lead-in-new-window' target='_blank' href='" + link +"'>View Lead in new window</a>";
   $("#poststuff").before(full_link);
 });
