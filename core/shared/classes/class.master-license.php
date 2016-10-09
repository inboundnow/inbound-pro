<?php

if (!function_exists('inboundnow_add_master_license') && !defined('INBOUND_ACCESS_LEVEL') )
{

	/* Add Master License Key Setting*/
	add_filter('lp_define_global_settings', 'inboundnow_add_master_license', 1, 1);
	add_filter('wpleads_define_global_settings', 'inboundnow_add_master_license', 1, 1);
	add_filter('wp_cta_define_global_settings', 'inboundnow_add_master_license', 1, 1);
	function inboundnow_add_master_license($global_settings)
	{
		$key = '';
		switch(current_filter())
		{
			case 'lp_define_global_settings':
				$key = 'lp-license-keys';
				break;
			case 'wpleads_define_global_settings':
				$key = 'wpleads-license-keys';
				break;
			case 'wp_cta_define_global_settings':
				$key = 'wp-cta-license-keys';
				break;
		}

		$global_settings[$key]['settings']['master-key'] = 	array(
						'id'  => 'extensions-license-keys-master-key-header',
						'description' => __( "Head to http://www.inboundnow.com/ to retrieve your extension-ready license key.", 'inbound-pro' ),
						'type'  => 'header',
						'default' => '<h3 class="lp_global_settings_header">'. __( 'InboundNow Master Key', 'inbound-pro' ) .'</h3>'
				);

		$global_settings[$key]['settings']['master-key'] = 	array(
				'id'  => 'inboundnow_master_license_key',
				'option_name'  => 'inboundnow_master_license_key',
				'label' => __('Inbound Now API Key', 'inbound-pro' ),
				'description' => __( "Head to http://www.inboundnow.com/account to retrieve your extension-ready license key.", 'inbound-pro' ),
				'type'  => 'text',
				'default' => ''
		);

		return $global_settings;
	}
}
