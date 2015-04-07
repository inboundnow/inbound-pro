<?php

/**
 * 
 *	Manage Inbound Templates
 *
*/


class Inbound_API_Wrapper {
	
	static $downloads_api_uri = 'http://www.inboundnow.com/';
	static $docs_uri = 'http://docs.inboundnow.com/feed?post_type=doc-page';
	static $blog_uri = 'http://www.inboundnow.com/feed/';
	static $data;
	static $templates;
	static $extensions;
	static $remote_content; /* dataset of remote inbound now article/social content */
	static $blogs; /* dataset of blog */
	static $docs; /* 
	
	/**
	*  Gets data array of available products
	*/
	public static function get_downloads() {
		
		/* check history first for cached object */
		self::$data = Inbound_Options_API::get_option( 'inbound-api' , 'downloads' , array() );

		/* if data is not set to expire then do not update */
		if ( isset(self::$data['expire']) && self::$data['expire'] > gmdate( 'Y-m-d G:i:s' ) ) {
			return;
		}
		
		/* This call home gets a list of available downloads - We have minimum security because no sensitive information is revealed */
		$response = wp_remote_post( self::$downloads_api_uri , array(  'body' => array ( 'get_downloads' => true , 'key' => 'hudson11' ) ) );
		
		/* unserialize response */
		self::$data = unserialize( $response['body'] );

		/* build new expiration date */
		self::$data['expire'] = gmdate('Y-m-d G:i:s' , strtotime( "+6 hours" ));
		
		/* update data object */
		Inbound_Options_API::update_option( 'inbound-api' , 'downloads' , self::$data );
	}
	
	/**
	*  Get latest docs
	*/
	public static function get_docs() {
		/* check history first for cached object */
		self::$remote_content = Inbound_Options_API::get_option( 'inbound-api' , 'remote' , array() );

		/* if data is not set to expire then do not update */
		if ( isset(self::$remote_content['docs']['expire']) && self::$remote_content['docs']['expire'] > gmdate( 'Y-m-d G:i:s' ) ) {
			return self::$remote_content['docs']['items'];
		}

		/* This call home gets a list of available downloads - We have minimum security because no sensitive information is revealed */
		$response = wp_remote_get( self::$docs_uri );
		
		if (!isset($response['body'])) {
			return;
		}
		
		/* unserialize response */
		$xml = simplexml_load_string( $response['body'] , null  , LIBXML_NOCDATA );
		$json = json_encode($xml);
		self::$docs = json_decode($json,TRUE);
		
		/* build new expiration date */
		self::$remote_content['docs']['expire'] = gmdate('Y-m-d G:i:s' , strtotime( "+3 hours" ));
		
		/* build doc dataset */
		self::$remote_content['docs']['items'] = self::$docs['channel']['item']; 
		
		/* update data object */
		Inbound_Options_API::update_option( 'inbound-api' , 'remote' , self::$remote_content );
		
		return self::$docs['channel']['item'];
	}
	
	/**
	*  Get latest blog posts
	*/
	public static function get_blog_posts() {
		/* check history first for cached object */
		self::$remote_content = Inbound_Options_API::get_option( 'inbound-api' , 'remote' , array() );

		/* if data is not set to expire then do not update */
		if ( isset(self::$remote_content['blog']['expire']) && self::$remote_content['blog']['expire'] > gmdate( 'Y-m-d G:i:s' ) ) {
			return self::$remote_content['blog']['items'];
		}

		/* This call home gets a list of available downloads - We have minimum security because no sensitive information is revealed */
		$response = wp_remote_get( self::$blog_uri );

		/* unserialize response */
		$xml = simplexml_load_string( $response['body'] , null  , LIBXML_NOCDATA );
		$json = json_encode($xml);
		self::$blogs = json_decode($json,TRUE);
		
		/* build new expiration date */
		self::$remote_content['blog']['expire'] = gmdate('Y-m-d G:i:s' , strtotime( "+3 hours" ));
		
		/* build doc dataset */
		self::$remote_content['blog']['items'] = self::$blogs['channel']['item']; 
		
		/* update data object */
		Inbound_Options_API::update_option( 'inbound-api' , 'remote' , self::$remote_content );
		
		return self::$blogs['channel']['item'];
	}
	
	/**
	*  Returns templates from dataset
	*  @return ARRAY
	*/
	public static function get_pro_templates() {
		self::get_downloads();
		self::$templates = array();
		
		foreach ( self::$data as $key => $download ) {
			if ( $download->download_type == 'template' ) {
				self::$templates[] = (array) $download;
			}			
		}
		
		return self::$templates;
	}
	
	/**
	*  Returns extensions from dataset
	*  @return ARRAY
	*/
	public static function get_pro_extensions() {
		self::get_downloads();
		self::$extensions = array();
		
		foreach ( self::$data as $key => $download ) {
			if ( $download->download_type == 'extension' ) {
				self::$extensions[] = (array) $download;
			}			
		}
		
		return self::$extensions;
	}
	
	/**
	*  Get latest blog posts
	*/
	public static function get_inboundnow_blog_posts() {
	
	}
}
