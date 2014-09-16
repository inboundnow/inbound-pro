jQuery(document).ready(function () {

	jQuery(document.body).on('click', '.run-automation' , function(){
		jQuery('.run-automation').text('Reloading please wait...');
		jQuery('.run-automation').removeClass('.run-automation');
		jQuery('.run-automation').css('cursor','wait');
		/* jQuery('.rules-processing').css('display','inline'); */

		jQuery.ajax({
			type: 'POST',
			url: automation_rule.admin_url,
			data: {
				action: 'automation_run_automation_on_all_leads',
				automation_id: automation_rule.automation_id
			},
			success: function(data){
					//alert('Reload page to estimate progress.');
					location.reload();
				   },
			error: function(MLHttpRequest, textStatus, errorThrown){
					//alert(MLHttpRequest+' '+errorThrown+' '+textStatus);
				}

		});
	});

	jQuery(document.body).on('click', '.automation-delete-condition-button' , function(){
		var this_id = this.id.replace('automation-delete-condition-button-','');
		jQuery('#tabs-automation_condition_' + this_id ).remove();
		jQuery('#automation_main_container_conditions_' + this_id ).remove();
		jQuery('#automation_container_hidden_input_'+this_id).remove();

		var switch_id = jQuery('#ma-st-tabs-0 a:first').attr('rel');
		jQuery('#tabs-automation_condition_' + switch_id ).click();

	});


	jQuery('#automation-delete-condition-button-0').show();
	jQuery('#ma-a-add-new-automation-condition').live('click', function() {
		//add new tab
		var tab_html = jQuery('#tabs-automation_condition_0').clone().wrap('<div></div>').parent().html();
		var tab_new_count =  jQuery('.nav-tab-wrapper-conditions').find('.ma-nav-tab').size();
		var tab_new_label_id =  tab_new_count + 1;

		//get id of last item in nav contatiner
		var last_rel_id = jQuery('.nav-tab-wrapper-conditions a:last').attr('rel');
		var new_rel_id = parseInt(last_rel_id) + 1;
		//alert(new_rel_id);



		var new_html = tab_html.replace('_0', '_'+new_rel_id);
		new_html = new_html.replace('-0', '-'+new_rel_id);
		new_html = new_html.replace('rel="0"', 'rel="'+new_rel_id+'"');
		new_html = new_html.replace('Condition 1', 'Condition '+tab_new_label_id);
		jQuery('.nav-tab-wrapper-conditions').append(new_html);


		//toggle new tab visible and hide other conditions
		jQuery('.automation-tab-display').css('display','none');
		jQuery('.ma-nav-tab').removeClass('nav-tab-special-active');
		jQuery('#tabs-automation_condition_'+new_rel_id).addClass('nav-tab-special-active');

		//add new condition content
		var condition_html = jQuery('#automation_main_container_conditions_0').clone().wrap('<div></div>').parent().html();
		var condition_new_html = condition_html.replace(/_0/g , "_"+new_rel_id);
		condition_new_html = condition_new_html.replace(/-0/g, "-"+new_rel_id);
		condition_new_html = condition_new_html.replace(/rel="0"/g, 'rel="'+new_rel_id+'"');
		condition_new_html = "<input type='hidden' name='automation_condition_blocks[]' id='id-open-tab' value='"+new_rel_id+"'>"+condition_new_html;
		jQuery('#automation_conditions_container').append(condition_new_html);

		//toggle the new condition visible
		jQuery('#automation_main_container_conditions_'+new_rel_id).css('display','block');
	});

	//tabb through conditions
	jQuery(document).on('click','.ma-nav-tab' , function() {

		var this_id = this.id.replace('tabs-automation_condition_','');

		jQuery('.automation-tab-display').css('display','none');
		jQuery('.automation-delete-condition-button').css('display','none');
		jQuery('#automation_main_container_conditions_'+this_id).css('display','block');
		jQuery('#automation-delete-condition-button-'+this_id).show();
		jQuery('.ma-nav-tab').removeClass('nav-tab-special-active');
		jQuery('#tabs-automation_condition_'+this_id).addClass('nav-tab-special-active');
	});

	//remove condition
	jQuery(document).on('click','.ma-remove-condition' , function() {

		var this_id = this.id.replace('rules-a-remove-automation-condition-','');
		jQuery('#automation_main_container_conditions_'+this_id).remove();
		jQuery('#automation_main_container_conditions_0').css('display','block');
		jQuery('#tabs-automation_condition_'+this_id).remove();
		jQuery('#automation_container_hidden_input_'+this_id).remove();
		jQuery('#tabs-automation_condition_0').addClass('nav-tab-special-active');
	});

	jQuery(document).on('change', '.automation_if', function() {
		var this_id = jQuery(this).val();
		var this_rel = jQuery(this).attr('rel');

		//alert(this_id.indexOf("list_specific"));
		if (this_id.indexOf("category_specific") >= 0)
		{
			jQuery('#tr_automation_condition_category'+'_'+this_rel).removeClass('automation-hidden-steps');
		}
		else
		{
			jQuery('#tr_automation_condition_category'+'_'+this_rel).addClass('automation-hidden-steps');
		}

		if (this_id.indexOf("page_views_") >= 0)
		{
			jQuery('#tr_automation_condition_number'+'_'+this_rel).removeClass('automation-hidden-steps');
		}
		else if (this_id.indexOf("page_conversions_") >= 0)
		{
			jQuery('#tr_automation_condition_number'+'_'+this_rel).removeClass('automation-hidden-steps');
		}
		else if (this_id.indexOf("sesions_recorded_") >= 0)
		{
			jQuery('#tr_automation_condition_number'+'_'+this_rel).removeClass('automation-hidden-steps');
		}

	});


	//set default css if rule is pre-defined
	jQuery('.automation_if').each(function(index,value){
		var selectedIF = jQuery(this).find(":selected").val();
		var this_rel = jQuery(this).attr('rel');
		//alert(selectedIF);
		if (selectedIF.indexOf("category_specific") >= 0)
		{
			jQuery('#tr_automation_condition_category'+'_'+this_rel).removeClass('automation-hidden-steps');
		}
		else
		{
			jQuery('#tr_automation_condition_category'+'_'+this_rel).addClass('automation-hidden-steps');
		}
	});
});