<?php

class Inbound_Analytics_Tracking {
	
	/**
	* Initalize Inbound_Analytics_UI_Containers Class
	*/
	public function __construct() {
		self::load_hooks();
	}


	/**
	* Load Hooks & Filters
	*/
	public static function load_hooks() {

		
		/* Automatically add tracking code to theme */
		add_action( 'wp_footer' , array( __CLASS__ , 'add_tracking_code' ) );
	}
	
	public static function add_tracking_code() {
		
		/* load settings */
		$ga_settings = get_option('inbound_ga' , false);
		
		if (
			!$ga_settings
			||
			!isset($ga_settings['linked_profile'])
			||
			!$ga_settings['linked_profile']
			/* if automatic insert disabled then return */
			|| (
				isset($ga_setting['disable_tracking_insert'])
				&&
				$ga_setting['disable_tracking_insert']
			)
		) {
			return;
		}
		
		$tracking_code = $ga_settings['profiles'][$ga_settings['linked_profile']]['webPropertyId'];
		?>
		<!-- Inbound Google Analytics -->
		<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		<?php
		/* if domain has .dev in it automatically whitelist as it is a local installation */
		if (strstr($_SERVER['HTTP_HOST'], '.dev') || strstr($_SERVER['HTTP_HOST'], 'localhost')) {
			echo "ga('create', '".$tracking_code."', {'cookieDomain': 'none'} );";
		} else {
			echo "ga('create', '".$tracking_code."', 'auto');";
		}
		?>

		ga('send', 'pageview');
		</script>
		<!-- End Google Analytics -->
		<?php
	}
	
}

new Inbound_Analytics_Tracking;