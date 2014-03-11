<?php

add_action('admin_init', 'inboundnow_hubspot_extension_setup');

function inboundnow_hubspot_extension_setup()
{
	/*PREPARE THIS EXTENSION FOR LICESNING*/
	if ( class_exists( 'INBOUNDNOW_EXTEND' ) )   
		$license = new INBOUNDNOW_EXTEND( INBOUNDNOW_HUBSPOT_FILE , INBOUNDNOW_HUBSPOT_LABEL , INBOUNDNOW_HUBSPOT_SLUG , INBOUNDNOW_HUBSPOT_CURRENT_VERSION  , INBOUNDNOW_HUBSPOT_REMOTE_ITEM_NAME ) ;
	
	
	/* ADD ADMIN NOTICE WHEN REQUIRED KEYS ARE EMPTY */
	$hubspot_api_key = get_option('inboundnow_hubspot_api_key' , '' );
	$hubspot_portal_id =  get_option('inboundnow_hubspot_portal_id' , '' );	
	if ( !$hubspot_api_key || !$hubspot_portal_id )
	{
		add_action( 'admin_notices', 'inboundnow_hubspot_admin_notice' );
		function inboundnow_hubspot_admin_notice() 
		{
		?>
		<div class="updated">
			<p><?php _e( 'InboundNow HubSpot Extension requires a HubSpot API Key and HubSpot Portal ID to opperate.', INBOUNDNOW_LABEL ); ?></p>
		</div>
		<?php
		}
	}
}