<?php

if ( ! class_exists( 'Landing_Pages_Variations' ) ) {

	class Landing_Pages_Variations {

		public function __construct() {		
			self::load_hooks();		
		}

		public static function load_hooks() {
			
		}

		
		/**
		* Deletes variation for	a call to action
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid id of variation to delete
		*
		*/
		public static function delete_variation( $landing_page_id	,	$vid ) {
			
			$variations = Landing_Pages_Variations::get_variations( $landing_page_id );
            
			/* unset variation */
			if( ($key = array_search($vid, $variations)) !== false) {
					unset($variations[$key]);
			}

			
			/* set next variation to be open */
			$current_variation_id = current($variations);
			$_SESSION['lp_ab_test_open_variation'] = $current_variation_id;			
			$_GET['lp-variation-id'] = $current_variation_id;
			
			/* update variations */
			Landing_Pages_Variations::update_variations( $landing_page_id , $variations );


			if (isset($_GET['lp-variation-id']) && $_GET['lp-variation-id'] > 0 ) {
				$suffix = '-'.$_GET['lp-variation-id'];
				$len = strlen($suffix);
			} else {
				$suffix = '';
				$len = strlen($suffix);
			}

			//delete each meta value associated with variation
			global $wpdb;
			$data   =   array();
			$wpdb->query("
				SELECT `meta_key`, `meta_value`
				FROM $wpdb->postmeta
				WHERE `post_id` = ".$landing_page_id."
			");

			foreach($wpdb->last_result as $k => $v){
				$data[$v->meta_key] =   $v->meta_value;
			};
			
			//echo $len;exit;
			foreach ($data as $key=>$value)
			{
				if (substr($key,-$len)==$suffix)
				{
					delete_post_meta( $landing_page_id , $key, $value);
				}
			}

			
		}
		
		/**
		* Pauses variation for a call to action
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid id of variation to delete
		*
		*/
		public static function pause_variation( $landing_page_id	,	$vid ) {
			
		}
		
		/**
		* Activations variation for a call to action
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid id of variation to play
		*
		*/
		public static function play_variation( $landing_page_id	,	$vid ) {
		
		}
		
		/**
		* Sets the variation status to a custom status
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid id of variation to delete
		* @param STRING $status custom status 
		*
		*/
		public static function set_variation_status( $landing_page_id , $vid , $status = 'play' ) {
			
		
		}


		/**
		* Returns array of variation data given a landing page id
		*
		* @param INT $landing_page_id id of landing page
		* @param INT $vid id of specific variation
		*
		* @returns ARRAY of variation data
		*/
		public static function get_variations( $landing_page_id	, $vid = null ) {
			
			$variations = get_post_meta(  $landing_page_id , 'lp-ab-variations' , true );
			$variations = explode( ',' , $variations );
			$variations = array_filter( $variations , 'is_numeric' );

			return $variations;
		}
				

		/**
		* Returns the status of a variation given landing_page_id and vid
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid variation id of call to action
		*
		* @returns STRING status
		*/
		public static function get_variation_status( $landing_page_id , $vid = null ) {
			
		
		}
		
		
		/**
		*  Set Variant Marker - When automated emails are sent we still want to rotate variations if they exist. When an email is bein sent to one lead, batching needs a consistant way to rotate variations
		*  @param INT $landing_page_id
		*  @return INT $next_variant_marker
		*/
		public static function get_next_variant_marker( $landing_page_id ) {
			
			
			
		}
		
		/**
		*  Updates variation marker (used for single sends)
		*  @param INT $landing_page_id
		*  @param INT $variation_marker
		*/
		public static function set_variation_marker( $landing_page_id , $variation_marker ) {
			
		}
			
		/**
		* Returns the permalink of a variation given landing_page_id and vid
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid variation id of call to action
		*
		* @returns STRING permalink
		*/
		public static function get_variation_permalink( $landing_page_id , $vid = null ) {
			
			if ( $vid === null ) {
				$vid = Landing_Pages_Variations::get_current_variation_id();
			}
			
			$permalink = get_permalink($landing_page_id);
		
			return add_query_arg( array('inbvid'=> $vid ) , $permalink ) ;
		}
		
		/**
		* Updates 'inbound-email-variations' meta key with json object
		*
		* @param INT $landing_page_id id of call to action
		* @param variations ARRAY of variation data
		*
		*/
		public static function update_variations ( $landing_page_id , $variations ) {
			
			if ( !is_array($variations) ) {
				$variations = implode(',' , $variations);
			}
			
			update_post_meta( $landing_page_id , 'lp-ab-variations', $variations );

		}
		
		
		/**
		* Returns array of variation specific meta data 
		*
		* @param INT $landing_page_id ID of call to action
		* @param INT $vid ID of variation belonging to call to action
		*
		* @return ARRAY $meta array of variation meta data
		*/
		public static function get_variation_meta ( $landing_page_id , $vid ) {
			$meta = array();
			
			$inbound_email_meta = get_post_meta( $landing_page_id );

			$suffix = '-'.$vid;
			$len = strlen($suffix);
			
			foreach ($inbound_email_meta as $key=>$value)
			{
				if (substr($key,-$len)==$suffix)
				{
					$meta[$key] = $value[0];
				}
			}
			
			return $meta;
		}
		
		/**
		* Gets the call to action variation notes
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid variation id of call to action variation, will attempt to autodetect if left as null
		*
		* @return STRING $notes variation notes.
		*/
		public static function get_variation_notes ( $landing_page_id , $vid = null) {
			
			if ( $vid === null ) {
				$vid = Landing_Pages_Variations::get_current_variation_id();
			}
			
			$notes = get_post_meta( $landing_page_id , 'inbound-mailer-variation-notes-' . $vid , true );
			
			return $notes;
			
		}
		
		/**
		* Gets the call to action variation custom css
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid variation id of call to action variation, will attempt to autodetect if left as null
		*
		* @return STRING $custom_css.
		*/
		public static function get_variation_custom_css ( $landing_page_id , $vid = null) {
			
			if ( $vid === null ) {
				$vid = Landing_Pages_Variations::get_current_variation_id();
			}
			
			$custom_css = get_post_meta( $landing_page_id , 'inbound-mailer-custom-css-' . $vid , true );
			
			return $custom_css;
			
		}	
		
		/**
		* Gets the call to action variation custom js
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid variation id of call to action variation, will attempt to autodetect if left as null
		*
		* @return STRING $custom_js.
		*/
		public static function get_variation_custom_js ( $landing_page_id , $vid = null) {
			
			if ( $vid === null ) {
				$vid = Landing_Pages_Variations::get_current_variation_id();
			}
			
			$custom_js = get_post_meta( $landing_page_id , 'inbound-mailer-custom-js-' . $vid , true );
			
			return $custom_js;
			
		}
		
		/* Adds variation id onto base meta key 
		*
		* @param id STRING of meta key to store data into for given setting
		* @param INT $vid id of variation belonging to call to action, will attempt to autodetect if left as null
		*
		* @returns STRING of meta key appended with variation id
		*/
		public static function prepare_input_id( $id , $vid = null ) {
			
			if ( $vid === null ) {
				$vid =	Landing_Pages_Variations::get_current_variation_id();
			}
		
			return $id . '-' . $vid;
		}
		
		/* 
		* Gets the current variation id
		*
		* @returns INT of variation id
		*/
		public static function get_current_variation_id() {
			if ( isset($_GET['ab-action']) &&is_admin()) {
				return $_SESSION['lp_ab_test_open_variation'];
			}
			
			if (!isset($_SESSION['lp_ab_test_open_variation'])&&!isset($_REQUEST['lp-variation-id'])) {
				$current_variation_id = 0;
			}
			
			if (isset($_REQUEST['lp-variation-id'])) {
				$_SESSION['lp_ab_test_open_variation'] = $_REQUEST['lp-variation-id'];
				$current_variation_id = $_REQUEST['lp-variation-id'];
			}
			
			if (isset($_GET['message'])&&$_GET['message']==1&&isset( $_SESSION['lp_ab_test_open_variation'] )) {
				$current_variation_id = $_SESSION['lp_ab_test_open_variation'];
				//echo "here:".$_SESSION['lp_ab_test_open_variation'];
			}
			
			if (isset($_GET['ab-action'])&&$_GET['ab-action']=='delete-variation') {
				$current_variation_id = 0;
				$_SESSION['lp_ab_test_open_variation'] = 0;
			}
			
			if (!isset($current_variation_id)) {
				$current_variation_id = 0 ;
			}
			
			return $current_variation_id;
		}
		
		/* 
		* Gets the next available variation id
		*
		* @returns INT of variation id
		*/
		public static function get_next_available_variation_id( $landing_page_id ) {
			
			$variations = Landing_Pages_Variations::get_variations( $landing_page_id );		
			$array_variations = $variations;

			end($array_variations);
			
			$last_variation_id = key($array_variations);
			
			return $last_variation_id + 1;
		}
		
		/* 
		* Gets string id of template given email id 
		*
		* @param INT $landing_page_id of call to action
		* @param INT $vid of variation id
		*
		* @returns STRING id of selected template
		*/	
		public static function get_current_template( $landing_page_id , $vid = null ) {
		
			if ( $vid === null ) {
				$vid =	Landing_Pages_Variations::get_current_variation_id();
			}

			$settings = Inbound_Email_Meta::get_settings( $landing_page_id );
			$variations = ( isset($settings['variations']) ) ? $settings['variations'] : null;
			
			$template = ( isset( $variations[ $vid ][ 'selected_template' ] ) ) ? $variations[ $vid ][ 'selected_template' ] : 'simple-responsive';
			
			/* If new variation use historic template id */
			if ( isset($_GET['new-variation'] ) ) {
				$vid = key($variations);
				$template = ( isset( $variations[ $vid ][ 'selected_template' ] ) ) ? $variations[ $vid ][ 'selected_template' ] : 'simple-responsive';	
			}
				
			return $template;
			
		}
		
		/**
		* Get Screenshot URL for Call to Action preview. If local environment show template thumbnail.
		*
		* @param INT $landing_page_id id if of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		* @return STRING url of preview
		*/
		public static function get_screenshot_url( $landing_page_id , $vid = null) {
			
			if ( $vid === null ) {
				$vid =	Landing_Pages_Variations::get_current_variation_id();
			}
			
			$template = Landing_Pages_Variations::get_current_template( $landing_page_id , $vid);
			
			if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
			
				if (file_exists(INBOUND_EMAIL_UPLOADS_URLPATH . 'templates/' . $template . '/thumbnail.png')) {
					$screenshot = INBOUND_EMAIL_UPLOADS_URLPATH . 'templates/' . $template . '/thumbnail.png';
				}
				else {
					$screenshot = INBOUND_EMAIL_URLPATH . 'templates/' . $template . '/thumbnail.png';
				}

			} else {
				$screenshot = 'http://s.wordpress.com/mshots/v1/' . urlencode(esc_url($permalink)) . '?w=140';
			}
			
			return $screenshot;
		}
		
		/**
		* Appends current variation id onto a URL
		*
		* @param link STRING URL that param will be appended onto
		* 
		*
		* @return STRING modified URL.
		*/
		public static function append_variation_id_to_url( $link ) {
			global $post;
			
			if ( !isset($post) || $post->post_type != 'inbound-email' ) {
				return $link;
			}
			
			$current_variation_id =	Landing_Pages_Variations::get_current_variation_id();

		
			$link = add_query_arg( array('inbvid' => $current_variation_id ) , $link );

			return $link;
		}
		
		/**
		* Discovers which alphabetic letter should be associated with a given cta's variation id.
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		* @return STRING alphebit letter.
		*/
		public static function vid_to_letter( $landing_page_id , $vid ) {
			$variations = Landing_Pages_Variations::get_variations( $landing_page_id );
			
			$i = 0;
			foreach ($variations as $key => $variation ) {
				if ( $vid == $key ) {
					break;
				}
				$i++;
			}
			
			$alphabet = array(	
				__( 'A' , 'inbound-email' ),
				__( 'B' , 'inbound-email' ),
				__( 'C' , 'inbound-email' ),
				__( 'D' , 'inbound-email' ),
				__( 'E' , 'inbound-email' ),
				__( 'F' , 'inbound-email' ),
				__( 'G' , 'inbound-email' ),
				__( 'H' , 'inbound-email' ),
				__( 'I' , 'inbound-email' ),
				__( 'J' , 'inbound-email' ),
				__( 'K' , 'inbound-email' ),
				__( 'L' , 'inbound-email' ),
				__( 'M' , 'inbound-email' ),
				__( 'N' , 'inbound-email' ),
				__( 'O' , 'inbound-email' ),
				__( 'P' , 'inbound-email' ),
				__( 'Q' , 'inbound-email' ),
				__( 'R' , 'inbound-email' ),
				__( 'S' , 'inbound-email' ),
				__( 'T' , 'inbound-email' ),
				__( 'U' , 'inbound-email' ),
				__( 'V' , 'inbound-email' ),
				__( 'W' , 'inbound-email' ),
				__( 'X' , 'inbound-email' ),
				__( 'Y' , 'inbound-email' ),
				__( 'Z' , 'inbound-email' )
			);

			if (isset($alphabet[$i])){
				return $alphabet[$i];
			}
		}

		/**
		* Returns impression for given cta and variation id
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		* @return INT impression count
		*/
		public static function get_impressions( $landing_page_id , $vid ) {
		
			$impressions = get_post_meta( $landing_page_id ,'inbound-mailer-ab-variation-impressions-'.$vid , true);
			
			if (!is_numeric($impressions)) {
				$impressions = 0;
			} 
			
			return $impressions;
		}

		/**
		* Increments impression count for given cta and variation id
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		*/
		public static function record_impression( $landing_page_id , $vid ) {
		
			$impressions = get_post_meta( $landing_page_id ,'inbound-mailer-ab-variation-impressions-'.$vid, true);
			
			if (!is_numeric($impressions)) {
				$impressions = 1;
			} else {
				$impressions++;
			}

			update_post_meta( $landing_page_id , 'inbound-mailer-ab-variation-impressions-'.$vid , $impressions);
		}
		
		/**
		* Manually sets conversion count for given cta id and variation id
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		*/
		public static function set_impression_count( $landing_page_id , $vid , $count) {

			update_post_meta( $landing_page_id , 'inbound-mailer-ab-variation-impressions-'.$vid , $count);
		}
		
		/**
		* Returns impression for given cta and variation id
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		* @return INT impression count
		*/
		public static function get_conversions( $landing_page_id , $vid ) {
		
			$conversions = get_post_meta( $landing_page_id ,'inbound-mailer-ab-variation-conversions-'.$vid, true);
			
			if (!is_numeric($conversions)) {
				$conversions = 0;
			} 
			
			return $conversions;
		}

		/**
		* Returns conversion rate for given cta and variation id
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		* @return INT conversion rate
		*/
		public static function get_conversion_rate( $landing_page_id , $vid ) {
			
			$impressions = Landing_Pages_Variations::get_impressions( $landing_page_id , $vid );
			$conversions = Landing_Pages_Variations::get_conversions( $landing_page_id , $vid );
			
			if ($impressions>0) {
				$conversion_rate = $conversions / $impressions;
				$conversion_rate_number = $conversion_rate * 100;
				$conversion_rate_number = round($conversion_rate_number, 2);
				$conversion_rate = $conversion_rate_number;
			} else {
				$conversion_rate = 0;
			}
			
			return $conversion_rate;
		}

		/**
		* Increments conversion count for given cta id and variation id
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		*/
		public static function record_conversion(	$landing_page_id , $vid ) {
			
			$conversions = get_post_meta( $landing_page_id , 'inbound-mailer-ab-variation-conversions-' . $vid , true);
			
			if (!is_numeric($conversions)) {
				$conversions = 1;
			} else {
				$conversions++;
			}		

			update_post_meta( $landing_page_id , 'inbound-mailer-ab-variation-conversions-'.$vid , $conversions);
		}
		
		/**
		* Manually sets conversion count for given cta id and variation id
		*
		* @param INT $landing_page_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		*/
		public static function set_conversion_count(	$landing_page_id , $vid , $count) {

			update_post_meta( $landing_page_id , 'inbound-mailer-ab-variation-conversions-'.$vid , $count);
		}
		
	}

	$GLOBALS['Landing_Pages_Variations'] = new Landing_Pages_Variations();

}