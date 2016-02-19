<?php


class Inbound_Mailer_Tokens {


	/**
	*  Initialize class
	*/
	public function __construct() {

		if ( isset($_GET['disable_shortcodes']) ) {
			return;
		}

		self::load_hooks();
	}

	/**
	*  Loads hooks and filters
	*/
	public function load_hooks() {

		/* Add button  */
		add_action( 'media_buttons_context' , array( __class__ , 'token_button' ) , 99 );

		/* Load supportive libraries */
		add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_js' ));

		/* Add shortcode generation dialog */
		add_action( 'admin_footer' , array( __CLASS__ , 'token_generation_popup' ) );

		/* Add supportive js */
		add_action( 'admin_footer' , array( __CLASS__ , 'token_generation_js' ) );

		/* Add supportive css */
		add_action( 'admin_footer' , array( __CLASS__ , 'token_generation_css' ) );

		/* Add shortcode handler */
		add_shortcode( 'lead-field', array( __CLASS__, 'process_lead_field_shortcode' ) );

		/* Add shortcode handler */
		add_shortcode( 'unsubscribe-link', array( __CLASS__, 'process_unsubscribe_link' ) );

	}

	/**
	*  Displays token select button
	*/
	public static function token_button() {
		global $post;

		if ( !isset($post) || $post->post_type!='inbound-email' ) {
			return;
		}

		$html = '<a href="#" class="button lead-fields-button" id="lead-fields-button-'.rand ( 10 , 1200 ).'" style="padding-left: .4em;"  >';
		$html .= '<span class="wp-media-buttons-icon" id="inbound_lead_fields_button"></span>'. __( 'Lead Fields' , 'inbound-email' ) .'</a>';

		return $html;
	}

	/**
	*  Enqueue JS
	*/
	public static function enqueue_js() {
		global $post;

		if (!isset($post) || $post->post_type != 'inbound-email' ) {
			return;
		}

		/* Enqueue popupModal */
		wp_enqueue_script('popModal_js', INBOUND_EMAIL_URLPATH . 'assets/libraries/popModal/popModal.min.js', array('jquery') );
		wp_enqueue_style('popModal_css', INBOUND_EMAIL_URLPATH . 'assets/libraries/popModal/popModal.min.css');


	}

	/**
	*  Token/Shortcode generation script
	*/
	public static function token_generation_popup() {
		global $post;

		if (  !isset($post) || $post->post_type!='inbound-email' ) {
			return;
		}

		$fields = Leads_Field_Map::build_map_array();
		?>
		<div id="lead_fields_popup_container" style="display:none;">
			<table>
				<tr>
					<td class='lf-label'>
						<?php _e( 'Select Field' , 'inbound-mail' ); ?>
					</td>
					<td  class='lf-value'>
						<select id='lf-field-dropdown' class='form-control'>
						<?php
						array_shift($fields);
						foreach ( $fields as $id => $label ) {
							echo '<option value="'.$id.'">'.$label.'</option>';
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td class='lf-label'>
						<?php _e( 'Set Default' , 'inbound-mail' ); ?>
					</td>
					<td  class='lf-value'>
						<input id="lf-default" value="" class='form-control'>
					</td>
				</tr>
				<tr>
					<td class='lf-submit' colspan='2'>
						<span class='button-primary lf-submit-button' id="lf-insert-shortcode" href='#'><?php _e( 'Insert Shortcode' , 'inbound-email' ); ?></span>
					</td>
				</tr>
			</table>

		</div>
		<?php
	}

	/**
	*  Loads JS to support the token generation thickbox
	*/
	public static function token_generation_js() {
		global $post;

		if ( isset($post) && $post->post_type!='inbound-email' ) {
			return;
		}

		?>
		<script type='text/javascript'>
		jQuery( document ).ready( function() {

			/* Add listener to throw popup on button click */
			jQuery('body').on( 'click' , '.lead-fields-button' , function( ) {
				jQuery('#' + this.id ).popModal({
					html: jQuery('#lead_fields_popup_container').html(),
					placement : 'bottomLeft',
					showCloseBut : true,
					onDocumentClickClose : true,
					onDocumentClickClosePrevent : '',
					overflowContent : false,
					inline : true,
					beforeLoadingContent : 'Please, waiting...',
					onOkBut : function(){ },
					onCancelBut : function(){ },
					onLoad : function(){ },
					onClose : function(){ }
				});
			});

			/* Add listener to generate shortcode */
			jQuery('body').on( 'click' , '#lf-insert-shortcode' , function() {
				LFShortcode.build_shortcode();
			});

		});

		var LFShortcode = ( function () {

			var field_id;
			var field_default;
			var shortcode;

			var construct = {
				/**
				*  Builds shortcode given inputs
				*/
				build_shortcode: function() {
					this.field_id = jQuery('#lf-field-dropdown').val();
					this.field_default = jQuery('#lf-default').val();
					this.generate_shortcode();
					this.insert_shortcode();
				},
				/**
				*  Generates html shortcode from given inputs
				*/
				generate_shortcode: function() {
					this.shortcode = '[lead-field id="'
					+ this.field_id
					+ '" default="'
					+ this.add_slashes(this.field_default)
					+ '"]';
				},
				/**
				*  insert shortcode
				*/
				insert_shortcode: function() {
					wp.media.editor.insert( this.shortcode );
					jQuery('.close').click();
				},
				/**
				*  escapes quotation marks
				*/
				add_slashes: function (string) {
					return string.replace('"', '\"');;
				}
			}

			return construct;
		})();
		</script>
		<?php
	}

	/**
	*  Loads CSS to support the token generation popup
	*/
	public static function token_generation_css() {
		global $post;

		if ( isset($post) && $post->post_type!='inbound-email' ) {
			return;
		}

		?>
		<style type='text/css'>
		.lf-label {
			width: 80px;
		}

		.lf-submit {
			padding-top:10px;
			margin-bottom:-10px
		}

		.lf-submit-button {
			width:100%;
		}
		</style>
		<?php
	}

	/**
	*  Process [lead-field] shortcode
	*  @param ARRAY $params
	*/
	public static function process_lead_field_shortcode( $params ) {
		global $post;

		$lead_id = null;

		$params = shortcode_atts( array( 'default' => '' , 'id' => '' , 'lead_id' => null ), $params );

		/* check to see if lead id is set as a REQUEST */
		if ( isset($params['lead_id']) ) {
			$lead_id = $params['lead_id'];
		} else if ( isset($_REQUEST['lead_id']) ) {
			$lead_id = $_REQUEST['lead_id'];
		} else if ( isset($_COOKIE['wp_lead_id']) ) {
			$lead_id = $_COOKIE['wp_lead_id'];
		}

		/* return default if no lead id discovered */
		if (!$lead_id) {
			return $params['default'];
		}

		/* get lead value */
		$value = Leads_Field_Map::get_field( $lead_id , $params['id'] );

		/* return lead field value if it exists */
		if ($value) {
			return $value;
		} else {
			return $params['default'];
		}
	}

	/**
	*  Process unsubscribe link
	*/
	public static function process_unsubscribe_link( $params ) {

		$params = shortcode_atts( array(
			'lead_id' => null,
			'list_ids' => '-1',
			'email_id' => '-1'
		), $params );

		/* check to see if lead id is set as a REQUEST */
		if ( isset($params['lead_id']) ) {
			$params['lead_id'] = $params['lead_id'];
		} else if ( isset($_REQUEST['lead_id']) ) {
			$params['lead_id'] = $_REQUEST['lead_id'];
		} else if ( isset($_COOKIE['wp_lead_id']) ) {
			$params['lead_id'] = $_COOKIE['wp_lead_id'];
		}

		/* Add variation id to unsubscribe link */
		$params['variation_id'] = ( isset($_REQUEST['inbvid']) )  ? $_REQUEST['inbvid'] : 0;

		/* generate unsubscribe link */
		$unsubscribe_link =  Inbound_Mailer_Unsubscribe::generate_unsubscribe_link( $params );

		return $unsubscribe_link;
	}
}

/**
*  Loads token engine on the administrator side
*/
function inbound_load_token_engine() {
	$Inbound_Mailer_Tokens = new Inbound_Mailer_Tokens();
}
add_action( 'init' , 'inbound_load_token_engine' , 1 );