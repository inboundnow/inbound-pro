<?php

add_action('admin_init', 'inboundnow_constantcontact_extension_setup');

function inboundnow_constantcontact_extension_setup()
{
	/*PREPARE THIS EXTENSION FOR LICESNING*/
	if ( class_exists( 'Inbound_License' ) )   
		$license = new Inbound_License( INBOUNDNOW_CONSTANTCONTACT_FILE , INBOUNDNOW_CONSTANTCONTACT_LABEL , INBOUNDNOW_CONSTANTCONTACT_SLUG , INBOUNDNOW_CONSTANTCONTACT_CURRENT_VERSION  , INBOUNDNOW_CONSTANTCONTACT_REMOTE_ITEM_NAME ) ;
	
	/* ADD ADMIN NOTICE WHEN REQUIRED KEYS ARE EMPTY */
	$api_key = get_option('inboundnow_constantcontact_api_key' , '' );
	$api_secret_key =  get_option('inboundnow_constantcontact_secret_key' , '' );
	$api_access_key =  get_option('inboundnow_constantcontact_access_key' , '' );
	
	
	if ( !$api_access_key || !$api_key )
	{
		add_action( 'admin_notices', 'inboundnow_constantcontact_admin_notice' );
		function inboundnow_constantcontact_admin_notice() 
		{
		?>
		<div class="updated">
			<p><?php _e( 'InboundNow ConstantContact Extension requires a ConstantContact API Key, an ConstantContact API Secret Key, and an ConstantContact API Access Key to opperate.', CONSTANTCONTACT_TEXT_DOMAIN ); ?></p>
		</div>
		<?php
		}
	}
}