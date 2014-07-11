<?php

add_action('admin_init', 'inboundnow_mailchimp_extension_setup');

function inboundnow_mailchimp_extension_setup()
{
	/*PREPARE THIS EXTENSION FOR LICESNING*/
	if ( class_exists( 'Inbound_License' ) )   
		$license = new Inbound_License( INBOUNDNOW_MAILCHIMP_FILE , INBOUNDNOW_MAILCHIMP_LABEL , INBOUNDNOW_MAILCHIMP_SLUG , INBOUNDNOW_MAILCHIMP_CURRENT_VERSION  , INBOUNDNOW_MAILCHIMP_REMOTE_ITEM_NAME ) ;
	
	$apikey = get_option('inboundnow_mailchimp_api_key' , true);
	if (!$apikey)
	{
		add_action( 'admin_notices', 'inboundnow_mailchimp_admin_notice' );
		function inboundnow_mailchimp_admin_notice() 
		{
		?>
		<div class="updated">
			<p><?php _e( 'InboundNow MailChimp Extension requires a MailChimp API Key to opperate.', INBOUNDNOW_MAILCHIMP_TEXTDOMAIN ); ?></p>
		</div>
		<?php
		}
	}

}

