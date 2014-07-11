<?php


/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
add_filter('lp_define_global_settings','inboundnow_aweber_add_global_settings');
add_filter('wpleads_define_global_settings','inboundnow_aweber_add_global_settings');
add_filter('wp_cta_define_global_settings','inboundnow_aweber_add_global_settings');
function inboundnow_aweber_add_global_settings($global_settings)
{
	switch (current_filter())
	{
		case "lp_define_global_settings":		
			$tab_slug = 'lp-extensions';
			break;
		case "wpleads_define_global_settings":		
			$tab_slug = 'wpleads-extensions';
			break;
		case "wp_cta_define_global_settings":		
			$tab_slug = 'wp-cta-extensions';
			break;
	}
	
	$global_settings[$tab_slug]['settings'][] = 
		array(
			'id'  => 'inboundnow_header_aweber',			
			'type'  => 'header', 
			'default'  => __('<h4>Aweber API Key</h4>', INBOUND_LABEL),
			'options' => null
		);

	$global_settings[$tab_slug]['settings'][] = 
			array(
				'id'  => 'inboundnow_aweber_app_id',
				'option_name'  => 'inboundnow_aweber_app_id',
				'label' => __('Aweber API ID', INBOUND_LABEL),
				'description' => __('The first thing we need is our App ID. Singup at https://labs.aweber.com/ and create your first application. Your App ID can be found on your MyApps page and is right next to your consumer keys. The App ID is used when creating public applications.', INBOUND_LABEL),
				'type'  => 'text', 
				'default'  => ''
			);

	$global_settings[$tab_slug]['settings'][] = 
			array(
				'id'  => 'inboundnow_aweber_consumer_key',
				'option_name'  => 'inboundnow_aweber_consumer_key',
				'label' => 'Aweber Consumer Key',
				'description' => "Find your consumer key here: https://labs.aweber.com/apps.",
				'type'  => 'text', 
				'default'  => ''
			);

	$global_settings[$tab_slug]['settings'][] = 
			array(
				'id'  => 'inboundnow_aweber_consumer_secret',
				'option_name'  => 'inboundnow_aweber_consumer_secret',
				'label' => 'Aweber Consumer Secret',
				'description' => "Find your consumer secret here: https://labs.aweber.com/apps.",
				'type'  => 'text', 
				'default'  => ''
			);
	
	$global_settings[$tab_slug]['settings'][] = 
			array(
				'id'  => 'inboundnow_aweber_oauth_reset',
				'option_name'  => 'inboundnow_aweber_oauth_reset',
				'label' => 'Oauth Reset',
				'description' => "Check this box to re-authorize your aweber account and update your access keys. ",
				'type'  => 'checkbox', 
				'options'  => array('1'=>'')
			);
	
	return $global_settings;
}

//authorize aweber and store token
add_action('lp_save_global_settings','inboundnow_aweber_save_data', 10, 2);
add_action('wpleads_save_global_settings','inboundnow_aweber_save_data', 10, 2);
add_action('wp_cta_save_global_settings','inboundnow_aweber_save_data', 10, 2);
function inboundnow_aweber_save_data( $field )
{
	global $debug; 

	if ($field['option_name'] == 'inboundnow_aweber_app_id' )
	{
		
		$check = get_option('inboundnow_aweber_oauth_accessKey' , 0);
		
		$today = date("YmdHis"); 
		$expiration_date = get_option('inboundnow_aweber_oauth_access_token_date',0);
		
		/*
		echo "AccessKey: $check<br>";
		echo "Today: $today <br>";
		echo "Expiration Date: $expiration_date<br>";
		*/
		
		//print_r($_POST);exit;
		if ( !$check || ( $today>$expiration_date ) || ( isset($_POST['inboundnow_aweber_oauth_reset'][0])&&$_POST['inboundnow_aweber_oauth_reset'][0]==1 ))
		{		
			update_option('inboundnow_aweber_consumer_key' , $_POST['inboundnow_aweber_consumer_key'] );
			update_option('inboundnow_aweber_consumer_secret' , $_POST['inboundnow_aweber_consumer_secret'] );
			update_option('inboundnow_aweber_oauth_reset' , 0 );
			
			$projected_time = date('YmdHis', strtotime("$today + 1 year"));
			update_option('inboundnow_aweber_oauth_access_token_date' , $projected_time );
			?>
			<br><br><br>
			<a href='https://auth.aweber.com/1.0/oauth/authorize_app/<?php echo $_POST['inboundnow_aweber_app_id']; ?>' target='_blank'>Click here to authorization code from Aweber. Save the code once you get it!</a>
			
			<br><br>
			Type authorization code here: 
			
			<form action='edit.php' method='get'>
				<input name='inboundnow_aweber_oauth_code' size='170'>
				<input type='hidden' name='post_type' value='<?php echo $_GET['post_type']; ?>'>
				<input type='hidden' name='page' value='<?php echo $_GET['page']; ?>'>
				<input type='submit' value='submit'>
			</form>
			<?php
			exit;
			
		}
	}
}
			
add_action('admin_init' , 'inboundnow_aweber_process_oauth_code');
function inboundnow_aweber_process_oauth_code()
{
	if (isset($_GET['inboundnow_aweber_oauth_code']))
	{
		$consumer_key =  get_option('inboundnow_aweber_consumer_key' , '' );
		$consumer_secret =  get_option('inboundnow_aweber_consumer_secret' , '' );
		
		/*
		echo 'Aweber Authorized!';
		echo $consumer_key;
		echo '<br>';
		echo $consumer_secret;
		echo '<br>';
		echo $_GET['inboundnow_aweber_oauth_code'];
		echo '<br>';
		*/
		
		$aweber = new AWeberAPI($consumer_key , $consumer_secret);
		$credentials = $aweber->getDataFromAweberID($_GET['inboundnow_aweber_oauth_code']);
		list($consumerKey, $consumerSecret, $accessKey, $accessSecret) = $credentials;
		
		//print_r($credentials);
		
		update_option('inboundnow_aweber_oauth_consumerKey' , $consumerKey );
		update_option('inboundnow_aweber_oauth_consumerSecret' , $consumerSecret );
		update_option('inboundnow_aweber_oauth_accessKey' , $accessKey );
		update_option('inboundnow_aweber_oauth_accessSecret' , $accessSecret );
			
		//exit;
	}
			

}