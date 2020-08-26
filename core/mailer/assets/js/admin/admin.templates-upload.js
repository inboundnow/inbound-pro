jQuery(document).ready(function($) {
		
	jQuery('body').on('click', '.subsubsub li a' ,  function () {
		
        var id = jQuery(this).attr('id');

        if (id == 'menu_upload') {
            jQuery('.templates_search').hide();
            jQuery('.templates_search').removeClass('current');
			
            jQuery('.templates_upload').show();
			jQuery('.templates_upload').addClass('current');
        } 
		else if (id == 'menu_search')
		{
			jQuery('.templates_upload').hide();
			jQuery('.templates_upload').removeClass('current');
			
            jQuery('.templates_search').show();
			jQuery('.templates_search').addClass('current');
        }

    });
				
});