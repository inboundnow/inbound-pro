<?php

function inboundnow_aweber_get_aweber_lists()
{
	
	$aweber_lists = get_transient('inboundnow_aweber_lists');
	
	if ($aweber_lists)
		return $aweber_lists;
	
	$consumer_key = get_option('inboundnow_aweber_consumer_key' , '' );
	$consumer_secret =  get_option('inboundnow_aweber_consumer_secret' , '' );
	$accessKey =  get_option('inboundnow_aweber_oauth_accessKey' , '' );
	$accessSecret =  get_option('inboundnow_aweber_oauth_accessSecret' , '' );
	
	if ( !$consumer_key || !$consumer_secret || !$accessKey || !$accessSecret )
		return null;
	

	//echo "Consumer Key: {$consumer_key} <br>\r\n"; 
	//echo "Consumer Secret: $consumer_secret <br>\r\n";
	$aweber = new AWeberAPI($consumer_key , $consumer_secret);	
	
	$account = $aweber->getAccount($accessKey, $accessSecret);
	$object = $account->lists;
	$data = $object->data;
	$lists = $data['entries'];

	//print_r($lists);exit;
	
	foreach($lists as $key => $list) {
		$options[$list['id']] = $list['name'];
	}
	
	if (!isset($options))
		$options['0'] = "No lists discovered.";
	
	
	set_transient( 'inboundnow_aweber_lists', $options, 60*5 );
	
	return $options;
}


//add options to bulk edit
add_action('admin_footer-edit.php', 'inboundnow_aweber_bulk_actions_add_options');
function inboundnow_aweber_bulk_actions_add_options() {
  
	if(isset($_GET['post_type']) && $_GET['post_type'] == 'wp-lead')
	{

	$lists = inboundnow_aweber_get_aweber_lists();

	$html = "<select id='aweber_list_select' name='action_aweber_list_id'>";
	foreach ($lists as $key=>$value)
	{
		$html .= "<option value='".$key."'>".$value."</option>";
	}
	$html .="</select>";
	?>
	<script type="text/javascript">
	  jQuery(document).ready(function() {
		jQuery('<option>').val('export-aweber').text('<?php _e('Export to Aweber List')?>').appendTo("select[name='action']");
		jQuery('<option>').val('export-aweber').text('<?php _e('Export to Aweber List')?>').appendTo("select[name='action2']");
		
		jQuery(document).on('change','select[name=action]', function() {
			var this_id = jQuery(this).val();
			//alert(this_id);
			if (this_id.indexOf("export-aweber") >= 0)
			{
				var html  = "<?php echo $html; ?>";
				
				jQuery("select[name='action']").after(html);
			}
			else
			{
				jQuery('#aweber_list_select').remove();
			}
		});
	  });
	</script>
	<?php
  }
}

add_action('load-edit.php', 'wpleads_bulk_action_aweber');
function wpleads_bulk_action_aweber() {
	
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
			case 'export-aweber':
				
				$target_list = $_REQUEST['action_aweber_list_id'];
					
				$consumer_key =  get_option('inboundnow_aweber_consumer_key' , '' );
				$consumer_secret =  get_option('inboundnow_aweber_consumer_secret' , '' );
				$accessKey =  get_option('inboundnow_aweber_oauth_accessKey' , '' );
				$accessSecret =  get_option('inboundnow_aweber_oauth_accessSecret' , '' );
					
				$aweber = new AWeberAPI($consumer_key , $consumer_secret);
				$account = $aweber->getAccount($accessKey, $accessSecret);
				$account_data = $account->data;
				$account_id = $account_data['id'];
				
				//$api_url = "https://api.aweber.com/1.0/accounts/{$account_id}/lists/{$target_list}";
				//var_dump($account);
				//echo $account_id;exit;
				//echo $api_url;
				//$list = $account->loadFromUrl($api_url);
				$list = $account->loadFromUrl("/accounts/{$account_id}/lists/{$target_list}");
				$subscribers = $list->subscribers;
				//var_dump($subscribers);exit;
						
				
				$exported = 0;
				
				foreach( $post_ids as $post_id ) {		
						
					
					$lead_first_name = get_post_meta($post_id,'wpleads_first_name', true);
					$lead_last_name =  get_post_meta($post_id,'wpleads_last_name', true);
					$lead_email =  get_post_meta($post_id,'wpleads_email_address', true);
					
					
					# create a subscriber
					$params = array(
						'email' => $lead_email,
						//'ip_address' => '127.0.0.1',
						//'ad_tracking' => 'client_lib_example',
						//'last_followup_message_number_sent' => 1,
						//'misc_notes' => 'my cool app',
						'name' => "$lead_first_name $lead_last_name"
					);
					
					$params = apply_filters('inboundnow_aweber_subscriber_params',$params);

					$new_subscriber = $subscribers->create($params);

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