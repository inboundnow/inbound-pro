<?php

add_action('admin_init', 'inboundnow_zapier_extension_setup');

function inboundnow_zapier_extension_setup()
{
	/*PREPARE THIS EXTENSION FOR LICESNING*/
	if ( class_exists( 'INBOUNDNOW_EXTEND' ) )   
		$license = new INBOUNDNOW_EXTEND( INBOUNDNOW_ZAPIER_FILE , INBOUNDNOW_ZAPIER_LABEL , INBOUNDNOW_ZAPIER_SLUG , INBOUNDNOW_ZAPIER_CURRENT_VERSION  , INBOUNDNOW_ZAPIER_REMOTE_ITEM_NAME ) ;
	
	
	/* ADD ADMIN NOTICE WHEN REQUIRED KEYS ARE EMPTY */
	$inboundnow_zapier_webhook_url = get_option('inboundnow_zapier_webhook_url' , '' );
	if ( !$inboundnow_zapier_webhook_url)
	{
		add_action( 'admin_notices', 'inboundnow_zapier_admin_notice' );
		function inboundnow_zapier_admin_notice() 
		{
		?>
		<div class="updated">
			<p><?php _e( 'InboundNow Zapier Extension requires a Zapier Webhook URL to opperate.', INBOUNDNOW_LABEL ); ?></p>
		</div>
		<?php
		}
	}
}