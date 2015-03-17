jQuery(document).ready(function($) {

	/* Add listener to detect selected unsubscribe list */
	jQuery('.lead-list-label input').change( function() {
		 if(this.checked) {
			jQuery('.unsubsribe-comments').show();
		}	
	});

});