<?php
/*
Action Name: Relay Data
Action Description: Relay trigger data to extenal URL using several different methods.
Action Author: Inbound Now
Contributors: Hudson Atwell
*/

if ( !class_exists( 'Inbound_Automation_Action_Relay_Data' ) ) {

	class Inbound_Automation_Action_Relay_Data {

		function __construct() {

			add_filter( 'inbound_automation_actions' , array( __CLASS__ , 'define_action' ) , 1 , 1);
		}

		/* Build Action Definitions */
		public static function define_action( $actions ) {

			/* Build Action */
			$actions['relay_data'] = array (
				'class_name' => get_class(),
				'id' => 'relay_data',
				'label' => __( 'Relay Data', 'marketing-automation') ,
				'description' => __('Send data intercepted by Trigger to extenal URL using POST or GET methods.' , 'inbound-pro' ),
				'settings' => array (
					array (
						'id' => 'send_method',
						'label' => 'Send Method',
						'type' => 'dropdown',
						'default' => 'POST',
						'options' => array (
										'POST' => __('POST (post variables directly to URL)', 'inbound-pro' ),
										'GET' => __('GET (append parameters to URL)','inbound-pro' ),
										'JSON-POST' => __('JSON (Post JSON packet directly to URL) ', 'inbound-pro' ),
									)
						),					
					array (
						'id' => 'destination',
						'label' => __('Destination URL:', 'inbound-pro' ),
						'type' => 'text',
						'default' => 'http://www.somesite.com/somescript.php'
						)
				)
			);

			return $actions;
		}

		/*
		* Sends the Data 
		*/
		public static function run_action( $action , $arguments ) {
			
			$final_args = $arguments;

			if (isset($arguments['object_id'])) {
				$ID = $arguments['object_id'];
				$final_args['postmeta'] = get_post_meta($ID);
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $ID ), 'single-post-thumbnail' );
				$final_args['featured_image'] = (isset($image[0])) ? $image[0] : '';
				$final_args['the_content'] = do_shortcode(get_the_content($ID));
				$final_args['post'] = get_post($ID , ARRAY_A );
			}

			/* Open CURL connection to destination URL */
			$ch = curl_init();   
			
			switch ($action['send_method']) {
				case 'POST' :
					
					curl_setopt($ch, CURLOPT_URL,  $action['destination'] );  
					curl_setopt($ch, CURLOPT_POST,  1 );                                                                        
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
					curl_setopt($ch, CURLOPT_POSTFIELDS , http_build_query($final_args) );
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					
					break;
					
				case 'GET' :
					$query = http_build_query($final_args, '', '&');
					curl_setopt($ch, CURLOPT_URL,  $action['destination'] . '?' . $query );  
					//curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  'GET' );                                                                        
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					
					break;
					
				case 'JSON-POST' :
					
					$data_string = json_encode( $final_args );                                                                                   
					                                                                   
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
						'Content-Type: application/json',                                                                                
						'Content-Length: ' . strlen($data_string))                                                                       
					);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					 
					
					
					break;
			}
			
			/* execute and gret response */
			$return = curl_exec($ch);
			if(!$return) {
				$action_encoded = json_encode($action) ;
				inbound_record_log(  'Action Event - Sending Data to External URL - Error' , '<h2>Settings</h2><pre>'.'Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch).'</pre> <h2>Settings</h2><pre>'. $action_encoded.'</pre> <h2>Arguments</h2><pre>' . json_encode($arguments) . '</pre>', $action['rule_id'] , 'action_event' );
			} 
			
			curl_close($ch);
			
			$action_encoded = json_encode($action) ;
			inbound_record_log(  'Action Event - Sending Data to External URL' , '<h2>Server Response</h2><pre>'. $return .'</pre> <h2>Settings</h2><pre>'. $action_encoded .'</pre> <h2>Arguments</h2><pre>' . json_encode($arguments) . '</pre>', $action['rule_id'] , 'action_event' );
			
		}

	}

	/* Load Action */
	$Inbound_Automation_Action_Relay_Data = new Inbound_Automation_Action_Relay_Data();

}
