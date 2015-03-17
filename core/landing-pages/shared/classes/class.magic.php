<?php
/**
 * Fixes jQuery and Javascript issues from popping up
 *
 * Usage: When using jQuery: use InboundQuery instead of jQuery / $
 *
 */

if ( ! class_exists( 'Inbound_Magic' ) ) {

	class Inbound_Magic {

		static function init() {
			/* determines if in ajax mode */
			if(is_admin()) {
				add_action( 'admin_enqueue_scripts', array( __CLASS__ , 'start_buffer'), -9999 );
				add_action( 'admin_head', array( __CLASS__ , 'end_buffer'), -9999 );
			} else {
				add_action( 'wp_enqueue_scripts', array( __CLASS__ , 'start_buffer'), -9999 );
				add_action( 'wp_head', array( __CLASS__ , 'end_buffer'), -9999 );
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
			$patternFrontEnd = "#wp-includes/js/jquery/jquery\.js\?ver=([^']+)'></script>#";
			$externalPattern = "#/jquery.min.js'></script>#";
			$patternAdmin = "#load-scripts.php\?([^']+)'></script>#";
			$content = "<script>/* before anything */</script>" . $content;
			//window.onerror=function(o,n,l){return console.log(o),console.log(n),console.log(l),!0};

			if ( preg_match( $patternFrontEnd, $content ) ) {
				//InboundQuery = (typeof jQuery !== "undefined") ? jQuery : false;
				$content = preg_replace( $patternFrontEnd, '$0<script>InboundQuery = jQuery;</script>', $content );
				return $content;
			}
			/* match external google lib */
			if ( preg_match( $externalPattern, $content ) ) {
				$content = preg_replace( $externalPattern, '$0<script>InboundQuery = jQuery;</script>', $content );
				return $content;
			}

			if ( preg_match( $patternAdmin, $content ) ) {
				$content = preg_replace( $patternAdmin, '$0<script>InboundQuery = jQuery;</script>', $content );
				return $content;
			}

		}
		/**
		 * Flushes the buffer
		 */
		public static function end_buffer() {
			ob_end_flush();
		}

	}

Inbound_Magic::init();

}