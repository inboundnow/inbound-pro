<?php

/* Provide backwards compatibility for older data array model */
//add_filter('lp_extension_data','inboundnow_mailchimp_add_metaboxes');
//add_filter('wp_cta_extension_data','inboundnow_mailchimp_add_metaboxes');

function inboundnow_mailchimp_add_metaboxes( $metabox_data )
{
	$lists = inboundnow_mailchimp_get_mailchimp_lists();
	
	$metabox_data['inboundnow-mailchimp']['info']['data_type'] = 'metabox';
	$metabox_data['inboundnow-mailchimp']['info']['position'] = 'side';
	$metabox_data['inboundnow-mailchimp']['info']['priority'] = 'default';
	$metabox_data['inboundnow-mailchimp']['info']['label'] = 'Mailchimp Integration';
		
	$metabox_data['inboundnow-mailchimp']['settings'] = array(
		//ADD METABOX - SELECTED TEMPLATE	
		array(
			'id'  => 'mailchimp_integration',
			'label' => 'Enable:',
			'description' => "Enable this setting to send email related conversion data to mailchimp list. Email must be present in conversion form for this feature to work.",
			'type'  => 'dropdown', // this is not honored. Template selection setting is handled uniquely by core.
			'default'  => '0',
			'options' => array('1'=>'on','0'=>'off') // this is not honored. Template selection setting is handled uniquely by core.
		),
		array(
			'id'  => 'mailchimp_list',
			'label' => 'Target list:',
			'description' => "Select the mailchimp list that converted data will be sent to. Must have setup a mailchimp api key and enabled the setting above for this feature to work.",
			'type'  => 'dropdown', // this is not honored. Main Headline Input setting is handled uniquely by core.
			'default'  => '',
			'options' => $lists
		)
	);
	
	return $metabox_data;
}

	
	//add_action('admin_menu', 'inboundnow_mailchimp_add_other_metaboxes');
	function inboundnow_mailchimp_add_other_metaboxes()
	{

		//add_meta_box( INBOUNDNOW_MAILCHIMP_SLUG, 'Enable Mailchump on Forms', 'inboundnow_mailchimp_add_other_metaboxes_display' , 'post', 'side', 'default' );
		//add_meta_box(INBOUNDNOW_MAILCHIMP_SLUG, 'Enable Mailchump on Forms', 'inboundnow_mailchimp_add_other_metaboxes_display' , 'page', 'side', 'default' );
		
	}
	
	
	function inboundnow_mailchimp_add_other_metaboxes_display()
	{
		global $post;
		global $table_prefix;


		
		?>
		<table class="form-table">
			<tbody>
			<tr class="inboundnow-mailchimp-mailchimp_integration mailchimp_integration landing-page-option-row">
				<th class="landing-page-table-header mailchimp_integration-label"><label for="inboundnow-mailchimp-mailchimp_integration">Enable:</label></th>
				<td class="landing-page-option-td"><select class="mailchimp_integration" id="inboundnow-mailchimp-mailchimp_integration" name="inboundnow-mailchimp-mailchimp_integration"><option value="1" selected="selected">on</option><option value="0">off</option></select><div title="Enable this setting to send email related conversion data to mailchimp list. Email must be present in conversion form for this feature to work." class="lp_tooltip"></div></td></tr><tr class="inboundnow-mailchimp-mailchimp_list mailchimp_list landing-page-option-row">
				<th class="landing-page-table-header mailchimp_list-label"><label for="inboundnow-mailchimp-mailchimp_list">Target list:</label></th>
				<td class="landing-page-option-td">
					<select class="mailchimp_list" id="inboundnow-mailchimp-mailchimp_list" name="inboundnow-mailchimp-mailchimp_list">
						<option value="ce29f86f54">AlphaBeta</option>
						<option value="b03ad1c87e" selected="selected">List Beta</option>
						<option value="08d0a87a77">List Alpha</option>
					</select>
				<div title="Select the mailchimp list that converted data will be sent to. Must have setup a mailchimp api key and enabled the setting above for this feature to work." class="lp_tooltip"></div></td>
			</tr>
			</tbody>
		</table>
		<?php
	}