jQuery(document).ready(function ($) {
	jQuery('body').on( 'click' , '.lp_select_template' , function() {
		var this_template = jQuery(this);
		swal({
			title: sweetalert.title,
			text: sweetalert.text,
			type: "info",
			showCancelButton: true,
			confirmButtonColor: "#2ea2cc",
			confirmButtonText: sweetalert.confirmButtonText,
			closeOnConfirm: false
		}, function () {
			swal({
				title: sweetalert.waitTitle,
				text: sweetalert.waitText,
				imageUrl: sweetalert.waitImage
			});

			var template = this_template.attr('id');
			jQuery('#lp_select_template').val(template);

			/* save post */
			jQuery('#publish').click();
		});
	});


	jQuery('#lp-cancel-selection').click(function(){
		jQuery(".lp-template-selector-container").fadeOut(500,function(){
			jQuery(".wrap").fadeIn(500, function(){
			});
		});

	});
});