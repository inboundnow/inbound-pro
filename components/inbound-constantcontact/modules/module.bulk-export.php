<?php

//add options to bulk edit
add_action('admin_footer-edit.php', 'inboundnow_constantcontact_bulk_actions_add_options');
function inboundnow_constantcontact_bulk_actions_add_options() {


  if(isset($_GET['post_type']) && $_GET['post_type'] == 'wp-lead') {

	$lists = inboundnow_constantcontact_get_lists();

	$html = "<select id='constantcontact_list_select' name='action_constantcontact_list_id'>";
	foreach ($lists as $key=>$value)
	{
		$html .= "<option value='".$key."'>".$value."</option>";
	}
	$html .="</select>";
	?>
	<script type="text/javascript">
	  jQuery(document).ready(function() {
		jQuery('<option>').val('export-constantcontact').text('<?php _e('Export to ConstantContact List')?>').appendTo("select[name='action']");
		jQuery('<option>').val('export-constantcontact').text('<?php _e('Export to ConstantContact List')?>').appendTo("select[name='action2']");
		
		jQuery(document).on('change','select[name=action]', function() {
			var this_id = jQuery(this).val();
			//alert(this_id);
			if (this_id.indexOf("export-constantcontact") >= 0)
			{
				var html  = "<?php echo $html; ?>";
				
				jQuery("select[name='action']").after(html);
			}
			else
			{
				jQuery('#constantcontact_list_select').remove();
			}
		});
	  });
	</script>
	<?php
  }
}

add_action('load-edit.php', 'wpleads_bulk_action_constantcontact');
function wpleads_bulk_action_constantcontact() {
	
	if (isset($_REQUEST['post_type'])&&$_REQUEST['post_type']=='wp-lead'&&isset($_REQUEST['post']))
	{
		// 1. get the action
		$wp_list_table = _get_list_table('WP_Posts_List_Table');
		$action = $wp_list_table->current_action();
		  
	  
		if ( !current_user_can('manage_options') ) {
			die();
		}
		
		$post_ids = array_map('intval', $_REQUEST['post']);
		
		switch($action) {
			case 'export-constantcontact':
				
				$target_list = $_REQUEST['action_constantcontact_list_id'];
											
				
				$exported = 0;
				
				foreach( $post_ids as $post_id ) {		
						
					
					$lead_data['wpleads_first_name'] = get_post_meta($post_id,'wpleads_first_name', true);
					$lead_data['wpleads_last_name'] =  get_post_meta($post_id,'wpleads_last_name', true);
					$lead_data['wpleads_email'] =  get_post_meta($post_id,'wpleads_email_address', true);
					
					inboundnow_constantcontact_add_subscriber( $lead_data , $target_list );

					$exported++;
				}
				
				$sendback = add_query_arg( array('exported' => $exported , 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids) ), $sendback );	
				// 4. Redirect client
				wp_redirect($sendback);
				exit();
				
			break;	
		}		  
	}
}