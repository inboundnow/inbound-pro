
function centerModal() {
    jQuery(this).css('display', 'block');
    var $dialog = $(this).find(".modal-dialog");
    var offset = ($(window).height() - $dialog.height()) / 2;
    // Center modal vertically in window
    $dialog.css("margin-top", offset);
}

jQuery(document).ready(function () {
	
	/* Load tooltips on labels and links */
	jQuery("a,label").tooltip({
		placement : 'left'
	});
	

	jQuery('.modal').on('show.bs.modal', centerModal);
	
	jQuery(window).on("resize", function () {
		jQuery('.modal:visible').each(centerModal);
	});
});

