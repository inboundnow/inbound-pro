<?php
/**
 * Report Template
 * @package     InboundPro
 * @subpackage  ReportTemplate
 */

if ( !class_exists('Inbound_Quick_View') ) {

	class Inbound_Quick_View extends Inbound_Reporting_Templates {

		static $range;
		static $gaData;
		static $statistics;

		/**
		 *    Initializes class
		 */
		public static function init() {

			/* build timespan for analytics report */
			self::define_range();

			/* Display date range conditions */
			add_action('inbound-analytics/quick-view', array(__CLASS__, 'display_navigation') , 1 );

			/* set Inbound powered content stats */
			add_action('inbound-analytics/quick-view', array(__CLASS__, 'display_content_breakdown') , 20);

			/* set Inbound powered content stats */
			add_action('inbound-analytics/quick-view', array(__CLASS__, 'display_action_breakdown') , 30);
		}

		/**
		 *    Given the $_GET['analytics_range'] parameter set the timespan to request analytics data on
		 */
		public static function define_range() {
			if (!isset($_GET['range'])) {
				self::$range = get_user_option(
					'inbound_screen_option_range',
					get_current_user_id()
				);
				self::$range = (self::$range) ? self::$range : 90;
			} else {
				self::$range = $_GET['range'];
			}
		}

		/**
		 * Load impressions from inbound_page_views table
		 * @return mixed
		 */
		public static function load_impressions() {

			$data = apply_filters('inbound-analytics/quick-view/load-impressions' , self::$statistics );

			/* allow this data to be set by a 3rd party */
			if (isset($data['impressions'])) {
				self::$statistics['impressions'] = $data['impressions'];
				return self::$statistics;
			}

			self::$statistics['impressions']['current'][self::$range] = self::get_impressions(array(
				'per_days' => self::$range,
				'skip' => 0
			));

			self::$statistics['impressions']['past'][self::$range] = self::get_impressions(array(
				'per_days' => self::$range,
				'skip' => 1
			));

			/* determine rate */
			self::$statistics['impressions']['difference'][self::$range] = self::get_percentage_change(self::$statistics['impressions']['current'][self::$range], self::$statistics['impressions']['past'][self::$range]);

			return self::$statistics;
		}

		/**
		 * Load visitors from inbound_page_views table
		 * @return mixed
		 */
		public static function load_visitors() {

			$data = apply_filters('inbound-analytics/quick-view/load-visitors' , self::$statistics );

			/* allow this data to be set by a 3rd party */
			if (isset($data['visitors'])) {
				self::$statistics['visitors'] = $data['visitors'];
				return self::$statistics;
			}

			/* get visitor count in current time period */
			self::$statistics['visitors']['current'][self::$range] = self::get_visitors(array(
				'per_days' => self::$range,
				'skip' => 0
			));

			/* get visitor count in past time period */
			self::$statistics['visitors']['past'][self::$range] = self::get_visitors(array(
				'per_days' => self::$range,
				'skip' => 1
			));

			/* determine rate */
			self::$statistics['visitors']['difference'][self::$range] = self::get_percentage_change(self::$statistics['visitors']['current'][self::$range], self::$statistics['visitors']['past'][self::$range]);


		}

		public static function load_actions() {
			self::load_action_totals();
			self::load_submissions();
			self::load_cta_clicks();
			self::load_content_clicks();
		}


		public static function load_action_totals() {

			/* get action count in current time period */
			self::$statistics['actions']['current'][self::$range] = self::get_actions(array(
				'per_days' => self::$range,
				'skip' => 0
			));

			/* get action count in past time period */
			self::$statistics['actions']['past'][self::$range] = self::get_actions(array(
				'per_days' => self::$range,
				'skip' => 1
			));


			/* determine difference rate */
			self::$statistics['actions']['difference'][self::$range] = self::get_percentage_change(self::$statistics['actions']['current'][self::$range], self::$statistics['actions']['past'][self::$range]);

			/* determine action to impression rate for current time period */
			self::$statistics['actions']['rate']['current'][self::$range] = (self::$statistics['impressions']['current'][self::$range]) ? self::$statistics['actions']['current'][self::$range] / self::$statistics['impressions']['current'][self::$range] : 0;

			/* determine action to impression rate for past time period */
			self::$statistics['actions']['rate']['past'][self::$range] = (self::$statistics['impressions']['past'][self::$range]) ? self::$statistics['actions']['past'][self::$range] / self::$statistics['impressions']['past'][self::$range] : 0;

			/* determine action to impression rate for past time period */
			self::$statistics['actions']['rate']['difference'][self::$range] = self::get_percentage_change(self::$statistics['actions']['rate']['current'][self::$range], self::$statistics['actions']['rate']['past'][self::$range]);

		}

		public static function load_submissions() {
			/* get form submission count in current time period */
			self::$statistics['submissions']['current'][self::$range] = self::get_submissions(array(
				'per_days' => self::$range,
				'skip' => 0
			));

			/* get action count in past time period */
			self::$statistics['submissions']['past'][self::$range] = self::get_submissions(array(
				'per_days' => self::$range,
				'skip' => 1
			));

			/* determine difference rate */
			self::$statistics['submissions']['difference'][self::$range] = self::get_percentage_change(self::$statistics['actions']['current'][self::$range], self::$statistics['actions']['past'][self::$range]);

			/* determine action to impression rate for current time period */
			self::$statistics['submissions']['rate']['current'][self::$range] = (self::$statistics['impressions']['current'][self::$range]) ? self::$statistics['submissions']['current'][self::$range] / self::$statistics['impressions']['current'][self::$range] : 0;

			/* determine action to impression rate for current time period */
			self::$statistics['submissions']['rate']['past'][self::$range] = (self::$statistics['impressions']['past'][self::$range]) ? self::$statistics['submissions']['past'][self::$range] / self::$statistics['impressions']['past'][self::$range] : 0;

			/* determine action to impression rate for past time period */
			self::$statistics['actions']['rate']['past'][self::$range] = (self::$statistics['impressions']['past'][self::$range]) ? self::$statistics['submissions']['past'][self::$range] / self::$statistics['impressions']['past'][self::$range] : 0;

			/* determine action to impression rate for past time period */
			self::$statistics['actions']['rate']['difference'][self::$range] = self::get_percentage_change(self::$statistics['submissions']['rate']['current'][self::$range], self::$statistics['submissions']['rate']['past'][self::$range]);


		}

		public static function load_cta_clicks() {

			/* get cta clickthrough count in current time period */
			self::$statistics['cta-clicks']['current'][self::$range] = self::get_tracked_cta_clicks(array(
				'per_days' => self::$range,
				'skip' => 0
			));

			/* get cta clickthrough count in past time period */
			self::$statistics['cta-clicks']['past'][self::$range] = self::get_tracked_cta_clicks(array(
				'per_days' => self::$range,
				'skip' => 1
			));

			/* determine difference rate */
			self::$statistics['cta-clicks']['difference'][self::$range] = self::get_percentage_change(self::$statistics['cta-clicks']['current'][self::$range], self::$statistics['cta-clicks']['past'][self::$range]);

			/* determine action to impression rate for current time period */
			self::$statistics['cta-clicks']['rate']['current'][self::$range] = (self::$statistics['impressions']['current'][self::$range]) ? self::$statistics['cta-clicks']['current'][self::$range] / self::$statistics['impressions']['current'][self::$range] : 0;

			/* determine action to impression rate for past time period */
			self::$statistics['cta-clicks']['rate']['past'][self::$range] = (self::$statistics['impressions']['past'][self::$range]) ? self::$statistics['cta-clicks']['past'][self::$range] / self::$statistics['impressions']['past'][self::$range] : 0;

			/* determine action to impression rate for past time period */
			self::$statistics['cta-clicks']['rate']['difference'][self::$range] = self::get_percentage_change(self::$statistics['cta-clicks']['rate']['current'][self::$range], self::$statistics['cta-clicks']['rate']['past'][self::$range]);

		}


		public static function load_content_clicks() {

			/* get cta clickthrough count in current time period */
			self::$statistics['content-clicks']['current'][self::$range] = self::get_tracked_content_clicks(array(
				'per_days' => self::$range,
				'skip' => 0
			));

			/* get cta clickthrough count in past time period */
			self::$statistics['content-clicks']['past'][self::$range] = self::get_tracked_content_clicks(array(
				'per_days' => self::$range,
				'skip' => 1
			));

			/* determine difference rate */
			self::$statistics['content-clicks']['difference'][self::$range] = self::get_percentage_change(self::$statistics['content-clicks']['current'][self::$range], self::$statistics['content-clicks']['past'][self::$range]);

			/* determine action to impression rate for current time period */
			self::$statistics['content-clicks']['rate']['current'][self::$range] = (self::$statistics['impressions']['current'][self::$range]) ? self::$statistics['content-clicks']['current'][self::$range] / self::$statistics['impressions']['current'][self::$range] : 0;

			/* determine action to impression rate for past time period */
			self::$statistics['content-clicks']['rate']['past'][self::$range] = (self::$statistics['impressions']['past'][self::$range]) ? self::$statistics['content-clicks']['past'][self::$range] / self::$statistics['impressions']['past'][self::$range] : 0;

			/* determine action to impression rate for past time period */
			self::$statistics['content-clicks']['rate']['difference'][self::$range] = self::get_percentage_change(self::$statistics['content-clicks']['rate']['current'][self::$range], self::$statistics['content-clicks']['rate']['past'][self::$range]);

		}


		/**
		 *    Loads the analytics template
		 *
		 */
		public static function load_template() {
			do_action('inbound-analytics/quick-view' , self::$statistics );
		}

		public static function display_navigation() {
			global $post;
			$base = 'post.php?post=' . $post->ID . '&action=edit';
			?>
			<ul class="nav nav-pills date-range">
				<li <?php echo (self::$range == 1) ? "class='active'" : "class=''"; ?> data-range='1' title='<?php _e('Past 24 hours', 'inbound-pro'); ?>'>
					<a href='<?php echo $base; ?>&range=1'>1</a>
				</li>
				<li <?php echo (self::$range == 7) ? "class='active'" : "class=''"; ?> data-range='7' title='<?php _e('Past 7 days', 'inbound-pro'); ?>'>
					<a href='<?php echo $base; ?>&range=7'>7</a>
				</li>
				<li <?php echo (self::$range == 30) ? "class='active'" : "class=''"; ?> data-range='30' title='<?php _e('Past 30 days', 'inbound-pro'); ?>'>
					<a href='<?php echo $base; ?>&range=30'>30</a>
				</li>
				<li <?php echo (self::$range == 90) ? "class='active'" : "class=''"; ?> data-range='90' title='<?php _e('Past 90 days', 'inbound-pro'); ?>'>
					<a href='<?php echo $base; ?>&range=90'>90</a>
				</li>
				<li <?php echo (self::$range == 365) ? "class='active'" : "class=''"; ?> data-range='365' title='<?php _e('Past 365 days', 'inbound-pro'); ?>'>
					<a href='<?php echo $base; ?>&range=365'>365</a>
				</li>
			</ul>
			<?php
		}

		public static function display_sparkline() {
			/*
			?>
			<br>
			<img src='<?php echo INBOUND_GA_URLPATH; ?>assets/img/example-sparkline.png' title='Example sparkline'>
			<?php
			*/
		}

		public static function display_content_breakdown() {
			self::load_impressions();
			self::load_actions();
			self::load_visitors();

			global $post;

			?>
			<table class='ia-table-summary'>
				<tr>
					<td class='ia-td-th'>
						<label title='<?php _e('Type fo statistic.', 'inbound-pro'); ?>'>
							<?php _e('Statistic', 'inbound-pro'); ?>
						</label>
					</td>
					<td class='ia-td-th'>
						<label title='<?php _e('Statistic value for given time period', 'inbound-pro'); ?>'>
							<?php _e('Value', 'inbound-pro'); ?>
						</label>
					</td>
					<td class='ia-td-th' title='<?php _e('Change in growth compared to corresponding previous timeperiod.', 'inbound-pro'); ?>'>
						<label>
							<?php _e('Change', 'inbound-pro'); ?>
						</label>
					</td>
				</tr>
				<tr>
					<td class='ia-td-label'>
						<label title='<?php _e('Total number of visits to this page', 'inbound-pro'); ?>'>
							<?php _e('Impressions:', 'inbound-pro'); ?>
						</label>
					</td>
					<td>
						<a href='<?php echo admin_url('index.php?action=inbound_generate_report&page_id='.$post->ID.'&class=Inbound_Impressions_Report&range='.self::$range.'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'><?php echo self::$statistics['impressions']['current'][self::$range]; ?></a>
					</td>

					<td>
					<span class='stat label <?php echo (self::$statistics['impressions']['difference'][self::$range] > 0) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf(__('%s impressions in the last %s days versus %s impressions in the prior %s day period)', 'inbound-pro'), self::$statistics['impressions']['current'][self::$range], self::$range, self::$statistics['impressions']['past'][self::$range], self::$range); ?>" data-toggle="tooltip" data-placement="left"><?php echo self::prepare_rate_format(self::$statistics['impressions']['difference'][self::$range]); ?></a>
					</td>
				</tr>
				<tr>
					<td class='ia-td-label'>
						<label title='<?php _e('Total number of visitors', 'inbound-pro'); ?>'>
							<?php _e('Visitors:', 'inbound-pro'); ?>
						</label>
					</td>
					<td>
						<a href='<?php echo admin_url('index.php?action=inbound_generate_report&page_id='.$post->ID.'&class=Inbound_Visitors_Report&range='.self::$range.'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
							<?php echo self::$statistics['visitors']['current'][self::$range]; ?>
						</a>
					</td>

					<td>
						<span class='stat label  <?php echo (self::$statistics['visitors']['difference'][self::$range] > 0) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf(__('%s visitors in the last %s days versus %s visitors in the prior %s day period)', 'inbound-pro'), self::$statistics['visitors']['current'][self::$range], self::$range, self::$statistics['visitors']['past'][self::$range], self::$range); ?>"><?php echo self::prepare_rate_format(self::$statistics['visitors']['difference'][self::$range]); ?></span>
					</td>
				</tr>
				<tr>
					<td class='ia-td-label'>
						<label title='<?php _e('Total number of event actions originating from this page.', 'inbound-pro'); ?>'>
							<?php _e('Actions:', 'inbound-pro'); ?>
						</label>
					</td>
					<td class='ia-td-value'>
						<a href='<?php echo admin_url('index.php?action=inbound_generate_report&page_id='.$post->ID.'&class=Inbound_Events_Report&range='.self::$range.'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'><?php echo self::$statistics['actions']['current'][self::$range]; ?></a>
					</td>
					<td class='ia-td-value'>
					<span class='stat label  <?php echo (self::$statistics['actions']['difference'][self::$range] > 0) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf(__('%s actions in the last %s days versus %s actions in the prior %s day period)', 'inbound-pro'), self::$statistics['actions']['current'][self::$range], self::$range, self::$statistics['actions']['past'][self::$range], self::$range); ?>" data-toggle="tooltip" data-placement="left"><?php echo self::prepare_rate_format(self::$statistics['actions']['difference'][self::$range]); ?></a>

					</td>
				</tr>
				<tr>
					<td class='ia-td-label'>
						<label title='<?php _e('Total percentage of actions to impressions.', 'inbound-pro'); ?>'>
							<?php _e('Action Rate:', 'inbound-pro'); ?>
						</label>
					</td>
					<td class='ia-td-value'>
						<span class="label label-info"><?php echo self::prepare_rate_format(self::$statistics['actions']['rate']['current'][self::$range], false); ?></span>
					</td>
					<td class='ia-td-value'>
						<span class='stat label  <?php echo (self::$statistics['actions']['rate']['difference'][self::$range] > 0) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf(__('%s action rate in the last %s days versus an %s action rate in the prior %s day period)', 'inbound-pro'), self::prepare_rate_format(self::$statistics['actions']['rate']['current'][self::$range]), self::$range, self::prepare_rate_format(self::$statistics['actions']['rate']['past'][self::$range]), self::$range); ?>"><?php echo self::prepare_rate_format(self::$statistics['actions']['rate']['difference'][self::$range]); ?></span>
					</td>
				</tr>
				<?php
				do_action('inbound-analytics/quick-view/content-breakdown' , self::$statistics );
				?>
			</table>
			<?php
		}

		public static function display_action_breakdown() {
			global $post;
			?>
			<br>
			<table class='ia-table-summary'>
				<tr>
					<td class='ia-td-th'>
						<?php _e('Action Breakdown', 'inbound-pro'); ?>
					</td>
					<td class='ia-td-th'>
						<?php _e('Count', 'inbound-pro'); ?>
					</td>
					<td class='ia-td-th'>
						<?php _e('Rate', 'inbound-pro'); ?>
					</td>
					<td class='ia-td-th'>
						<?php _e('Change', 'inbound-pro'); ?>
					</td>
				</tr>
				<tr>
					<td class='ia-td-label'>
						<label title='<?php _e('Total number of Inbound Form submissions originating from this page', 'inbound-pro'); ?>'><?php _e('Inbound Form Submissions', 'inbound-pro'); ?>
							:</label>
					</td>
					<td class='ia-td-value'>
						<a href='<?php echo admin_url('index.php?action=inbound_generate_report&page_id='.$post->ID.'&class=Inbound_Event_Report&event_name=inbound_form_submission&range='.self::$range.'&title='.__('Inbound Form Submissions', 'inbound-pro').'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
							<?php echo self::$statistics['submissions']['current'][self::$range]; ?>
						</a>
					</td>
					<td class='ia-td-value'>
						<span class="label label-info" title='<?php _e('Rate of action events compared to impressions.', 'inbound-pro'); ?>'><?php echo self::prepare_rate_format(self::$statistics['submissions']['rate']['current'][self::$range], false); ?></span>
					</td>
					<td class='ia-td-value'>
						<span class='stat label <?php echo (self::$statistics['submissions']['difference'][self::$range] > 0) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf(__('%s action rate in the last %s days versus an %s action rate in the prior %s day period)', 'inbound-pro'), self::prepare_rate_format(self::$statistics['submissions']['rate']['current'][self::$range]), self::$range, self::prepare_rate_format(self::$statistics['submissions']['rate']['past'][self::$range]), self::$range); ?>"><?php echo self::prepare_rate_format(self::$statistics['actions']['rate']['difference'][self::$range]); ?></span>
					</td>
				</tr>
				<tr>
					<td class='ia-td-label'>
						<label title='<?php _e('Total number of clicked tracked call to action links related to this page.', 'inbound-pro'); ?>'>
							<?php _e('CTA Clicks', 'inbound-pro'); ?>
						</label>
					</td>
					<td class='ia-td-value'>
						<a href='<?php echo admin_url('index.php?action=inbound_generate_report&page_id='.$post->ID.'&class=Inbound_Event_Report&event_name=inbound_cta_click&range='.self::$range.'&title='.__('CTA Clicks', 'inbound-pro').'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
							<?php echo self::$statistics['cta-clicks']['current'][self::$range]; ?>
						</a>
					</td>
					<td class='ia-td-value'>
						<span class="label label-info" title='<?php _e('Rate of action events compared to impressions.', 'inbound-pro'); ?>' title="<?php echo sprintf(__('%s action rate in the last %s days versus an %s action rate in the prior %s day period)', 'inbound-pro'), self::prepare_rate_format(self::$statistics['cta-clicks']['rate']['current'][self::$range]), self::$range, self::prepare_rate_format(self::$statistics['cta-clicks']['rate']['past'][self::$range]), self::$range); ?>"><?php echo self::prepare_rate_format(self::$statistics['cta-clicks']['rate']['current'][self::$range], false); ?></span>
					</td>
					<td class='ia-td-value'>
						<span class="stat label  <?php echo (self::$statistics['cta-clicks']['rate']['difference'][self::$range] > 0) ? 'label-success' : 'label-warning'; ?>" title="<?php echo sprintf(__('%s action rate in the last %s days versus an %s action rate in the prior %s day period)', 'inbound-pro'), self::prepare_rate_format(self::$statistics['cta-clicks']['rate']['current'][self::$range]), self::$range, self::prepare_rate_format(self::$statistics['cta-clicks']['rate']['past'][self::$range]), self::$range); ?>"><?php echo self::prepare_rate_format(self::$statistics['cta-clicks']['rate']['difference'][self::$range]); ?></span>
					</td>
				</tr>
				<tr>
					<td class='ia-td-label'>
						<label title='<?php _e('Total number of clicked tracked content links related to this page.', 'inbound-pro'); ?>'>
							<?php _e('Content Clicks', 'inbound-pro'); ?>
						</label>
					</td>
					<td class='ia-td-value'>
						<a href='<?php echo admin_url('index.php?action=inbound_generate_report&page_id='.$post->ID.'&class=Inbound_Event_Report&event_name=inbound_content_click&range='.self::$range.'&title='.__('Content Clicks', 'inbound-pro').'&tb_hide_nav=true&TB_iframe=true&width=1000&height=600'); ?>' class='thickbox inbound-thickbox'>
							<?php echo self::$statistics['content-clicks']['current'][self::$range]; ?>
						</a>
					</td>
					<td class='ia-td-value'>
						<span class="label label-info" title='<?php _e('Rate of action events compared to impressions.', 'inbound-pro'); ?>' title="<?php echo sprintf(__('%s action rate in the last %s days versus an %s action rate in the prior %s day period)', 'inbound-pro'), self::prepare_rate_format(self::$statistics['content-clicks']['rate']['current'][self::$range]), self::$range, self::prepare_rate_format(self::$statistics['content-clicks']['rate']['past'][self::$range]), self::$range); ?>"><?php echo self::prepare_rate_format(self::$statistics['content-clicks']['rate']['current'][self::$range], false); ?></span>
					</td>
					<td class='ia-td-value'>
						<span class="stat label  <?php echo (self::$statistics['content-clicks']['rate']['difference'][self::$range] > 0) ? 'label-success' : 'label-warning'; ?>" title="<?php echo sprintf(__('%s action rate in the last %s days versus an %s action rate in the prior %s day period)', 'inbound-pro'), self::prepare_rate_format(self::$statistics['content-clicks']['rate']['current'][self::$range]), self::$range, self::prepare_rate_format(self::$statistics['content-clicks']['rate']['past'][self::$range]), self::$range); ?>"><?php echo self::prepare_rate_format(self::$statistics['content-clicks']['rate']['difference'][self::$range]); ?></span>
					</td>
				</tr>
				<?php
				do_action('inbound-analytics/quick-view/action-breakdown' , self::$statistics , self::$range ) ; ?>
			</table>
			<?php
		}

		public static function get_impressions($args) {
			global $post;

			$wordpress_date_time = date_i18n('Y-m-d G:i:s');

			$args['page_id'] = $post->ID;

			if ($args['skip']) {
				$args['start_date'] = date( 'Y-m-d' , strtotime("-". $args['per_days'] * ( $args['skip'] + 1 )." days" , strtotime($wordpress_date_time) ) );
				$args['end_date'] = date( 'Y-m-d' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time) ));
			} else {
				$args['start_date'] = date( 'Y-m-d' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time )));
				$args['end_date'] = $wordpress_date_time;
			}

			$result = Inbound_Events::get_page_views_by('page_id' , $args );

			return count($result);
		}

		public static function get_visitors($args) {
			global $post;

			$wordpress_date_time =  date_i18n('Y-m-d G:i:s');

			$args['page_id'] = $post->ID;

			if ($args['skip']) {
				$args['start_date'] = date( 'Y-m-d' , strtotime("-". $args['per_days'] * ( $args['skip'] + 1 )." days" , strtotime($wordpress_date_time) ) );
				$args['end_date'] = date( 'Y-m-d' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time) ));
			} else {
				$args['start_date'] = date( 'Y-m-d' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time )));
				$args['end_date'] = $wordpress_date_time;
			}

			return Inbound_Events::get_visitors_count($post->ID, $args);
		}


		public static function get_actions($args) {
			global $post;

			$wordpress_date_time = date_i18n('Y-m-d G:i:s');

			if ($args['skip']) {
				$start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] * ($args['skip'] + 1) . " days", strtotime($wordpress_date_time)));
				$end_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
			} else {
				$start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
				$end_date = $wordpress_date_time;
			}

			return Inbound_Events::get_page_actions_count($post->ID, $activity = 'any', $start_date, $end_date);
		}

		public static function get_submissions($args) {
			global $post;

			$wordpress_date_time = date_i18n('Y-m-d G:i:s');

			if ($args['skip']) {
				$start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] * ($args['skip'] + 1) . " days", strtotime($wordpress_date_time)));
				$end_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
			} else {
				$start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
				$end_date = $wordpress_date_time;
			}


			return count(Inbound_Events::get_form_submissions_by('page_id', array('page_id' => $post->ID, 'start_date' => $start_date, 'end_date' => $end_date)));
		}

		/**
		 * Get count of cta click events given a page_id and server other arguments including date time constrictions
		 * @param $args
		 * @return int
		 */
		public static function get_tracked_cta_clicks($args) {
			global $post;

			$wordpress_date_time = date_i18n('Y-m-d G:i:s');

			if ($args['skip']) {
				$start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] * ($args['skip'] + 1) . " days", strtotime($wordpress_date_time)));
				$end_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
			} else {
				$start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
				$end_date = $wordpress_date_time;
			}

			return count(Inbound_Events::get_cta_clicks_by('page_id', array('page_id' => $post->ID, 'start_date' => $start_date, 'end_date' => $end_date)));
		}


		/**
		 * Get count of content click events given a page_id and server other arguments including date time constrictions
		 * @param $args
		 * @return int
		 */
		public static function get_tracked_content_clicks($args) {
			global $post;

			$wordpress_date_time = date_i18n('Y-m-d G:i:s');

			if ($args['skip']) {
				$start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] * ($args['skip'] + 1) . " days", strtotime($wordpress_date_time)));
				$end_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
			} else {
				$start_date = date('Y-m-d G:i:s', strtotime("-" . $args['per_days'] . " days", strtotime($wordpress_date_time)));
				$end_date = $wordpress_date_time;
			}

			$events = Inbound_Events::get_content_clicks_by('page_id', array('page_id' => $post->ID, 'start_date' => $start_date, 'end_date' => $end_date));

			return count($events);
		}



		public static function get_percentage_change($current, $past) {
			$difference = $current - $past;
			$total = $current + $past;


			if (!$past && $current) {
				return 1;
			}

			if (!$past && !$current) {
				return 0;
			}

			$rate = $difference / $total;

			return round($rate * 100, 2);

		}

		public static function prepare_rate_format($rate, $plusminus = true) {
			$plus = ($plusminus) ? '+' : '';

			if ($rate == 1) {
				return '100%';
			} else if ($rate > 0) {
				return $plus . round($rate, 2) * 100 . '%';
			} else if ($rate == 0) {
				return '0%';
			} else {
				return  round($rate, 2) . '%';
			}
		}
	}

	add_action('admin_init', array('Inbound_Quick_View', 'init'), 10);
}
