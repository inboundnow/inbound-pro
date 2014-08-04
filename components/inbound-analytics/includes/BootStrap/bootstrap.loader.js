
function centerModal() {
	alert('hi');
    jQuery(this).css('display', 'block');
    var dialog = jQuery("#ia-modal-container");
    var offset = (jQuery(window).height() - dialog.height()) / 2;
    // Center modal vertically in window
    dialog.css("margin-top", offset);
    dialog.css("z-index", '9999999');
}

jQuery(document).ready(function () {
	
	/* Load tooltips on labels and links */
	jQuery("a,label").tooltip({
		placement : 'left'
	});
	
	
    jQuery("#ia-modal-container").modal();
 
});

