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
		
	}
	
	/**
	*  	Create New Nav Tabs in WordPress Leads - Lead UI
	*/
	public static function create_nav_tabs( $nav_items ) {
		global $post;
		
		self::$click_events = Inbound_Mailer_Tracking::get_click_events( $post->ID );
		self::$unsubscribe_events = Inbound_Mailer_Unsubscribe::get_unsubscribe_events( $post->ID );
		
		/* Add email click events */
		$nav_items[] = array(
			'id'=>'wpleads_lead_inbound_email_click_tab',
			'label'=> __( 'Email Clicks' , 'inbound-email' ),
			'count' => count( self::$click_events )
		);
		
		/* Add email unsubscribe events */
		$nav_items[] = array(
			'id'=>'wpleads_lead_inbound_unsubscribes_click_tab',
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

				foreach( self::$click_events as $key=>$event) {
					$id = $event['id'];
					$title = get_the_title($id);

					$date_raw = new DateTime($event['datetime']);

					$date_of_conversion = $date_raw->format('F jS, Y \a\t g:ia (l)');
					$clean_date = $date_raw->format('Y-m-d H:i:s');
				
					echo '<div class="lead-timeline recent-conversion-item cta-tracking-item" data-date="'.$clean_date.'">
								<a class="lead-timeline-img" href="#non">
									<!--<i class="lead-icon-target"></i>-->
								</a>

								<div class="lead-timeline-body">
									<div class="lead-event-text">
										<p><span class="lead-item-num">'.$count.'. </span><span class="lead-helper-text">'.__('Email Clickthrough' , 'inbound-email' ).': </span><a href="#">'.$title.'</a><span class="conversion-date">'.$date_of_conversion.'</span></p>
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
		<div id="wpleads_lead_inbound_email_unsubscribe_tab" class='lead-activity'>
			<h2><?php _e( 'Unsubscribes' , 'inbound-email' ); ?></h2>
			<?php

			if ( self::$unsubscribe_events ) {
				$count = 1;

				foreach( self::$unsubscribe_events as $key => $event) {

					$date_raw = new DateTime($event['datetime']);

					$datetime = $date_raw->format('F jS, Y \a\t g:ia (l)');
					$clean_date = $date_raw->format('Y-m-d H:i:s');
				
					echo '<div class="lead-timeline recent-conversion-item cta-tracking-item" data-date="'.$clean_date.'">
								<a class="lead-timeline-img" href="#non">
									<!--<i class="lead-icon-target"></i>-->
								</a>

								<div class="lead-timeline-body">
									<div class="lead-event-text">
										<p><span class="lead-item-num">'.$count.'. </span><span class="lead-helper-text">'.__('Lead unsubscribed from' , 'inbound-email' ).': </span><a target="_blank" href="'.admin_url( "edit.php?page=lead_management&post_type=wp-lead&wplead_list_category%5B%5D=".$event['list_id']['term_id']."&relation=AND&orderby=date&order=asc&s=&t=&submit=Search+Leads").'">'.$event['list_id']['name'].'</a><span class="conversion-date">'.$datetime.'</span><br><br><span>'.htmlentities($event['comments']).'</span></p>
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

}

/* Load Post Type Pre Init */
$GLOBALS['Inbound_Mailer_WordPress_Leads'] = new Inbound_Mailer_WordPress_Leads();
