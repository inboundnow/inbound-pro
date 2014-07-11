<?php


//add options to bulk edit
add_action('admin_footer-edit.php', 'inboundnow_hubspot_bulk_actions_add_options');
function inboundnow_hubspot_bulk_actions_add_options()
{
  	if(isset($_GET['post_type']) && $_GET['post_type'] == 'wp-lead')
	{

		$lists = inboundnow_hubspot_get_hubspot_lists();

		$html = "<select id='hubspot_list_select' name='action_hubspot_list_id'>";
		foreach ($lists as $key=>$value)
		{
			$html .= "<option value='".$key."'>".$value."</option>";
		}
		$html .="</select>";
		?>
		<script type="text/javascript">
		  jQuery(document).ready(function() {
			jQuery('<option>').val('export-hubspot').text('<?php _e('Export to HubSpot List')?>').appendTo("select[name='action']");
			jQuery('<option>').val('export-hubspot').text('<?php _e('Export to HubSpot List')?>').appendTo("select[name='action2']");
			
			jQuery(document).on('change','select[name=action]', function() {
				var this_id = jQuery(this).val();
				//alert(this_id);
				if (this_id.indexOf("export-hubspot") >= 0)
				{
					var html  = "<?php echo $html; ?>";
					
					jQuery("select[name='action']").after(html);
				}
				else
				{
					jQuery('#hubspot_list_select').remove();
				}
			});
		  });
		</script>
		<?php
	}
}

add_action('load-edit.php', 'wpleads_bulk_action_hubspot');
function wpleads_bulk_action_hubspot() {
	
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
			case 'export-hubspot':
				
				
				$target_list = $_REQUEST['action_hubspot_list_id'];
				
				$exported = 0;				
				foreach( $post_ids as $post_id ) {		
						
					/* get lead data */					
					$lead_data['first_name'] = get_post_meta($post_id,'wpleads_first_name', true);
					$lead_data['last_name'] =  get_post_meta($post_id,'wpleads_last_name', true);
					$lead_data['email'] =  get_post_meta($post_id,'wpleads_email_address', true);
					
					inboundnow_hubspot_add_subscriber( $lead_data  , $target_list);
								
					$exported++;
				}
				
				$sendback = add_query_arg( array('exported' => $exported , 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids) ), $sendback );	
				wp_redirect($sendback);
				exit();
				
			break;	
		}		  
	}
}