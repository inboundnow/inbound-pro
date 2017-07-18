<?php

/**
 * Class for preparing and monitoring tracked links inside post type content, referred to as 'Content Clicks'
 * @package     InboundPro
 * @subpackage  Tracking
 */

class Inbound_Tracking {

	/**
	 *  Initiate class
	 */
	public function __construct() {
		self::load_hooks();
	}

	/**
	 *  Load hooks and filters
	 */
	public static function load_hooks() {

		/* modify localizated data to disable tracking */
		add_filter( 'inbound_analytics_localized_data' , array( __CLASS__ , 'filter_lead_tracking_rules' ) );

		/* prevent landing page conversions */
		add_filter( 'inbound_analytics_stop_track' , array( __CLASS__ , 'filter_conversions' ) );

		/* Load Google Charting API & Inbound Analytics Styling CSS*/
		add_action('admin_enqueue_scripts', array(__CLASS__, 'load_admin_scripts'));

		/* listen for tracked links in content and add tracking */
		add_filter( 'the_content' , array( __CLASS__ , 'prepare_tracked_links') , 110 , 1 );
		add_filter( 'get_the_content' , array( __CLASS__ , 'prepare_tracked_links') , 110 , 1 );
		add_filter( 'inbound_track_links' , array( __CLASS__ , 'prepare_tracked_links') , 110 , 1);

		/* track masked cta links */
		add_action( 'inbound_track_link', array(__CLASS__, 'track_link'));

		/* add quick stat  */
		add_action('wpleads_display_quick_stat', array(__CLASS__, 'display_quick_stat_content_clicks') , 15 );

	}

	/**
	 * Loads Google charting scripts
	 */
	public static function load_admin_scripts() {

		global $post;


		if (!isset($post) || strstr($post->post_type, 'inbound-')) {
			return;
		}

		wp_register_script('inbound-track-link', INBOUND_PRO_URLPATH . 'assets/js/admin/track-link.js');
		wp_enqueue_script('inbound-track-link');

	}

	/**
	 *  Hooks into Inbound Analytics and enables/disabled lead tracking based on IP or is admin.
	 */
	public static function filter_lead_tracking_rules( $inbound_localized_data ) {
		global $inbound_settings;

		$disable = false;

		$ignore_admin = ( isset( $inbound_settings['inbound-analytics-rules']['admin-tracking'] ) && $inbound_settings['inbound-analytics-rules']['admin-tracking'] == 'off' ) ? true : false;

		/* determine if user is admin and admin filtering is on */
		if ( current_user_can( 'manage_options' ) && $ignore_admin ) {
			$disable = true;
		}

		/* determine if visitor's IP address is in blacklist */
		$ip_addresses = ( isset( $inbound_settings['inbound-analytics-rules']['ip-addresses']) ) ? $inbound_settings['inbound-analytics-rules']['ip-addresses'] : array();
		if ( in_array( $inbound_localized_data['ip_address'] , $ip_addresses ) ) {
			$disable = true;
		}

		if (!$disable) {
			return $inbound_localized_data;
			return $inbound_localized_data;
		}

		$inbound_localized_data['page_tracking'] = 'off';
		$inbound_localized_data['search_tracking'] = 'off';
		$inbound_localized_data['comment_tracking'] = 'off';

		return $inbound_localized_data;
	}

	/**
	 *  Prevents conversion tracking if visitor is on no track list
	 *  @param BOOL $do_not_track sets to true to disable conversion tracking
	 *  @return BOOL
	 */
	public static function filter_conversions( $do_not_track ) {
		global $inbound_settings;

		$ignore_admin = ( isset( $inbound_settings['inbound-analytics-rules']['admin-tracking'] ) && $inbound_settings['inbound-analytics-rules']['admin-tracking'] == 'off' ) ? true : false;

		/* determine if user is admin and admin filtering is on */
		if ( current_user_can( 'manage_options' ) && $ignore_admin ) {
			$do_not_track = true;
		}

		/* determine if visitor's IP address is in blacklist */
		$ip_addresses = ( isset( $inbound_settings['inbound-analytics-rules']['ip-addresses']) ) ? $inbound_settings['inbound-analytics-rules']['ip-addresses'] : array();
		if ( in_array( LeadStorage::lookup_ip_address() , $ip_addresses ) ) {
			$do_not_track = true;
		}

		return $do_not_track;
	}

	/**
	 *  Loop through content HTML and create masked links
	 *  @param HTML $html post_content
	 */
	public static function prepare_tracked_links( $html ) {
		global $post;


		/* skip if not main page/post query and is not a manually invoked filter */
		if (
			( !is_single() || !in_the_loop() || !is_main_query() )
			&&
			(current_filter() != 'inbound_track_links')
		) {
			return $html;
		}

		/* skip if no tracked links */
		if (!strstr($html , 'inbound-track-link')) {
			return $html;
		}

		$doc = new DOMDocument();

		/* try to catch errors */
		if (function_exists('libxml_use_internal_errors')) {
			libxml_use_internal_errors(true);
			$error = false;
		}

		/* load HTML into $doc object */
		if (!function_exists('mb_convert_encoding')) {
			@$doc->loadHTML($html);
		} else {
			@$doc->loadHTML( mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
		}

		/* try to catch errors */
		if (function_exists('libxml_use_internal_errors')) {
			$error = libxml_get_last_error();
			libxml_use_internal_errors(false);
		}

		/* if error exists then return original content and do not attempt to track links */
		if (isset($error->level) && $error->level == LIBXML_ERR_FATAL ) {
			return $html;
		}

		foreach($doc->getElementsByTagName('a') as $anchor) {
			/* skip links with do-not-track in class */
			$class = $anchor->getAttribute('class');

			if (!strstr( $class, 'inbound-track-link' )) {
				continue;
			}

			$href = $anchor->getAttribute('href');

			/* prepare tracked link */
			$link = Inbound_API::analytics_track_links( array(
				'page_id' => ( isset($post) && $post->ID  ? $post->ID : null ) ,
				'vid' => 0 , /* we don't support variations for non landing pages yet */
				'url' => $href ,
				'tracking_id' => __( 'Inbound Tracked Link', 'inbound-pro' ) /* required but not being used atm */
			));

			/* standardize & symbol */
			$link['url'] = str_replace('&amp;', '&' , $link['url'] );
			//$href = str_replace('&amp;', '&' , $href );

			$anchor->setAttribute('href', $link['url']);

		}

		$doc->saveHTML();

		$html = '';

		foreach($doc->getElementsByTagName('body')->item(0)->childNodes as $element) {
			$html .= $doc->saveXML($element, LIBXML_NOEMPTYTAG);
		}

		/* remove cdata */
		$html = str_replace('<![CDATA[' , '' , $html);
		$html = str_replace(']]>' , '' , $html);

		return $html;
	}

	/**
	 *  Listens for tracked masked link processing
	 */
	public static function track_link( $args ) {

		$do_not_track = apply_filters('inbound_analytics_stop_track', false );

		/* do not track for CTA Clicks or for those who have tracking disabled */
		if ( $do_not_track || isset($args['cta_id']) || !$args['page_id'] || !wp_get_referer() ) {
			return;
		}

		$args = array(
			'event_name' => 'inbound_content_click',
			'page_id' => $args['page_id'],
			'variation_id' => (isset($args['vid'])) ? $args['vid'] : 0,
			'event_details' => json_encode($args),
		);

		Inbound_Events::store_event($args);

	}

	/**
	 * Adds a quick stat form CTA clicks to the Quick Stats box
	 */
	public static function display_quick_stat_content_clicks() {
		global $post;

		$content_clicks = Inbound_Events::get_content_clicks( $post->ID )

		?>
		<div class="quick-stat-label">
			<div class="label_1"><?php _e('Content Clicks', 'inbound-pro'); ?></div>
			<div class="label_2">
				<?php
				if (class_exists('Inbound_Analytics')) {
					?>
					<a href='<?php echo admin_url('index.php?action=inbound_generate_report&lead_id='.$post->ID.'&class=Inbound_Event_Report&event_name=inbound_content_click&range=10000&title='. urlencode(Inbound_Events::get_event_label('inbound_content_click')).'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
						<?php echo count($content_clicks); ?>
					</a>
					<?php
				} else {
					echo count($content_clicks);
				}
				?>
			</div>
			<div class="clearfix"></div>
		</div>

		<?php

	}

}

new Inbound_Tracking();
