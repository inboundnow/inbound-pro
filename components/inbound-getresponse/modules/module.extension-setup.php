<?php

add_action('admin_init', 'inboundnow_getresponse_extension_setup');

function inboundnow_getresponse_extension_setup()
{

	/*PREPARE THIS EXTENSION FOR LICESNING*/
	if ( class_exists( 'Inbound_License' ) )   
		$license = new Inbound_License( INBOUNDNOW_GETRESPONSE_FILE , INBOUNDNOW_GETRESPONSE_LABEL , INBOUNDNOW_GETRESPONSE_SLUG , INBOUNDNOW_GETRESPONSE_CURRENT_VERSION  , INBOUNDNOW_GETRESPONSE_REMOTE_ITEM_NAME ) ;
	
	/* ADD ADMIN NOTICE WHEN REQUIRED KEYS ARE EMPTY */
	$getresponse_api_key = get_option('inboundnow_getresponse_api_key' , '' );
	if ( !$getresponse_api_key )
	{
		add_action( 'admin_notices', 'inboundnow_getresponse_admin_notice' );
		function inboundnow_getresponse_admin_notice() 
		{
		?>
		<div class="updated">
			<p><?php _e( 'InboundNow GetResponse Extension requires a <a href="https://app.getresponse.com/my_api_key.html" target="_blank">GetResponse API Key</a> to opperate.', INBOUNDNOW_GETRESPONSE_TEXT_DOMAIN ); ?></p>
		</div>
		<?php
		}
	}
}