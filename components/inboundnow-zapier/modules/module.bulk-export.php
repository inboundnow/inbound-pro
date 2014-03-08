<?php


//add options to bulk edit
add_action('admin_footer-edit.php', 'inboundnow_zapier_bulk_actions_add_options');
function inboundnow_zapier_bulk_actions_add_options() {
  global $post_type;

  if($post_type == 'wp-lead') {

	?>
	<script type="text/javascript">
	  jQuery(document).ready(function() {
		jQuery('<option>').val('export-zapier').text('<?php _e('Export to Zapier List')?>').appendTo("select[name='action']");
		jQuery('<option>').val('export-zapier').text('<?php _e('Export to Zapier List')?>').appendTo("select[name='action2']");
	  });
	</script>
	<?php
  }
}

add_action('load-edit.php', 'wpleads_bulk_action_zapier');
function wpleads_bulk_action_zapier() {
	
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
			case 'export-zapier':
				
				$exported = 0;				
				foreach( $post_ids as $post_id ) {		
						
					/* get lead data */					
					$lead_data['first_name'] = get_post_meta($post_id,'wpleads_first_name', true);
					$lead_data['last_name'] =  get_post_meta($post_id,'wpleads_last_name', true);
					$lead_data['email'] =  get_post_meta($post_id,'wpleads_email_address', true);
					
					inboundnow_zapier_add_subscriber( $lead_data );
								
					$exported++;
				}
				
				$sendback = add_query_arg( array('exported' => $exported , 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids) ), $sendback );	
				wp_redirect($sendback);
				exit();
				
			break;	
		}		  
	}
}