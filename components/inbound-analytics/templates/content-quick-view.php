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
					<div class='timespan-img'data-range='7' title='<?php _e('Past 7 hours' , 'inbound-pro'); ?>'>7</div>
					<div class='timespan-img'data-range='30' title='<?php _e('Past 30 days' , 'inbound-pro'); ?>'>30</div>
					<div class='timespan-img'data-range='90' title='<?php _e('Past 90 days' , 'inbound-pro'); ?>'>90</div>
					<div class='timespan-img'data-range='365' title='<?php _e('Past 365 days' , 'inbound-pro'); ?>'>365</div>
				</div>
				<br>
				<div id="chart_div" style="width: 275px;margin-left:-13px;"></div>
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
							<label title='<?php _e( 'Type fo statistic.' , 'inbound-pro'); ?>'>Statistic</label>
						</td>
						<td class='ia-td-th'>	
							<label title='<?php _e( 'Statistic value for given time period' , 'inbound-pro'); ?>'>Value</label>
						</td>
						<td class='ia-td-th'  title='<?php _e( 'Change in growth compared to corresponding previous timeperiod.' , 'inbound-pro'); ?>'>	
							<label>Change</label>
						</td>
					</tr>
					<tr>
						<td class='ia-td-label' >
							<label title='<?php _e( 'Total number of visits to this page' , 'inbound-pro' ); ?>'>Impressions:</label>
						</td>
						<td>	
							<a href='#' class='ia-int md-trigger' data-modal='modal-6'>400</a>
						</td>
						
						<td>	
							<a href='' class='ia-rate-change positive' title="<?php _e('growth compared to last time period' , 'inbound-pro' ); ?>">.01%</a>
						</td>
					</tr>
					<tr>
						<td class='ia-td-label' >
							<label title='<?php _e( 'Total number of visitors' , 'inbound-pro' ); ?>'>Visitors:</label>
						</td>
						<td>	
							<a href='' class='ia-int'>456</a>
						</td>
						
						<td>	
							<a href='' class='ia-rate-change positive' title="<?php _e('growth compared to last time period' , 'inbound-pro' ); ?>">.01%</a>
						</td>
					</tr>
					<tr>
						<td class='ia-td-label'>
							<label title='<?php _e( 'Total number of event actions originating from this page.' , 'inbound-pro' ); ?>'>Actions:</label>
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
							<label title='<?php _e( 'Total percentage of actions to impressions.' , 'inbound-pro' ); ?>'>Action Rate:</label>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-rate'>4.3%</a>
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
							Action Breakdown
						</td>
						<td class='ia-td-th'>	
							Count
						</td>
						<td class='ia-td-th'>	
							Rate
						</td>
						<td class='ia-td-th'>	
							Change
						</td>
					</tr>
					<tr>
						<td class='ia-td-label'>
							<label title='<?php _e( 'Total number of Inbound Form submissions originating from this page' , 'inbound-pro' ); ?>'>Form Submissions:</label>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-int'  title='<?php _e( 'Total number of actions performed within the given time period for this content.' , 'inbound-pro' ); ?>'>5</a>
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
							<label title='<?php _e( 'Total number of Inbound Form submissions originating from this page' , 'inbound-pro' ); ?>'>Click Tracking Events:</label>
						</td>
						<td class='ia-td-value'>	
							<a href='' class='ia-int' title='<?php _e( 'Total number of actions performed within the given time period for this content.' , 'inbound-pro' ); ?>'>5</a>
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
							<label title='<?php _e( 'Total number of Inbound Form submissions originating from this page' , 'inbound-pro' ); ?>'>Custom Event Submissions:</label>
						</td>
						<td>	
							<a href='' class='ia-int' title='<?php _e( 'Total number of actions performed within the given time period for this content.' , 'inbound-pro' ); ?>'>5</a>
						</td>
						<td>	
							<a href='' class='ia-rate' title='<?php _e( 'Rate of action events compared to impressions.' , 'inbound-pro' ); ?>'>4.3%</a>
						</td>
						<td>	
							<a href='' class='ia-rate-change negative' title='<?php _e( 'Change from last corresponding time period.' , 'inbound-pro' ); ?>'>.001%</a>
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