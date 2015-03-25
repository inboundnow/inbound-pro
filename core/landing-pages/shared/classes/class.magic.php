<?php
/**
 * Fixes jQuery and Javascript issues from popping up
 *
 * Usage: When using jQuery: use jQuery instead of jQuery / $
 *
 */

if ( ! class_exists( 'Inbound_Magic' ) ) {

	class Inbound_Magic {
		static $end_buffer_fired;

		static function init() {
			/* determines if in ajax mode */
			if(is_admin()) {
				add_action( 'admin_enqueue_scripts', array( __CLASS__ , 'start_buffer'), -9999 );
				add_action( 'admin_head', array( __CLASS__ , 'end_buffer'), -9999 );
			} else {
				add_action( 'wp_enqueue_scripts', array( __CLASS__ , 'start_buffer'), -9999 );
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				// check for plugin using plugin name
				if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
				  //plugin is activated
				  add_action( 'wp_footer', array( __CLASS__ , 'end_buffer'), -9999 );
				} else {
				  add_action( 'wp_head', array( __CLASS__ , 'end_buffer'), -9999 );
				}

			}

		}

		/* Fix JavaScript Conflicts in WordPress */
		public static function start_buffer() {
			ob_start( array( 'Inbound_Magic', 'buffer_callback' ) );
		}
		/**
		 * Collects the buffer, and injects a `jQueryWP` JS object as a
		 * copy of `jQuery`, so that dumb themes and plugins can't hurt it
		 */
		public static function buffer_callback( $content ) {

			
			$main = "#/jquery\.js(.*?)</script>#";
			$patternFrontEnd = "#wp-includes/js/jquery/jquery\.js\?ver=([^']+)'></script>#";
			$patternFrontTwo = "#wp-includes/js/jquery/jquery\.js'></script>#";
			$patternFrontThree = "#jquery\.min\.js\?ver\=([^']+)'></script>#";
			$externalPattern = "#/jquery.min.js'></script>#";
			$patternAdmin = "#load-scripts.php\?([^']+)'></script>#";
			$content = "<!-- /* This Site's marketing is powered by InboundNow.com */ -->" . $content;
			//window.onerror=function(o,n,l){return console.log(o),console.log(n),console.log(l),!0};

			if ( preg_match( $main, $content ) ) {
				//jQuery = (typeof jQuery !== "undefined") ? jQuery : false;
		    	$content = preg_replace( $main, '$0<script>jQuery = jQuery;</script>', $content );
				return $content;

			}else if ( preg_match( $patternFrontEnd, $content ) ) {
				//jQuery = (typeof jQuery !== "undefined") ? jQuery : false;
				$content = preg_replace( $patternFrontEnd, '$0<script>jQuery = jQuery;</script>', $content );
				return $content;

			} else if ( preg_match( $patternFrontTwo, $content ) ) {
				//jQuery = (typeof jQuery !== "undefined") ? jQuery : false;
			    $content = preg_replace( $patternFrontTwo, '$0<script>jQuery = jQuery;</script>', $content );
				return $content;

			} else if ( preg_match( $patternFrontThree, $content ) ) {
				//jQuery = (typeof jQuery !== "undefined") ? jQuery : false;
		    	$content = preg_replace( $patternFrontThree, '$0<script>jQuery = jQuery;</script>', $content );
				return $content;

			}  else if ( preg_match( $externalPattern, $content ) ) {
				/* match external google lib */
				$content = preg_replace( $externalPattern, '$0<script>jQuery = jQuery;</script>', $content );
				return $content;
			}

			if ( preg_match( $patternAdmin, $content ) ) {
				$content = preg_replace( $patternAdmin, '$0<script>jQuery = jQuery;</script>', $content );
				return $content;
			}

			//return $content;

		}
		/**
		 * Flushes the buffer
		 */
		public static function end_buffer() {
			if (self::$end_buffer_fired) {
				return;
			}

			ob_end_flush();

			self::$end_buffer_fired = true;
		}

	}

	Inbound_Magic::init();

}