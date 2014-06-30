<?php

$wp_cta_bt = new WPCTA_Behavioral();

class WPCTA_Behavioral {
	
	private $bt_ctas;
	
	function __construct()
	{
		add_action('wp_cta_content', array( $this , 'load_behaviorals') );
		
		add_action('wp_footer', array( $this , 'select_behaviorals' );
	}
	
	function load_behaviorals($cta_template) 
	{
		global $post;
		
		/* get all behavioral calls to action */
		$args = array(
				'post_type' => 'wp-call-to-action',
				'posts_per_page' => -1,
				'meta_query' => array(
									array(
								   'key' => 'wp_cta_global_bt_status',
								   'value' => 'on',
								   'compare' => 'IN',
									)
								) 
		);
		
		$behavioral_ctas = get_posts( $args );

		foreach( $behavioral_ctas  as $cta)
		{
			
			$lists_targeted = get_post_meta( $cta->ID, 'wp_cta_global_bt_lists', TRUE );
			$bt_ctas[$cta->ID]['lists'] = $lists_targeted;
			
			$url = get_permalink( $cta->ID );
				
			$bt_ctas[$cta->ID]['id'] = $cta->ID;
			$bt_ctas[$cta->ID]['url'] = $url;
			$bt_ctas[$cta->ID]['variations'] = explode( ',', get_post_meta( $cta->ID, 'cta_ab_variations', true ) );
			
			$meta = get_post_meta(  $cta->ID ); // move to ext

			if ( count($bt_ctas[$cta->ID]['variations'])==1 && !$bt_ctas[$cta->ID]['variations'][0] )
			{
				$bt_ctas[$cta->ID]['variations'] = array( 0 => 0 );
			}
			
			foreach ($bt_ctas[$cta->ID]['variations'] as $vid)
			{
				($vid<1) ? $suffix = '' : $suffix = '-'.$vid;
					
				$template_slug = $meta['wp-cta-selected-template'.$suffix][0];
				$bt_ctas[$cta->ID]['templates'][$vid]['slug'] = $template_slug;
				$bt_ctas[$cta->ID]['meta'][$vid]['wp-cta-selected-template-'.$vid] = $template_slug;
				
				/* determin where template exists for asset loading	*/			
				if (file_exists(WP_CTA_PATH.'templates/'.$template_slug.'/index.php'))
				{					
					$bt_ctas[$cta->ID]['templates'][$vid]['path'] = WP_CTA_PATH.'templates/'.$template_slug.'/';
					$bt_ctas[$cta->ID]['templates'][$vid]['urlpath'] = WP_CTA_URLPATH.'templates/'.$template_slug.'/';
				}
				else
				{
					//query_posts ($query_string . '&showposts=1');
					$bt_ctas[$cta->ID]['templates'][$vid]['path'] = WP_CTA_UPLOADS_PATH.$template_slug.'/';
					$bt_ctas[$cta->ID]['templates'][$vid]['urlpath'] = WP_CTA_UPLOADS_URLPATH.$template_slug.'/';
				
				}

				/* split meta value by variation */
				foreach ($meta as $k=>$value)
				{

					$count = strlen("-{$vid}");
					if (substr( $k , - $count) == "-{$vid}")
					{
						$bt_ctas[$cta->ID]['meta'][$vid][$k] = $value[0];
						unset($meta[$k]);
					}
				}					
			}
			
			/* make remaining meta pretty */
			foreach ($meta as $k=>$value)
			{					
				$meta[$k] = $value[0];
			}	
			
			/* set remaining meta to variation 0 */
			if (isset($bt_ctas[$cta->ID]['meta'][0]))
			{
				$bt_ctas[$cta->ID]['meta'][0] = array_merge($bt_ctas[$cta->ID]['meta'][0] , $meta);
			}
			else
			{
				$bt_ctas[$cta->ID]['meta'][0] = $meta;
			}
			
			/* manually set content for variation 0 */
			$bt_ctas[$cta->ID]['meta'][0]['content-0'] = get_post_field( 'post_content', $cta->ID );	
		}

		$this->bt_ctas = $bt_ctas;
		
		foreach ($bt_ctas as $cta_id => $selected_cta)
		{
			$bt_cta_template .= CallsToAction::build_cta_content( $selected_cta );
		}
		
		$cta_template = $cta_template.$bt_cta_template;
	
		return $cta_template;
	}
	
	function select_behaviorals()
	{
		if ( !isset($this->bt_ctas) ){
			return;
		}
		
		foreach ($this->bt_ctas as $cta_id => $selected_cta)
		{
			$cta_ids[] = $cta_id;
		}
		
		wp_enqueue_script('wp-cta-bt-script', CTA_BT_URLPATH.'js/targeting.js', array('jquery'), "1", true);
		$params = array( 'rules' => $this->bt_ctas , 'cta_ids' => $cta_ids );
		wp_localize_script( 'wp-cta-bt-script', 'wp_cta_bt', $params );
	}
		
}