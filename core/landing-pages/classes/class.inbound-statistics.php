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
			add_action( 'wp_ajax_inbound_content_clear_stats', array( __CLASS__ , 'ajax_clear_stats' ) );

            /* records page impression */
            add_action( 'lp_record_impression' , array( __CLASS__ , 'record_impression' ) , 10, 3);

            /* record landing page conversion */
            add_action( 'inboundnow_store_lead_pre_filter_data' , array( __CLASS__ , 'record_conversion' ) ,10,1);

            /* load impressions/conversions collumns on non lp post types */
            if (is_admin()) {

                /* Register Columns */
                add_filter( 'manage_post_posts_columns' , array( __CLASS__ , 'register_columns') , 20 );
                add_filter( 'manage_page_posts_columns' , array( __CLASS__ , 'register_columns') , 20 );

                /* Prepare Column Data */
                add_action( "manage_posts_custom_column", array( __CLASS__ , 'prepare_column_data' ) , 10, 2 );

				/* enqueue admin scripts */
				add_action('admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_scripts'));
            }

		}

		/**
		 * Enqueue admin scripts
		 */
		public static function enqueue_scripts() {

			if (!isset($_GET['post'])){
				return;
			}

			wp_enqueue_style('lp-content-stats', LANDINGPAGES_URLPATH . 'assets/css/admin/content-stats.css');

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

						if (confirm( '<?php _e( "Are you sure you want to delete stats for this post?" , "landing-pages" ); ?> ')) {
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


					$impressions = (is_numeric($impressions)) ?  $impressions :  0;
					$conversions = (is_numeric($conversions)) ?  $conversions :  0;

					if ($impressions > 0) {
						$conversion_rate = $conversions / $impressions;
						$sign = (($conversions === 0)) ?  "" : "%";
						$conversion_rate = round($conversion_rate, 2) * 100 . $sign;
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

        /**
         * Records landing page & non landing page impression
         * @param $post_id
         * @param string $post_type
         * @param int $variation_id
         */
        public static function record_impression($post_id, $post_type = 'landing-page', $variation_id = 0) {

            /* If Landing Page Post Type */
            if ( $post_type == 'landing-page' ) {
                $impressions = Landing_Pages_Variations::get_impressions( $post_id, $variation_id );
                $impressions++;
                Landing_Pages_Variations::set_impressions_count( $post_id, $variation_id, $impressions );
            }
            /* If Non Landing Page Post Type */
            else {
                $impressions = Inbound_Content_Statistics::get_impressions_count( $post_id );
                $impressions++;
                Inbound_Content_Statistics::set_impressions_count( $post_id, $impressions );
            }
        }

        /**
         * Listens for new lead creation events and if the lead converted on a landing page then capture the conversion
         * @param $data
         */
        public static function record_conversion($data) {

            if (!isset( $data['page_id'] ) ) {
                return;
            }

            $post = get_post( $data['page_id'] );
            if ($post) {
                $data['post_type'] = $post->post_type;
            }

            /* this filter is used by Inbound Pro to check if visitor's ip is on a not track list */
            $do_not_track = apply_filters('inbound_analytics_stop_track' , false );

            if ( $do_not_track ) {
                return;
            }

            /* increment conversions for landing pages */
            if( isset($data['post_type']) && $data['post_type'] === 'landing-page' ) {
                $conversions = Landing_Pages_Variations::get_conversions( $data['page_id'] , $data['variation'] );
                $conversions++;
                Landing_Pages_Variations::set_conversions_count( $data['page_id'] , $data['variation'] , $conversions );

            }
            /* increment conversions for non landing pages */
            else  {
                $conversions = Inbound_Content_Statistics::get_conversions_count( $data['page_id'] );
				$conversions++;
                Inbound_Content_Statistics::set_conversions_count( $data['page_id'] , $conversions );
            }

            return $data;
        }

        /**
         *  Register Columns
         */
        public static function register_columns( $cols ) {

            $cols['inbound_impressions'] = __( 'Impressions' , 'inbound-email' );
            $cols['inbound_conversions'] = __( 'Conversions' , 'inbound-email' );
            $cols['inbound_conversion_rate'] = __( 'Conversion Rate' , 'inbound-email' );

            return $cols;
        }

        /**
         *  Prepare Column Data
         */
        public static function prepare_column_data( $column , $post_id ) {
            global $post;

            switch ($column) {
                case "inbound_impressions":
                    echo self::get_impressions_count( $post->ID );
                    break;
                case "inbound_conversions":
                    echo self::get_conversions_count( $post->ID );
                    break;
                case "inbound_conversion_rate":
                    echo self::get_conversion_rate( $post->ID);
                    break;
            }
        }


        /**
         * Returns impression count for non landing pages. See Landing_Pages_Variations class for retrieving landing page statistics
         *
         * @param INT $post_id id of call to action
         *
         * @return INT impression count
         */
        public static function get_impressions_count( $post_id ) {

            $impressions = get_post_meta( $post_id , '_inbound_impressions_count' , true);

            if (!is_numeric($impressions)) {
                $impressions = 0;
            }

            return $impressions;
        }

        /**
         * Returns conversion count for non landing page. See Landing_Pages_Variations class for retrieving landing page statistics
         *
         * @param INT $post_id id
         *
         * @return INT impression count
         */
        public static function get_conversions_count( $post_id ) {


            $conversions = get_post_meta( $post_id , '_inbound_conversions_count' , true);

            if (!is_numeric($conversions)) {
                $conversions = 0;
            }

            return $conversions;
        }

        /**
         * Returns conversion count for non landing page.  See Landing_Pages_Variations class for retrieving landing page statistics
         *
         * @param INT $post_id id
         *
         * @return INT
         */
        public static function get_conversion_rate( $post_id ) {

            $impressions = Inbound_Content_Statistics::get_impressions_count( $post_id );
            $conversions = Inbound_Content_Statistics::get_conversions_count( $post_id );

            if ($impressions > 0) {
                $conversion_rate = $conversions / $impressions;
                $conversion_rate_number = $conversion_rate * 100;
                $conversion_rate_number = round($conversion_rate_number, 2);
                $conversion_rate = $conversion_rate_number;
            } else {
                $conversion_rate = 0;
            }

            return $conversion_rate;
        }


        /**
         * Set impression count
         */
        public static function set_impressions_count( $post_id , $count ) {
            update_post_meta( $post_id, '_inbound_impressions_count', $count );
        }

        /**
         * Set conversion count
         */
        public static function set_conversions_count( $post_id , $count ) {
            update_post_meta( $post_id, '_inbound_conversions_count', $count );
        }

    }

	new Inbound_Content_Statistics;
}