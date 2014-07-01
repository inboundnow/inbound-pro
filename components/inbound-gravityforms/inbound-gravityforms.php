<?php
/*
Plugin Name: Gravity Forms - Leads Integration
Description: Integrates Gravity forms with Leads through automatic field mapping. Also allows for conversions to be sorted into lead lists. 
Version: 1.0.5
Author: Hudson Atwell
Author URI: http://www.hudsonatwell.co
*/

/* 
---------------------------------------------------------------------------------------------------------
- Conditional checks to see if landing pages plugin core is installed and activated, else we do nothing.
---------------------------------------------------------------------------------------------------------
*/ 


if(!defined('INBOUNDNOW_GRAVITYFORMS_CURRENT_VERSION')) { define('INBOUNDNOW_GRAVITYFORMS_CURRENT_VERSION', '1.0.5' ); }
if(!defined('INBOUNDNOW_GRAVITYFORMS_LABEL')) { define('INBOUNDNOW_GRAVITYFORMS_LABEL' , 'Gravity Forms Integration' ); }
if(!defined('INBOUNDNOW_GRAVITYFORMS_SLUG')) { define('INBOUNDNOW_GRAVITYFORMS_SLUG' , plugin_basename( dirname(__FILE__) ) ); }
if(!defined('INBOUNDNOW_GRAVITYFORMS_FILE')) { define('INBOUNDNOW_GRAVITYFORMS_FILE' ,  __FILE__ ); }
if(!defined('INBOUNDNOW_GRAVITYFORMS_REMOTE_ITEM_NAME')) { define('INBOUNDNOW_GRAVITYFORMS_REMOTE_ITEM_NAME' , 'gravityforms-integration' ); }
if(!defined('INBOUNDNOW_GRAVITYFORMS_URLPATH')) { define('INBOUNDNOW_GRAVITYFORMS_URLPATH', plugins_url( ' ', __FILE__ ) ); }
if(!defined('INBOUNDNOW_GRAVITYFORMS_PATH')) { define('INBOUNDNOW_GRAVITYFORMS_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' ); }

/*
------------------------------------------------------------------------------------
- Let's setup elements that will initiate this extension as a premium landing page extension.
- Here we only need to perform operations while in wp-admin.
------------------------------------------------------------------------------------
*/


if (!class_exists('GravityFormsLeads')) {


class GravityFormsLeads
{
	private $map;

	function __construct()
	{
		$this->hooks();
	}
	
	function hooks()
	{
		/* Setup Automatic Updating & Licensing */
		add_action('admin_init', array( __CLASS__ , 'license_setup') );
		
		/* Store Lead After Gform submission */
		add_action('gform_after_submission', array( $this , 'store_lead') , 10 , 2 );
		
		/* Required a First Name to Submit */
		add_filter('inboundnow_store_lead_pre_filter_data', array( $this , 'filter_data_check') , 10 , 1 );
	}
	
	/* 
	* Setups Software Update API 
	*/
	public static function license_setup() {
		
		/*PREPARE THIS EXTENSION FOR LICESNING*/
		if ( class_exists( 'Inbound_License' ) ) {
			$license = new Inbound_License( INBOUNDNOW_GRAVITYFORMS_FILE , INBOUNDNOW_GRAVITYFORMS_LABEL , INBOUNDNOW_GRAVITYFORMS_SLUG , INBOUNDNOW_GRAVITYFORMS_CURRENT_VERSION  , INBOUNDNOW_GRAVITYFORMS_REMOTE_ITEM_NAME ) ;
		}
	}
	
	
	/* 
	* Required a First Name to Submit 
	*/
	function filter_data_check( $lead_data ) {
		if (!$lead_data['wpleads_first_name']) {
			return array();
		} else {
			 return $lead_data;
		}
		
	}
	
	function store_lead( $entry, $form )
	{			
		global $post;
		
		$list_id = $form['gravityforms_leads_list_id'];
		
		foreach ($entry as $key=>$value)
		{
			if (strstr($key,'.'))
			{
				$key_exploded = explode('.',$key);
				$key_parent = $key_exploded[0];
				$entry[$key_parent][] = $value;
			}
		}

		foreach ($form['fields'] as $key => $field)
		{
			$field_id = $field['id'];
			$label = $field['label'];
			$value = $entry[$field_id];

			if ( is_array($field['inputs']) ) {
				foreach ($field['inputs'] as $k=>$f)
				{
					$this->build_map( $f['label'] , array_shift($value));
				}
				
			} else {

		    	$this->build_map( $label , $value );
			}
		}
		

		if(!isset($this->map['wpleads_email_address']))
		{
			return;
		}
		
		if (isset($_COOKIE['wp_lead_uid']))
		{
			$this->map['wp_lead_uid'] = $_COOKIE['wp_lead_uid'];	
		}
		else
		{
			$this->map['wp_lead_uid'] = md5($this->map['wpleads_email_address']);
			setcookie('wp_lead_uid' , $this->map['wp_lead_uid'] , time() + (20 * 365 * 24 * 60 * 60),'/');
		}
		
		/* account for company name */
		if (!isset( $this->map['wpleads_company_name'] ) )
		{
			$this->map['wpleads_company_name'] = 'Not Provided';
		}
		
		$lead_id = inbound_store_lead( $this->map );

		if ( $list_id && $lead_id ){
			wpleads_add_lead_to_list( $list_id, $lead_id );
		}
	}
	
	function build_map( $label , $value)
	{
		if (stristr($label,'name')&&stristr($label,'first')){
			$this->map['wpleads_first_name'] = $value;
		}
		else if (stristr($label,'name')&&stristr($label,'last')){
			$this->map['wpleads_last_name'] = $value;
		}	
		else if (stristr($label,'contact name')||stristr($label,'full name')){
			$value = explode(' ' , $value);
			$first_name = $value[0];
			$last_name = $value[1];
			$this->map['wpleads_first_name'] = $first_name;
			$this->map['wpleads_last_name'] = $last_name;
		}	
		else if (stristr($label,'first')&& !isset($this->map['wpleads_first_name'])){
			$this->map['wpleads_first_name'] = $value;
		}
		else if (stristr($label,'last')&& !isset($this->map['wplead_last_name'])){
			$this->map['wpleads_last_name'] = $value;
		}	
		else if (stristr($label,'email') || stristr($label,'e-mail')){
			$this->map['wpleads_email_address'] = $value;
		}		
		else if (stristr($label,'Company')&&stristr($label,'Name'))	{				
			$this->map['wpleads_company_name'] = $value;
		}	
		else if (stristr($label,'name') && !isset($this->map['wpleads_first_name'])){
			$this->map['wpleads_first_name'] = $value;
		}				
		else if (stristr($label,'phone')){
			$this->map['wpleads_work_phone'] = $value;
		}						
		else if (stristr($label,'fax')){
			$this->map['wpleads_fax_number'] = $value;
		}					
		else if (stristr($label,'comments') || stristr($label,'message') ){
			$this->map['wpleads_notes'] = $value;	
		}				
		else if (stristr($label,'zip')){
			$this->map['wpleads_zip'] = $value;	
		}			
		else if (stristr($label,'address')){
			$this->map['wpleads_address_line_1'] = $value;	
		}			
		else if ( stristr($label,'address') && stristr($label,'2')) {
			$this->map['wpleads_address_line_2'] = $value;	
		}			
		else if ( stristr($label,'city') ) {
			$this->map['wpleads_city'] = $value;	
		}		
		else if ( stristr($label,'address') || stristr($label,'region') ) {
			$this->map['wpleads_region_name'] = $value;	
		}			
		else if ( stristr($label,'country')) {
			$this->map['wpleads_country_code'] = $value;	
		}		
		else if ( stristr($label,'website')) {
			$this->map['wpleads_website'] = $value;	
		}
		else
		{
			$this->map[$label] = $value;
		}
	}	
}

if (is_admin())
{		
	//no need to have these metaboxes. Setting up leads integration in gform creation.
	//include_once('modules/m.metaboxes.php');
	
	add_filter("gform_settings_menu", 'gravityforms_leads_add_settings');
	function gravityforms_leads_add_settings($settings_tabs)
	{		
		$settings_tabs[] = array(
						'name' => 'leads',
						'label' => 'WordPress Leads'
						);
						
		return $settings_tabs;
	}
	
	add_filter('gform_form_settings', 'gravityforms_leads_define_settings', 10, 2);
	function gravityforms_leads_define_settings($settings, $form) {
		
		$selected_list = rgar($form, 'gravityforms_leads_list_id');
		
		$lists = wpleads_get_lead_lists_as_array();

		$html = "<select id='gravityforms_leads_list_id' name='gravityforms_leads_list_id'>";
		$html .= "<option value='0'>No Sorting</option>";
		foreach ( $lists as $id => $label  )
		{
			 ( $selected_list == $id ) ? $selected = 'selected=selected' : $selected = ''; 

			$html .= "<option value='".$id."' ".$selected.">".$label."</option>";
		}
		$html .="</select>";

		
		$settings['WordPress Leads']['gravityforms_leads_list_id'] = '
			<tr>
				<th>
					<label for="gravityforms_leads_list_id">Sort into list:</label> 
					<a title="&lt;h6&gt;Sort into list:&lt;/h6&gt;" class="gf_tooltip tooltip tooltip_form_title" onclick="return false;" href="#">(?)</a>
				</th>
					
				<td>'.$html.'</td>
			</tr>';
		
		return $settings;
	}

	// save your custom form setting
	add_filter('gform_pre_form_settings_save', 'save_gravityforms_leads_define_settings');
	function save_gravityforms_leads_define_settings($form) {
		$form['gravityforms_leads_list_id'] = rgpost('gravityforms_leads_list_id');
		return $form;
	}
	
}


$gf_leads = new GravityFormsLeads();

}