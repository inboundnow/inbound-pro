jQuery(document).ready(function ($) {	
	var variations = new Array();
	var has_variations = 0;
	if (variation.variations)
	{	
		variations = variation.variations.split(",");
	}


	var hidden_html = '<input type="hidden" id="better-ab-testing-variation" name="lp-variation-id" value="'+ variation.vid +'">';
	jQuery('.wrap form').prepend(hidden_html);
	
	var replace_slash = '\\'; 

	//variation.content_area = variation.content_area.replace(/\n/g, replace_slash);
	//variation.content_area = variation.content_area.replace(/\r\n/g, "<br/>").replace(/\r/g, "<br/>").replace(/\n/g, "<br/>");
	
	jQuery("#wp-content-editor-container textarea").val(variation.content_area);
	jQuery("#content_ifr").contents().find("body").html(variation.content_area);
		

	var html;
	if (variation.vid>0&&variation.new_variation!=1)
	{
		html = '<a class="add-new-h2" href="?post='+variation.pid+'&action=edit&lp-variation-id='+variation.vid+'&ab-action=delete-variation">Delete This Variation</a>';
		jQuery('.wrap h2:first').append(html); 		
	}
	
	if (variation.vid>0)
	{
		jQuery('#delete-action').remove();
	}	
	
	//alter preview and customizer buttons based on open variation
	var preview_href = jQuery('#post-preview').attr('href');
	jQuery('#post-preview').attr('href',preview_href+'?lp-variation-id='+variation.vid);
	//jQuery('#view-post-btn a:first').attr('href',preview_href+'?lp-variation-id='+variation.vid);
	//jQuery('.new-save-lp-frontend').attr('href',preview_href+'?template-customize=on&lp-variation-id='+variation.vid);
	
	//setup timer and and navigation change events
	var input_change = jQuery("#switch-lp").text();	
	jQuery('.wrap').on('keyup change', jQuery('form').find('input[type=text],textarea,select'), function() {	
		jQuery("#switch-lp").text("1");
		console.log("change");
	});
	
	/*setTimeout(function () {
		input_change = 1;
	}, 15000);
	*/
	jQuery('.wrap').on('click', '.nav-tab-wrapper a', function(e) {	
		var this_id = this.id.replace('tabs-','');
		if (input_change==1)
		{
			var answer = confirm('Do you want to change variations without saving the changes made here?');
			    if (answer){
			        // do the default action
			    } else {
			      e.preventDefault();
			    }
		}
		
		jQuery('.lp-tab-display').css('display','none');
		jQuery('#'+this_id).css('display','block');
		jQuery('.lp-nav-tab').removeClass('nav-tab-special-active');
		jQuery('.lp-nav-tab').addClass('nav-tab-special-inactive');
		jQuery('#tabs-'+this_id).addClass('nav-tab-special-active');						
		jQuery('#id-open-tab').val(this_id);
		
	});

});