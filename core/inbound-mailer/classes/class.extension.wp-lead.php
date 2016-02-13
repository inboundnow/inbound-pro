<?php

/**
*  Adds events section to a Lead's Activity tab
*/
class Inbound_Mailer_WordPress_Leads {

	static $click_events;
	static $unsubscribe_events;
	
	/**
	*  Initiate class
	*/
	function __construct() {
		self::load_hooks();
	}

	/**
	*  Loads hooks and filters
	*/
	private function load_hooks() {
		
		add_filter('wpl_lead_activity_tabs', array( __CLASS__ , 'create_nav_tabs' ) , 10, 1);
		add_action('wpleads_after_activity_log' , array( __CLASS__ , 'show_inbound_email_click_content' ) );
		add_action('wpleads_after_activity_log' , array( __CLASS__ , 'show_inbound_email_unsubscribe_content' ) );

		/* add quick stat  */
		add_action('wpleads_dsiplay_quick_stat', array(__CLASS__, 'display_quick_stat_email_clicks') , 15 );
		add_action('wpleads_dsiplay_quick_stat', array(__CLASS__, 'display_quick_stat_email_unsubscribes') , 15 );

	}
	
	/**
	*  	Create New Nav Tabs in WordPress Leads - Lead UI
	*/
	public static function create_nav_tabs( $nav_items ) {
		global $post;
		
		self::$click_events = Inbound_Events::get_email_clicks( $post->ID );
		self::$unsubscribe_events = Inbound_Events::get_unsubscribes( $post->ID );
		
		/* Add email click events */
		$nav_items[] = array(
			'id'=>'wpleads_lead_inbound_email_click_tab',
			'label'=> __( 'Email Clicks' , 'inbound-email' ),
			'count' => count( self::$click_events )
		);
		
		/* Add email unsubscribe events */
		$nav_items[] = array(
			'id'=>'wpleads_lead_inbound_email_unsubscribes_tab',
			'label'=> __( 'Unsubscribes' , 'inbound-email' ),
			'count' => count( self::$unsubscribe_events )
		);
		
		
		return $nav_items;
	}
	
	/**
	*  Display Email Clicks in Activity log
	*/
	public static function show_inbound_email_click_content() {
		global $post; 
		?>
		<div id="wpleads_lead_inbound_email_click_tab" class='lead-activity'>
			<h2><?php _e( 'Email\'s Clicked' , 'inbound-email' ); ?></h2>
			<?php

			if ( self::$click_events ) {
				$count = 1;

				foreach( self::$click_events as $key => $event) {
					$email_title = get_the_title($event['email_id']);
					$email_permalink = get_permalink($event['email_id']);
					$destination_title = get_the_title($event['page_id']);
					$destination_permalink = get_permalink($event['page_id']);

					$date_raw = new DateTime($event['datetime']);

					$date_of_conversion = $date_raw->format('F jS, Y \a\t g:ia (l)');
					$clean_date = $date_raw->format('Y-m-d H:i:s');
				
					echo '<div class="lead-timeline recent-conversion-item cta-tracking-item" data-date="'.$clean_date.'">
								<a class="lead-timeline-img" href="#non">
									<!--<i class="lead-icon-target"></i>-->
								</a>

								<div class="lead-timeline-body">
									<div class="lead-event-text">
										<p>
										<span class="lead-item-num">'.$count.'. </span>
										<span class="conversion-date"><b>'. __('Email Clickthrough' , 'inbound-mailer' ) .' - ' .$date_of_conversion.'</b></span>
										<br>
										<span class="lead-helper-text">'.__('Email clickthrough through this email ' , 'inbound-email' ).' </span><a href="'.$email_permalink.'" target="_blank">'.$email_title.'</a>
										<br>
										<span class="lead-helper-text">'.__('Lead visited this link ' , 'inbound-email' ).': </span><a href="'.$destination_permalink.'" target="_blank">'.$destination_title.'</a>
										</p>
									</div>
								</div>
							</div>';

					$count++;
				}
			}
			else
			{
				echo '<span id=\'wpl-message-none\'>'. __( 'No Email Clickthroughs!' , 'inbound-email' ) .'</span>';
			}


			?>
		</div>
		<?php
	}
	
	/**
	*  Display Email Unsubscribes in Activity log
	*/
	public static function show_inbound_email_unsubscribe_content() {
		global $post; 
		?>
		<div id="wpleads_lead_inbound_email_unsubscribes_tab" class='lead-activity'>
			<h2><?php _e( 'Unsubscribes' , 'inbound-email' ); ?></h2>
			<?php

			if ( self::$unsubscribe_events ) {
				$count = 1;

				foreach( self::$unsubscribe_events as $key => $event) {

					/* details */
					$event_details = json_decode( $event['event_details'] , true );

					/* dates */
					$date_raw = new DateTime($event['datetime']);
					$datetime = $date_raw->format('F jS, Y \a\t g:ia (l)');
					$clean_date = $date_raw->format('Y-m-d H:i:s');
				
					echo '<div class="lead-timeline recent-conversion-item cta-tracking-item" data-date="'.$clean_date.'">
								<a class="lead-timeline-img" href="#non">
									<!--<i class="lead-icon-target"></i>-->
								</a>

								<div class="lead-timeline-body">
									<div class="lead-event-text">
										<p>
											<span class="lead-item-num">'.$count.'. </span>
											<span class="conversion-date"><b>' . __('Unsubscribe Event','inbound-mailer') . ' - ' . $datetime . '</b></span>
											<br>
											<span class="lead-helper-text">'.__('Lead unsubscribed from' , 'inbound-email' ).': </span>
											<br>
											';

											foreach ( $event_details['list_ids'] as $key => $list_id) {
												$list = Inbound_Leads::get_lead_list_by( 'id', $list_id );
												echo '<a target="_blank" href="'.admin_url( "edit.php?page=lead_management&post_type=wp-lead&wplead_list_category%5B%5D=".$list['term_id']."&relation=AND&orderby=date&order=asc&s=&t=&submit=Search+Leads'").'">'.$list['name'].'</a>';

												/* throw comma if needed */
												$next = $key + 1;
												if (isset($event_details['list_ids'][$next])) {
													echo ',';
												}
											}

											if (isset($event_details['comments']) && $event_details['comments']) {
												echo '<span><pre>'.htmlentities($event_details['comments']).'</pre></span></p>';
											}

									echo '</div>
								</div>
							</div>';

					$count++;
				}
			}
			else
			{
				echo '<span id=\'wpl-message-none\'>'. __( 'No Unsuvscribe Events!' , 'inbound-email' ) .'</span>';
			}


			?>
		</div>
		<?php
	}

	/**
	 * Adds a quick stat form email clicks to the Quick Stats box
	 */
	public static function display_quick_stat_email_clicks() {
		global $post;

		self::$click_events = Inbound_Events::get_email_clicks( $post->ID );

		/* skip stat if none available */
		if (!self::$click_events) {
			return;
		}

		?>
		<div  class="quick-stat-label"><?php _e('Email Clicks', 'inbound-mailer'); ?>
			<span class="quick-stat-total"><?php echo count(self::$click_events); ?></span>
		</div>
		<?php

	}
	/**
	 * Adds a quick stat form email clicks to the Quick Stats box
	 */
	public static function display_quick_stat_email_unsubscribes() {
		global $post;

		self::$unsubscribe_events = Inbound_Events::get_unsubscribes( $post->ID );

		/* skip stat if none available */
		if (!self::$unsubscribe_events) {
			return;
		}

		?>
		<div  class="quick-stat-label"><?php _e('Unsubscribes', 'inbound-mailer'); ?>
			<span class="quick-stat-total"><?php echo count(self::$unsubscribe_events); ?></span>
		</div>
		<?php

	}

}

/* Load Post Type Pre Init */
$GLOBALS['Inbound_Mailer_WordPress_Leads'] = new Inbound_Mailer_WordPress_Leads();
