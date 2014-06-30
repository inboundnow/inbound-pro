<?php

$class['CTA_Global_Placements_Advanced_Settings'] = new CTA_Global_Placements_Advanced_Settings();

class CTA_Global_Placements_Advanced_Settings
{
	function __construct()
	{
		$this->load_hooks();
	}
	
	function load_hooks()
	{
		/* settings for wordpress-call-to-action cpt */
		add_filter( "wp_cta_extension_data", array( $this , "add_advanced_settings") , 10 , 1 );
		
		/* handler for empty inputs */
		add_filter( "save_post", array( $this , "save_data_fallback") , 10 , 1 );
	}
	
		
	function add_advanced_settings( $wp_cta_data )
	{

		$parent_key = 'wp-cta';
		
		$wp_cta_data[$parent_key]['settings']['global-placements-header'] =   array(
			'datatype' => 'setting',
			'region' => 'advanced',
			'description'  => '<h3>Global Placements</h3>',
			'id'    => 'global-placements-header',
			'type'  => 'html-block'
		);		
		
		$wp_cta_data[$parent_key]['settings']['post-type-placements-header'] =   array(
			'datatype' => 'setting',
			'region' => 'advanced',
			'description'  => '&nbsp;&nbsp;<i>Placement by Post Type</i>',
			'id'    => 'post-type-placements-header',
			'type'  => 'html-block',
			'global' => true
		);		
		
		/* build option array of post types */
		$post_types= get_post_types( array('public'=>true) ,'names');
		
		$options = array();
		foreach ($post_types as $post_type ) 
		{
			if ( in_array( $post_type , array( 'revision','nav_menu_item' , 'attachment' , 'wp-call-to-action' ) ) ) {
				continue;
			}
			$options[ $post_type ] = $post_type; 
		}

		$wp_cta_data[$parent_key]['settings']['post-types'] = array(
			'datatype' => 'setting',
			'region' => 'advanced',
			'label' => 'Post Types',
			'description'  => 'Place on these post types',
			'id'    => 'post-type',
			'type'  => 'multiselect',
			'options' => $options,
			'global' => true
		);

		$wp_cta_data[$parent_key]['settings']['cta_content_placement']  = array(
			'data_type' => 'setting',
			'region' => 'advanced',
			'label' => 'Placement',
			'description' => "Where would you like to insert the CTA on this page?",
			'id'  => 'content-placement',
			'type'  => 'dropdown',
			'default'  => 'off',
			'options' => array( 
								'above'=>'Above Content',
								'middle' => 'Middle of Content',
								'below' => 'Below Content',
								'widget_1' => 'Use Dynamic Sidebar Widget'
							  ),
			'context'  => 'normal',
			'class' => 'cta-per-page-option',
			'global' => true
		);
		
		return $wp_cta_data;
	}
	
	/* This hook will delete the wp-cta-post-type meta pair if it's empty */
	public static function save_data_fallback( $cta_id ) {
		global $post;
		unset($_POST['post_content']);

		if ( wp_is_post_revision( $cta_id ) ) {
			return;
		}
		
		if (  !isset($_POST['post_type']) || $_POST['post_type'] != 'wp-call-to-action' ) {
			return;
		}
		
		if (!isset($_POST['wp-cta-post-type'])) {
			delete_post_meta( $cta_id , 'wp-cta-post-type' );
		}
	}
}