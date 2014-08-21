<?php

function inboundnow_mailchimp_get_mailchimp_lists()
{
	
	$mailchimp_lists = get_transient('inboundnow_mailchimp_lists');

	if ($mailchimp_lists)
		return $mailchimp_lists;
		
	$apikey = get_option('inboundnow_mailchimp_api_key' , true);
	
	if (!$apikey)
		return;
		
	$MailChimp = new MailChimp($apikey);

	$lists = $MailChimp->call('lists/list');
	
	
	
	if ( isset($lists['total']) && $lists['total'] >0 )
	{
		foreach ( $lists['data'] as $list )
		{
			$options[$list['id']] = $list['name'];
		}
	}
	
	if (!isset($options))
		$options['0'] = "No lists discovered.";
	
	
	set_transient( 'inboundnow_mailchimp_lists', $options, 60*5 );
	
	return $options;
}

//add options to bulk edit
add_action('admin_footer-edit.php', 'inboundnow_mailchimp_bulk_actions_add_options');
function inboundnow_mailchimp_bulk_actions_add_options() {
 
 	if( isset($_GET['post_type']) && $_GET['post_type'] == 'wp-lead' )
	{

		$lists = inboundnow_mailchimp_get_mailchimp_lists();

		$html = "<select id='mailchimp_list_select' name='action_mailchimp_list_id'>";
		foreach ($lists as $key=>$value)
		{
			$html .= "<option value='".$key."'>".str_replace('"', '' , $value )."</option>";
		}
		$html .="</select>";
		?>
		<script type="text/javascript">
		  jQuery(document).ready(function() {
			jQuery('<option>').val('export-mailchimp').text('<?php _e('Export to MailChimp List')?>').appendTo("select[name='action']");
			jQuery('<option>').val('export-mailchimp').text('<?php _e('Export to MailChimp List')?>').appendTo("select[name='action2']");
			
			jQuery(document).on('change','select[name=action]', function() {
				var this_id = jQuery(this).val();
				//alert(this_id);
				if (this_id.indexOf("export-mailchimp") >= 0)
				{
					var html  = "<?php echo $html; ?>";
					
					jQuery("select[name='action']").after(html);
				}
				else
				{
					jQuery('#mailchimp_list_select').remove();
				}
			});
		  });
		</script>
		<?php
	}
}

add_action('load-edit.php', 'wpleads_bulk_action_mailchimp');
function wpleads_bulk_action_mailchimp() {
	
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
			case 'export-mailchimp':
				$apikey = get_option('inboundnow_mailchimp_api_key' , true);
				$target_list = $_REQUEST['action_mailchimp_list_id'];
				$exported = 0;
				
				foreach( $post_ids as $post_id ) {		
						
				

					$lead_first_name = get_post_meta($post_id,'wpleads_first_name', true);
					$lead_last_name =  get_post_meta($post_id,'wpleads_last_name', true);
					$lead_email =  get_post_meta($post_id,'wpleads_email_address', true);
					
					$MailChimp = new MailChimp($apikey);
					$result = $MailChimp->call('lists/subscribe', array(
						'id'                => $target_list,
						'email'             => array('email'=>$lead_email),
						'merge_vars'        => array('FNAME'=>$lead_first_name, 'LNAME'=>$lead_last_name),
						'double_optin'      => false,
						'update_existing'   => true,
						'replace_interests' => false,
						'send_welcome'      => false,
					));
					
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