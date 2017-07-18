<?php
/**
 * Class for storing and retrieving call to action variation data
 * @package CTA
 * @subpackage Variations
 */

if ( ! class_exists( 'CTA_Variations' ) ) {

	class CTA_Variations {

		public function __construct() {
			self::load_hooks();
		}

		public static function load_hooks() {

			/* Delete variation listener */
			add_action( 'admin_init', array(__CLASS__, 'add_listeners'));

			/* Builds variation object on CTA save */
			add_action( 'save_post', array(__CLASS__, 'save_variation_object_data'));

			/* Filter to add variation id to end of meta key */
			add_filter( 'wp_cta_prepare_input_id', array(__CLASS__, 'prepare_input_id'));

			/* Appends variation id to given url */
			add_filter( 'wp_cta_customizer_customizer_link', array(__CLASS__, 'append_variation_id_to_url'));
			add_filter( 'post_type_link', array(__CLASS__, 'append_variation_id_to_url'));

		}

		/* Listens for commands */
		public static function add_listeners() {

			/* Start session */
			if(session_id() == '') {
				session_start();
			}

			if (!isset($_GET['post'])){
				return;
			}

			$cta_id = intval($_GET['post']);
			$post = get_post(intval($cta_id));

			if (!isset($post)||$post->post_type!='wp-call-to-action') {
				return;
			}

			$variation_id = (isset($_GET['vid'])) ? intval($_GET['vid']) : 0;

			/* Let's disable autosave */
			if(!defined('AUTOSAVE_INTERVAL')) {
				define('AUTOSAVE_INTERVAL', 86400);
			}

			/* Listen for delete variation command */
			if ( isset($_GET['ab-action']) && $_GET['ab-action']=='delete-variation' ) {
				self::delete_variation( $cta_id	, $variation_id );
			}

			/* Listen for pause variation command */
			if ( isset($_GET['ab-action']) && $_GET['ab-action']=='pause-variation' ) {
				self::pause_variation( $cta_id	, $variation_id );
			}

			/* Listen for play variation command */
			if ( isset($_GET['ab-action']) && $_GET['ab-action']=='play-variation' ) {
				self::play_variation( $cta_id , $variation_id );
			}

			/* Listen for clone  variation command */
			if ( isset($_GET['ab-action']) && $_GET['ab-action']=='play-variation' ) {
				self::play_variation( $cta_id , $variation_id );
			}

			/* Listen for new variation / clone variation command & localize the correct data */
			(isset($_GET['new-variation'])&&$_GET['new-variation']==1) ? $new_variation = 1 : $new_variation = 0;

			$current_variation_id = CTA_Variations::get_current_variation_id();

			/* enqueue and localize scripts */
			//wp_enqueue_style('wp-cta-ab-testing-admin-css', WP_CTA_URLPATH . 'assets/css/admin-ab-testing.css');
			//wp_enqueue_script('wp-cta-ab-testing-admin-js', WP_CTA_URLPATH . 'assets/js/admin/admin.post-edit-ab-testing.js', array( 'jquery'));
			//wp_localize_script( 'wp-cta-ab-testing-admin-js', 'variation', array( 'pid' => $_GET['post'], 'vid' => $current_variation_id	, 'new_variation' => $new_variation	, 'variations'=> $variations	));

		}

		/**
		* Deletes variation for	a call to action
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid id of variation to delete
		*
		*/
		public static function delete_variation( $cta_id	,	$vid ) {

			/* Update variations meta object */
			$variations = self::get_variations( $cta_id );
			unset($variations[$vid]);


			self::update_variations( $cta_id, $variations );

			/* Delete meta values associated with variation */
			$variation_meta = self::get_variation_meta ( $cta_id, $vid );


			foreach ($variation_meta as $key => $value) {
				delete_post_meta( $cta_id, $key );
			}

			/* Get first array variation and set it as open variation */
			reset($variations);
			$vid = key($variations);
			$_SESSION[ $cta_id . '-variation-id'] = $vid;
		}

		/**
		* Pauses variation for a call to action
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid id of variation to delete
		*
		*/
		public static function pause_variation( $cta_id	,	$vid ) {

			/* Update variations meta object */
			$variations = self::get_variations( $cta_id );
			$variations[ $vid ]['status'] = 'paused';

			self::update_variations( $cta_id, $variations );
		}

		/**
		* Activations variation for a call to action
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid id of variation to play
		*
		*/
		public static function play_variation( $cta_id	,	$vid ) {

			/* Update variations meta object */
			$variations = self::get_variations( $cta_id );
			$variations[ $vid ]['status'] = 'active';

			self::update_variations( $cta_id, $variations );
		}

		/**
		* Sets the variation status to a custom status
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid id of variation to delete
		* @param STRING $status custom status
		*
		*/
		public static function set_variation_status( $cta_id, $vid, $status = 'play' ) {

			/* Update variations meta object */
			$variations = self::get_variations( $cta_id );
			$variations[ $vid ]['status'] = $status ;

			self::update_variations( $cta_id, $variations );
		}

		/* Updates variation object data on post save
		*
		* @param INT $cta_id of call to action id
		*
		*/
		public static function save_variation_object_data( $cta_id )
		{

			global $post;

			if ( wp_is_post_revision( $cta_id ) ) {
				return;
			}

			if (	!isset($_POST['post_type']) || $_POST['post_type'] != 'wp-call-to-action' ) {
				return;
			}

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
				return;
			}

			$current_variation = (isset($_POST['wp-cta-variation-id'])) ? intval($_POST['wp-cta-variation-id']) : '0';
			$variations = self::get_variations( $cta_id );

			/* Set current variation status */
			$variations[ $current_variation ]['status'] = apply_filters( 'wp_cta_save_variation_status',  sanitize_text_field($_POST['wp-cta-variation-status'][ $current_variation ] ) );

			/* Update variation meta object */
			self::update_variations( $cta_id, $variations );

		}



		/**
		* Returns array of variation data given a call to action id
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid id of specific variation
		*
		* @returns ARRAY of variation data
		*/
		public static function get_variations( $cta_id	, $vid = null ) {

			$variations = json_decode( get_post_meta( $cta_id ,'wp-cta-variations', true), true );
			$variations = ( is_array( $variations ) ) ? $variations : array( 0 => array( 'status' => 'active'));

			/* unset unneeded	variation data if $vid is specified */
			if ($vid !== null ) {
				foreach ($variations as $id => $variation) {
					if ($id != $vid ) {
						unset($variations[ $id ]);
					}
				}
			}

			return $variations;
		}


		/**
		* Returns the status of a variation given cta_id and vid
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid variation id of call to action
		*
		* @returns STRING status
		*/
		public static function get_variation_status( $cta_id, $vid = null ) {

			if ( $vid === null ) {
				$vid = CTA_Variations::get_current_variation_id();
			}

			$variations = CTA_Variations::get_variations( $cta_id );
			$status = ( is_array( $variations ) && isset($variations[ $vid ][ 'status' ]) ) ? $variations[ $vid ][ 'status' ] : 'active';

			return $status;
		}

		/**
		* Returns the permalink of a variation given cta_id and vid
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid variation id of call to action
		*
		* @returns STRING permalink
		*/
		public static function get_variation_permalink( $cta_id, $vid = null ) {

			if ( $vid === null ) {
				$vid = CTA_Variations::get_current_variation_id();
			}

			$permalink = get_permalink($cta_id);

			return add_query_arg( array('wp-cta-variation-id'=> $vid ), $permalink ) ;
		}

		/**
		* Updates 'wp-cta-variations' meta key with json object
		*
		* @param INT $cta_id id of call to action
		* @param variations ARRAY of variation data
		*
		*/
		public static function update_variations ( $cta_id, $variations ) {

			update_post_meta( $cta_id, 'wp-cta-variations', json_encode( $variations, JSON_FORCE_OBJECT ));

		}


		/**
		* Returns array of variation specific meta data
		*
		* @param INT $cta_id ID of call to action
		* @param INT $vid ID of variation belonging to call to action
		*
		* @return ARRAY $meta array of variation meta data
		*/
		public static function get_variation_meta ( $cta_id, $vid ) {
			$meta = array();

			$cta_meta = get_post_meta( $cta_id );

			$suffix = '-'.$vid;
			$len = strlen($suffix);

			foreach ($cta_meta as $key=>$value)
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
		* @param INT $cta_id id of call to action
		* @param INT $vid variation id of call to action variation, will attempt to autodetect if left as null
		*
		* @return STRING $notes variation notes.
		*/
		public static function get_variation_notes ( $cta_id, $vid = null) {

			if ( $vid === null ) {
				$vid = CTA_Variations::get_current_variation_id();
			}

			$notes = get_post_meta( $cta_id, 'wp-cta-variation-notes-' . $vid, true );

			return $notes;

		}

		/**
		* Gets the call to action variation custom css
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid variation id of call to action variation, will attempt to autodetect if left as null
		*
		* @return STRING $custom_css.
		*/
		public static function get_variation_custom_css ( $cta_id, $vid = null) {

			if ( $vid === null ) {
				$vid = CTA_Variations::get_current_variation_id();
			}

			$custom_css = get_post_meta( $cta_id, 'wp-cta-custom-css-' . $vid, true );

			return $custom_css;

		}

		/**
		* Gets the call to action variation custom js
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid variation id of call to action variation, will attempt to autodetect if left as null
		*
		* @return STRING $custom_js.
		*/
		public static function get_variation_custom_js ( $cta_id, $vid = null) {

			if ( $vid === null ) {
				$vid = CTA_Variations::get_current_variation_id();
			}

			$custom_js = get_post_meta( $cta_id, 'wp-cta-custom-js-' . $vid, true );

			return $custom_js;

		}

		/* Adds variation id onto base meta key
		*
		* @param id STRING of meta key to store data into for given setting
		* @param INT $vid id of variation belonging to call to action, will attempt to autodetect if left as null
		*
		* @returns STRING of meta key appended with variation id
		*/
		public static function prepare_input_id( $id, $vid = null ) {

			if ( $vid === null ) {
				$vid =	CTA_Variations::get_current_variation_id();
			}

			return $id . '-' . $vid;
		}

		/*
		* Gets the current variation id
		*
		* @returns INT of variation id
		*/
		public static function get_current_variation_id() {
			global $post;

			if (isset($_REQUEST['wp-cta-variation-id'])){
				return intval($_REQUEST['wp-cta-variation-id']);
			}

			(isset($post->ID)) ? $post_id = $post->ID : $post_id = intval($_REQUEST['post']);

			if (isset($_SESSION[ $post_id . '-variation-id'])) {
				return intval($_SESSION[ $post_id . '-variation-id']);
			}


			return 0;
		}

		/*
		* Gets the next available variation id
		*
		* @returns INT of variation id
		*/
		public static function get_next_available_variation_id( $cta_id ) {

			$variations = CTA_Variations::get_variations( $cta_id );
			$array_variations = $variations;

			end($array_variations);

			$last_variation_id = key($array_variations);

			return $last_variation_id + 1;
		}

		/*
		* Gets id of template given cta id
		*
		* @param INT $cta_id of call to action
		* @param INT $vid of variation id
		*
		* @returns STRING id of selected template
		*/
		public static function get_current_template( $cta_id, $vid = null ) {

			if ( $vid === null ) {
				$vid =	CTA_Variations::get_current_variation_id();
			}

			$template = get_post_meta( $cta_id, 'wp-cta-selected-template-' . $vid, true);
			if (!$template) {
				return 'blank-template';
			} else {
				return $template;
			}
		}

		/**
		 * Returns templat thumbnail
		 */
		public static function get_template_thumbnail( $template ) {
			if (file_exists(WP_CTA_PATH.'templates/'.$template."/thumbnail.png")) {
				$thumbnail = WP_CTA_URLPATH.'templates/'.$template."/thumbnail.png";
			} else if (file_exists(WP_CTA_UPLOADS_PATH.$template."/thumbnail.png")) {
				$thumbnail = WP_CTA_UPLOADS_URLPATH.$template."/thumbnail.png";
			} else if (file_exists(WP_CTA_THEME_TEMPLATES_PATH.$template."/thumbnail.png")) {
				$thumbnail = WP_CTA_THEME_TEMPLATES_URLPATH.$template."/thumbnail.png";
			}

			return $thumbnail;
		}


		/**
		* Get Screenshot URL for Call to Action preview. If local environment show template thumbnail.
		*
		* @param INT $cta_id id if of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		* @return STRING url of preview
		*/
		public static function get_screenshot_url( $cta_id, $vid = null) {

			if ( $vid === null ) {
				$vid =	CTA_Variations::get_current_variation_id();
			}

			$template = CTA_Variations::get_current_template( $cta_id, $vid);

			if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {

				if (file_exists(WP_CTA_UPLOADS_URLPATH . 'templates/' . $template . '/thumbnail.png')) {
					$screenshot = WP_CTA_UPLOADS_URLPATH . 'templates/' . $template . '/thumbnail.png';
				}
				else {
					$screenshot = WP_CTA_URLPATH . 'templates/' . $template . '/thumbnail.png';
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

			if ( !isset($post) || $post->post_type != 'wp-call-to-action' ) {
				return $link;
			}

			$current_variation_id =	CTA_Variations::get_current_variation_id();


			$link = add_query_arg( array('wp-cta-variation-id' => $current_variation_id ), $link );

			return $link;
		}

		/**
		* Discovers which alphabetic letter should be associated with a given cta's variation id.
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		* @return STRING alphebit letter.
		*/
		public static function vid_to_letter( $cta_id, $vid ) {
			$variations = CTA_Variations::get_variations( $cta_id );

			$i = 0;
			foreach ($variations as $key => $variation ) {
				if ( $vid == $key ) {
					break;
				}
				$i++;
			}

			$alphabet = array(
				__( 'A', 'inbound-pro' ),
				__( 'B', 'inbound-pro' ),
				__( 'C', 'inbound-pro' ),
				__( 'D', 'inbound-pro' ),
				__( 'E', 'inbound-pro' ),
				__( 'F', 'inbound-pro' ),
				__( 'G', 'inbound-pro' ),
				__( 'H', 'inbound-pro' ),
				__( 'I', 'inbound-pro' ),
				__( 'J', 'inbound-pro' ),
				__( 'K', 'inbound-pro' ),
				__( 'L', 'inbound-pro' ),
				__( 'M', 'inbound-pro' ),
				__( 'N', 'inbound-pro' ),
				__( 'O', 'inbound-pro' ),
				__( 'P', 'inbound-pro' ),
				__( 'Q', 'inbound-pro' ),
				__( 'R', 'inbound-pro' ),
				__( 'S', 'inbound-pro' ),
				__( 'T', 'inbound-pro' ),
				__( 'U', 'inbound-pro' ),
				__( 'V', 'inbound-pro' ),
				__( 'W', 'inbound-pro' ),
				__( 'X', 'inbound-pro' ),
				__( 'Y', 'inbound-pro' ),
				__( 'Z', 'inbound-pro' )
			);

			if (isset($alphabet[$i])){
				return $alphabet[$i];
			}
		}

		/**
		* Returns impression for given cta and variation id
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		* @return INT impression count
		*/
		public static function get_impressions( $cta_id, $vid ) {

			$impressions = get_post_meta( $cta_id ,'wp-cta-ab-variation-impressions-'.$vid, true);

			if (!is_numeric($impressions)) {
				$impressions = 0;
			}

			return $impressions;
		}


		/**
		* Manually sets conversion count for given cta id and variation id
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		*/
		public static function set_impression_count( $cta_id, $vid, $count) {

			update_post_meta( $cta_id, 'wp-cta-ab-variation-impressions-'.$vid, $count);
		}

		/**
		* Returns total conversions for given cta and variation id
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		* @return INT total conversions count
		*/
		public static function get_conversions( $cta_id, $vid ) {

			/* get clicks */
			$clicks = self::get_clicks( $cta_id, $vid);

			/* get form submissions */
			$submissions = self::get_form_submissions( $cta_id, $vid );

			$conversions = $clicks + $submissions;

			return $conversions;
		}

		/**
		* Returns clicks for given cta and variation id
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		* @return INT click count
		*/
		public static function get_clicks( $cta_id, $vid ) {

			/* get clicks */
			$clicks = Inbound_Events::get_cta_clicks_by( 'cta_id', array( 'cta_id' => $cta_id , 'variation_id' => $vid));

			$clicks = count($clicks);

			if (!is_numeric($clicks)) {
				$clicks = 0;
			}

			return $clicks;
		}/**
		* Returns clicks for given cta and variation id
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		* @return INT form submission count
		*/
		public static function get_form_submissions( $cta_id, $vid ) {

			/* get form submissions */
			$submissions = Inbound_Events::get_form_submissions_by( 'cta_id', array( 'cta_id' => $cta_id , 'variation_id' => $vid));

			$submissions = count($submissions);

			if (!is_numeric($submissions)) {
				$submissions = 0;
			}

			return $submissions;
		}

		/**
		* Returns conversion rate for given cta and variation id
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		* @return INT conversion rate
		*/
		public static function get_conversion_rate( $cta_id, $vid ) {

			$impressions = CTA_Variations::get_impressions( $cta_id, $vid );
			$conversions = CTA_Variations::get_conversions( $cta_id, $vid );

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
		* Manually sets conversion count for given cta id and variation id
		*
		* @param INT $cta_id id of call to action
		* @param INT $vid id of variation belonging to call to action
		*
		*/
		public static function set_conversion_count(	$cta_id, $vid, $count) {

			update_post_meta( $cta_id, 'wp-cta-ab-variation-conversions-'.$vid, $count);
		}

	}

	$GLOBALS['CTA_Variations'] = new CTA_Variations();

}