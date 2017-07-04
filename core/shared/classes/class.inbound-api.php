<?php
/**
 * Inbound Leads API
 *
 * This class provides a front-facing JSON API that makes it possible to
 * query data within the Leads database
 *
 *
 * @package     Leads
 * @subpackage  Classes/Leads API
 *
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if (!class_exists('Inbound_API')) {

	/**
	 * Inbound_API Class
	 *
	 * Renders API returns as a JSON array
	 *
	 */
	class Inbound_API {

		/**
		 * API Version
		 */
		const VERSION = '1';

		/**
		 * Pretty Print?
		 *
		 * @var bool
		 * @since 1.5
		 */
		static $pretty_print = false;

		/**
		 * Log API requests?
		 *
		 * @var bool
		 * @access static
		 * @since 1.5
		 */
		static $log_requests = true;

		/**
		 * Is this a valid request?
		 *
		 * @var bool
		 * @access static
		 * @since 1.5
		 */
		static $is_valid_request = false;

		/**
		 * User ID Performing the API Request
		 *
		 * @var int
		 * @access static
		 * @since 1.5.1
		 */
		static $user_id = 0;

		/**
		 * Instance of EDD Stats class
		 *
		 * @var object
		 * @access static
		 */
		static $stats;

		/**
		 * Response data to return
		 *
		 * @var array
		 * @access static
		 */
		static $data = array();

		/**
		 *
		 * @var bool
		 * @access static
		 */
		static $override = true;

		/**
		 *
		 * @var integer
		 * @access static
		 */
		static $results_per_page = 50;

		/**
		 *
		 * @var string
		 * @access static
		 */
		static $tracking_endpoint = 'inbound' ;

		/**
		 * Initialize the Inbound Leads API
		 *
		 */
		public function __construct() {
			/* Create endpoint listeners */
			add_action( 'init',                     array(__CLASS__, 'add_endpoint'     ) );

			/* Build Query Router */
			add_action( 'template_redirect',        array(__CLASS__, 'process_api_query'    ), -1 );
			add_action( 'template_redirect',        array(__CLASS__, 'process_tracked_link'    ), -1 );

			/* Listen for & execute api key commands */
			add_action( 'inbound_process_api_key',  array(__CLASS__, 'process_api_key'  ) );

			/* Determine if JSON_PRETTY_PRINT is available */
			self::$pretty_print = defined( 'JSON_PRETTY_PRINT' ) ? JSON_PRETTY_PRINT : null;

			/* Allow API request logging to be turned off */
			self::$log_requests = apply_filters( 'inbound_api_log_requests', self::$log_requests );

		}

		/**
		 * Registers a new rewrite endpoint for accessing the API
		 *
		 * @access public
		 * @param array $rewrite_rules WordPress Rewrite Rules
		 *
		 */
		public static function add_endpoint( $rewrite_rules ) {

			/* for API calls */
			add_rewrite_endpoint( 'inbound-api', EP_ALL );

			/* for click event tracking */
			self::$tracking_endpoint = apply_filters( 'inbound_event_endpoint', self::$tracking_endpoint );
			add_rewrite_endpoint( 'inbound', EP_ALL );
		}

		/**
		 * Validate the API request
		 *
		 * Checks for the user's public key and token against the secret key
		 *
		 * @access private
		 * @global object $wp_query WordPress Query
		 * @uses Inbound_API::get_user()
		 * @uses Inbound_API::invalid_key()
		 * @uses Inbound_API::invalid_auth()
		 * @return void
		 */
		private static function validate_request() {
			global $wp_query;

			self::$override = false;

			/* Check for presence of keys and tokens */
			if ( empty( $_REQUEST['token'] ) || empty( $_REQUEST['key'] ) ) {
				self::missing_auth();
			}

			/* Retrieve the user by public API key and ensure they exist */
			if ( ! ( $user = self::get_user( $_REQUEST['key'] ) ) ) {
				self::invalid_key();
			} else {
				$token  = urldecode( $_REQUEST['token'] );
				$secret = get_user_meta( $user, 'inbound_user_secret_key', true );
				$public = urldecode( $_REQUEST['key'] );

				if ( hash( 'md5', $secret . $public ) === $token ) {
					self::$is_valid_request = true;
				} else {
					self::invalid_auth();
				}
			}

		}

		/**
		 * Retrieve the user ID based on the public key provided
		 *
		 * @access public
		 * @global object $wpdb Used to query the database using the WordPress Database API
		 *
		 * @param string $key Public Key
		 *
		 * @return bool if user ID is found, false otherwise
		 */
		public static function get_user( $key = '' ) {
			global $wpdb, $wp_query;

			if( empty( $key ) )
				$key = urldecode( $_REQUEST['key'] );

			if ( empty( $key ) ) {
				return false;
			}

			$user = get_transient( md5( 'inbound_api_user_' . $key ) );

			if ( false === $user ) {
				$user = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'inbound_user_public_key' AND meta_value = %s LIMIT 1", $key ) );
				set_transient( md5( 'inbound_api_user_' . $key ), $user, DAY_IN_SECONDS );
			}

			if ( $user != NULL ) {
				self::$user_id = $user;
				return $user;
			}

			return false;
		}

		/**
		 * Get tracked link arguments given a storage token
		 * @param $token
		 * @return array|mixed
		 */
		public static function get_args_from_token( $token ) {
			global $wpdb;

			/* Pull record from database */
			$table_name = $wpdb->prefix . "inbound_tracked_links";
			$profiles = $wpdb->get_results("SELECT * FROM {$table_name} where `token` = '{$token}' ;");

			if (empty( $profiles )) {
				return array();
			}

			/* Get first result & prepare args */
			$profile = $profiles[0];
			$args = unserialize($profile->args);

			return $args;
		}

		/**
		 * Displays a missing authentication error if all the parameters aren't
		 * provided
		 *
		 * @access private
		 * @uses Inbound_API::output()
		 */
		private static function missing_auth() {
			$error['error'] = __( 'You must specify both a token and API key!', 'inbound-pro' );

			self::$data = $error;
			self::output( 401 );
		}

		/**
		 * Displays an authentication failed error if the user failed to provide valid
		 * credentials
		 *
		 * @access private
		 * @uses Inbound_API::output()
		 * @return void
		 */
		private static function invalid_auth() {
			$error['error'] = __( 'Your request could not be authenticated! (check your token)', 'inbound-pro' );

			self::$data = $error;
			self::output( 401 );
		}

		/**
		 * Displays an invalid API key error if the API key provided couldn't be
		 * validated
		 *
		 * @access private
		 * @uses Inbound_API::output()
		 * @return void
		 */
		private static function invalid_key() {
			$error['error'] = __( 'Invalid API key!', 'inbound-pro' );

			self::$data = $error;
			self::output( 401 );
		}

		/**
		 * Validates parameter type
		 *
		 * @access private
		 * @uses Inbound_API::output()
		 * @param MIXED $value value to measure
		 * @param $accepted value type desired
		 * @return $value or die();
		 */
		private static function validate_parameter( $value, $key, $accepted ) {

			if (gettype($value) == $accepted ) {
				return $value;
			}

			$error['error'] = sprintf( __( 'Invalid parameter provided. Expecting a %1$s for \'%2$s\' while a field type with %3$s was provided', 'inbound-pro' ), $accepted, $key, gettype($value)) ;

			self::$data = $error;
			self::output( 401 );
		}

		/**
		 * Displays an invalid parameter error
		 *
		 * @access private
		 * @uses Inbound_API::output()
		 * @return void
		 */
		private static function invalid_parameter( $key, $accepted, $provided ) {
			$error['error'] = sprintf( __( 'Invalid parameter provided. Expecting %1$s for %2$s the %3$s was provided', 'inbound-pro' ), $accepted, $key, $provided) ;

			self::$data = $error;
			self::output( 401 );
		}

		/**
		 * Displays generic WP_Error
		 *
		 * @access private
		 * @uses Inbound_API::output()
		 * @return void
		 */
		private static function throw_wp_error( $error_obj ) {
			$error['error'] = $error_obj->get_error_message();

			self::$data = $error;
			self::output( 401 );
		}


		/**
		 * Listens for the API and then processes the API requests
		 *
		 * @access public
		 * @global $wp_query
		 * @return void
		 */
		public static function process_api_query() {
			global $wp_query;

			/* Check for inbound-api var. Get out if not present */
			if ( ! isset( $wp_query->query_vars['inbound-api'] ) ) {
				return;
			}

			/* Check for a valid user and set errors if necessary */
			self::validate_request();

			/* Only proceed if no errors have been noted */
			if( ! self::$is_valid_request ) {
				return;
			}

			if( ! defined( 'INBOUND_DOING_API' ) ) {
				define('INBOUND_DOING_API', true );
			}

			/* Determine the kind of query */
			$query_type = self::get_query_type();

			$data = array();

			switch( $query_type ) :

				case 'v1/leads' :
					/* get leads */
					$data = self::leads_get();
					BREAK;

				case 'v1/leads/add' :
					/* Add leads */
					$data = self::leads_add();
					BREAK;

				case 'v1/leads/modify' :
					/* Update lead records */
					$data = self::leads_update();
					BREAK;

				case 'v1/leads/delete' :
					/* delete leads */
					$data = self::leads_delete();
					BREAK;

				case 'v1/lists' :
					/* get lead lists */
					$data = self::lists_get();
					BREAK;

				case 'v1/lists/add' :
					/* add lead lists */
					$data = self::lists_add();
					BREAK;

				case 'v1/lists/modify' :
					/* add lead lists */
					$data = self::lists_update();
					BREAK;

				case 'v1/lists/delete' :
					/* delete lead lists */
					$data = self::lists_delete();
					BREAK;

				case 'v1/field-map' :
					/* add lead lists */
					$data = self::fieldmap_get();
					BREAK;

				case 'v1/analytics/track-link' :
					/* delete lead lists */
					$data = self::analytics_track_links();
					BREAK;

			endswitch;

			/* Allow extensions to setup their own return data */
			self::$data = apply_filters( 'inbound_api_output_data', $data, $query_type );


			/* Send out data to the output function */
			self::output();
		}

		/**
		 * Determines the kind of query requested and also ensures it is a valid query
		 *
		 * @global $wp_query
		 * @return string $query type of query to run
		 */
		public static function get_query_type() {
			global $wp_query;

			/* Whitelist our query options */
			$accepted = apply_filters( 'inbound_api_valid_query_types', array(
				'v1/leads',
				'v1/leads/add',
				'v1/leads/modify',
				'v1/leads/delete',
				'v1/lists',
				'v1/lists/add',
				'v1/lists/modify',
				'v1/lists/delete',
				'v1/field-map',
				'v1/analytics/track-link',
			) );

			$query = isset( $wp_query->query_vars['inbound-api'] ) ? $wp_query->query_vars['inbound-api'] : null;

			/* Make sure our query is valid */
			if ( ! in_array( $query, $accepted ) ) {
				$error['error'] = __( 'Invalid endpoint: ' . $query, 'inbound-pro' );

				self::$data = $error;
				self::output();
			}

			return $query;
		}

		/**
		 * Get page number
		 *
		 * @access private
		 * @global $wp_query
		 * @return int $_REQUEST['page'] if page number returned (default: 1)
		 */
		public static function get_paged() {
			global $wp_query;

			return isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1;
		}

		/**
		 * Get number of results per page to return
		 * uses REQUEST but falls back on self::$results_per_page
		 *
		 * @access private
		 * @return int
		 */
		public static function get_results_per_page() {
			return isset( $_REQUEST['results_per_page'] ) ? $_REQUEST['results_per_page'] : self::$results_per_page;
		}

		/**
		 * Sets up the dates used to retrieve leads
		 *
		 * @access public
		 * @since 1.5.1
		 * @param array $args Arguments to override defaults
		 * @return array $dates
		 */
		public static function get_dates( $args = array() ) {
			$dates = array();

			$defaults = array(
				'type'      => '',
				'product'   => null,
				'date'      => null,
				'startdate' => null,
				'enddate'   => null
			);

			$args = wp_parse_args( $args, $defaults );

			$current_time = current_time( 'timestamp' );

			if ( 'range' === $args['date'] ) {
				$startdate          = strtotime( $args['startdate'] );
				$enddate            = strtotime( $args['enddate'] );
				$dates['day_start'] = date( 'd', $startdate );
				$dates['day_end'] 	= date( 'd', $enddate );
				$dates['m_start'] 	= date( 'n', $startdate );
				$dates['m_end'] 	= date( 'n', $enddate );
				$dates['year'] 		= date( 'Y', $startdate );
				$dates['year_end'] 	= date( 'Y', $enddate );
			} else {
				/* Modify dates based on predefined ranges */
				switch ( $args['date'] ) :

					case 'this_month' :
						$dates['day'] 	    = null;
						$dates['m_start'] 	= date( 'n', $current_time );
						$dates['m_end']		= date( 'n', $current_time );
						$dates['year']		= date( 'Y', $current_time );
						break;

					case 'last_month' :
						$dates['day'] 	  = null;
						$dates['m_start'] = date( 'n', $current_time ) == 1 ? 12 : date( 'n', $current_time ) - 1;
						$dates['m_end']	  = $dates['m_start'];
						$dates['year']    = date( 'n', $current_time ) == 1 ? date( 'Y', $current_time ) - 1 : date( 'Y', $current_time );
						break;

					case 'today' :
						$dates['day']		= date( 'd', $current_time );
						$dates['m_start'] 	= date( 'n', $current_time );
						$dates['m_end']		= date( 'n', $current_time );
						$dates['year']		= date( 'Y', $current_time );
						break;

					case 'yesterday' :
						$month              = date( 'n', $current_time ) == 1 && date( 'd', $current_time ) == 1 ? 12 : date( 'n', $current_time );
						$days_in_month      = cal_days_in_month( CAL_GREGORIAN, $month, date( 'Y', $current_time ) );
						$yesterday          = date( 'd', $current_time ) == 1 ? $days_in_month : date( 'd', $current_time ) - 1;
						$dates['day']		= $yesterday;
						$dates['m_start'] 	= $month;
						$dates['m_end'] 	= $month;
						$dates['year']		= $month == 1 && date( 'd', $current_time ) == 1 ? date( 'Y', $current_time ) - 1 : date( 'Y', $current_time );
						break;

					case 'this_quarter' :
						$month_now = date( 'n', $current_time );

						$dates['day'] 	        = null;

						if ( $month_now <= 3 ) {

							$dates['m_start'] 	= 1;
							$dates['m_end']		= 3;
							$dates['year']		= date( 'Y', $current_time );

						} else if ( $month_now <= 6 ) {

							$dates['m_start'] 	= 4;
							$dates['m_end']		= 6;
							$dates['year']		= date( 'Y', $current_time );

						} else if ( $month_now <= 9 ) {

							$dates['m_start'] 	= 7;
							$dates['m_end']		= 9;
							$dates['year']		= date( 'Y', $current_time );

						} else {

							$dates['m_start'] 	= 10;
							$dates['m_end']		= 12;
							$dates['year']		= date( 'Y', $current_time );

						}
						break;

					case 'last_quarter' :
						$month_now = date( 'n', $current_time );

						$dates['day'] 	        = null;

						if ( $month_now <= 3 ) {

							$dates['m_start'] 	= 10;
							$dates['m_end']		= 12;
							$dates['year']		= date( 'Y', $current_time ) - 1; /* Previous year */

						} else if ( $month_now <= 6 ) {

							$dates['m_start'] 	= 1;
							$dates['m_end']		= 3;
							$dates['year']		= date( 'Y', $current_time );

						} else if ( $month_now <= 9 ) {

							$dates['m_start'] 	= 4;
							$dates['m_end']		= 6;
							$dates['year']		= date( 'Y', $current_time );

						} else {

							$dates['m_start'] 	= 7;
							$dates['m_end']		= 9;
							$dates['year']		= date( 'Y', $current_time );

						}
						break;

					case 'this_year' :
						$dates['day'] 	    = null;
						$dates['m_start'] 	= null;
						$dates['m_end']		= null;
						$dates['year']		= date( 'Y', $current_time );
						break;

					case 'last_year' :
						$dates['day'] 	    = null;
						$dates['m_start'] 	= null;
						$dates['m_end']		= null;
						$dates['year']		= date( 'Y', $current_time ) - 1;
						break;

				endswitch;
			}

			/**
			 * Returns the filters for the dates used to retrieve earnings/sales
			 *
			 * @param object $dates The dates used for retrieving earnings/sales
			 */

			return apply_filters( 'inbound_api_stat_dates', $dates );
		}


		/**
		 * Retrieve the output data
		 *
		 * @access public
		 * @return array
		 */
		public static function get_output() {
			return self::$data;
		}

		/**
		 * Output Query in either JSON/XML. The query data is outputted as JSON
		 * by default
		 *
		 * @global $wp_query
		 *
		 * @param int $status_code
		 */
		public static function output( $status_code = 200 ) {
			global $wp_query;

			$format = apply_filters('inbound_api_output_format', 'json');

			status_header( $status_code );

			do_action( 'inbound_api_output_before', self::$data );

			switch ( $format ) :

				case 'json' :

					header( 'Content-Type: application/json' );
					if ( ! empty( self::$pretty_print ) ) {
						echo json_encode( self::$data, self::$pretty_print );
					} else {
						echo json_encode( self::$data );
					}

					break;


				default :

					/* Allow other formats to be added via extensions */
					do_action( 'inbound_api_output_' . $format, self::$data, $this );

					break;

			endswitch;

			do_action( 'inbound_api_output_after', self::$data );

			die();
		}

		/**
		 * Retrieve the user's token
		 *
		 * @access private
		 * @param int $user_id
		 * @return string
		 */
		private static function get_token( $user_id = 0 ) {
			$user = get_userdata( $user_id );
			return hash( 'md5', $user->inbound_user_secret_key . $user->inbound_user_public_key );
		}

		/**
		 *  Query designed to return leads based on conditions defined by user.
		 *
		 *  @access public
		 *  @param ARRAY $params key/value pairs that will direct the building of WP_Query, optional
		 */
		public static function leads_get( $params = array() ) {

			/* Merge POST & GET & @param vars into array variable */
			$params = array_merge( $params, $_REQUEST );

			/* prepare WP_Query defaults */
			$args = self::leads_prepare_defaults( $params );

			/* Prepare WP_Query arguments with tax_query rules */
			$args = self::leads_prepare_tax_query( $args, $params );

			/* Prepare WP_Query arguments with meta_query rules */
			if (isset($params['meta_query'])) {
				$args['meta_query'] = self::validate_parameter( $params['meta_query'], 'meta_query',  'array'  );
			}

			/* Run Query */
			$results = new WP_Query( $args );

			/* If no results let them know */
			if (!$results) {
				$message['message'] = __( 'No leads were found given this query.', 'inbound-pro' ) ;
				self::$data = $message;
				self::output( 401 );
			}

			/* Secret mod to return results without additional data */
			if ( isset($params['fields']) && $params['fields'] == 'ids' ) {
				return $results;
			}

			/* Get meta data for each result */
			$results = self::prepare_lead_results( $results );

			return $results;
		}


		/**
		 *  Sets the API defaults for the /leads/(get) endpoint
		 *
		 *  @access public
		 *  @param ARRAY $params
		 *  @returns ARRAY $params
		 */
		public static function leads_prepare_defaults( $params ) {

			$args['s'] = (isset($params['email'])) ? self::validate_parameter( $params['email'], 'email', 'string'  ) : '';
			$args['p'] = (isset($params['ID'])) ? self::validate_parameter( intval($params['ID']), 'ID', 'integer'  ) : '';
			$args['posts_per_page'] = isset($params['results_per_page']) ? $params['results_per_page'] :  self::get_results_per_page();
			$args['paged'] = (isset($params['page'])) ? self::validate_parameter( intval($params['page']), 'page', 'integer' ) : 1 ;
			$args['orderby'] = (isset($params['orderby'])) ?  $params['orderby'] : 'date' ;

			if (isset($params['fields'])) {
				$args['fields'] = $params['fields'];
			}


			if ($args['orderby'] != 'rand') {
				$args['order'] = (isset($params['order'])) ? self::validate_parameter( $params['order'], 'order_by', 'string' ) : 'DESC' ;
			}

			$args['post_type'] = 'wp-lead';

			return $args;
		}

		/**
		 *  Builds a tax_query ARRAY from included parameters if applicable.
		 *  Used for tag searches and lead list searches.
		 *
		 *  @param ARRAY $args arguments for WP_Query
		 *  @param ARRAY $params includes param key/value pairs submitted to the api
		 *  @returns ARRAY $args
		 */
		public static function leads_prepare_tax_query( $args, $params ) {

			/* if tax_query ovverride rules are manually set by user then use them  */
			if (isset($params['tax_query'])) {
				$args['tax_query'] = $params['tax_query'];
				return $args;
			}

			if ( isset($params['include_lists']) || isset($params['exclude_lists']) || isset($params['include_tags']) || isset($params['exclude_tags'])) {
				$args['tax_query']['relation'] = 'AND';
			}

			if ( isset($params['include_lists']) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'wplead_list_category',
					'field'    => 'term_id',
					'terms'    => self::validate_parameter( $params['include_lists'], 'include_lists', 'array'  ),
					'operator' => 'IN',
				);
			}

			if ( isset($params['exclude_lists']) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'wplead_list_category',
					'field'    => 'term_id',
					'terms'    => self::validate_parameter( $params['exclude_lists'], 'exclude_lists', 'array'  ),
					'operator' => 'NOT IN',
				);
			}

			if ( isset($params['include_tags']) ) {
				$tags = self::get_tag_ids(self::validate_parameter( $params['include_tags'], 'include_tags', 'array'  ));
				$args['tax_query'][] = array(
					'taxonomy' => 'lead-tags',
					'field'    => 'term_id',
					'terms'    => $tags,
					'operator' => 'AND'
				);
			}

			if ( isset($params['exclude_tags']) ) {
				$tags = self::get_tag_ids(self::validate_parameter( $params['exclude_tags'], 'exclude_tags', 'array'  ));
				$args['tax_query'][] = array(
					'taxonomy' => 'lead-tags',
					'field'    => 'ID',
					'terms'    => $tags,
					'operator' => 'NOT IN'
				);
			}


			return $args;
		}


		/**
		 *  Get tag ids from given names
		 *  @param ARRAY $tags contains array of tag names
		 *  @returns ARRAY $tag_ids contains array of tag term ids
		 */
		public static function get_tag_ids( $tags ) {
			$tag_ids = array();

			foreach ($tags as $name) {
				$tag = get_term_by( 'name', $name, 'lead-tags' );

				if ($tag) {
					$tag_ids[] = $tag->term_id;
				}
			}

			return $tag_ids;
		}



		/**
		 *  Converts WP_Query object into array and imports additional lead data
		 *
		 *  @param OBJECT $results WP_Query results
		 *  @return ARRAY $leads updated array of results
		 */
		public static function prepare_lead_results( $results ) {

			if ( !$results->have_posts() ) {
				return null;
			}

			$leads = array();
			$leads['results_count'] = $results->found_posts;
			$leads['results_per_page'] = self::get_results_per_page();
			$leads['max_pages'] = $results->max_num_pages;

			while ( $results->have_posts() ) : $results->the_post();

				$ID = $results->post->ID;

				/* set ID */
				$leads['results'][ $ID ]['ID'] = $ID;

				/* set lead lists */
				$lists = get_the_terms( $ID, 'wplead_list_category' );
				$leads['results'][ $ID ]['lists'] = $lists;

				/* set lead tags */
				$tags = get_the_terms( $ID, 'lead-tags' );
				$leads['results'][ $ID ]['tags'] = $tags;

				/* set lead meta data */
				$meta_data = get_post_custom($ID);
				$leads['results'][ $ID ]['meta_data'] = $meta_data;

				/* set the lead sources */
				$leads['results'][ $ID ]['sources'] = Inbound_Events::get_lead_sources( $ID );

			endwhile;

			return $leads;
		}

		/**
		 *  Adds a lead to the wp-lead custom post type
		 *  @param ARRAY $params key/value pairs that will direct the building of WP_Query, optional
		 *  @global OBJECT $Inbound_Leads Inbound_Leads
		 */
		public static function leads_add( $params = array() ) {


			/* Merge POST & GET & @param vars into array variable */
			$params = array_merge( $params, $_REQUEST );

			/* Account for Zapier */
			if (!isset($params['meta_data']) && file_get_contents('php://input')) {
				$params = json_decode(file_get_contents('php://input'), true);
			}

			/* check if meta_data is http query  */
			if (!is_array($params['meta_data']) && strstr($params['meta_data'] , '=')) {
				parse_str($params['meta_data'],$params['meta_data']);
			}

			/* Make sure our meta_data field is setup correctly */
			self::validate_parameter( $params['meta_data'], 'meta_data',  'array'  );

			$args = array(
				'post_title' =>  self::validate_parameter( $params['meta_data']['wpleads_email_address'], 'meta_data[wpleads_email_address]',  'string'  ), /* validate email address */
				'post_status' => 'publish',
				'post_type' => 'wp-lead'
			);

			/* check if lead exists */
			$already_exists = self::leads_get( array( 'email' => $params['meta_data']['wpleads_email_address'] ) );

			if ( $already_exists ) {
				$error['error'] = __( 'Lead already exists.', 'inbound-pro' ) ;

				self::$data = $error;
				self::output( 401 );
			}

			/* Insert Lead */
			$lead_id = wp_insert_post( $args, true );

			/* Check for error and send message back if contains error */
			if( is_wp_error($lead_id) ) {
				self::throw_wp_error( $lead_id );
			}

			/* determine last name from first name */
			if (isset($params['meta_data']['wpleads_first_name']) && !isset($params['meta_data']['wpleads_last_name']))  {
				$split = explode(' ' , $params['meta_data']['wpleads_first_name']);
				$params['meta_data']['wpleads_first_name'] = ($split[0]) ? $split[0] : $params['meta_data']['wpleads_first_name'];
				$params['meta_data']['wpleads_last_name'] = (isset($split[1])) ? $split[1] : '';
			}

			/* Add meta data to lead record */
			foreach ($params['meta_data'] as $key => $value ) {
				update_post_meta( $lead_id, $key, $value );
			}

			/* Add lead to lists */
			if (isset($params['lead_lists']) && !is_array($params['lead_lists']) ){
				$params['lead_lists'] = explode(',',$params['lead_lists']);
			}

			if (isset($params['lead_lists']) && self::validate_parameter( $params['lead_lists'], 'lead_lists', 'array' ) ) {
				foreach ( $params['lead_lists'] as $list_id ) {
					Inbound_Leads::add_lead_to_list( $lead_id, $list_id );
				}
			}

			/* Add tag to leads */
			if (isset($params['tags']) && !is_array($params['tags']) && $params['tags']){
				$params['tags'] = explode(',',$params['tags']);
			}

			if (isset($params['tags']) && self::validate_parameter( $params['tags'], 'tags', 'array' ) ) {
				foreach ( $params['tags'] as $tag ) {
					Inbound_Leads::add_tag_to_lead( $lead_id, $tag );
				}
			}

			return self::leads_get( array( 'ID' => $lead_id ) );

		}

		/**
		 *  Updates a Lead profile
		 *  @param ARRAY $params key/value pairs that will assist database queries
		 *  @global OBJECT $Inbound_Leads Inbound_Leads
		 */
		public static function leads_update( $params = array() ) {


			/* Merge POST & GET & @param vars into array variable */
			$params = array_merge( $params, $_REQUEST );

			/* Validate lead ID param */
			if (isset( $params['ID'] ) ) {
				self::validate_parameter( intval($params['ID']), 'ID',  'integer'  );
			}

			/* if email is set get lead ID by email */
			if ( isset( $params['email'] ) ) {
				$result = self::leads_get( array( 'email' => $params['email'] ) );
				if ( isset($result['results']) ) {
					$params['ID'] = key($result['results']);
				}
			}

			/* ID must be set by this point */
			if ( !isset( $params['ID'] ) ) {
				$error['error'] = __( 'Valid ID or email address not set.', 'inbound-pro' ) ;
				self::$data = $error;
				self::output( 401 );
			}

			/* Update email address in post_title if email is being updated */
			if ( isset( $params['meta_data']['wpleads_email_address'] ) ) {
				$args = array(
					'ID' => $params['ID'],
					'post_title' => self::validate_parameter( $params['meta_data']['wpleads_email_address'], 'meta_data[wpleads_email_address]',  'string'  ),
					'post_status' => 'publish',
					'post_type' => 'wp-lead'
				);
				wp_update_post( $args );
			}

			/* Update meta data */
			if ( isset( $params['meta_data']) ) {

				/* Make sure our meta_data field is setup correctly */
				self::validate_parameter( $params['meta_data'], 'meta_data',  'array'  );

				/* Loop through meta data fields and update key/value pairs */
				foreach ($params['meta_data'] as $key => $value ) {
					update_post_meta( $params['ID'], $key, $value );
				}
			}

			/* Add lead to lists  */
			if ( isset( $params['add_to_lists']) ) {

				/* Make sure our add_to_lists field is setup correctly */
				self::validate_parameter( $params['add_to_lists'], 'add_to_lists',  'array'  );

				/* Loop through list ids and add */
				foreach ($params['add_to_lists'] as $list_id ) {
					Inbound_Leads::add_lead_to_list( $params['ID'], $list_id );
				}
			}

			/* Remove lead from lists */
			if ( isset( $params['remove_from_lists']) ) {

				/* Make sure our remove_from_lists field is setup correctly */
				self::validate_parameter( $params['remove_from_lists'], 'remove_from_lists',  'array'  );

				/* Loop through list ids and remove */
				foreach ($params['remove_from_lists'] as $list_id ) {
					Inbound_Leads::remove_lead_from_list( $params['ID'], $list_id );
				}
			}

			/* add tags to lead */
			if ( isset( $params['add_tags']) ) {

				/* Make sure our add_tags field is setup correctly */
				self::validate_parameter( $params['add_tags'], 'add_tags',  'array'  );

				/* Loop through tags and add */
				foreach ($params['add_tags'] as $tag ) {
					Inbound_Leads::add_tag_to_lead( $params['ID'], $tag );
				}
			}

			/* remove tags from lead */
			if ( isset( $params['remove_tags']) ) {

				/* Make sure our remove_tags field is setup correctly */
				self::validate_parameter( $params['remove_tags'], 'remove_tags',  'array'  );

				/* Loop through tags and remove */
				foreach ($params['remove_tags'] as $tag ) {
					Inbound_Leads::remove_tag_from_lead( $params['ID'], $tag );
				}
			}

			return self::leads_get( array( 'ID' => $params['ID'] ) );
		}

		/**
		 *  Permanently deletes a lead profile
		 *  @param ARRAY $params key/value pairs
		 */
		public static function leads_delete( $params = array() ) {

			/* Merge POST & GET & @param vars into array variable */
			$params = array_merge( $params, $_REQUEST );

			/* Validate lead ID param */
			if ( isset( $params['ID'] ) ) {
				self::validate_parameter( intval($params['ID']), 'ID',  'integer'  );
			}
			/* if email is set get lead ID by email */
			else if ( isset( $params['email'] ) ) {
				$params['ID'] = self::leads_get_id_from_email( $params['email'] );

				self::validate_parameter( $params['email'], 'email',  'string'  );

				/* perform lead lookup by email */
				$result = self::leads_get( array( 'email' => $params['email'] ) );

				/* get lead id if lookup successful */
				if ( isset($result['results']) ) {
					$params['ID'] = key($result['results']);
				}
			}

			/* ID must be set by this point */
			if ( !isset( $params['ID'] ) ) {
				$error['error'] = __( 'Valid ID or email address not set.', 'inbound-pro' ) ;
				self::$data = $error;
				self::output( 401 );
			}

			/* Delete lead */
			wp_delete_post(  $params['ID'], true );

			return array (
				'message' => __( 'Lead successfully deleted', 'inbound-pro' ),
				'ID' => $params['ID']
			);
		}

		/**
		 *  Get lead ID from lead email address
		 *  @param STRING $email lead email address
		 *  @return INT $id
		 */
		public static function leads_get_id_from_email( $email ) {

			self::validate_parameter( $email, 'email',  'string'  );

			/* perform lead lookup by email */
			$result = self::leads_get( array( 'email' => $email ) );

			/* get lead id if lookup successful */
			if ( isset($result['results']) ) {
				return key($result['results']);
			} else {
				return null;
			}

		}

		/**
		 *  Gets all lead lists
		 *  @global OBJECT $Inbound_Leads Inbound_Leads
		 */
		public static function lists_get() {


			return Inbound_Leads::get_lead_lists_as_array();
		}

		/**
		 *  Create a new lead list
		 *  @global OBJECT $Inbound_Leads class Inbound_Leads
		 */
		public static function lists_add( $params = array() ) {


			/* Merge POST & GET & @param vars into array variable */
			$params = array_merge( $params, $_REQUEST );

			self::validate_parameter( $params['name'], 'name',  'string'  );

			/* Prepare list description */
			if (isset($params['description'])) {
				self::validate_parameter( $params['description'], 'description',  'string'  );
			} else {
				$params['description'] = '';
			}

			/* Prepare parent */
			if (isset($params['parent'])) {
				self::validate_parameter( $params['parent'], 'parent',  'string'  );
			} else {
				$params['parent'] = '';
			}

			return Inbound_Leads::create_lead_list( $params );
		}

		/**
		 *  Updates a list's data
		 *  @global OBJECT $Inbound_Leads class Inbound_Leads
		 *  @return ARRAY
		 */
		public static function lists_update( $params = array() ) {


			/* Merge POST & GET & @param vars into array variable */
			$params = array_merge( $params, $_REQUEST );

			/* Get list id */
			if (isset($params['id'])) {
				self::validate_parameter( intval($params['id']), 'id',  'integer'  );
			} else {
				$error['error'] = __( 'This endpoint requires that the \'id\' be set.', 'inbound-pro' ) ;
				self::$data = $error;
				self::output( 401 );
			}

			/* Prepare new list name if available */
			if (isset($params['name'])) {
				self::validate_parameter( $params['name'], 'name',  'string'  );
			} else {
				$params['name'] = '';
			}

			/* Prepare new list description if available */
			if (isset($params['description'])) {
				self::validate_parameter( $params['description'], 'description',  'string'  );
			} else {
				$params['description'] = '';
			}

			/* Prepare new parent if available*/
			if (isset($params['parent'])) {
				self::validate_parameter( intval($params['parent']), 'parent',  'integer'  );
			} else {
				$params['parent'] = 0;
			}

			return Inbound_Leads::update_lead_list( $params );
		}

		/**
		 *  Deletes a lead list
		 */
		public static function lists_delete( $params = array() ) {


			/* Merge POST & GET & @param vars into array variable */
			$params = array_merge( $params, $_REQUEST );

			/* Get list id */
			if (isset($params['id'])) {
				self::validate_parameter( intval($params['id']), 'id',  'integer'  );
			} else {
				$error['error'] = __( 'This endpoint requires that the \'id\' be set.', 'inbound-pro' ) ;
				self::$data = $error;
				self::output( 401 );
			}

			return Inbound_Leads::delete_lead_list( $params['id'] );
		}

		/**
		 *  Gets an array of mappable lead meta keys with their labels
		 */
		public static function fieldmap_get() {
			$lead_fields = Leads_Field_Map::build_map_array();
			array_shift($lead_fields);
			return $lead_fields;
		}

		/**
		 *  Generates random token
		 *  @param length
		 */
		public static function generate_token( $min = 7, $max = 11 ) {
			$length = mt_rand( $min, $max );
			return substr(str_shuffle("0123456789iloveinboundnow"), 0, $length);
		}

		/**
		 *  Stores tracked link data into wp_inbound_tracked_links table
		 *  @param ARRAY $args passed arguments
		 */
		public static function analytics_get_tracking_code( $args = array() ) {
			global $wpdb;

			$table_name = $wpdb->prefix . "inbound_tracked_links";

			/* check args to see if token already exists */
			$results = $wpdb->get_results("SELECT * FROM $table_name WHERE args = '".serialize( $args )."' LIMIT 1", ARRAY_A );
			if ($results) {
				return $results[0]['token'];
			}

			$token = self::generate_token();

			$wpdb->insert(
				$table_name,
				array(
					'token' => $token,
					'args' => serialize( $args )
				)
			);

			/* return tracked link */
			return $token;
		}

		/**
		 *  Generate tracked link
		 */
		public static function analytics_track_links( $params = array() ) {

			/* Merge POST & GET & @param vars into array variable */
			$params = array_merge( $params, $_REQUEST );

			/* lead email or lead id required */
			if ( !isset( $params['id'] ) && !isset( $params['email_id']) && !isset( $params['cta_id']) && !isset( $params['page_id']) ) {
				$error['error'] = __( 'This endpoint requires either the \'id\' or the \'email\' or the \'cta_id\'or the \'page_id\' parameter be set.', 'inbound-pro' ) ;
				self::$data = $error;
				self::output( 401 );
			}

			/* a link to mask is required */
			if ( !isset( $params['url'] ) && !isset( $params['url'] ) ) {
				$error['error'] = __( 'This endpoint requires the \'url\' parameter be set.', 'inbound-pro' ) ;
				self::$data = $error;
				self::output( 401 );
			}

			/* a tracking_id is required */
			if ( !isset( $params['tracking_id'] ) ) {
				$error['error'] = __( 'This endpoint requires the \'tracking_id\' parameter be set.', 'inbound-pro' ) ;
				self::$data = $error;
				self::output( 401 );
			}

			$args = $params;

			unset($args['token']);
			unset($args['key']);


			/* Set custom_data */
			if (isset($params['custom_data'])) {
				self::validate_parameter( $params['custom_data'], 'custom_data',  'array'  );

				$args = array_merge( $args, $params['custom_data'] );
			}

			/* get token args */
			$token = self::analytics_get_tracking_code( $args );

			/* get tracked link */
			$tracked_link =  get_site_url( get_current_blog_id(), self::$tracking_endpoint . '/' . $token );

			return array( 'url' => $tracked_link );
		}


		/**
		 * Listens for the tracked links and update lead event profile
		 *
		 * @access public
		 * @global $wp_query
		 * @return void
		 */
		public static function process_tracked_link() {
			global $wp_query, $wpdb;

			/* Check for inbound-api var. Get out if not present */
			if ( ! isset( $wp_query->query_vars[ self::$tracking_endpoint ] ) && ( isset($_SERVER["REQUEST_URI"]) && !strstr($_SERVER["REQUEST_URI"], self::$tracking_endpoint.'/' ) )) {
				return;
			}

			/* discover token */
			$parts = explode( self::$tracking_endpoint.'/',  $_SERVER["REQUEST_URI"] );
			$token = ( isset($wp_query->query_vars[ self::$tracking_endpoint ]) ) ? $wp_query->query_vars[ self::$tracking_endpoint ] : $parts[1] ;

			/* Pull record from database */
			$args = self::get_args_from_token($token);

			/* If no results exist send user to homepage */
			if (!$args) {
				/* redirect to  url */
				header('Location: '. get_site_url() );
				exit;
			}

			/* get lead id from cookie if it exists */
			$lead_id_cookie = (isset($_COOKIE['wp_lead_id'])) ? $_COOKIE['wp_lead_id'] : 0;

			/* if lead_id is set then apply it to 'id' */
			$args['id'] = (isset( $args['lead_id'] )  && $args['lead_id']) ? $args['lead_id'] : $args['id'];

			/* if no lead_id so far then fall back on cookie value */
			$args['id'] = (isset( $args['id'])  && $args['id'] ) ? $args['id'] : $lead_id_cookie;

			/* cookie lead id if availabled and not cookied */
			if (!isset($_COOKIE['wp_lead_id']) && $args['id'] ) {
				setcookie('wp_lead_id' , $args['id'] , time() + (20 * 365 * 24 * 60 * 60), '/' );
			}

			/* process extra lead events */
			if ($args['id']) {
				/* Add lead to lists */
				if (isset($args['add_lists']) && self::validate_parameter($args['add_lists'], 'add_lists', 'array')) {
					foreach ($args['add_lists'] as $list_id) {
						Inbound_Leads::add_lead_to_list($args['id'], $list_id);
					}
				}

				/* Remove lead from lists */
				if (isset($args['remove_lists']) && self::validate_parameter($args['remove_lists'], 'remove_lists', 'array')) {

					foreach ($args['remove_lists'] as $list_id) {
						Inbound_Leads::remove_lead_from_list($args['id'], $list_id);
					}
				}

				/* Add tag to leads */
				if (isset($args['add_tags']) && self::validate_parameter($args['add_tags'], 'add_tags', 'array')) {
					foreach ($args['add_tags'] as $tag) {
						Inbound_Leads::add_tag_to_lead($args['id'], $tag);
					}
				}

				/* Remvoe tags from leads */
				if (isset($args['remove_tags']) && self::validate_parameter($args['remove_tags'], 'remove_tags', 'array')) {
					foreach ($args['remove_tags'] as $tag) {
						Inbound_Leads::remove_tag_from_lead($args['id'], $tag);
					}
				}

			}

			/* check for known bots and ignore */
			if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'])) {
				return;
			}

			/* Process tracked link extras */
			do_action('inbound_track_link', $args);

			/* redirect to  url */
			header('Location: '. $args['url'] );
			exit;
		}
	}

	new Inbound_API();

}

