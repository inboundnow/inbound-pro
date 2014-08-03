<?php


if ( !class_exists('Analytics_Teamplte_Content_Quick_View') ) {

	class Analytics_Teamplte_Content_Quick_View {
		
		static $range;
		
		/**
		*	Initializes class
		*/
		public static function init() {
			
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
				self::$range = 30;
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
		
		/**
		*	Loads the analytics template
		*	
		*/
		public static function load_template( ) {
			
			$data = self::load_data();
			
			?>
				<div class='ia-range-buttons'>
					<div class='timespan-img' data-range='1' title='<?php _e('Past 24 hours' , 'inbound-pro'); ?>'>1</div>
					<div class='timespan-img' data-range='7' title='<?php _e('Past 7 hours' , 'inbound-pro'); ?>'>7</div>
					<div class='timespan-img' data-range='30' title='<?php _e('Past 30 days' , 'inbound-pro'); ?>'>30</div>
					<div class='timespan-img' data-range='90' title='<?php _e('Past 90 days' , 'inbound-pro'); ?>'>90</div>
					<div class='timespan-img' data-range='365' title='<?php _e('Past 365 days' , 'inbound-pro'); ?>'>365</div>
				</div>
				<br>
				<div id="chart_div" style="width: 275px;margin-left:-13px;height:196px;"></div>
				<script type="text/javascript">
				google.load("visualization", "1", {packages:["corechart"]});
				google.setOnLoadCallback(drawChart);
				function drawChart() {
				var data = google.visualization.arrayToDataTable([
					['Day', 'Impressions', 'Actions'],
					['7/15',	1000,		5],
					['7/16',	1000,		6],
					['7/17',	1000,		7],
					['7/18',	1000,		5],
					['7/19',	1170,		10],
					['7/20',	660,		9],
					['7/21',	1030,		11]
				]);

				var options = {
					title: 'Content Impressions',
					colors: ['darkgrey', '#e6693e', '#ec8f6e', '#f3b49f', '#f6c7b6']
				};

				var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
				chart.draw(data, options);
				}
				</script>
				<br>
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
							<a href='http://www.yahoo.com' class='ia-int' data-toggle="modal" data-target="#ia-modal-container" data-remote='http://www.yahoo.com'>800</a>
						</td>
						
						<td>	
							<a href='' class='ia-rate-change positive' title="<?php _e('growth compared to last time period' , 'inbound-pro' ); ?>"  data-toggle="tooltip" data-placement="left" >.01%</a>
						</td>
					</tr>
					<tr>
						<td class='ia-td-label' >
							<label title='<?php _e( 'Total number of visitors' , 'inbound-pro' ); ?>'>
							<?php _e( 'Visitors:' , 'inbound-pro' ); ?>
							</label>
						</td>
						<td>	
							<a href='' class='ia-int'>623</a>
						</td>
						
						<td>	
							<a href='' class='ia-rate-change positive' title="<?php _e('growth compared to last time period' , 'inbound-pro' ); ?>">.01%</a>
						</td>
					</tr>
					<tr>
						<td class='ia-td-label'>
							<label title='<?php _e( 'Total number of event actions originating from this page.' , 'inbound-pro' ); ?>'>
							<?php _e( 'Actions:' , 'inbound-pro' ); ?>
							</label>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-int'>45</a>
						</td>						
						<td class='ia-td-value'>	
							<a href='' class='ia-rate-change positive' title="<?php _e('growth compared to last time period' , 'inbound-pro' ); ?>">.01%</a>
						</td>
					</tr>
					<tr>
						<td class='ia-td-label'>
							<label title='<?php _e( 'Total percentage of actions to impressions.' , 'inbound-pro' ); ?>'>
							<?php _e( 'Action Rate:' , 'inbound-pro' ); ?>
							</label>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate'>5.6%</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate-change positive' title="<?php _e('growth compared to last time period' , 'inbound-pro' ); ?>">.01%</a>
						</td>
					</tr>
				</table>
				
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
							<label title='<?php _e( 'Total number of Inbound Form submissions originating from this page' , 'inbound-pro' ); ?>'>Form Submissions:</label>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-int'  title='<?php _e( 'Total number of actions performed within the given time period for this content.' , 'inbound-pro' ); ?>'>35</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate' title='<?php _e( 'Rate of action events compared to impressions.' , 'inbound-pro' ); ?>'>4.3%</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate-change negative'  title='<?php _e( 'Change from last corresponding time period.' , 'inbound-pro' ); ?>'>.001%</a>
						</td>
					</tr>
					<tr>
						<td class='ia-td-label'>
							<label title='<?php _e( 'Total number of clicked tracked links related to this page.' , 'inbound-pro' ); ?>'>
								<?php _e( 'Tracked Click Events' , 'inbound-pro' ); ?>
							</label>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-int' title='<?php _e( 'Total number of actions performed within the given time period for this content.' , 'inbound-pro' ); ?>'>5</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate' title='<?php _e( 'Rate of action events compared to impressions.' , 'inbound-pro' ); ?>'>0.6%</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate-change negative'  title='<?php _e( 'Change from last corresponding time period.' , 'inbound-pro' ); ?>'>.001%</a>
						</td>
					</tr>
					<tr>
						<td class='ia-td-label'>
							<label title='<?php _e( 'Total number of Inbound Form submissions originating from this page' , 'inbound-pro' ); ?>'>
								<?php _e( 'Custom Event Submissions' , 'inbound-pro' ); ?>
							</label>
						</td>
						<td>	
							<a href='' class='ia-int' title='<?php _e( 'Total number of actions performed within the given time period for this content.' , 'inbound-pro' ); ?>'>5</a>
						</td>
						<td>	
							<a href='' class='ia-rate' title='<?php _e( 'Rate of action events compared to impressions.' , 'inbound-pro' ); ?>'>0.6%</a>
						</td>
						<td>	
							<a href='' class='ia-rate-change negative' title='<?php _e( 'Change from last corresponding time period.' , 'inbound-pro' ); ?>'>.001%</a>
						</td>
					</tr>
				</table>
				
				<br>
				<table class='ia-table-summary'>
					<tr>
						<td class='ia-td-th' >
							<label title='<?php _e( 'The statistics below reveal where traffic has arrived on this content page from.' , 'inbound-pro'); ?>'>
							<?php _e( 'Referring Traffic Breakdown' , 'inbound-pro' ); ?>
							</label>
						</td>
						<td class='ia-td-th' >	
							<label title='<?php _e( 'Number of visits within set timeperiod.' , 'inbound-pro'); ?>'>
							<?php _e( 'Count' , 'inbound-pro' ); ?>
							</label>
						</td>
						<td class='ia-td-th'>	
							<label title='<?php _e( 'Percent of these visits compared to all referred traffic.' , 'inbound-pro'); ?>'>
								<?php _e( '%' , 'inbound-pro' ); ?>
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
							<a href='' class='ia-int'  title='<?php _e( 'Total number of visits without a referral.' , 'inbound-pro' ); ?>'>100</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate' title='<?php _e( 'Rate of direct access visits versus other types of referrals.' , 'inbound-pro' ); ?>'>10%</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate-change positive'  title='<?php _e( 'Change from last corresponding time period.' , 'inbound-pro' ); ?>'>.001%</a>
						</td>
					</tr>
					<tr>
						<td class='ia-td-label'>
							<label title='<?php _e( 'Total number of visits to this page from another page on this site.' , 'inbound-pro' ); ?>'>
								<?php _e( 'Internal Access' , 'inbound-pro' ); ?>
							</label>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-int'  title='<?php _e( 'Total number of visits without a referral.' , 'inbound-pro' ); ?>'>200</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate' title='<?php _e( 'Rate of direct access visits versus other types of referrals.' , 'inbound-pro' ); ?>'>50%</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate-change positive'  title='<?php _e( 'Change from last corresponding time period.' , 'inbound-pro' ); ?>'>.001%</a>
						</td>
					</tr>
					<tr>
						<td class='ia-td-label'>
							<label title='<?php _e( 'Total number of visits referred by an external site. This statistic excludes major search engines and social sites as those are listed in a separate statistic below.' , 'inbound-pro' ); ?>'>
							<?php _e( '3rd Party' , 'inbound-pro' ); ?>
							</label>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-int'  title='<?php _e( 'Total number of visits referred by an external site.' , 'inbound-pro' ); ?>'>100</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate' title='<?php _e( 'Rate of 3rd party referrals versus other types of referrals.' , 'inbound-pro' ); ?>'>10%</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate-change positive'  title='<?php _e( 'Change from last corresponding time period.' , 'inbound-pro' ); ?>'>.001%</a>
						</td>
					</tr>
					<tr>
						<td class='ia-td-label'>
							<label title='<?php _e( 'Total number of visits referred by a search engine. Search engine must be major search engine to be included in this statistic.' , 'inbound-pro' ); ?>'>
								<?php _e( 'Search Engine' , 'inbound-pro' ); ?>
							</label>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-int'  title='<?php _e( 'Total number of visits referred by a search engine.' , 'inbound-pro' ); ?>'>100</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate' title='<?php _e( 'Rate of search engine referrals versus other types of referrals.' , 'inbound-pro' ); ?>'>25%</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate-change positive'  title='<?php _e( 'Change from last corresponding time period.' , 'inbound-pro' ); ?>'>.001%</a>
						</td>
					</tr>
					<tr>
						<td class='ia-td-label'>
							<label title='<?php _e( 'Total number of visits referred by a major social media site.' , 'inbound-pro' ); ?>'>
							<?php _e( 'Social Media' , 'inbound-pro' ); ?>
							</label>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-int'  title='<?php _e( 'Total number of visits referred by a search engine.' , 'inbound-pro' ); ?>'>100</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate' title='<?php _e( 'Rate of search engine referrals versus other types of referrals.' , 'inbound-pro' ); ?>'>25%</a>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate-change positive'  title='<?php _e( 'Change from last corresponding time period.' , 'inbound-pro' ); ?>'>.001%</a>
						</td>
					</tr>
				</table>
				
			
			<?php
		}
		
		/**
		*	Loads data from Inbound Cloud given parameters
		*	
		*	@param ARRAY $args
		*/
		public static function load_data() {
			$Inbound_Analytics = new Inbound_Analytics_Connect();
		}
		
	}
	
	add_action( 'admin_init' , array( 'Analytics_Teamplte_Content_Quick_View' , 'init' ) , 10 );

}