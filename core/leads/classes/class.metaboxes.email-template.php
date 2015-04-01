<?php

if ( !class_exists( 'Inbound_Metaboxes_Email_Templates' ) ) {

	class Inbound_Metaboxes_Email_Templates {

		static $Inbound_Email_Templates;
		static $post_type;
		static $is_core_template;

		/**
		*  Initialize class
		*/
		public function __construct() {
			self::$post_type = 'email-template';
			self::load_hooks();
		}

		/**
		*  Load hooks and filters
		*/
		public static function load_hooks() {
			/* Setup Variables */
			add_action( 'posts_selection' , array( __CLASS__ , 'load_variables') );

			/* Add Metaboxes */
			add_action( 'add_meta_boxes' , array( __CLASS__ , 'define_metaboxes') );

			/* Replace Default Title Text */
			add_filter( 'enter_title_here' , array( __CLASS__ , 'change_title_text' ) , 10, 2 );

			/* Add Save Actions */
			add_action( 'save_post' , array( __CLASS__ , 'save_markup' ) );

			/* Enqueue JS */
			add_action( 'admin_enqueue_scripts', array( __CLASS__ , 'enqueue_admin_scripts' ) );
			add_action( 'admin_print_footer_scripts', array( __CLASS__ , 'print_admin_scripts' ) );
		}

		/**
		*  
		*/
		public static function load_variables() {
			global $post;

			if ( !isset($post) || $post->post_type != self::$post_type ) {
				return;
			}

			self::$is_core_template = get_post_meta( $post->ID , 'inbound_is_core', true );
		}
		
		/**
		*  
		*/
		public static function define_metaboxes() {
			global $post;

			if ( $post->post_type != self::$post_type ) {
				return;
			}

			/* Template Select Metabox */
			add_meta_box(
				'inbound_email_templates_metabox_select_template', // $id
				__( 'Template Options', 'leads' ),
				array( __CLASS__ , 'display_markup' ), // $callback
				self::$post_type ,
				'normal',
				'high'
			);

			/* Restore Default Template */
			if ( has_term('inbound-core','email_template_category' , $post) || has_term('wordpress-core','email_template_category' , $post) ) {
				add_meta_box(
					'inbound_email_templates_metabox_restore_template', // $id
					__( 'Restore Template', 'leads' ),
					array( __CLASS__ , 'display_restore_template' ), // $callback
					self::$post_type ,
					'side',
					'low'
				);
			}

			/* Core Tokens */
			add_meta_box(
				'inbound_email_templates_metabox_core_tokens', // $id
				__( 'Core Tokens', 'leads' ),
				array( __CLASS__ , 'display_core_tokens' ), // $callback
				self::$post_type ,
				'side',
				'low'
			);

			/* Lead Tokens */
			add_meta_box(
				'inbound_email_templates_metabox_lead_tokens', // $id
				__( 'Form Submission Tokens', 'leads' ),
				array( __CLASS__ , 'display_form_submission_tokens' ), // $callback
				self::$post_type ,
				'side',
				'low'
			);

			/* User Tokens */
			add_meta_box(
				'inbound_email_templates_metabox_user_tokens', // $id
				__( 'User Tokens', 'leads' ),
				array( __CLASS__ , 'display_user_tokens' ), // $callback
				self::$post_type ,
				'side',
				'low'
			);

			/* Comment Tokens */
			add_meta_box(
				'inbound_email_templates_metabox_comment_tokens', // $id
				__( 'Comment Tokens', 'leads' ),
				array( __CLASS__ , 'display_comment_tokens' ), // $callback
				self::$post_type ,
				'side',
				'low'
			);

		}
		
		/**
		*  
		*/
		public static function display_markup() {
			global $post;

			$subject = get_post_meta( $post->ID , 'inbound_email_subject_template' , true );
			$body = get_post_meta( $post->ID , 'inbound_email_body_template' , true );
			$description = get_post_meta( $post->ID , 'inbound_email_description' , true );

			$line_count = substr_count( $body , "\n" );

			($line_count) ? $line_count : $line_count = 5;

			echo '<h2>Description:</h2>';
			if ( has_term('inbound-core','email_template_category' , $post) || has_term('wordpress-core','email_template_category' , $post) ) {
				echo '<i>'. $description .'</i>';
			} else {
				echo '<textarea name="inbound_email_description" id="inbound_email_description" rows="1" cols="30" style="width:100%;">'.$description.'</textarea>';
			}

			echo '<h2>Subject-Line Template:</h2>';
			echo '<input type="text" name="inbound_email_subject_template" style="width:100%;" value="'. str_replace( '"', '\"', $subject ) .'">';

			echo '<h2>Email Body Template:</h2>';
			echo '<textarea name="inbound_email_body_template"	id="inbound_email_body_template" rows="'.$line_count.'" cols="30" style="width:100%;">'.$body.'</textarea>';

		}

		/**
		*  
		*/
		public static function display_restore_template() {
			global $Inbound_Email_Templates_Post_Type, $post;

			/* Load template files */
			$inbound_email_templates = $Inbound_Email_Templates_Post_Type->load_template_files();

			/* Get this template id */
			$template_id = get_post_meta( $post->ID , '_inbound_template_id', true );

			?>
			<div class='inbound_email_templates_restore_template' style='text-align:center;'>
				<span class="button" id='inbound_restore_template' style='width:100%;'>Restore Default Template</span>
			</div>
			<div id='<?php echo $template_id; ?>' style='display:none'><?php echo htmlentities($inbound_email_templates[ $template_id ]); ?></div>
			<script>
				jQuery(document).ready(function($) {
					jQuery("#inbound_restore_template").click( function(e)
					{
						e.preventDefault();
						if (confirm('<?php _e('Are you sure you want to restore the original template?' , 'leads' ); ?>')) {
							var html = jQuery('#<?php echo $template_id; ?>').html();
							var html_decoded = $('<textarea />').html(html).text();
							jQuery('#inbound_email_body_template').val(html_decoded);
							alert('<?php _e( 'Template restored!' , 'leads' ); ?>');
						};
					});
				});
			</script>
			<?php
		}

		/*
		* Display list of available core tokens
		*/
		public static function display_core_tokens() {

			?>
			<div class='inbound_email_templates_core_tokens'>
				<span class='core_token' title='Email address of sender' style='cursor:pointer;'>{{admin-email-address}}</span><br>
				<span class='core_token' title='Name of this website' style='cursor:pointer;'>{{site-name}}</span><br>
				<span class='core_token' title='URL of this website' style='cursor:pointer;'>{{site-url}}</span><br>
				<span class='core_token' title='Datetime of Sent Email.' style='cursor:pointer;'>{{date-time}}</span><br>
				<span class='core_token' title='URL to Wordpress Leads Directory.' style='cursor:pointer;'>{{leads-urlpath}}</span><br>
				<span class='core_token' title='URL to Wordpress Landing Pages Directory.' style='cursor:pointer;'>{{landingpages-urlpath}}</span><br>
			</div>

			<?php
		}

		/**
		*  
		*/
		public static function display_form_submission_tokens() {

			?>
			<div class='inbound_email_templates_form_submission_tokens'>
				<span class='lead_token' title='The ID of the lead stored in the WordPress database,' style='cursor:pointer;'>{{lead-id}}</span><br>
				<span class='lead_token' title='First & Last name of recipient' style='cursor:pointer;'>{{lead-full-name}}</span><br>
				<span class='lead_token' title='First name of recipient' style='cursor:pointer;'>{{lead-first-name}}</span><br>
				<span class='lead_token' title='Last name of recipient' style='cursor:pointer;'>{{lead-last-name}}</span><br>
				<span class='lead_token' title='Email address of recipient' style='cursor:pointer;'>{{lead-email-address}}</span><br>
				<span class='lead_token' title='Company Name of recipient' style='cursor:pointer;'>{{lead-company-name}}</span><br>
				<span class='lead_token' title='Address Line 1 of recipient' style='cursor:pointer;'>{{lead-address-line-1}}</span><br>
				<span class='lead_token' title='Address Line 2 of recipient' style='cursor:pointer;'>{{lead-address-line-2}}</span><br>
				<span class='lead_token' title='City of recipient' style='cursor:pointer;'>{{lead-city}}</span><br>
				<span class='lead_token' title='Name of Inbound Now form user converted on' style='cursor:pointer;'>{{form-name}}</span><br>
				<span class='lead_token' title='Page the visitor singed-up on.' style='cursor:pointer;'>{{source}}</span><br>
			</div>

			<?php
		}
		
		/**
		*  
		*/
		public static function display_user_tokens() {

			?>
			<div class='inbound_email_templates_user_tokens'>
				<span class='user_token' title='The ID of WP User' style='cursor:pointer;'>{{wp-user-id}}</span><br>
				<span class='user_token' title='The username of WP User' style='cursor:pointer;'>{{wp-user-login}}</span><br>
				<span class='user_token' title='The first name of WP User' style='cursor:pointer;'>{{wp-user-first-name}}</span><br>
				<span class='user_token' title='The last name of WP User' style='cursor:pointer;'>{{wp-user-last-name}}</span><br>
				<span class='user_token' title='The password of WP User' style='cursor:pointer;'>{{wp-user-password}}</span><br>
				<span class='user_token' title='The nicename of WP User' style='cursor:pointer;'>{{wp-user-nicename}}</span><br>
				<span class='user_token' title='The display of WP User' style='cursor:pointer;'>{{wp-user-displayname}}</span><br>

			</div>

			<?php
		}

		/**
		*  
		*/
		public static function display_comment_tokens() {

			?>
			<div class='inbound_email_templates_user_tokens'>
				<span class='user_token' title='The ID of Comment' style='cursor:pointer;'>{{wp-comment-id}}</span><br>
				<span class='user_token' title='The URL of the Comment' style='cursor:pointer;'>{{wp-comment-url}}</span><br>
				<span class='user_token' title='The author name of Comment' style='cursor:pointer;'>{{wp-comment-author}}</span><br>
				<span class='user_token' title='The author url of Comment' style='cursor:pointer;'>{{wp-comment-author-url}}</span><br>
				<span class='user_token' title='The author ip of Comment' style='cursor:pointer;'>{{wp-comment-author-ip}}</span><br>
				<span class='user_token' title='The author ip of Comment' style='cursor:pointer;'>{{wp-comment-gravitar-url}}</span><br>
				<span class='user_token' title='The content of Comment' style='cursor:pointer;'>{{wp-comment-content}}</span><br>
				<span class='user_token' title='The date of Comment' style='cursor:pointer;'>{{wp-comment-date}}</span><br>
				<span class='user_token' title='The karma of Comment' style='cursor:pointer;'>{{wp-comment-karma}}</span><br>

			</div>

			<?php
		}
		
		/**
		*  
		*/
		public static function save_markup( $post_id ) {
			global $post;

			if ( !isset( $post ) ) {
				return;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
				return;
			}

			if ( ! current_user_can( 'edit_post', $post_id ) ){
				return;
			}

				if ($post->post_type!=self::$post_type)	{
				return;
			}


			if ( isset ( $_POST[ 'inbound_email_subject_template' ] ) ) {
				update_post_meta( $post_id, 'inbound_email_subject_template', $_POST[ 'inbound_email_subject_template' ] );
			}

			if ( isset ( $_POST[ 'inbound_email_body_template' ] ) ) {
				update_post_meta( $post_id, 'inbound_email_body_template', $_POST[ 'inbound_email_body_template' ] );
			}

			if ( isset ( $_POST[ 'inbound_email_description' ] ) ) {
				update_post_meta( $post_id, 'inbound_email_description', $_POST[ 'inbound_email_description' ] );
			}

		}

		/**
		*  
		*/
		public static function change_title_text( $text, $post ) {
			if ($post->post_type==self::$post_type) {
				return __( 'Email Template Name' , 'leads' );
			} else {
				return $text;
			}
		}


		/**
		*  Enqueue Admin Scripts 
		*/
		public static function enqueue_admin_scripts( $hook ) {
			global $post;

			if ( !isset($post) || $post->post_type != self::$post_type ) {
				return;
			}

			if ( $hook == 'post-new.php' ) {
			}

			if ( $hook == 'post.php' ) {
			}

			if ($hook == 'post-new.php' || $hook == 'post.php') {
			}
		}

		/**
		*  Print Admin Scripts 
		*/
		public static function print_admin_scripts() {
			global $post;
			
			if ( ! function_exists( 'get_current_screen' ) ) {
				return;
			}
			
			$screen = get_current_screen();

			if ( isset($screen) && $screen->base != 'post' && $screen->post_type !='email-template' ) {
				return;
			}

			if ( has_term('inbound-core','email_template_category' , $post) || has_term('wordpress-core','email_template_category' , $post) ) {
				?>
				<script>
					jQuery(document).ready(function($) {
						jQuery('#delete-action').remove();
						jQuery('#email_template_categorydiv').hide();
					});
				</script>
				<?php
			}
			/* Hide Core Categories */
			else {
				?>
				<script>
					jQuery(document).ready(function($) {
						jQuery('.popular-category label:contains("inbound-core")').hide();
						jQuery('.popular-category label:contains("wordpress-core")').hide();
					});
				</script>
				<?php
			}
		}
	}


	$Inbound_Metaboxes_Email_Templates = new Inbound_Metaboxes_Email_Templates;
}