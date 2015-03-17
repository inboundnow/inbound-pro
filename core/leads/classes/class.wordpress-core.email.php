<?php
/*
Class Name: Inbound_WP_Core_Email_Templates
Class Description: Opens up core WordPress Email Templates to modification through the Inbound Now email templating system.
Class Author: Hudson Atwell
*/

if ( !class_exists( 'Inbound_WP_Core_Email_Templates' ) ) {

	class Inbound_WP_Core_Email_Templates {

		public function __construct( $toggle = true ) {

			if (!$toggle) {
				return false;
			}
			
			self::load_hooks();
			
		}
		
		public static function load_hooks() {
			
			/* New User Notifications */
			add_action( 'wp_new_user_notification' , array( __CLASS__ , 'new_user_notification' ) , 2 , 2 );	
			
			/* Comment Notifications  */
			add_filter( 'comment_notification_subject' , array( __CLASS__ , 'notify_postauthor' ) , 20 , 2 );
			add_filter( 'comment_notification_text' , array( __CLASS__ , 'notify_postauthor' ) , 20 , 2 );
			
			/* Moderator Notifications */
			add_filter( 'comment_moderation_subject' , array( __CLASS__ , 'notify_moderator' ) , 20 , 2 );
			add_filter( 'comment_moderation_text' , array( __CLASS__ , 'notify_moderator' ) , 20 , 2 );
			
		}
		
		/* Get Email Template By meta_value $template_name where meta_key is _inbound_template_id */
		public static function get_template( $template_name ) {
		
			$email_template = array();

			$templates = get_posts(array(
				'post_type' => 'email-template',
				'posts_per_page' => 1,
				'meta_key' => '_inbound_template_id',
				'meta_value' => $template_name
			));

			foreach ( $templates as $template ) {
				$email_template['ID'] = $template->ID;
				$email_template['subject'] = get_post_meta( $template->ID , 'inbound_email_subject_template' , true );
				$email_template['body'] = get_post_meta( $template->ID , 'inbound_email_body_template' , true );
			}

			return $email_template;
			
		}
		
		/* Sets the Email Content Type to Use HTML */
		public static function set_email_type() {
			add_filter( 'wp_mail_content_type', array( __CLASS__ , 'email_type' ));
		}
		
		public static function email_type() {
			return 'text/html';
		}
		
		/* Notify New User of Account Details */
		public static function new_user_notification( $user_id , $plaintext_pass ) {
			
			self::set_email_type();
			
			$Inbound_Templating_Engine = Inbound_Templating_Engine();
			
			$template = self::get_template( 'wp-new-user-notification' );
			
			$user = new WP_User($user_id);
			
			if ( !$plaintext_pass ) {
				$plaintext_pass = __( '<i>hidden</h1>' , 'leads' );
			}
			
			$args = array(
				array(
					'wp_user_id' => $user_id,
					'wp_user_login' => stripslashes($user->user_login),
					'wp_user_email' => stripslashes($user->user_email),
					'wp_user_first_name' => stripslashes($user->first_name),
					'wp_user_last_name' => stripslashes($user->last_name),
					'wp_user_password' => stripslashes($plaintext_pass),
					'wp_user_nice_name' => stripslashes($user->nice_name),
					'wp_user_display_name' => stripslashes($user->display_name)
				)
			);
			
			$subject = apply_filters('inbound_email_new_user_notification_subject' , $Inbound_Templating_Engine->replace_tokens( $template['subject'] , $args  ) );
			$body = apply_filters('inbound_email_new_user_notification_body' , $Inbound_Templating_Engine->replace_tokens( $template['body'] , $args  ) );
			
			wp_mail( stripslashes($user->user_email) , $subject , $body );
				 
		}
		
		/* Notify Post/Comment Author of new Post */
		public static function notify_postauthor( $template , $comment_id ) {

			$comment = get_comment( $comment_id );
			
			if ( empty( $comment ) ) {
                return false;
			}
	
	        $post    = get_post( $comment->comment_post_ID );
			$author  = get_userdata( $post->post_author );
			
			/* Ignore Pingbacks & Trackbacks */
			switch ( $comment->comment_type ) {
				case 'trackback':
					return $template;
					break;
				case 'pingback':
					return $template;
					break;
			}
	
			/* Sets Email Type as HTML */
			self::set_email_type();
			
			/* Get Template Array */
			$Inbound_Templating_Engine = Inbound_Templating_Engine();
			$template_array = self::get_template( 'wp-notify-post-author' );
			
			/* Discover if Subject or Body Template is Needed */
			if (current_filter() == 'comment_notification_subject') {
				$template = $template_array['subject'];
			} else if (current_filter() == 'comment_notification_text' ) {
				$template = $template_array['body'];
			}
			

			$args = array(
				/* Comment Data */
				array(
					'wp_comment_id' => $comment->ID,
					'wp_comment_url' => get_permalink($comment->comment_post_ID).'#comments-'.$comment->ID,
					'wp_comment_author' => $comment->comment_author,
					'wp_comment_author_email' =>  $comment->comment_author_email ,
					'wp_comment_author_url' =>  $comment->comment_author_url ,
					'wp_comment_author_ip' =>  $comment->comment_author_ip ,
					'wp_comment_date' => $comment->comment_date,
					'wp_comment_content' => $comment->comment_content,
					'wp_comment_karma' => $comment->comment_karma,
					'wp_comment_type' => $comment->comment_type,
				),
				/* Post Data */
				array(
					'wp_post_id' => $post->ID,
					'wp_post_title' => $post->post_title,
					'wp_post_url' => get_permalink( $post->ID ),
					'wp_post_date' => $post->post_date,
					'wp_post_content' => $post->post_content,
					'wp_post_excerpt' => $post->post_excerpt
				),
				/* user data */
				array(
					'wp_user_id' => $author->ID,
					'wp_user_login' => stripslashes($author->user_login),
					'wp_user_email' => stripslashes($author->user_email),
					'wp_user_first_name' => stripslashes($author->first_name),
					'wp_user_last_name' => stripslashes($author->last_name),
					'wp_user_password' => stripslashes($plaintext_pass),
					'wp_user_nicename' => stripslashes($author->nice_name),
					'wp_user_displayname' => stripslashes($author->display_name)
				)
			);
			
			/* Replace Tokens */
			$template = $Inbound_Templating_Engine->replace_tokens( $template , $args  );
			
			return $template;				 
		}	
		
		/* Notify Moderator of new Comment */
		public static function notify_moderator( $template , $comment_id ) {

			$comment = get_comment( $comment_id );
			
			if ( empty( $comment ) ) {
                return false;
			}
	
	        $post    = get_post( $comment->comment_post_ID );
			$author  = get_userdata( $post->post_author );
			
			/* Ignore Pingbacks & Trackbacks */
			switch ( $comment->comment_type ) {
				case 'trackback':
					return $template;
					break;
				case 'pingback':
					return $template;
					break;
			}
	
			/* Sets Email Type as HTML */
			self::set_email_type();
			
			/* Get Template Array */
			$Inbound_Templating_Engine = Inbound_Templating_Engine();
			$template_array = self::get_template( 'wp-notify-moderator' );
			
			/* Discover if Subject or Body Template is Needed */
			if (current_filter() == 'comment_moderation_subject') {
				$template = $template_array['subject'];
			} else if (current_filter() == 'comment_moderation_text' ) {
				$template = $template_array['body'];
			}
			

			$args = array(
				/* Comment Data */
				array(
					'wp_comment_id' => $comment->comment_ID,
					'wp_comment_url' => get_permalink($comment->comment_post_ID).'#comments-'.$comment->comment_post_ID,
					'wp_comment_author' => $comment->comment_author,
					'wp_comment_author_email' =>  $comment->comment_author_email ,
					'wp_comment_author_url' =>  $comment->comment_author_url ,
					'wp_comment_author_ip' =>  $comment->comment_author_IP ,
					'wp_comment_date' => $comment->comment_date,
					'wp_comment_content' => $comment->comment_content,
					'wp_comment_karma' => $comment->comment_karma,
					'wp_comment_type' => $comment->comment_type,
				),
				/* Post Data */
				array(
					'wp_post_id' => $post->ID,
					'wp_post_title' => $post->post_title,
					'wp_post_url' => get_permalink( $post->ID ),
					'wp_post_date' => $post->post_date,
					'wp_post_content' => $post->post_content,
					'wp_post_excerpt' => $post->post_excerpt
				),
				/* user data */
				array(
					'wp_user_id' => $author->ID,
					'wp_user_login' => stripslashes($author->user_login),
					'wp_user_email' => stripslashes($author->user_email),
					'wp_user_first_name' => stripslashes($author->first_name),
					'wp_user_last_name' => stripslashes($author->last_name),
					'wp_user_nicename' => stripslashes($author->nice_name),
					'wp_user_displayname' => stripslashes($author->display_name)
				)
			);
			
			/* Replace Tokens */
			$template = $Inbound_Templating_Engine->replace_tokens( $template , $args  );
			
			return $template;				 
		}
		

	}

	/* Load Class */
	$Inbound_WP_Core_Email_Templates = new Inbound_WP_Core_Email_Templates(get_option('inbound_email_replace_core_template' , '1' ));


	/* Overwrite Core Pluggable Functions With Our Own If Template Replacement is Enabled */
	if (!function_exists('wp_new_user_notification')) {
		if (get_option('inbound_email_replace_core_template' , '1' )) {
			function wp_new_user_notification( $user_id , $plaintext_pass = null ) {			
				do_action( 'wp_new_user_notification' , $user_id , $plaintext_pass);
			}
		}
	}	

	
}

