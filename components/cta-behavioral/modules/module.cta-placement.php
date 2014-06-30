<?php

$wp_cta_bt = new WPCTA_Behavioral();

class WPCTA_Behavioral {
	
	private $bt_ctas;
	private $protected;
	
	function __construct()
	{
		
		add_action('wp_footer', array( $this , 'select_behaviorals' ) );
		
		add_filter('wp_cta_variation_class', array( $this, 'add_behavioral_class' ) , 10 , 3 );
		
		add_filter('wp_cta_variation_attributes', array( $this, 'add_behavioral_attributes' ) , 10 , 3 );
		
	}
	
	
	function select_behaviorals()
	{
		wp_enqueue_script('wp-cta-bt-script', CTA_BT_URLPATH.'js/targeting.js', array('jquery'), "1", true);
		$params = array( 'ajax_url'=>WP_CTA_URLPATH.'modules/module.ajax-get-variation.php' ,  'admin_url' => admin_url( 'admin-ajax.php' ) );
		wp_localize_script( 'wp-cta-bt-script', 'wp_cta_bt', $params );
	}
	
	function add_behavioral_class( $class , $cta_id , $vid)
	{
		if ($vid>0)
		{
			$suffix = '-'.$vid;
		}
		else
		{
			$suffix = '';
		}
		
		$is_behavioral = get_post_meta( $cta_id, 'wp-cta-bt-status'.$suffix , true );
		
		
		if ($is_behavioral)
		{
			$class = $class.' is_behavioral';			
		}
		
		return $class;
	}
	
	function add_behavioral_attributes( $attributes , $cta_id , $vid)
	{
		if ($vid>0)
		{
			$suffix = '-'.$vid;
		}
		else
		{
			$suffix = '';
		}
		
		$is_behavioral = get_post_meta( $cta_id, 'wp-cta-bt-status'.$suffix , true );
		
		
		if ($is_behavioral)
		{

			$lists = get_post_meta( $cta_id, 'wp-cta-bt-lists'.$suffix , true );
			if($lists)
			{
				$lists = implode(',', $lists);
				$attributes = $attributes.' behavioral="'.$lists.'" cta_id="'.$cta_id.'" vid="'.$vid.'"';
			}
		}
		
		return $attributes;
	}
	
		
}
