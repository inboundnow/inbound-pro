<?php

add_action('admin_init', 'inboundnow_aweber_extension_setup');

function inboundnow_aweber_extension_setup()
{
	/* upgrade testing */	
	//update_option('_site_transient_update_plugins','');

	/*PREPARE THIS EXTENSION FOR LICESNING*/
	if ( class_exists( 'Inbound_License' ) )   
		$license = new Inbound_License( INBOUNDNOW_AWEBER_FILE , INBOUNDNOW_AWEBER_LABEL , INBOUNDNOW_AWEBER_SLUG , INBOUNDNOW_AWEBER_CURRENT_VERSION  , INBOUNDNOW_AWEBER_REMOTE_ITEM_NAME ) ;
	
	
	/* ADD ADMIN NOTICE WHEN REQUIRED KEYS ARE EMPTY */
	$app_id = get_option('inboundnow_aweber_app_id' , '' );
	$consumer_key = get_option('inboundnow_aweber_consumer_key' , '' );
	$consumer_secret =  get_option('inboundnow_aweber_consumer_secret' , '' );
	
	
	if ( !$consumer_key || !$consumer_secret || !$app_id )
	{
		add_action( 'admin_notices', 'inboundnow_aweber_admin_notice' );
		function inboundnow_aweber_admin_notice() 
		{
		?>
		<div class="updated">
			<p><?php _e( 'InboundNow Aweber Extension requires a Aweber App ID, an Aweber Consumer Key, and an Aweber Consumer Secret to opperate.', INBOUNDNOW_LABEL ); ?></p>
		</div>
		<?php
		}
	}
}