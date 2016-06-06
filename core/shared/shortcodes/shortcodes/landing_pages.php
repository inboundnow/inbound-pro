<?php
/**
*   Entries Shortcode
*/

/* 	Shortcode generator config
 * 	----------------------------------------------------- */
	$shortcodes_config['landing_pages'] = array(
		'no_preview' => true,
		'options' => array(
			'heading' => array(
				'name' => __('Heading Text', 'inbound-pro' ),
				'desc' => __('Enter the heading text.', 'inbound-pro' ),
				'type' => 'text',
				'std' => 'Recent Landing Pages'
			),
			'column' => array(
				'name' => __('Column', 'inbound-pro' ),
				'desc' => __('Select the number of column(s).', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'1' => __('1 Column', 'inbound-pro' ),
					'2' => __('2 Columns', 'inbound-pro' ),
					'3' => __('3 Columns', 'inbound-pro' ),
					'4' => __('4 Columns', 'inbound-pro' ),
					'5' => __('5 Columns', 'inbound-pro' )
				),
				'std' => '4'
			),
			'number' => array(
				'name' => __('Post Number', 'inbound-pro' ),
				'desc' => __('Enter the number of post to show. (enter -1 for all posts)', 'inbound-pro' ),
				'type' => 'text',
				'std' => '4'
			),
			'cat' => array(
				'name' => __('Category', 'inbound-pro' ),
				'desc' => __('Optional you can sort by a category.', 'inbound-pro' ),
				'type' => 'select',
				'options' => $lp_cats,
				'std' => ''
			),
			'excerpt_lenght' => array(
				'name' => __('Excerpt Lenght', 'inbound-pro' ),
				'desc' => __('The post excerpt word lenght.', 'inbound-pro' ),
				'type' => 'text',
				'std' => '30'
			),
			'thumbs' => array(
				'name' => __('Show Featured Thumbnails', 'inbound-pro' ),
				'checkbox_text' => __('Uncheck to hide featured thumbnails', 'inbound-pro' ),
				'desc' => '',
				'type' => 'checkbox',
				'std' => '1'
			),
		),
		'shortcode' => '[landing_pages heading="{{heading}}" column="{{column}}" number="{{number}}" cat="{{cat}}" excerpt_lenght="{{excerpt_lenght}}" thumbs="{{thumbs}}"]',
		'popup_title' => __('Insert Landing Page List Shortcode', 'inbound-pro' )
	);

/* 	Page builder module config
 * 	----------------------------------------------------- */
	$freshbuilder_modules['landing_pages'] = array(
		'name' => __('Entries', 'inbound-pro' ),
		'size' => 'one_full',
		'options' => array(
			'heading' => array(
				'name' => __('Heading', 'inbound-pro' ),
				'desc' => __('Enter the heading text.', 'inbound-pro' ),
				'type' => 'text',
				'std' => 'Recent Posts',
				'class' => '',
				'is_content' => '0'
			),
			'column' => array(
				'name' => __('Column', 'inbound-pro' ),
				'desc' => __('Select the number of column(s).', 'inbound-pro' ),
				'type' => 'select',
				'options' => array(
					'1' => __('1 Column', 'inbound-pro' ),
					'2' => __('2 Columns', 'inbound-pro' ),
					'3' => __('3 Columns', 'inbound-pro' ),
					'4' => __('4 Columns', 'inbound-pro' ),
					'5' => __('5 Columns', 'inbound-pro' )
				),
				'std' => '4',
				'class' => '',
				'is_content' => '0'
			),
			'number' => array(
				'name' => __('Post Number', 'inbound-pro' ),
				'desc' => __('Enter the number of post to show.', 'inbound-pro' ),
				'type' => 'text',
				'std' => '4',
				'class' => '',
				'is_content' => '0'
			),
			'cat' => array(
				'name' => __('Category', 'inbound-pro' ),
				'desc' => __('Optional you can sort by a category.', 'inbound-pro' ),
				'type' => 'select',
				'options' => $lp_cats,
				'std' => '',
				'class' => '',
				'is_content' => '0'
			),
			'excerpt_lenght' => array(
				'name' => __('Excerpt Lenght', 'inbound-pro' ),
				'desc' => __('The post excerpt word lenght.', 'inbound-pro' ),
				'type' => 'text',
				'std' => '30',
				'class' => '',
				'is_content' => '0'
			)
		)
	);

/* 	Add shortcode
 * 	----------------------------------------------------- */
	add_shortcode('landing_pages', 'inbound_shortcode_landing_pages');
	if (!function_exists('inbound_shortcode_landing_pages')) {
		function inbound_shortcode_landing_pages( $atts, $content = null ) {
			extract(shortcode_atts(array(
				'heading' => 'Recent Posts',
				'column' => '4',
				'number' => '4',
				'cat' => '',
				'excerpt_lenght' => '30',
				'thumbs' => '1'
			), $atts));

			$grid = ' inbound-grid full';
			if ($column == '2') $grid = ' inbound-grid one-half';
			if ($column == '3') $grid = ' inbound-grid one-third';
			if ($column == '4') $grid = ' inbound-grid one-fourth';
			if ($column == '5') $grid = ' inbound-grid one-fifth';

			$out = '';
			$i = 0;

			$out .= '<div class="inbound-row">';
			if ($heading != '') $out .= '<div class="inbound-grid full"><div class="heading"><h3>'.$heading.'</h3><div class="sep"></div></div></div>';

			$number = ($number) ? $num = $number : $num = -1;

			$cat = ($cat) ? "$cat" : '';

			//$args = "post_type=landing-page$number$cat"; // &post_type=landing-page
			if ($cat === 'all' || $cat === '') {
				$args = array(
	                        'post_type' => 'landing-page',
	                        'posts_per_page' => $number,
	        			);
			} else {
				$args = array(
	                        'post_type' => 'landing-page',
	                        'posts_per_page' => $number,
	                        'tax_query' => array(
	                                        array(
	                                            'taxonomy' => 'landing_page_category',
	                                            'field' => 'id',
	                                            'terms' => $cat
	                                        ))
	        		);
			}

			$loop = new WP_Query( $args);

			if ( $loop->have_posts() ) :
				while ( $loop->have_posts() ) : $loop->the_post(); $i++;
				$id = $loop->post->ID;
				$thumbnail = '';
				$title = get_the_title($id);

				if(empty($title)){
					$title = get_post_meta( $id , 'lp-main-headline',true );
				}

				if ($thumbs != 0){
				$thumbnail = get_the_post_thumbnail($post->ID, '500x360');
				}


				//print_r($loop);
				$out .= '<div class="'.$grid.'">';
				$out .= '<div class="recent-entry inbound-clearfix">'.$thumbnail.'';
					$out .= '<header class="recent-entry-header">';
						$out .= '<h3 class="recent-entry-title"><a class="reserve" href="'.get_permalink().'" title="" rel="bookmark">'.$title.'</a></h3>';
					$out .= '</header>';

					$out .= '<div class="recent-entry-summary">';
					//$out .= the_excerpt($id);
					$out .= '</div>';
				$out .= '</div>';
				$out .= '</div>';

				if( $i == $column ) $out .= '<div class="inbound-clear"></div>';

				endwhile;
			endif;
			wp_reset_postdata();
			$out .= '</div>';

			return $out;
		}
	}