<?php

add_action('init','CTA_Global_Placements' , 10);
function CTA_Global_Placements()
{
	$calls_to_action =  new CTA_Global_Placements();
}


class CTA_Global_Placements 
{
	private $post_type_placements;
	private $ctas;
	private $cta_list;
	private $cta_already_loaded;
	private $cta_placement;
	private $obj;
	private $obj_id;
	private $obj_post_type;
	
	
	function __construct()
	{	
		
		$this->load_global_ctas();
		$this->hooks();
	}
	
	function hooks()
	{
		/* Get Global $post Object */
		add_action('wp', array( $this, 'read_wp_enviroment' ) , 1 );
		
		/* Build Qualified CTA List */
		add_action('wp', array( $this, 'qualify_ctas' ) , 1 );
		
		/* Add Qualified CTAs to Display List */
		add_filter('wp_cta_display_list' , array( $this , 'load_cta_placement_list' ) );
		
		/* Set Placement If Not Already Defined */
		add_filter('wp_cta_content_placement' , array( $this , 'set_cta_content_placement' ) );
	}
	
	
	function load_global_ctas()
	{
		$posts = get_posts( array(
			'post_type' => 'wp-call-to-action',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
			   'key' => 'wp-cta-post-type',
			   'compare' => 'EXISTS',
				)
			) 
		));
				

		$ctas = array();
		foreach ($posts as $post)
		{
			$ctas[$post->ID]['placement'] = get_post_meta( $post->ID , 'wp-cta-content-placement' , true );
			$ctas[$post->ID]['post_types'] = get_post_meta( $post->ID , 'wp-cta-post-type' , true );
		}
		
		$this->ctas = $ctas;
	}
	
	function read_wp_enviroment()
	{
		global $wp_query;
		
		$this->obj = $wp_query->get_queried_object();
		$this->obj_id = $wp_query->get_queried_object_id();
		$this->obj_post_type = get_post_type( $this->obj_id );
		
	}
	
	function qualify_ctas()
	{
		foreach ($this->ctas as $cta_id => $cta)
		{
			/* check post type */
			if ( in_array( $this->obj_post_type , $cta['post_types'] ) ) {
				$cta_list[$cta_id]['id'] = $cta_id;
				$cta_list[$cta_id]['placement'] = $cta['placement'];
			}
		}
		
		if ( !isset($cta_list) || !$cta_list ) {
			$this->cta_list = array();
			return;
		}
		
		/* return one cta out of list of available ctas */
		$key = array_rand($cta_list);
		
		$this->cta_list = array( '0' => $key );
		$this->cta_placement = $cta_list[$key]['placement'];

	}
	
	function load_cta_placement_list($cta_display_list)
	{	
		if ( $cta_display_list || !$this->cta_list ) { 
			$this->cta_already_loaded = true;
			return $cta_display_list;
		}
		
		return $this->cta_list;
	}
	
	function set_cta_content_placement($cta_content_placement)
	{	
		if ( $this->cta_already_loaded ) {
			return $cta_content_placement;
		}
		
		return $this->cta_placement;
	}
}