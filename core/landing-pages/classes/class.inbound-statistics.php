<?php

/**
*  This class adds a impressions/conversions counter box to all post types that are not a landing page
*/

if (!class_exists('Inbound_Content_Statistics')) {
	
	/**
	*  Adds impression and conversion tracking statistics to all pieces of content
	*/
	class Inbound_Content_Statistics {
		
		/**
		*  Initiate class
		*/
		public function __construct() {
			self::load_hooks();
		}
	
		/**
		*  load hooks and filters
		*/
		public static function load_hooks() {
			/* add statistics metabox to non landing-page post types */
			add_action( 'add_meta_boxes' , array( __CLASS__ , 'add_statistics_metabox' ) , 10 );
			
			/*  Adds Ajax for Clear Stats button */
			add_action( 'wp_ajax_nopriv_inbound_content_clear_stats', array( __CLASS__ , 'ajax_clear_stats' ) );
			add_action( 'wp_ajax_inbound_content_clear_stats', array( __CLASS__ , 'ajax_clear_stats' ) );

		}
		
		/**
		*  Add mtatistic metabox to non blacklisted post types
		*/
		public static function add_statistics_metabox( $post_type ) {
			global $pagenow;

			$exclude[] = 'attachment';
			$exclude[] = 'revisions';
			$exclude[] = 'nav_menu_item';
			$exclude[] = 'wp-lead';
			$exclude[] = 'automation';
			$exclude[] = 'rule';
			$exclude[] = 'list';
			$exclude[] = 'wp-call-to-action';
			$exclude[] = 'tracking-event';
			$exclude[] = 'inbound-forms';
			$exclude[] = 'email-template';
			$exclude[] = 'inbound-email';
			$exclude[] = 'inbound-log';
			$exclude[] = 'landing-page';
			$exclude[] = 'acf-field-group';

			if ( $pagenow === 'post.php' && !in_array($post_type,$exclude) ) {
				add_meta_box( 'inbound-content-statistics', __( 'Inbound Statistics' , 'landing-pages' ) , array( __CLASS__ , 'display_statistics' ) , $post_type, 'side', 'high');
			}

		}
		
		/**
		*  Display Inbound Content Statistics
		*/
		public static function display_statistics() {

			global $post;
			
			?>
			<div>
				<script >
				jQuery(document).ready(function($) { 	
					jQuery( 'body' ).on( 'click', '.lp-delete-var-stats', function() {
						var post_id = jQuery(this).attr("rel");
					
						if (confirm( '<?php _e( 'Are you sure you want to delete stats for this post?' , 'landing-pages' ); ?> ')) {	  
							jQuery.ajax({
								  type: 'POST',
								  url: ajaxurl,
								  context: this,
								  data: {
									action: 'inbound_content_clear_stats',
									post_id: post_id
								  },				  
								success: function(data){
									jQuery(".bab-stat-span-impressions").text("0");
									jQuery(".bab-stat-span-conversions").text("0");
									jQuery(".bab-stat-span-conversion_rate").text("0");
									
								},

								  error: function(MLHttpRequest, textStatus, errorThrown){
									alert("Ajax not enabled");
									}
								});
								
								return false;
						} 
					});
				});
				</script>
				<div class="inside" style='margin-left:-8px;'>
					<div id="bab-stat-box">
					
					<?php
					$impressions = apply_filters('inbound_impressions' , get_post_meta($post->ID,'_inbound_impressions_count', true) );
					$conversions = apply_filters('inbound_conversions' , get_post_meta($post->ID,'_inbound_conversions_count', true) );


					(is_numeric($impressions)) ? $impressions = $impressions : $impressions = 0;
					(is_numeric($conversions)) ? $conversions = $conversions : $conversions = 0;

					if ($impressions>0) {
						$conversion_rate = $conversions / $impressions;
						(($conversions===0)) ? $sign = "" : $sign = "%";
						$conversion_rate = round($conversion_rate,2) * 100 . $sign;
					} else {
						$conversion_rate = 0;
					}
					?>
						<div id="" class="bab-variation-row" >
							<div class="bab-stat-row">
								<div class='bab-stat-stats' colspan='2'>
									<div class='bab-stat-container-impressions bab-number-box'>
										<span class='bab-stat-span-impressions'><?php echo $impressions; ?></span>
										<span class="bab-stat-id"><?php _e( 'Views' , 'landing-pages' ); ?></span>
									</div>
									<div class='bab-stat-container-conversions bab-number-box'>
										<span class='bab-stat-span-conversions'><?php echo $conversions; ?></span>
										<span class="bab-stat-id"><?php _e( 'Conversions' , 'landing-pages' ); ?></span></span>
									</div>
									<div class='bab-stat-container-conversion_rate bab-number-box'>
										<span class='bab-stat-span-conversion_rate'><?php echo $conversion_rate; ?></span>
										<span class="bab-stat-id bab-rate"><?php _e( 'Conversion Rate' , 'landing-pages' ); ?></span>
									</div>						
								</div>
							</div>
							<div class='bab-stat-control-container'>
								<span class="lp-delete-var-stats" rel='<?php echo $post->ID;?>' title="<?php _e( 'Delete this variations stats' , 'landing-pages' ); ?>"><?php _e( 'Clear Stats' , 'landing-pages' ); ?></span>
							</div>
						</div>
					</div>

				</div>
			</div>

			<?php
		}
		
		/**
		*  Ajax listener to clear stats related to content
		*/
		public static function ajax_clear_stats() {
			global $wpdb;

			$newrules = "0";
			$post_id = mysql_real_escape_string($_POST['post_id']);
			$vid = $_POST['variation'];

			update_post_meta( $post_id, '_inbound_impressions_count', '0' );
			update_post_meta( $post_id, '_inbound_conversions_count', '0' );

			header('HTTP/1.1 200 OK');
		}

	}

	new Inbound_Content_Statistics;
}
