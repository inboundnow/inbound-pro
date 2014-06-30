<?php

/* Filter the status during post_save hook */
add_filter( "wp_cta_save_variation_status" , "wp_cta_bt_update_variation_status");
function wp_cta_bt_update_variation_status( $status )
{
	/* get variation */
	$vid = $_POST['wp-cta-variation-id'];
	
	
	/* if behavior dependancy is not checked */
	if (!isset($_POST['wp-cta-bt-status-' . $vid])) {
		return $status;
	}

	/* If behavior dependancy is checked */			
	return 'behavioral';
	
}


/* account for behavioral variation status */
add_action('wp_cta_print_variation_status_note', 'wp_cta_print_behavioral_status');
function wp_cta_print_behavioral_status($variation_status)
{
	if ( $variation_status == 'behavioral' ){
		echo "<span class='is-behavoral' style='font-size:10px;font-style:italic;padding-left:10px;'>behavioral</span>";
	}
}

/* settings for wordpress-call-to-action cpt */
add_filter( "wp_cta_extension_data", "wp_cta_bt_meta_boxes" , 10 , 1 );
function wp_cta_bt_meta_boxes( $wp_cta_data )
{

	$parent_key = 'wp-cta';

	$wp_cta_data[$parent_key]['settings']['bt-header'] =	array(
		'datatype' => 'setting',
		'region' => 'advanced',
		'description'	=> '<h3>Behavioral Targeting</h3>',
		'id'	=> 'behavioral-targeting-header',
		'type'	=> 'html-block'
	);

	$wp_cta_data[$parent_key]['settings']['bt-status'] =	array(
		'datatype' => 'setting',
		'region' => 'advanced',
		'label' => 'Enable',
		'description'	=> 'Turn on behavorial Targeting',
		'id'	=> 'bt-status',
		'options_area' => 'advanced',
		'class' => 'behavorial-targeting',
		'type'	=> 'checkbox'
	);

	/* build array of lead lists */
	$options = array();
	$categories = get_terms( 'wplead_list_category', array(
						'orderby'	=> 'count',
						'hide_empty' => 0
					) );
					
	if (isset($categories->errors)) {
		return $wp_cta_data;
	}
	
	foreach ($categories as $cat){
		$options[ $cat->term_id ] = $cat->name;
	}


	$wp_cta_data[$parent_key]['settings']['bt-lists'] = array(
		'datatype' => 'setting',
		'region' => 'advanced',
		'label' => 'Which Lists',
		'description'	=> 'Select the lead list(s) that you would like to trigger this call to action',
		'id'	=> 'bt-lists',
		'options_area' => 'advanced',
		'class' => 'behavorial-targeting',
		'type'	=> 'multiselect',
		'options' => $options ,
		);



	return $wp_cta_data;
}



/* metaboxes for non wp-call-to-action post types */
/*
add_action('','wp_cta_bt_global_metabox_display');
function wp_cta_bt_global_metabox_display()
{

}
*/