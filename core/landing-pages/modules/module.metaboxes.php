<?php
/**
 * Prepare Landing Page Form Metabox
 * Roll into /shared/
 */

// Add additonal WYSIWYG edit box to landing page custom post type:
define('WYSIWYG_META_BOX_ID', 'lp_2_form_content');
define('WYSIWYG_EDITOR_ID', 'landing-page-myeditor');
define('WYSIWYG_META_KEY', 'lp-conversion-area');

/* ADD THUMBNAIL METABOX TO SIDEBAR */
add_action('add_meta_boxes', 'lp_display_thumbnail_metabox');
function lp_display_thumbnail_metabox() {
	global $post;
	if($post->post_status !== 'draft') {
		add_meta_box(
		'lp-thumbnail-sidebar-preview',
		__( 'Template Preview', 'landing-pages'),
		'lp_thumbnail_metabox',
		'landing-page' ,
		'side',
		'high' );
	}
}

function lp_thumbnail_metabox() {
	global $post;

	$template = get_post_meta($post->ID, 'lp-selected-template', true);
	$template = apply_filters('lp_selected_template',$template);
	$var_id = (isset($_GET['lp-variation-id'])) ? $_GET['lp-variation-id'] : '0';
	$original_perma = get_permalink($post->ID);

	if ( preg_match( '/lp-variation-id/', $original_perma ) ) {
	    $iframe_preview_link = get_permalink($post->ID) . "&cache_bust=true&dont_save=true";
	} else {
		$iframe_preview_link = get_permalink($post->ID) . "?lp-variation-id=$var_id&cache_bust=true&dont_save=true";
	}

	$datetime = the_modified_date('YmjH',null,null,false);
	$permalink = $original_perma.'?dt='.$datetime;

	if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {

		if (file_exists(LANDINGPAGES_UPLOADS_PATH .  $template . '/thumbnail.png')) {
			$thumbnail = LANDINGPAGES_UPLOADS_URLPATH . $template . '/thumbnail.png';
		}
		else {
			$thumbnail = LANDINGPAGES_URLPATH . 'templates/' . $template . '/thumbnail.png';
		}

	} else {
		$thumbnail = 'http://s.wordpress.com/mshots/v1/' . urlencode(esc_url($permalink)) . '?w=250';
	}
	$permalink = apply_filters('lp_live_screenshot_url', $permalink);
	?>

	<style type="text/css">
	#lp-thumbnail-sidebar-preview {
		background: transparent !important;
	}
	#lp-thumbnail-sidebar-preview .handlediv, #lp-thumbnail-sidebar-preview .hndle {
		display: none !important;
	}
	#lp-thumbnail-sidebar-preview .inside {
		padding: 0px !important;
		  margin: 0px;
		  border: none !important;
		  margin-top: -20px !important;
		  margin-bottom: -10px;
	}
	#lp-thumbnail-sidebar-preview  #zoomer-wrapper {
		vertical-align: top;
	}
	#lp-thumbnail-sidebar-preview iframe#zoomer {
		 margin-top: -30px;
	}
	</style>
	<?php if (!isset($_GET['new-variation']) ) { ?>

		<div class="inside" >

			<?php
				echo "<iframe src='$iframe_preview_link' id='zoomer'></iframe>";
				//echo "<a href='$permalink' target='_blank' ><img src='$thumbnail' style='width:250px;height:250px;' title='". __( 'Preview this theme' , 'landing-pages') ." ,  ({$template})'></a>";
			?>

		</div>

	<?php }
}

/* ADD CONVERSION AREA METABOX */

add_action('add_meta_boxes', 'lp_display_meta_box_lp_conversion_area');
function lp_display_meta_box_lp_conversion_area(){
	add_meta_box( WYSIWYG_META_BOX_ID, __('Landing Page Form or Conversion Button - <em>click the black & blue power button icon to build forms/buttons</em>', 'landing-pages'), 'lp_meta_box_conversion_area', 'landing-page', 'normal', 'high' );
	//add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
}

// Phasing out conversion area sometime soon
function lp_meta_box_conversion_area(){

	global $post;

	$meta_box_id = WYSIWYG_META_BOX_ID;
	$editor_id = WYSIWYG_EDITOR_ID;

	//Add CSS & jQuery goodness to make this work like the original WYSIWYG
	echo "
			<style type='text/css'>
					#$meta_box_id #edButtonHTML, #$meta_box_id #edButtonPreview {background-color: #F1F1F1; border-color: #DFDFDF #DFDFDF #CCC; color: #999;}
					#$editor_id{width:100%;}
					#$meta_box_id #editorcontainer{background:#fff !important;}
					#$meta_box_id #editor_id_fullscreen{display:none;}
			</style>

			<script type='text/javascript'>
					jQuery(function($){
							$('#$meta_box_id #editor-toolbar > a').click(function(){
									$('#$meta_box_id #editor-toolbar > a').removeClass('active');
									$(this).addClass('active');
							});

							if($('#$meta_box_id #edButtonPreview').hasClass('active')){
									$('#$meta_box_id #ed_toolbar').hide();
							}

							$('#$meta_box_id #edButtonPreview').click(function(){
									$('#$meta_box_id #ed_toolbar').hide();
							});

							$('#$meta_box_id #edButtonHTML').click(function(){
									$('#$meta_box_id #ed_toolbar').show();
							});

			//Tell the uploader to insert content into the correct WYSIWYG editor
			$('#media-buttons a').bind('click', function(){
				var customEditor = $(this).parents('#$meta_box_id');
				if(customEditor.length > 0){
					edCanvas = document.getElementById('$editor_id');
				}
				else{
					edCanvas = document.getElementById('content');
				}
			});
					});
			</script>
	";

	//Create The Editor
	$conversion_area = lp_conversion_area(null,null,true,false,false);
	wp_editor($conversion_area, $editor_id);

	//Clear The Room!
	echo "<div style='clear:both; display:block;'></div>";
	echo "<div style='width:100%;text-align:right;margin-top:11px;'><div class='lp_tooltip'  title=\"". __('To help track conversions Landing Pages Plugin will automatically add a tracking class to forms. If you would like to track a link add this class to it' , 'landing-pages') ." class='wpl-track-me-link'\" ></div></div>";

}

add_action('save_post', 'lp_wysiwyg_save_meta');
function lp_wysiwyg_save_meta(){

	$editor_id = WYSIWYG_EDITOR_ID;
	$meta_key = WYSIWYG_META_KEY;

	if(isset($_REQUEST[$editor_id]))
	{
		$data = wpautop($_REQUEST[$editor_id]);

		update_post_meta($_REQUEST['post_ID'], WYSIWYG_META_KEY, $data);
	}
}


// Add in Main Headline
add_action( 'edit_form_after_title', 'lp_landing_page_header_area' );
add_action( 'save_post', 'lp_save_header_area' );
add_action( 'save_post', 'lp_save_notes_area' );

function lp_landing_page_header_area()
{
	global $post;
	$lp_variation = (isset($_GET['lp-variation-id'])) ? $_GET['lp-variation-id'] : '0';
	$main_title = get_post_meta( $post->ID , 'lp-main-headline', true );
	$variation_notes = get_post_meta( $post->ID , 'lp-variation-notes', true );
    if ( empty ( $post ) || 'landing-page' !== get_post_type( $GLOBALS['post'] ) )
        return;

    if ( ! $main_title = get_post_meta( $post->ID , 'lp-main-headline',true ) )
        $main_title = '';

    if ( ! $variation_notes = get_post_meta( $post->ID , 'lp-variation-notes',true ) )
    $variation_notes = '';
	$main_title = apply_filters('lp_edit_main_headline', $main_title, 1);
	$variation_notes = apply_filters('lp_edit_variation_notes', $variation_notes, 1);
	$variation_id = apply_filters( 'lp_display_notes_input_id' , 'lp-variation-notes' );

	echo "<div id='lp-notes-area'>";
	echo "<span id='add-lp-notes'>". __('Notes' , 'landing-pages') .":</span><input placeholder='". __('Add Notes to your variation. Example: This version is testing a green submit button ' , 'landing-pages') ."' type='text' class='lp-notes' name='{$variation_id}' id='{$variation_id}' value='{$variation_notes}' size='30'>";
	echo '</div><div id="main-title-area"><input type="text" name="lp-main-headline" placeholder="'. __('Primary Headline Goes here. This will be visible on the page' , 'landing-pages') .'" id="lp-main-headline" value="'.$main_title.'" title="'. __('This headline will appear in the landing page template.' , 'landing-pages') .'"></div><div id="lp-current-view">'.$lp_variation.'</div><div id="switch-lp">0</div>';
	echo ""; ?>

	<?php
   // Frontend params
    if(isset($_REQUEST['frontend']) && $_REQUEST['frontend'] == 'true') {
    echo('<input type="hidden" name="frontend" id="frontend-on" value="true" />');
}

}
function lp_save_header_area( $post_id )
{
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;

    if ( ! current_user_can( 'edit_post', $post_id ) )
        return;

    $key = 'lp-main-headline';

    if ( isset ( $_POST[ $key ] ) )
        return update_post_meta( $post_id, $key, $_POST[ $key ] );


    delete_post_meta( $post_id, $key );
}

function lp_save_notes_area( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;

    if ( ! current_user_can( 'edit_post', $post_id ) )
        return;

    $key = 'lp-variation-notes';

    if ( isset ( $_POST[ $key ] ) )
        return update_post_meta( $post_id, $key, $_POST[ $key ] );

    delete_post_meta( $post_id, $key );
}


add_filter( 'enter_title_here', 'lp_change_enter_title_text', 10, 2 );
function lp_change_enter_title_text( $text, $post ) {
	if ($post->post_type=='landing-page') {
        return __( 'Enter Landing Page Description' , 'landing-pages');
	} else {
		return $text;
	}
}


add_action('add_meta_boxes', 'add_custom_meta_box_select_templates');
function add_custom_meta_box_select_templates() {

	add_meta_box(
		'lp_metabox_select_template', // $id
		__( 'Landing Page Templates', 'landing-pages'),
		'lp_display_meta_box_select_template', // $callback
		'landing-page', // $page
		'normal', // $context
		'high'); // $priority
}

// Render select template box
function lp_display_meta_box_select_template() {
	global $post;
	$template =  get_post_meta($post->ID, 'lp-selected-template', true);

	$template = apply_filters('lp_selected_template',$template);
	//echo $template;
	if (!isset($template)||isset($template)&&!$template){
		$template = 'default';
	}

	$name = apply_filters('lp_selected_template_id','lp-selected-template');

	// Use nonce for verification
	echo "<input type='hidden' name='lp_lp_custom_fields_nonce' value='".wp_create_nonce('lp-nonce')."' />";
	?>

	<div id="lp_template_change"><h2><a class="button" id="lp-change-template-button"><?php _e( 'Choose Another Template' , 'landing-pages'); ?></a></div>
	<input type='hidden' id='lp_select_template' name='<?php echo $name; ?>' value='<?php echo $template; ?>'>
		<div id="template-display-options"></div>

	<?php
}

add_action('admin_notices', 'lp_display_meta_box_select_template_container');

// Render select template box
function lp_display_meta_box_select_template_container() {
	global $post;

	$current_url = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]."";

	if (isset($post)&&$post->post_type!='landing-page'||!isset($post)){ return false; }

	$toggle = ( !strstr( $current_url, 'post-new.php')) ? "display:none" : "";

	$extension_data = lp_get_extension_data();
	$extension_data_cats = Landing_Pages_Load_Extensions::get_template_categories();

	unset($extension_data['lp']);

	ksort($extension_data_cats);
	$uploads = wp_upload_dir();
	$uploads_path = $uploads['basedir'];
	$extended_path = $uploads_path.'/landing-pages/templates/';

	$template =  get_post_meta($post->ID, 'lp-selected-template', true);
	$template = apply_filters('lp_selected_template',$template);

	echo "<div class='lp-template-selector-container' style='{$toggle}'>";
	echo "<div class='lp-selection-heading'>";
	echo "<h1>". __( 'Select Your Landing Page Template!' , 'landing-pages') ."</h1>";
	echo '<a class="button-secondary" style="display:none;" id="lp-cancel-selection">'. __('Cancel Template Change' , 'landing-pages') .'</a>';
	echo "</div>";
		echo '<ul id="template-filter" >';
			echo '<li class="button-primary button"><a href="#" data-filter=".template-item-boxes">'. __( 'All' , 'landing-pages') .'</a></li>';
			echo '<li class="button-primary button"><a href="#" data-filter=".theme">'. __( 'Theme' , 'landing-pages' ) .'</a></li>';
			$categories = array('Theme');
			foreach ($extension_data_cats as $cat)
			{

				$slug = str_replace(' ','-',$cat['value']);
				$slug = strtolower($slug);
				$cat['value'] = ucwords($cat['value']);
				if (!in_array($cat['value'],$categories))
				{
					echo '<li class="button"><a href="#" data-filter=".'.$slug.'">'.$cat['value'].'</a></li>';
					$categories[] = $cat['value'];
				}

			}
		echo "</ul>";
		echo '<div id="templates-container" >';

		foreach ($extension_data as $this_extension=>$data)
		{

			if (substr($this_extension,0,4)=='ext-')
				continue;

			if (isset($data['info']['data_type']) && $data['info']['data_type']=='metabox')
				continue;


			$cats = explode( ',' , $data['info']['category'] );
			foreach ($cats as $key => $cat) {
				$cat = trim($cat);
				$cat = str_replace(' ', '-', $cat);
				$cats[$key] = trim(strtolower($cat));
			}

			$cat_slug = implode(' ', $cats);

			$thumb = false;
			// Get Thumbnail
			if (file_exists(LANDINGPAGES_PATH.'templates/'.$this_extension."/thumbnail.png")) {
				if ($this_extension=='default') {

					$thumbnail =  get_bloginfo('template_directory')."/screenshot.png";

				} else {

					$thumbnail = LANDINGPAGES_URLPATH.'templates/'.$this_extension."/thumbnail.png";

				}
				$thumb = true;
			}

			if (file_exists(LANDINGPAGES_UPLOADS_PATH.$this_extension."/thumbnail.png")) {
				$thumbnail = LANDINGPAGES_UPLOADS_URLPATH.$this_extension."/thumbnail.png";
				$thumb = true;
			}

			if ($thumb === false) {
				$thumbnail = LANDINGPAGES_URLPATH.'templates/default/thumbnail.png';

			}
			$demo_link = (isset($data['info']['demo'])) ? $data['info']['demo'] : '';
			?>
			<div id='template-item' class="<?php echo $cat_slug; ?> template-item-boxes">
				<div id="template-box">
					<div class="lp_tooltip_templates" title="<?php echo $data['info']['description']; ?>"></div>
				<a class='lp_select_template' href='#' label='<?php echo $data['info']['label']; ?>' id='<?php echo $this_extension; ?>'>
					<img src="<?php echo $thumbnail; ?>" class='template-thumbnail' alt="<?php echo $data['info']['label']; ?>" id='lp_<?php echo $this_extension; ?>'>
				</a>
				<p>
					<div id="template-title"><?php echo $data['info']['label']; ?></div>
					<a href='#' label='<?php echo $data['info']['label']; ?>' id='<?php echo $this_extension; ?>' class='lp_select_template'><?php _e( 'Select' , 'landing-pages'); ?></a> |
					<a class='<?php echo $cat_slug;?>' target="_blank" href='<?php echo $demo_link;?>' id='lp_preview_this_template'><?php _e( 'Preview' , 'landing-pages'); ?></a>
				</p>
				</div>
			</div>
			<?php
		}
	echo '</div>';
	echo "<div class='clear'></div>";
	echo "</div>";
	echo "<div style='display:none;' class='currently_selected'>". __( 'This is Currently Selected' , 'landing-pages') ."</a></div>";
}

// Custom CSS Widget
add_action('add_meta_boxes', 'add_custom_meta_box_lp_custom_css');
add_action('save_post', 'landing_pages_save_custom_css');

function add_custom_meta_box_lp_custom_css() {
   add_meta_box('lp_3_custom_css', __( 'Custom CSS' , 'landing-pages') , 'lp_custom_css_input', 'landing-page', 'normal', 'low');
}

function lp_custom_css_input() {
	global $post;

	_e("<em>Custom CSS may be required to customize this landing page.</em><strong> <u>Format</u>: #element-id { display:none !important; }</strong>" , 'landing-pages');
	echo '<input type="hidden" name="lp-custom-css-noncename" id="lp_custom_css_noncename" value="'.wp_create_nonce(basename(__FILE__)).'" />';
	$custom_css_name = apply_filters('lp_custom_css_name','lp-custom-css');
	echo '<textarea name="'.$custom_css_name.'" id="lp-custom-css" rows="5" cols="30" style="width:100%;">'.get_post_meta($post->ID,$custom_css_name,true).'</textarea>';
}

function landing_pages_save_custom_css($post_id) {
	global $post;

	if (!isset($post) || ( isset($post) && $post->post_type!='landing-page' ) )
		return;


	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;


	$custom_css_name = apply_filters('lp_custom_css_name','lp-custom-css');

	$lp_custom_css = $_POST[$custom_css_name];
	update_post_meta($post_id, 'lp-custom-css', $lp_custom_css);
}

//Insert custom JS box to landing page
add_action('add_meta_boxes', 'add_custom_meta_box_lp_custom_js');
add_action('save_post', 'landing_pages_save_custom_js');

function add_custom_meta_box_lp_custom_js() {
   add_meta_box('lp_3_custom_js', __('Custom JS' , 'landing-pages') , 'lp_custom_js_input', 'landing-page', 'normal', 'low');
}

function lp_custom_js_input() {
	global $post;
	echo "<em></em>";

	$custom_js_name = apply_filters('lp_custom_js_name','lp-custom-js');

	echo '<input type="hidden" name="lp_custom_js_noncename" id="lp_custom_js_noncename" value="'.wp_create_nonce(basename(__FILE__)).'" />';
	echo '<textarea name="'.$custom_js_name.'" id="lp_custom_js" rows="5" cols="30" style="width:100%;">'.get_post_meta($post->ID,$custom_js_name,true).'</textarea>';
}

function landing_pages_save_custom_js($post_id) {
	global $post;
	if (!isset($post) || ( isset($post) && $post->post_type!='landing-page' ) )
		return;


	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;

	$custom_js_name = apply_filters('lp_custom_js_name','lp-custom-js');

	$lp_custom_js = $_POST[$custom_js_name];

	update_post_meta($post_id, 'lp-custom-js', $lp_custom_js);
}

// Insert custom JS box
add_action('add_meta_boxes', 'add_custom_meta_box_lp_conversion_log');

function add_custom_meta_box_lp_conversion_log() {
   //add_meta_box('lp_conversion_log_metabox', __( 'Lead Captures' , 'landing-pages') , 'lp_conversion_log_metabox', 'landing-page', 'normal', 'low');
}


/* This class lists recent conversions for a landing page */
function lp_conversion_log_metabox() {


	class LP_LEAD_LOG extends WP_List_Table
	{
		private $extension_data;
		private $singular;
		private $plural;
		private $post;
		private $wpdb;

		function __construct()
		{
			global $post;
			global $wpdb;

			$final_data = array();


			$query = "SELECT
				wposts.*
				FROM ".$wpdb->posts." AS wposts
				INNER JOIN ".$wpdb->postmeta." AS wpostmeta
				ON wpostmeta.post_id = wposts.ID
				AND wpostmeta.meta_key = 'wpleads_landing_page_{$post->ID}'
				WHERE wposts.post_type = 'wp-lead' ";
			$result = mysql_query($query);
			if (!$result){ echo $query; echo mysql_error();}

			while($row = mysql_fetch_array($result))
			{
				$this_data = array();
				$wplead_data = get_post_custom($row['ID']);
				//print_r($wplead_data);

				$conversion_data = $wplead_data['wpleads_conversion_data'][0];
				$conversion_data = json_decode($conversion_data,true);
				//print_r($conversion_data);
				//echo "<br>";


				$date_raw = new DateTime($conversion_data[1]['datetime']);
				$datetime = $date_raw->format('F jS, Y \a\t g:ia');
				(isset($conversion_data[$post->ID]['first_time'])) ? $first_time = 1 : $first_time = 0;

				//echo $first_time;
				//echo "<br>";

				//echo $datetime;
				if (isset($wplead_data['wpleads_email_address'][0]) && !empty($wplead_data['wpleads_email_address'][0]))
				{
					$full_name = $wplead_data['wpleads_first_name'][0].' '.$wplead_data['wpleads_last_name'][0];
					$this_data['ID']  = $row['ID'];
					$this_data['date']  = $datetime;

					$this_data['name']  = $full_name;
					$this_data['email']  = $wplead_data['wpleads_email_address'][0];
					$this_data['first_time']  = $first_time;

					$this_data = apply_filters('lp_lead_table_data_construct',$this_data);

					$final_data[] = $this_data;
				}

			}
			//print_r($final_data);
			$this->table_data = $final_data;
			$this->singular = 'ID';
			$this->plural = 'ID';

			//print_r($args);exit;
			$args['plural'] = sanitize_key( '' );
			$args['singular'] = sanitize_key( '' );
			$this->screen = get_current_screen();
			$this->_args = $args;
		}

		function get_columns()
		{
			$columns = array(
			'date' => 'Date',
			'name' => 'Name',
			'email' => 'Email',
			'details' => 'Details',
			);
			$columns = apply_filters('lp_lead_table_data_columns',$columns);
			return $columns;
		}


		function get_sortable_columns()
		{
			$sortable_columns = array(
				//'template'  => array('template',false),
				//'category' => array('category',false),
				//'version'   => array('version',false)
			);

			$sortable_columns = apply_filters('lp_lead_table_data_sortable_columns',$sortable_columns);

			return $sortable_columns;
		}

		function usort_reorder( $a, $b )
		{
			// If no sort, default to title
			$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'date';
			// If no order, default to asc
			$order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'dsc';
			// Determine sort order
			$result = strcmp( $a[$orderby], $b[$orderby] );
			// Send final sort direction to usort

			return ( $order === 'asc' ) ? $result : -$result;
		}

		function prepare_items()
		{

			$columns  = $this->get_columns();


			$hidden = array('ID');
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );
			if ($this->table_data)
				usort( $this->table_data, array( &$this, 'usort_reorder' ) );

			$per_page = 25;
			$current_page = $this->get_pagenum();

			$total_items = count( $this->table_data );

			if ($this->table_data)
				$this->found_data = array_slice( $this->table_data,( ( $current_page-1 )* $per_page ), $per_page );

			else
			{
				$this->found_data = array();
			}

			$this->set_pagination_args( array(
				'total_items' => $total_items,                  //WE have to calculate the total number of items
				'per_page'    => $per_page                     //WE have to determine how many items to show on a page
			) );

			$this->items = $this->found_data;

		}

		function column_default( $item, $column_name )
		{
			global $post;
			//echo $item[ 'first_time' ];
			switch( $column_name )
			{
				case 'date':
					return $item[ $column_name ];
				case 'name':
					return $item[ $column_name ];
				case 'email':
					return "<a href='mailto:".$item[ $column_name ]."'>".$item[ $column_name ]."</a>";
				case 'details':
					echo '<a href="' . 	LANDINGPAGES_URLPATH.'modules/module.lead-splash.php?lead_id=' . $item[ 'ID' ] . '&post_id=' . $post->ID . '&height=400&width=600&TB_iframe=true" class="thickbox">' . __( 'View Lead' ,'landing-pages') .'</a>';
					echo '&nbsp;&nbsp;';
					//print_r($item);
					if ($item[ 'first_time' ]==1)
					{
						echo '<img src="'.LANDINGPAGES_URLPATH.'images/new-lead.png" title="' . __( 'First timer!' , 'landing-pages') .'" style="float:right;">';
					}
					do_action('lp_lead_table_data_is_details_column',$item);
					return;
			}

			do_action('lp_lead_table_data_add_column_listeners',$column_name);

		}

		function admin_header()
		{
			//$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;

			//if( 'lp_manage_templates' != $page )
			//return;
		}

		function no_items()
		{
			_e( 'No conversions recorded yet...' , 'landing-pages');
		}

		function get_bulk_actions()
		{
			$actions = array(

			//	'upgrade'    => 'Upgrade',
			//	'delete'    => 'Delete',
			//
			);

			return $actions;
		}

	}
	echo '<div id="leads-table-container">';
	echo '<h2 id="convert-header">'. __( 'Conversion logs:' , 'landing-pages') .'</h2>';
	echo '<div id="leads-table-container-inside">';
	$myListTable = new LP_LEAD_LOG();
	$myListTable->prepare_items();
	$myListTable->display();
	echo '</div>';
	echo '</div>';
}

/**
 * Generate Template & Extension Metaboxes
 */
// The Callback

add_action('add_meta_boxes', 'lp_generate_meta');
function lp_generate_meta()
{
	global $post;
	if ($post->post_type!='landing-page')
		return;

	$extension_data = lp_get_extension_data();

	$current_template = get_post_meta( $post->ID , 'lp-selected-template' , true);
	$current_template = apply_filters('lp_variation_selected_template',$current_template, $post);

	//echo $current_template; exit;
	foreach ($extension_data as $key=>$data)
	{
		//echo "$key : $current_template <br>";
		if ($key!='lp'&&substr($key,0,4)!='ext-' && $key==$current_template)
		{
			$template_name = ucwords(str_replace('-',' ',$key));
			$id = strtolower(str_replace(' ','-',$key));
			//echo $key."<br>";
			add_meta_box(
				"lp_{$id}_custom_meta_box", // $id
				__( "<small>$template_name Options:</small>", 'landing-pages'),
				'lp_show_metabox', // $callback
				'landing-page', // post-type
				'normal', // $context
				'default',// $priority
				array('key'=>$key)
				); //callback args
		}
	}

	foreach ($extension_data as $key=>$data)
	{
		if ( substr($key,0,4)=='ext-' || isset($data['info']['data_type']) && $data['info']['data_type'] =='metabox' )
		{
			//echo 1; exit;
			$id = "metabox-".$key;

			(isset($data['info']['label'])) ? $name = $data['info']['label'] : $name = ucwords(str_replace(array('-','ext '),' ',$key). " Extension Options");
			(isset($data['info']['position'])) ? $position = $data['info']['position'] : $position = "normal";
			(isset($data['info']['priority'])) ? $priority = $data['info']['priority'] : $priority = "default";


			//echo $key."<br>";
			add_meta_box(
				"lp_{$id}_custom_meta_box", // $id
				__( "$name", 'landing-pages'),
				'lp_show_metabox', // $callback
				'landing-page', // post-type
				$position , // $context
				$priority ,// $priority
				array('key'=>$key)
				); //callback args

		}
	}
}

function lp_show_metabox($post,$key)
{

	$extension_data = lp_get_extension_data();
	$key = $key['args']['key'];
	// Get current cat list for templates
	if (isset($_GET['lp-cats'])) {
	 get_all_template_categories($extension_data);
	}
	$lp_custom_fields = $extension_data[$key]['settings'];
	$lp_custom_fields = apply_filters('lp_show_metabox',$lp_custom_fields, $key);

	lp_render_metabox($key,$lp_custom_fields,$post);


}


function lp_render_metabox($key,$custom_fields,$post)
{
	// Use nonce for verification
	echo "<input type='hidden' name='lp_{$key}_custom_fields_nonce' value='".wp_create_nonce('lp-nonce')."' />";

	// Begin the field table and loop
	echo '<div class="form-table" id="inbound-meta">';

	foreach ($custom_fields as $field) {

		$field_id = $key . "-" .$field['id'];
		$field_name = $field['id'];
		$label_class = $field['id'] . "-label";
		$type_class = " inbound-" . $field['type'];
		$type_class_row = " inbound-" . $field['type'] . "-row";
		$type_class_option = " inbound-" . $field['type'] . "-option";
		$option_class = (isset($field['class'])) ? $field['class'] : '';
		//$status = (isset($field['status'])) ? $field['status'] : '';
		$ink = get_option('lp-license-keys-'. $key);
		$status = get_option('lp_license_status-'. $key);
		$status_test = (isset($status) && $status != "") ? $status : 'inactive';
		// get value of this field if it exists for this post
		$meta = get_post_meta($post->ID, $field_id, true);
		$global_meta = get_post_meta($post->ID, $field_name, true);
		if(empty($global_meta)) {
			$global_meta = $field['default'];
		}

		if (!metadata_exists('post',$post->ID,$field_id))
		{
			$meta = $field['default'];
		}

	    // Remove prefixes on global => true template options
	    if (isset($field['global']) && $field['global'] === true) {
			$field_id = $field_name;
			$meta = get_post_meta($post->ID, $field_name, true);
	    }

			// begin a table row with
			echo '<div class="'.$field['id'].$type_class_row.' div-'.$option_class.' wp-call-to-action-option-row inbound-meta-box-row">';

					if ($field['type'] != "description-block" && $field['type'] != "custom-css" ) {
						echo '<div id="inbound-'.$field_id.'" data-actual="'.$field_id.'" class="inbound-meta-box-label wp-call-to-action-table-header '.$label_class.$type_class.'"><label for="'.$field_id.'">'.$field['label'].'</label></div>';
					}

					echo '<div class="wp-call-to-action-option-td inbound-meta-box-option '.$type_class_option.'" data-field-type="'.$field['type'].'">';
					switch($field['type']) {
						// default content for the_content
						case 'default-content':
							echo '<span id="overwrite-content" class="button-secondary">Insert Default Content into main Content area</span><div style="display:none;"><textarea name="'.$field_id.'" id="'.$field_id.'" class="default-content" cols="106" rows="6" style="width: 75%; display:hidden;">'.$meta.'</textarea></div>';
							break;
						case 'description-block':
							echo '<div id="'.$field_id.'" class="description-block">' . $field['description'].'</div>';
							break;
						case 'custom-css':
							echo '<style type="text/css">'.$field['default'].'</style>';
							break;
						// text
						case 'colorpicker':
							if (!$meta)
							{
								$meta = $field['default'];
							}
							$var_id = (isset($_GET['new_meta_key'])) ? "-" . $_GET['new_meta_key'] : '';
							echo '<input type="text" class="jpicker" style="background-color:#'.$meta.'" name="'.$field_id.'" id="'.$field_id.'" value="'.$meta.'" size="5" /><span class="button-primary new-save-lp" data-field-type="text" id="'.$field_id.$var_id.'" style="margin-left:10px; display:none;">Update</span>
									<div class="lp_tooltip tool_color" title="'.$field['description'].'"></div>';
							break;
						case 'datepicker':
							echo '<div class="jquery-date-picker inbound-datepicker" id="date-picking" data-field-type="text">
							<span class="datepair" data-language="javascript">
										Date: <input type="text" id="date-picker-'.$key.'" class="date start" /></span>
										Time: <input id="time-picker-'.$key.'" type="text" class="time time-picker" />
										<input type="hidden" name="'.$field_id.'" id="'.$field_id.'" value="'.$meta.'" class="new-date" value="" >
										<p class="description">'.$field['description'].'</p>
								</div>';
							break;
						case 'text':
							echo '<input type="text" name="'.$field_id.'" id="'.$field_id.'" value="'.$meta.'" size="30" />
									<div class="lp_tooltip" title="'.$field['description'].'"></div>';
							break;
						case 'number':

							echo '<input type="number" class="'.$option_class.'" name="'.$field_id.'" id="'.$field_id.'" value="'.$meta.'" size="30" />
									<div class="lp_tooltip" title="'.$field['description'].'"></div>';

							break;
						// textarea
						case 'textarea':
							echo '<textarea name="'.$field_id.'" id="'.$field_id.'" cols="106" rows="6" style="width: 75%;">'.$meta.'</textarea>
									<div class="lp_tooltip tool_textarea" title="'.$field['description'].'"></div>';
							break;
						// wysiwyg
						case 'wysiwyg':
							echo "<div class='iframe-options iframe-options-".$field_id."' id='".$field['id']."'>";
							wp_editor( $meta, $field_id, $settings = array( 'editor_class' => $field_name ) );
							echo	'<p class="description">'.$field['description'].'</p></div>';
							break;
						// media
						case 'media':
							//echo 1; exit;
							echo '<label for="upload_image" data-field-type="text">';
							echo '<input name="'.$field_id.'"  id="'.$field_id.'" type="text" size="36" name="upload_image" value="'.$meta.'" />';
							echo '<input class="upload_image_button" id="uploader_'.$field_id.'" type="button" value="Upload Image" />';
							echo '<p class="description">'.$field['description'].'</p>';
							break;
						// checkbox
						case 'checkbox':
							$i = 1;
							echo "<table class='lp_check_box_table'>";
							if (!isset($meta)){$meta=array();}
							elseif (!is_array($meta)){
								$meta = array($meta);
							}
							foreach ($field['options'] as $value=>$label) {
								if ($i==5||$i==1)
								{
									echo "<tr>";
									$i=1;
								}
									echo '<td data-field-type="checkbox"><input type="checkbox" name="'.$field_id.'[]" id="'.$field_id.'" value="'.$value.'" ',in_array($value,$meta) ? ' checked="checked"' : '','/>';
									echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label></td>';
								if ($i==4)
								{
									echo "</tr>";
								}
								$i++;
							}
							echo "</table>";
							echo '<div class="lp_tooltip tool_checkbox" title="'.$field['description'].'"></div>';
						break;
						// radio
						case 'radio':
							foreach ($field['options'] as $value=>$label) {
								//echo $meta.":".$field_id;
								//echo "<br>";
								echo '<input type="radio" name="'.$field_id.'" id="'.$field_id.'" value="'.$value.'" ',$meta==$value ? ' checked="checked"' : '','/>';
								echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
							}
							echo '<div class="lp_tooltip" title="'.$field['description'].'"></div>';
						break;
						// select
						case 'dropdown':
							echo '<select name="'.$field_id.'" id="'.$field_id.'" class="'.$field['id'].'">';
							foreach ($field['options'] as $value=>$label) {
								echo '<option', $meta == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$label.'</option>';
							}
							echo '</select><div class="lp_tooltip" title="'.$field['description'].'"></div>';
						break;



					} //end switch
				echo '</div></div>';
			} // end foreach
			echo '</div>'; // end table
			//exit;
		}

add_action('save_post', 'lp_save_meta');
function lp_save_meta($post_id) {
	global $post;

	$extension_data = lp_get_extension_data();

	if (!isset($post)||isset($_POST['split_test']))
		return;

	if ($post->post_type=='revision')
	{
		return;
	}

	if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||(isset($_POST['post_type'])&&$_POST['post_type']=='revision'))
	{
		return;
	}

	if ($post->post_type=='landing-page')
	{
		//print_r($extension_data);exit;
		foreach ($extension_data as $key=>$data)
		{
			if ($key=='lp')
			{
				// verify nonce
				if (!wp_verify_nonce($_POST["lp_{$key}_custom_fields_nonce"], 'lp-nonce'))
				{
					return $post_id;
				}

				$lp_custom_fields = $extension_data[$key]['settings'];

				foreach ($lp_custom_fields as $field)
				{
					$id = $key."-".$field['id'];
					$old = get_post_meta($post_id, $id, true);
					(isset($_POST[$id]))? $new = $_POST[$id] : $new = null;

					if (isset($new) && $new != $old ) {
						update_post_meta($post_id, $id, $new);
					} elseif ('' == $new && $old) {
						delete_post_meta($post_id, $id, $old);
					}
				}
			}
			else if ( substr($key,0,4)=='ext-' || isset($data['info']['data_type']) && $data['info']['data_type']=='metabox')
			{

				$lp_custom_fields = $extension_data[$key]['settings'];

				// verify nonce
				if (!wp_verify_nonce($_POST["lp_{$key}_custom_fields_nonce"], 'lp-nonce'))
				{
					return $post_id;
				}

				// loop through fields and save the data
				foreach ($lp_custom_fields as $field) {
					$id = $key."-".$field['id'];

					if($field['type'] == 'tax_select') continue;
						$old = get_post_meta($post_id, $id, true);

						(isset($_POST[$id]))? $new = $_POST[$id] : $new = null;
						//echo "$old:".$new."<br>";

						if (isset($new) && $new != $old ) {
							update_post_meta($post_id, $id, $new);
						} elseif ('' == $new && $old) {
							delete_post_meta($post_id, $id, $old);
						}
				} // end foreach
			}
			else if ((isset($_POST['lp-selected-template'])&&$_POST['lp-selected-template']==$key))
			{
				$lp_custom_fields = $extension_data[$key]['settings'];

				// loop through fields and save the data
				foreach ($lp_custom_fields as $field) {
					$id = $key."-".$field['id'];

					if($field['type'] == 'tax_select' || !isset($_POST[$id]))
						continue;

					$old = get_post_meta($post_id, $id, true);

					(isset($_POST[$id]))? $new = $_POST[$id] : $new = null;
					//echo "$old:".$new."<br>";		exit;

					if (isset($new) && $new != $old ) {
						update_post_meta($post_id, $id, $new);
					} elseif ('' == $new && $old) {
						delete_post_meta($post_id, $id, $old);
					}
				}
			}
			else
			{
				//echo "key:$key<br>";
			}
		}

		// save taxonomies
		$post = get_post($post_id);
		//$category = $_POST['landing_page_category'];
		//wp_set_object_terms( $post_id, $category, 'landing_page_category' );
	}
}