<?php
/**
 * Class for adding Call To Action event reports to Lead profile
 * @package CTA
 * @subpackage LeadsProfile
 */

if ( !class_exists('CTA_WordPress_Leads') ) {

	class CTA_WordPress_Leads {

		static $cta_clicks;

		function __construct() {
			self::load_hooks();
		}

		public static function load_hooks() {

			add_filter('wpl_lead_activity_tabs', array(__CLASS__, 'create_nav_tabs'), 10, 1);
			add_action('wpleads_after_activity_log', array(__CLASS__, 'show_cta_click_content'));

			/* add quick stat  */
			add_action('wpleads_display_quick_stat', array(__CLASS__, 'display_quick_stat_cta_clicks') , 15 );


		}

		/* Create New Nav Tabs in WordPress Leads - Lead UI */
		public static function create_nav_tabs( $nav_items ) {
			global $post;

			self::$cta_clicks = Inbound_Events::get_cta_clicks( $post->ID );

			$nav_items[] = array(
				'id'=>'wpleads_lead_cta_click_tab',
				'label'=> __( 'CTA Clicks', 'inbound-pro' ),
				'count' => self::get_click_count()
			);
			return $nav_items;
		}

		/* Display CTA Click Content */
		public static function show_cta_click_content() {
			global $post;
			?>
			<div id="wpleads_lead_cta_click_tab" class='lead-activity'>
				<h2><?php _e( 'CTA\'s Clicked', 'inbound-pro' ); ?></h2>
				<?php

				if (self::$cta_clicks) {
					$count = 1;

					foreach(self::$cta_clicks as $key=>$event) {
						$cta_id = ($event['cta_id']) ? $event['cta_id']  : __('undefined','cta');
						$variation_id = ($event['variation_id']) ? $event['variation_id']  : 0;
						$cta_name = ($event['cta_id']) ? get_the_title($event['cta_id'])  : __('undefined','cta');
						$converted_page_id = ($event['page_id']) ? $event['page_id']  : 0;
						$converted_page_permalink = ($converted_page_id) ? get_permalink($converted_page_id) : '';
						$converted_page_title = ($converted_page_id) ? get_the_title($converted_page_id) : __('undefined','cta');
						$date_raw = new DateTime($event['datetime']);
						$datetime = $date_raw->format('F jS, Y \a\t g:ia (l)');


						// Display Data
						?>
						<div class="lead-timeline recent-conversion-item cta-click" data-date="<?php echo $event['datetime']; ?>">
							<a class="lead-timeline-img" href="#non">
								<!--<i class="lead-timeline-img page-views"></i>-->
							</a>

							<div class="lead-timeline-body">
								<div class="lead-event-text">
									<p>
										<span class="lead-item-num"><?php echo $count; ?></span>
										<span class="conversion-date"><b><?php echo __('CTA Click', 'cta'). ' - ' .$datetime; ?></b></span>
										<br>
                                    <span class="lead-helper-text" style="padding-left:6px;">
                                        <?php
										_e(' Clicked on page','leads');
										?>
                                    </span>
										<a href="<?php echo $converted_page_permalink; ?>" id="lead-session-<?php echo $count; ?>" rel="<?php echo $count; ?>" target="_blank"  title="<?php echo ( $cta_id ? __('This is the page the call to action was placed on.','cta') : __( 'Event data is incomplete. ' , 'cta') )?>"><?php echo $converted_page_title; ?></a>
										<?php
										_e('through the call to action ','leads');
										echo '<a href="'.admin_url('post.php?post='.$cta_id.'&action=edit&inbound-editor=false&wp-cta-variation-id='.$variation_id).'" target="_blank" title="'. ( $cta_id ? __('This is the call to action the user clicked a link on.','cta') : __( 'Event data is incomplete.' , 'cta') ).'">'.$cta_name.' ('. __('variation id:','cta'). $variation_id.')</a>';
										?>
									</p>
								</div>
							</div>
						</div>
						<?php
						$count++;
					}
				}
				else
				{
					_e( '<span id=\'wpl-message-none\'>No Call to Action Clicks found!</span>"', 'inbound-pro' );
				}


				?>
			</div>
			<?php
		}

		/**
		 * Adds a quick stat form CTA clicks to the Quick Stats box
		 */
		public static function display_quick_stat_cta_clicks() {
			global $post;

			self::$cta_clicks = Inbound_Events::get_cta_clicks( $post->ID );


			?>
			<div class="quick-stat-label">
				<div class="label_1"><?php _e('CTA Clicks:', 'inbound-pro'); ?></div>
				<div class="label_2">
					<?php
					if (class_exists('Inbound_Analytics')) {
						?>
						<a href='<?php echo admin_url('index.php?action=inbound_generate_report&lead_id='.$post->ID.'&class=Inbound_Event_Report&event_name=inbound_cta_click&range=10000&title='. urlencode(Inbound_Events::get_event_label('inbound_cta_click')).'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
							<?php echo count(self::$cta_clicks); ?>
						</a>
						<?php
					} else {
						echo count(self::$cta_clicks);
					}
					?>
				</div>
				<div class="clearfix"></div>
			</div>

			<?php

		}


		/**
		*	Gets number of tracked link clicks
		*/
		public static function get_click_count() {
			global $post;

			return count(self::$cta_clicks);
		}
	}

	/* Load Post Type Pre Init */
	new CTA_WordPress_Leads();
}