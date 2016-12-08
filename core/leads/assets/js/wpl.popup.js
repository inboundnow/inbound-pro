jQuery(document).ready(function($) {
	var link = window.location.href.replace('&small_lead_preview=true&', '');
	var link = link.replace('&small_lead_preview=true', '');
	var full_link = jQuery('<a>')
							.attr('id', 'view-lead-in-new-window')
							.attr('target','_blank')
							.attr('href',link)
							.html(' Popout <i class="fa fa-external-link" aria-hidden="true"></i>');
    full_link.appendTo("#post-body-content");
 });
