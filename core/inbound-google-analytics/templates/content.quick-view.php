<?php



class Analytics_Template_Content_Quick_View {

	static $range;
	static $ga_settings;
	static $gaData;
	static $statistics;

	/**
	*	Initializes class
	*/
	public static function init() {

		/* load settings */
		self::$ga_settings = get_option('inbound_ga' , false );

		if (!isset(self::$ga_settings['linked_profile']) ||  !self::$ga_settings['linked_profile']) {
			return;
		}

		/* build timespan for analytics report */
		self::define_range();

		/* add this template to the list of available templates */
		add_filter( 'inbound_analytics_templates' , array( __CLASS__ , 'define_template' ) );

	}

	/**
	*	Given the $_GET['analytics_range'] parameter set the timespan to request analytics data on
	*/
	public static function define_range() {
		if ( !isset( $_GET['range'] ) ) {
			self::$range = 90;
		} else {
			self::$range = $_GET['range'];
		}
	}

	/**
	*	Adds template to list of available templates
	*/
	public static function define_template( $templates ) {

		$templates[ get_class() ] = array (
			'class_name' => get_class(),
			'label' => __('Content Analytics Summary' , 'inbound-pro' ),
			'report_type' => 'content_quick_view'
		);

	}

	public static function load_data() {
		
		self::load_impressions();
		self::load_visitors();
		self::load_actions();
		self::load_traffic_sources();

		return self::$statistics;
	}
	
	public static function load_impressions() {
		//error_log(self::$range);
		self::$statistics['impressions']['current'][self::$range] = self::get_impressions( array(
			'per_days' => self::$range,
			'skip' => 0
		));

		self::$statistics['impressions']['past'][self::$range] = self::get_impressions( array(
			'per_days' => self::$range,
			'skip' => 1
		));
		
		/* determine rate */
		self::$statistics['impressions']['difference'][self::$range] = self::get_percentage_change( self::$statistics['impressions']['current'][self::$range] , self::$statistics['impressions']['past'][self::$range] );

		return self::$statistics;
	}
	
	public static function load_visitors() {
				
		/* get visitor count in current time period */
		self::$statistics['visitors']['current'][self::$range] = self::get_visitors( array(
			'per_days' => self::$range,
			'skip' => 0
		));
		
		/* get visitor count in past time period */
		self::$statistics['visitors']['past'][self::$range] = self::get_visitors( array(
			'per_days' => self::$range,
			'skip' => 1
		));
		
		/* determine rate */
		self::$statistics['visitors']['difference'][self::$range] = self::get_percentage_change( self::$statistics['visitors']['current'][self::$range] , self::$statistics['visitors']['past'][self::$range] );


	}
	
	public static function load_traffic_sources() {
				
		/* get visitor count in current time period */
		self::$statistics['traffic_sources']['current'][self::$range] = self::get_traffic_sources( array(
			'per_days' => self::$range,
			'skip' => 0
		));

		/* loop through and total referrals for current date range*/
		$total = 0;
		foreach (self::$statistics['traffic_sources']['current'][self::$range] as $type => $value ) {
			 $total = $total + $value;
		}
		self::$statistics['traffic_sources']['current'][self::$range]['total'] = $total;

		/* get visitor count in past time period */
		self::$statistics['traffic_sources']['past'][self::$range] = self::get_traffic_sources( array(
			'per_days' => self::$range,
			'skip' => 1
		));

		/* loop through and total referrals for past */
		$total = 0;
		foreach (self::$statistics['traffic_sources']['past'][self::$range] as $type => $value ) {
			$total = $total + $value;
		}
		self::$statistics['traffic_sources']['past'][self::$range]['total'] = $total;


		/* determine rates */
		self::$statistics['traffic_sources']['rate']['current'][self::$range]['direct'] =  (self::$statistics['traffic_sources']['current'][self::$range]['direct']) ? self::$statistics['traffic_sources']['current'][self::$range]['direct'] / self::$statistics['traffic_sources']['current'][self::$range]['total'] : 0;
		self::$statistics['traffic_sources']['rate']['current'][self::$range]['social'] =  (self::$statistics['traffic_sources']['current'][self::$range]['social']) ? self::$statistics['traffic_sources']['current'][self::$range]['social'] / self::$statistics['traffic_sources']['current'][self::$range]['total'] : 0;
		self::$statistics['traffic_sources']['rate']['current'][self::$range]['3rdparty'] =  (self::$statistics['traffic_sources']['current'][self::$range]['3rdparty']) ? self::$statistics['traffic_sources']['current'][self::$range]['3rdparty'] / self::$statistics['traffic_sources']['current'][self::$range]['total'] : 0;
		self::$statistics['traffic_sources']['rate']['current'][self::$range]['search'] =  (self::$statistics['traffic_sources']['current'][self::$range]['search']) ? self::$statistics['traffic_sources']['current'][self::$range]['search'] / self::$statistics['traffic_sources']['current'][self::$range]['total'] : 0;

		/* determine rates */
		self::$statistics['traffic_sources']['rate']['past'][self::$range]['direct'] =  (self::$statistics['traffic_sources']['past'][self::$range]['direct']) ? self::$statistics['traffic_sources']['past'][self::$range]['direct'] / self::$statistics['traffic_sources']['past'][self::$range]['total'] : 0;
		self::$statistics['traffic_sources']['rate']['past'][self::$range]['social'] =  (self::$statistics['traffic_sources']['past'][self::$range]['social']) ? self::$statistics['traffic_sources']['past'][self::$range]['social'] / self::$statistics['traffic_sources']['past'][self::$range]['total'] : 0;
		self::$statistics['traffic_sources']['rate']['past'][self::$range]['3rdparty'] =  (self::$statistics['traffic_sources']['past'][self::$range]['3rdparty']) ? self::$statistics['traffic_sources']['past'][self::$range]['3rdparty'] / self::$statistics['traffic_sources']['past'][self::$range]['total'] : 0;
		self::$statistics['traffic_sources']['rate']['past'][self::$range]['search'] =  (self::$statistics['traffic_sources']['past'][self::$range]['search']) ? self::$statistics['traffic_sources']['past'][self::$range]['search'] / self::$statistics['traffic_sources']['past'][self::$range]['total'] : 0;


		/* determine rate differences */
		self::$statistics['traffic_sources']['rate']['difference'][self::$range]['direct'] =  self::get_percentage_change( self::$statistics['traffic_sources']['rate']['current'][self::$range]['direct'] , self::$statistics['traffic_sources']['rate']['past'][self::$range]['direct'] );
		self::$statistics['traffic_sources']['rate']['difference'][self::$range]['social'] =  self::get_percentage_change( self::$statistics['traffic_sources']['rate']['current'][self::$range]['social'] , self::$statistics['traffic_sources']['rate']['past'][self::$range]['social'] );
		self::$statistics['traffic_sources']['rate']['difference'][self::$range]['3rdparty'] =    self::get_percentage_change( self::$statistics['traffic_sources']['rate']['current'][self::$range]['3rdparty'] , self::$statistics['traffic_sources']['rate']['past'][self::$range]['3rdparty'] );
		self::$statistics['traffic_sources']['rate']['difference'][self::$range]['search'] =  self::get_percentage_change( self::$statistics['traffic_sources']['rate']['current'][self::$range]['search'] , self::$statistics['traffic_sources']['rate']['past'][self::$range]['search'] );

		error_log(print_r(self::$statistics,true));

	}

	
	public static function load_actions() {
		self::load_action_totals();
		self::load_submissions();
		self::load_clicks();
		/* self::load_custom_events(); */
	}
	
	
	public static function load_action_totals() {
				
		/* get action count in current time period */
		self::$statistics['actions']['current'][self::$range] = self::get_actions( array(
			'per_days' => self::$range,
			'skip' => 0
		));
		
		/* get action count in past time period */
		self::$statistics['actions']['past'][self::$range] = self::get_actions( array(
			'per_days' => self::$range,
			'skip' => 1
		));	
		
		
		/* determine difference rate */
		self::$statistics['actions']['difference'][self::$range] = self::get_percentage_change( self::$statistics['actions']['current'][self::$range] , self::$statistics['actions']['past'][self::$range] );
				
		/* determine action to impression rate for current time period */
		self::$statistics['actions']['rate']['current'][self::$range] =  (self::$statistics['impressions']['current'][self::$range]) ? self::$statistics['actions']['current'][self::$range] / self::$statistics['impressions']['current'][self::$range] : 0;
		
		/* determine action to impression rate for past time period */
		self::$statistics['actions']['rate']['past'][self::$range] =  (self::$statistics['impressions']['past'][self::$range]) ? self::$statistics['actions']['past'][self::$range] / self::$statistics['impressions']['past'][self::$range] : 0;
			
		/* determine action to impression rate for past time period */
		self::$statistics['actions']['rate']['difference'][self::$range] =  self::get_percentage_change( self::$statistics['actions']['rate']['current'][self::$range] , self::$statistics['actions']['rate']['past'][self::$range] );
		
	}
	
	public static function load_submissions() {
		/* get form submission count in current time period */
		self::$statistics['submissions']['current'][self::$range] = self::get_submissions( array(
			'per_days' => self::$range,
			'skip' => 0
		));
		
		/* get action count in past time period */
		self::$statistics['submissions']['past'][self::$range] = self::get_submissions( array(
			'per_days' => self::$range,
			'skip' => 1
		));
		
		/* determine difference rate */
		self::$statistics['submissions']['difference'][self::$range] = self::get_percentage_change( self::$statistics['actions']['current'][self::$range] , self::$statistics['actions']['past'][self::$range] );
		
		/* determine action to impression rate for current time period */
		self::$statistics['submissions']['rate']['current'][self::$range] =  (self::$statistics['impressions']['current'][self::$range]) ? self::$statistics['submissions']['current'][self::$range] / self::$statistics['impressions']['current'][self::$range] : 0;

		/* determine action to impression rate for current time period */
		self::$statistics['submissions']['rate']['past'][self::$range] =  (self::$statistics['impressions']['past'][self::$range]) ? self::$statistics['submissions']['past'][self::$range] / self::$statistics['impressions']['past'][self::$range] : 0;

		/* determine action to impression rate for past time period */
		self::$statistics['actions']['rate']['past'][self::$range] =  (self::$statistics['impressions']['past'][self::$range]) ? self::$statistics['submissions']['past'][self::$range] / self::$statistics['impressions']['past'][self::$range] : 0;
			
		/* determine action to impression rate for past time period */
		self::$statistics['actions']['rate']['difference'][self::$range] =  self::get_percentage_change( self::$statistics['submissions']['rate']['current'][self::$range] , self::$statistics['submissions']['rate']['past'][self::$range] );
		
		
	}
	
	public static function load_clicks() {
					
		/* get cta clickthrough count in current time period */
		self::$statistics['clicks']['current'][self::$range] = self::get_tracked_clicks( array(
			'per_days' => self::$range,
			'skip' => 0
		));
		
		/* get cta clickthrough count in past time period */
		self::$statistics['clicks']['past'][self::$range] = self::get_tracked_clicks( array(
			'per_days' => self::$range,
			'skip' => 1
		));
		
		/* determine difference rate */
		self::$statistics['clicks']['difference'][self::$range] = self::get_percentage_change( self::$statistics['clicks']['current'][self::$range] , self::$statistics['clicks']['past'][self::$range] );
		
		/* determine action to impression rate for current time period */
		self::$statistics['clicks']['rate']['current'][self::$range] =  (self::$statistics['clicks']['current'][self::$range]) ? self::$statistics['actions']['current'][self::$range] / self::$statistics['impressions']['current'][self::$range] : 0;
		
		/* determine action to impression rate for past time period */
		self::$statistics['clicks']['rate']['past'][self::$range] =  (self::$statistics['clicks']['past'][self::$range]) ? self::$statistics['actions']['past'][self::$range] / self::$statistics['impressions']['past'][self::$range] : 0;
			
		/* determine action to impression rate for past time period */
		self::$statistics['clicks']['rate']['difference'][self::$range] =  self::get_percentage_change( self::$statistics['actions']['rate']['current'][self::$range] , self::$statistics['actions']['rate']['past'][self::$range] );
		
	}
	
	public static function load_custom_events() {
		/* get cta clickthrough count in current time period */
		self::$statistics['custom_events']['current'][self::$range] = self::get_custom_events( array(
			'per_days' => self::$range,
			'skip' => 0
		));
		
		/* get cta clickthrough count in past time period */
		self::$statistics['custom_events']['past'][self::$range] = self::get_custom_events( array(
			'per_days' => self::$range,
			'skip' => 1
		));
		
		/* determine difference rate */
		self::$statistics['custom_events']['difference'][self::$range] = self::get_percentage_change( self::$statistics['custom_events']['current'][self::$range] , self::$statistics['custom_events']['past'][self::$range] );
		
		
		/* determine action to impression rate for current time period */
		self::$statistics['custom_events']['rate']['current'][self::$range] =  (self::$statistics['custom_events']['current'][self::$range]) ? self::$statistics['actions']['current'][self::$range] / self::$statistics['impressions']['current'][self::$range] : 0;
		
		/* determine action to impression rate for past time period */
		self::$statistics['custom_events']['rate']['past'][self::$range] =  (self::$statistics['custom_events']['past'][self::$range]) ? self::$statistics['actions']['past'][self::$range] / self::$statistics['impressions']['past'][self::$range] : 0;
			
		/* determine action to impression rate for past time period */
		self::$statistics['custom_events']['rate']['difference'][self::$range] =  self::get_percentage_change( self::$statistics['actions']['rate']['current'][self::$range] , self::$statistics['actions']['rate']['past'][self::$range] );
		
	}
	
	/**
	*	Loads the analytics template
	*
	*/
	public static function load_template( ) {
		
		self::load_data();
		self::display_navigation();
		/* self::display_sparkline(); */
		self::display_content_breakdown();
		self::display_action_breakdown();
		self::display_referring_traffic_breakdown();

	}

	public static function display_navigation() {
		global $post;
		$base = 'post.php?post='.$post->ID.'&action=edit';
		?>
		<ul class="nav nav-pills date-range">
			<li <?php echo ( self::$range == 1 ) ? "class='active'" : "class=''"; ?> data-range='1' title='<?php _e('Past 24 hours' , 'inbound-pro'); ?>'>
				<a href='<?php echo $base ;?>&range=1'>1</a>
			</li>
			<li <?php echo ( self::$range == 7 ) ? "class='active'" : "class=''"; ?> data-range='7' title='<?php _e('Past 7 hours' , 'inbound-pro'); ?>'>
				<a href='<?php echo $base ;?>&range=7'>7</a>
			</li>
			<li <?php echo ( self::$range == 30 ) ? "class='active'" : "class=''"; ?> data-range='30' title='<?php _e('Past 30 days' , 'inbound-pro'); ?>'>
				<a href='<?php echo $base ;?>&range=30'>30</a>
			</li>
			<li <?php echo ( self::$range == 90 ) ? "class='active'" : "class=''"; ?> data-range='90' title='<?php _e('Past 90 days' , 'inbound-pro'); ?>'>
				<a href='<?php echo $base ;?>&range=90'>90</a>
			</li>
			<li <?php echo ( self::$range == 365 ) ? "class='active'" : "class=''"; ?> data-range='365' title='<?php _e('Past 365 days' , 'inbound-pro'); ?>'>
				<a href='<?php echo $base ;?>&range=365'>365</a>
			</li>
		</ul>
		<?php
	}

	public static function display_sparkline() {
		?>
		<br>
		<img src='<?php echo INBOUND_GA_URLPATH; ?>assets/img/example-sparkline.png' title='Example sparkline'>
		<?php
	}
	
	public static function display_content_breakdown() {

		?>
		<table class='ia-table-summary'>
			<tr>
				<td class='ia-td-th'>
					<label title='<?php _e( 'Type fo statistic.' , 'inbound-pro'); ?>'>
					<?php _e( 'Statistic' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td class='ia-td-th'>
					<label title='<?php _e( 'Statistic value for given time period' , 'inbound-pro'); ?>'>
					<?php _e( 'Value' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td class='ia-td-th'  title='<?php _e( 'Change in growth compared to corresponding previous timeperiod.' , 'inbound-pro'); ?>'>
					<label>
					<?php _e( 'Change' , 'inbound-pro' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<td class='ia-td-label' >
					<label title='<?php _e( 'Total number of visits to this page' , 'inbound-pro' ); ?>'>
					<?php _e( 'Impressions:' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td>
					<a href='#' class='count' data-toggle="modal-disabled" data-target="#ia-modal-container" report-class-name="Analytics_Template_Content_Impressions_Expanded" modal-width='60%'><?php echo self::$statistics['impressions']['current'][self::$range]; ?></a>
				</td>

				<td>
					<span class='stat label <?php echo ( self::$statistics['impressions']['difference'][self::$range] > 0  ) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf( __('%s impressions in the last %s days versus %s impressions in the prior %s day period)' , 'inbound-pro' ) , self::$statistics['impressions']['current'][self::$range] , self::$range, self::$statistics['impressions']['past'][self::$range] , self::$range ) ; ?>"  data-toggle="tooltip" data-placement="left" ><?php echo self::prepare_rate_format( self::$statistics['impressions']['difference'][self::$range] ); ?></a>
				</td>
			</tr>
			<tr>
				<td class='ia-td-label' >
					<label title='<?php _e( 'Total number of visitors' , 'inbound-pro' ); ?>'>
					<?php _e( 'Visitors:' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td>
					<a href='#' class='count'><?php echo self::$statistics['visitors']['current'][self::$range]; ?></a>
				</td>

				<td>
					<span class='stat label  <?php echo ( self::$statistics['visitors']['difference'][self::$range] > 0  ) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf( __('%s visitors in the last %s days versus %s visitors in the prior %s day period)' , 'inbound-pro' ) , self::$statistics['visitors']['current'][self::$range] , self::$range, self::$statistics['visitors']['past'][self::$range] , self::$range ) ; ?>"><?php echo self::prepare_rate_format( self::$statistics['visitors']['difference'][self::$range] ); ?></span>
				</td>
			</tr>
			<tr>
				<td class='ia-td-label'>
					<label title='<?php _e( 'Total number of event actions originating from this page.' , 'inbound-pro' ); ?>'>
					<?php _e( 'Actions:' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td class='ia-td-value'>
					<a href='#' class='count'><?php echo self::$statistics['actions']['current'][self::$range]; ?></a>
				</td>
				<td class='ia-td-value'>
					<span class='stat label  <?php echo ( self::$statistics['actions']['difference'][self::$range] > 0  ) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf( __('%s actions in the last %s days versus %s actions in the prior %s day period)' , 'inbound-pro' ) , self::$statistics['actions']['current'][self::$range] , self::$range, self::$statistics['actions']['past'][self::$range] , self::$range ) ; ?>"  data-toggle="tooltip" data-placement="left" ><?php echo self::prepare_rate_format( self::$statistics['actions']['difference'][self::$range] ); ?></a>
				
				</td>
			</tr>
			<tr>
				<td class='ia-td-label'>
					<label title='<?php _e( 'Total percentage of actions to impressions.' , 'inbound-pro' ); ?>'>
					<?php _e( 'Action Rate:' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td class='ia-td-value'>
					<span class="label label-info"><?php echo self::prepare_rate_format(self::$statistics['actions']['rate']['current'][self::$range] , false); ?></span>
				</td>
				<td class='ia-td-value'>
					<span class='stat label  <?php echo ( self::$statistics['actions']['rate']['difference'][self::$range] > 0  ) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf( __('%s action rate in the last %s days versus an %s action rate in the prior %s day period)' , 'inbound-pro' ) , self::prepare_rate_format(self::$statistics['actions']['rate']['current'][self::$range]) , self::$range, self::prepare_rate_format(self::$statistics['actions']['rate']['past'][self::$range]) , self::$range ) ; ?>"><?php echo self::prepare_rate_format(self::$statistics['actions']['rate']['difference'][self::$range]); ?></span> 
				</td>
			</tr>
		</table>
		<?php
	}
	
	public static function display_action_breakdown() {
		?>
		<br>
		<table class='ia-table-summary'>
			<tr>
				<td class='ia-td-th'>
					<?php _e( 'Action Breakdown' , 'inbound-pro' ); ?>
				</td>
				<td class='ia-td-th'>
					<?php _e( 'Count' , 'inbound-pro' ); ?>
				</td>
				<td class='ia-td-th'>
					<?php _e( 'Rate' , 'inbound-pro' ); ?>
				</td>
				<td class='ia-td-th'>
					<?php _e( 'Change' , 'inbound-pro' ); ?>
				</td>
			</tr>
			<tr>
				<td class='ia-td-label'>
					<label title='<?php _e( 'Total number of Inbound Form submissions originating from this page' , 'inbound-pro' ); ?>'><?php _e( 'Form Submissions' , 'inbound-pro');?>:</label>
				</td>
				<td class='ia-td-value'>
					<a href='#' class='count'  title='<?php _e( 'Total number of form submissions performed within the given time period for this content.' , 'inbound-pro' ); ?>'><?php echo self::$statistics['submissions']['current'][self::$range]; ?></a>
				</td>
				<td class='ia-td-value'>
					<span class="label label-info" title='<?php _e( 'Rate of action events compared to impressions.' , 'inbound-pro' ); ?>'><?php echo self::prepare_rate_format(self::$statistics['submissions']['rate']['current'][self::$range] , false ); ?></span>
				</td>
				<td class='ia-td-value'>
					<span class='stat label <?php echo ( self::$statistics['submissions']['difference'][self::$range] > 0  ) ? 'label-success' : 'label-warning'; ?>' title="<?php echo sprintf( __('%s action rate in the last %s days versus an %s action rate in the prior %s day period)' , 'inbound-pro' ) , self::prepare_rate_format(self::$statistics['submissions']['rate']['current'][self::$range]) , self::$range, self::prepare_rate_format(self::$statistics['submissions']['rate']['past'][self::$range]) , self::$range ) ; ?>"><?php echo self::prepare_rate_format(self::$statistics['actions']['rate']['difference'][self::$range]); ?></span>
				</td>
			</tr>
			<tr>
				<td class='ia-td-label'>
					<label title='<?php _e( 'Total number of clicked tracked links related to this page.' , 'inbound-pro' ); ?>'>
						<?php _e( 'Tracked Click Events' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td class='ia-td-value'>
					<a href='#' class='count' title='<?php _e( 'Total number of actions performed within the given time period for this content.' , 'inbound-pro' ); ?>'><?php echo self::$statistics['clicks']['current'][self::$range]; ?></a>
				</td>
				<td class='ia-td-value'>
					<span class="label label-info" title='<?php _e( 'Rate of action events compared to impressions.' , 'inbound-pro' ); ?>'  title="<?php echo sprintf( __('%s action rate in the last %s days versus an %s action rate in the prior %s day period)' , 'inbound-pro' ) , self::prepare_rate_format(self::$statistics['clicks']['rate']['current'][self::$range]) , self::$range, self::prepare_rate_format(self::$statistics['clicks']['rate']['past'][self::$range]) , self::$range ) ; ?>"><?php echo self::prepare_rate_format(self::$statistics['clicks']['rate']['current'][self::$range] , false ); ?></span>
				</td>
				<td class='ia-td-value'>
					<span class="stat label  <?php echo ( self::$statistics['clicks']['rate']['difference'][self::$range] > 0  ) ? 'label-success' : 'label-warning'; ?>"  title="<?php echo sprintf( __('%s action rate in the last %s days versus an %s action rate in the prior %s day period)' , 'inbound-pro' ) , self::prepare_rate_format(self::$statistics['clicks']['rate']['current'][self::$range]) , self::$range, self::prepare_rate_format(self::$statistics['clicks']['rate']['past'][self::$range]) , self::$range ) ; ?>"><?php echo self::prepare_rate_format(self::$statistics['clicks']['rate']['difference'][self::$range]); ?></span>
				</td>
			</tr>
		</table>
		<?php
	}
	
	public static function display_referring_traffic_breakdown() {
		?>
		<br>
		<table class='ia-table-summary'>
			<tr>
				<td class='ia-td-th' >
					<label title='<?php _e( 'The statistics below reveal where traffic has arrived on this content page from.' , 'inbound-pro'); ?>'>
					<?php _e( 'Traffic Breakdown' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td class='ia-td-th' >
					<label title='<?php _e( 'Number of visits within set timeperiod.' , 'inbound-pro'); ?>'>
					<?php _e( 'Count' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td class='ia-td-th'>
					<label title='<?php _e( 'Percent of these visits compared to all referred traffic.' , 'inbound-pro'); ?>'>
						<?php _e( 'Rate' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td class='ia-td-th'>
					<label title='<?php _e( 'Change in growth compared to corresponding previous timeperiod.' , 'inbound-pro'); ?>'>
					<?php _e( 'Change' , 'inbound-pro' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<td class='ia-td-label'>
					<label title='<?php _e( 'Total number of visits to this page by directly accessing the URL.' , 'inbound-pro' ); ?>'>
					<?php _e( 'Direct Access' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td class='ia-td-value'>
					<a href='#' class='count'  title='<?php _e( 'Total number of visits without a referral.' , 'inbound-pro' ); ?>'>
						<?php echo self::$statistics['traffic_sources']['current'][self::$range]['direct']; ?>
					</a>
				</td>
				<td class='ia-td-value'>
					<span class="label label-info"  title='<?php _e( 'Rate of direct access visits versus other types of referrals.' , 'inbound-pro' ); ?>'>
						<?php echo self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['current'][self::$range]['direct'] , false ); ?>
					</span>
				</td>
				<td class='ia-td-value'>
					<span class='stat label  <?php echo ( self::$statistics['traffic_sources']['rate']['difference'][self::$range]['direct'] > 0  ) ? 'label-success' : 'label-warning'; ?>'  title="<?php echo sprintf( __('%s referrer volume rate of in the last %s days versus an %s action rate in the prior %s day period)' , 'inbound-pro' ) , self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['current'][self::$range]['direct']) , self::$range, self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['past'][self::$range]['direct']) , self::$range ) ; ?>">
						<?php echo self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['difference'][self::$range]['direct']); ?>
					</span>
				</td>
			</tr>
			<tr>
				<td class='ia-td-label'>
					<label title='<?php _e( 'Total number of visits referred by an external site. This statistic excludes major search engines and social sites as those are listed in a separate statistic below.' , 'inbound-pro' ); ?>'>
					<?php _e( '3rd Party' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td class='ia-td-value'>
					<a href='#' class='count'  title='<?php _e( 'Total number of visits referred by an external site.' , 'inbound-pro' ); ?>'>
						<?php echo self::$statistics['traffic_sources']['current'][self::$range]['3rdparty']; ?>
					</a>
				</td>
				<td class='ia-td-value'>
					<span class="label label-info" title='<?php _e( 'Rate of 3rd party referrals versus other types of referrals.' , 'inbound-pro' ); ?>'>
						<?php echo self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['current'][self::$range]['3rdparty'] , false ); ?>
					</span>
				</td>
				<td class='ia-td-value'>
					<span class='stat label  <?php echo ( self::$statistics['traffic_sources']['rate']['difference'][self::$range]['3rdparty'] > 0  ) ? 'label-success' : 'label-warning'; ?>'  title="<?php echo sprintf( __('%s referrer volume rate of in the last %s days versus an %s action rate in the prior %s day period)' , 'inbound-pro' ) , self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['current'][self::$range]['3rdparty']) , self::$range, self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['past'][self::$range]['3rdparty']) , self::$range ) ; ?>">
						<?php echo self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['difference'][self::$range]['3rdparty']); ?>
					</span>
				</td>
			</tr>
			<tr>
				<td class='ia-td-label'>
					<label title='<?php _e( 'Total number of visits referred by a search engine. Search engine must be major search engine to be included in this statistic.' , 'inbound-pro' ); ?>'>
						<?php _e( 'Search Engine' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td class='ia-td-value'>
					<a href='#' class='count'  title='<?php _e( 'Total number of visits referred by a search engine.' , 'inbound-pro' ); ?>'>
					<?php echo self::$statistics['traffic_sources']['current'][self::$range]['search']; ?>
					</a>
				</td>
				<td class='ia-td-value'>
					<span class="label label-info" title='<?php _e( 'Rate of search engine referrals versus other types of referrals.' , 'inbound-pro' ); ?>'>
						<?php echo self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['current'][self::$range]['search'] , false ); ?>
					</span>
				</td>
				<td class='ia-td-value'>
					<span class='stat label  <?php echo ( self::$statistics['traffic_sources']['rate']['difference'][self::$range]['search'] > 0  ) ? 'label-success' : 'label-warning'; ?>'  title="<?php echo sprintf( __('%s referrer volume rate of in the last %s days versus an %s action rate in the prior %s day period)' , 'inbound-pro' ) , self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['current'][self::$range]['search']) , self::$range, self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['past'][self::$range]['search']) , self::$range ) ; ?>">
						<?php echo self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['difference'][self::$range]['search']); ?>
					</span>
				</td>
			</tr>
			<tr>
				<td class='ia-td-label'>
					<label title='<?php _e( 'Total number of visits referred by a major social media site.' , 'inbound-pro' ); ?>'>
					<?php _e( 'Social Media' , 'inbound-pro' ); ?>
					</label>
				</td>
				<td class='ia-td-value'>
					<a href='#' class='count'  title='<?php _e( 'Total number of visits referred by a search engine.' , 'inbound-pro' ); ?>'>
						<?php echo self::$statistics['traffic_sources']['current'][self::$range]['social']; ?>
					</a>
				</td>
				<td class='ia-td-value'>
					<span class="label label-info"  title='<?php _e( 'Rate of search engine referrals versus other types of referrals.' , 'inbound-pro' ); ?>'>
						<?php echo self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['current'][self::$range]['social'] , false ); ?>
					</span>
				</td>
				<td class='ia-td-value'>
					<span class='stat label  <?php echo ( self::$statistics['traffic_sources']['rate']['difference'][self::$range]['social'] > 0  ) ? 'label-success' : 'label-warning'; ?>'  title="<?php echo sprintf( __('%s referrer volume rate of in the last %s days versus an %s action rate in the prior %s day period)' , 'inbound-pro' ) , self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['current'][self::$range]['social']) , self::$range, self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['past'][self::$range]['social']) , self::$range ) ; ?>">
						<?php echo self::prepare_rate_format(self::$statistics['traffic_sources']['rate']['difference'][self::$range]['social']); ?>
					</span>
				</td>
			</tr>
		</table>
		<?php
	}
	
	public static function get_impressions( $args ) {
		global $post;

		$default = array(
			'per_days' => 30 ,
			'skip' => 0,
			'query' => 'impressions',
			'path' => Inbound_Google_Connect::get_relative_permalink( $post->ID )
		);
		
		$request = array_replace( $default , $args );
		
		return Inbound_Google_Connect::load_data( $request );
	}

	public static function get_visitors( $args ) {
		global $post;

		$default = array(
			'per_days' => 30 ,
			'skip' => 0,
			'query' => 'visitors',
			'path' => Inbound_Google_Connect::get_relative_permalink( $post->ID )
		);
		
		$request = array_replace( $default , $args );
		
		return Inbound_Google_Connect::load_data( $request );
	}
	
	
	public static function get_traffic_sources( $args ) {
		global $post;

		$default = array(
			'per_days' => 30 ,
			'skip' => 0,
			'query' => 'traffic_sources',
			'path' => Inbound_Google_Connect::get_relative_permalink( $post->ID )
		);
		
		$request = array_replace( $default , $args );
		
		return Inbound_Google_Connect::load_data( $request );
	}


	public static function get_actions( $args ) {
		global $post;
		
		$wordpress_date_time =  date_i18n('Y-m-d G:i:s');
		
		if ($args['skip']) {
			$start_date = date( 'Y-m-d G:i:s' , strtotime("-". $args['per_days'] * ( $args['skip'] + 1 )." days" , strtotime($wordpress_date_time) ) );
			$end_date = date( 'Y-m-d G:i:s' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time) ));
		} else {
			$start_date = date( 'Y-m-d G:i:s' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time )));
			$end_date = $wordpress_date_time;
		}

		
		return Inbound_Events::get_page_actions($post->ID , $activity = 'any' , $start_date, $end_date );
	}

	public static function get_submissions( $args ) {
		global $post;
		
		$wordpress_date_time =  date_i18n('Y-m-d G:i:s');
		
		if ($args['skip']) {
			$start_date = date( 'Y-m-d G:i:s' , strtotime("-". $args['per_days'] * ( $args['skip'] + 1 )." days" , strtotime($wordpress_date_time) ) );
			$end_date = date( 'Y-m-d G:i:s' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time) ));
		} else {
			$start_date = date( 'Y-m-d G:i:s' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time )));
			$end_date = $wordpress_date_time;
		}

		
		return count(Inbound_Events::get_form_submissions_by( 'page_id' , array( 'page_id' => $post->ID , 'start_date' => $start_date , 'end_date' => $end_date )));
	}
	
	public static function get_tracked_clicks( $args ) {
		global $post;
		
		$wordpress_date_time =  date_i18n('Y-m-d G:i:s');
		
		if ($args['skip']) {
			$start_date = date( 'Y-m-d G:i:s' , strtotime("-". $args['per_days'] * ( $args['skip'] + 1 )." days" , strtotime($wordpress_date_time) ) );
			$end_date = date( 'Y-m-d G:i:s' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time) ));
		} else {
			$start_date = date( 'Y-m-d G:i:s' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time )));
			$end_date = $wordpress_date_time;
		}

		
		return count(Inbound_Events::get_cta_clicks_by( 'page_id' ,  array( 'page_id' => $post->ID , 'start_date' => $start_date, 'end_date' => $end_date )));
	}
	
	public static function get_custom_events( $args ) {
		global $post;
		
		$wordpress_date_time =  date_i18n('Y-m-d G:i:s');
		
		if ($args['skip']) {
			$start_date = date( 'Y-m-d G:i:s' , strtotime("-". $args['per_days'] * ( $args['skip'] + 1 )." days" , strtotime($wordpress_date_time) ) );
			$end_date = date( 'Y-m-d G:i:s' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time) ));
		} else {
			$start_date = date( 'Y-m-d G:i:s' , strtotime("-".$args['per_days']." days" , strtotime($wordpress_date_time )));
			$end_date = $wordpress_date_time;
		}

		
		return count(Inbound_Events::get_custom_event_data_by( 'page_id' , array( 'page_id' => $post->ID , 'start_date' => $start_date, 'end_date' => $end_date )));
	}


	public static function get_percentage_change( $current , $past ) {
		$difference = $current - $past;
		$total =  $current + $past;
		
		
		if (!$past && $current) {
			return 1;
		}

		if (!$past && !$current) {
			return 0;
		}
		
		$rate = $difference / $total;

		return round($rate * 100 , 2);	
		
	}
	
	public static function prepare_rate_format( $rate , $plusminus = true ) {
		$plus = ($plusminus) ? '+' : '';
		$minus = ($plusminus) ? '-' : '';

		if ($rate == 1 ) {
			return '100%';
		} else if ($rate > 0 ) {
			return $plus . round($rate , 2).'%';
		} else if ($rate==0) {
			return '0%';
		} else {
			return $minus . round($rate , 2) .'%';
		}
	}
}

add_action( 'admin_init' , array( 'Analytics_Template_Content_Quick_View' , 'init' ) , 10 );

