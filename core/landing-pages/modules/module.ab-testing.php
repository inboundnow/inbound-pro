<?php

/* ADMIN ONLY AB TESTING FUNCTIONS */

if (is_admin()) {
	include_once(LANDINGPAGES_PATH.'modules/module.ab-testing.metaboxes.php');

	/**
	 * [lp_ab_unset_variation description]
	 * @param  [type] $variations [description]
	 * @param  [type] $vid        [description]
	 * @return [type]             [description]
	 */
	function lp_ab_unset_variation($variations,$vid){
			if(($key = array_search($vid, $variations)) !== false) {
					unset($variations[$key]);
			}

			return $variations;
	}

	/**
	 * [lp_ab_get_lp_active_status returns if landing page is in rotation or not]
	 * @param  [OBJ] $post [description]
	 * @param  [INT] $vid  [description]
	 * @return [INT]
	 */
	function lp_ab_get_lp_active_status($post,$vid=null) {
		if ($vid==0)
		{
				$variation_status = get_post_meta( $post->ID , 'lp_ab_variation_status' , true);
		}
		else
		{
				$variation_status = get_post_meta( $post->ID , 'lp_ab_variation_status-'.$vid , true);
		}

		if (!is_numeric($variation_status))
		{
				return 1;
		}
		else
		{
				return $variation_status;
		}
	}


	add_action('init','lp_ab_testing_admin_init');
	function lp_ab_testing_admin_init($hook)
	{
		if (!is_admin()||!isset($_GET['post'])||!is_numeric($_GET['post'])) {
			return;
        }

		$post = get_post($_GET['post']);

		if (isset($post)&&($post->post_type=='landing-page'&&(isset($_GET['action'])&&$_GET['action']=='edit')))
		{

			$current_variation_id = lp_ab_testing_get_current_variation_id();
			//echo $current_variation_id;
			$variations = get_post_meta($post->ID,'lp-ab-variations', true);

			//remove landing page's main save_post action
			if ($current_variation_id>0) {
				remove_action('save_post','lp_save_meta',10);
			}

			//check for delete command
			if (isset($_GET['ab-action'])&&$_GET['ab-action']=='delete-variation')
			{
				$array_variations = explode(',',$variations);
				$array_variations = lp_ab_unset_variation($array_variations,$_GET['lp-variation-id']);

				/* set next variation to be open */
				$current_variation_id = current($array_variations);
				$_SESSION['lp_ab_test_open_variation'] = $current_variation_id;

				$variations = implode(',' , $array_variations);
				update_post_meta($post->ID,'lp-ab-variations', $variations);


				if (isset($_GET['lp-variation-id']) && $_GET['lp-variation-id'] > 0 ) {
					$suffix = '-'.$_GET['lp-variation-id'];
					$len = strlen($suffix);
				} else {
					$suffix = '';
					$len = strlen($suffix);
				}

				//delete each meta value associated with variation
				global $wpdb;
				$data = array();
				$post__ID =  (is_numeric($_GET['post'])) ? $_GET['post'] : '0';

				$wpdb->query("
					SELECT `meta_key`, `meta_value`
					FROM $wpdb->postmeta
					WHERE `post_id` = ".$post__ID."
				");

                foreach($wpdb->last_result as $k => $v){
                    $data[$v->meta_key] =   $v->meta_value;
                };
                //echo $len;exit;
                foreach ($data as $key=>$value)
                {
                    if (substr($key,-$len)==$suffix)
                    {
                        delete_post_meta($post__ID, $key, $value);
                    }
                }

                $_GET['lp-variation-id'] = $current_variation_id;
            }

            //check for pause command
            if (isset($_GET['ab-action'])&&$_GET['ab-action']=='pause-variation')
            {
                if ($_GET['lp-variation-id']==0)
                {
                    update_post_meta( $post->ID , 'lp_ab_variation_status' , '0' );
                }
                else
                {
                    update_post_meta( $post->ID , 'lp_ab_variation_status-'.$_GET['lp-variation-id'] , '0');
                }
            }

            //check for pause command
            if (isset($_GET['ab-action'])&&$_GET['ab-action']=='play-variation')
            {
                if ($_GET['lp-variation-id']==0)
                {
                    update_post_meta( $post->ID , 'lp_ab_variation_status' , '1' );
                }
                else
                {
                    update_post_meta( $post->ID , 'lp_ab_variation_status-'.$_GET['lp-variation-id'] , '1');
                }
            }

            //return;

            (isset($_GET['new-variation'])&&$_GET['new-variation']==1) ? $new_variation = 1 : $new_variation = 0;

            $content_area = lp_content_area($post,null,true);

            //prepare for new variation creation - use A as default content if not being cloned
            if (($new_variation==1&&!isset($_GET['clone']))||isset($_GET['clone'])&&$_GET['clone']==0)
            {
                $content_area = get_post_field('post_content', $_GET['post']);
                $content_area = wpautop($content_area);
            }
            else if ($new_variation==1&&isset($_GET['clone']))
            {
                $content_area = get_post_field('content-'.$_GET['clone'], $_GET['post']);
                $content_area = wpautop($content_area);
            }

            //if new variation and cloning then programatically prepare the next variation id
            if($new_variation==1&&isset($_GET['clone']))
            {
                $array_variations = explode(',',$variations);
                sort($array_variations,SORT_NUMERIC);

                $lid = end($array_variations);
                $current_variation_id = $lid+1;

                $_SESSION['lp_ab_test_open_variation'] = $current_variation_id;
            }
            //echo $current_variation_id;exit;
            //enqueue and localize scripts
            wp_enqueue_style('lp-ab-testing-admin-css', LANDINGPAGES_URLPATH . 'css/admin-ab-testing.css');
            wp_enqueue_script('lp-ab-testing-admin-js', LANDINGPAGES_URLPATH . 'js/admin/admin.post-edit-ab-testing.js', array( 'jquery' ));
            wp_localize_script( 'lp-ab-testing-admin-js', 'variation', array( 'pid' => $_GET['post'], 'vid' => $current_variation_id  , 'new_variation' => $new_variation  , 'variations'=> $variations  , 'content_area' => $content_area  ));

        }

    }

    /* force visual editor to open in text mode */

    function lp_ab_testing_force_default_editor() {
        //allowed: tinymce, html, test
        return 'html';
    }

    add_filter('lp_edit_main_headline','lp_ab_testing_admin_prepare_headline');
    function lp_ab_testing_admin_prepare_headline($main_headline)
    {

        $current_variation_id = lp_ab_testing_get_current_variation_id();

        if (isset($_REQUEST['post']))
        {
            $post_id = $_REQUEST['post'];
        }
        else if (isset($_REQUEST['lp_id']))
        {
            $post_id = $_REQUEST['lp_id'];
        }

        //return "hello";

        if ($current_variation_id>0&&!isset($_REQUEST['new-variation'])&&!isset($_REQUEST['clone']))
        {
            $main_headline = get_post_meta($post_id,'lp-main-headline-'.$current_variation_id, true);
        }
        else if (isset($_GET['clone'])&&$_GET['clone']>0)
        {
            $main_headline = get_post_meta($post_id,'lp-main-headline-'.$_GET['clone'], true);
        }

        if (!$main_headline&&isset($_REQUEST['post']))
        {
            get_post_meta($_REQUEST['post'],'lp-main-headline', true);
        }

        return $main_headline;
    }

    //disable this because it will populate all wp_editor isntances rather than targeted instances
    //add_filter('the_editor_content', 'lp_ab_testing_the_editor_content');
    function lp_ab_testing_the_editor_content($content) {
        $current_variation_id = lp_ab_testing_get_current_variation_id();

        if (isset($_REQUEST['post']))
        {
            $post_id = $_REQUEST['post'];
        }
        else if (isset($_REQUEST['lp_id']))
        {
            $post_id = $_REQUEST['lp_id'];
        }

        if ($current_variation_id>0&&!isset($_REQUEST['new-variation'])&&!isset($_REQUEST['clone']))
        {
            $content = get_post_field('content-'.$current_variation_id, $post_id);
        }
        else if (isset($_GET['clone']))
        {
            $content = get_post_meta($post_id,'lp-main-headline-'.$_GET['clone'], true);
        }

        return $content;
    }


    add_filter('lp_edit_variation_notes','lp_ab_testing_admin_prepare_notes');
    function lp_ab_testing_admin_prepare_notes($variation_notes)
    {
        $current_variation_id = lp_ab_testing_get_current_variation_id();

        if (isset($_REQUEST['post']))
        {
            $post_id = $_REQUEST['post'];
        }
        else if (isset($_REQUEST['lp_id']))
        {
            $post_id = $_REQUEST['lp_id'];
        }

        //return "hello";

        if ($current_variation_id>0&&!isset($_REQUEST['new-variation'])&&!isset($_REQUEST['clone']))
        {
            $variation_notes = get_post_meta($post_id,'lp-variation-notes-'.$current_variation_id, true);
        }
        else if (isset($_GET['clone'])&&$_GET['clone']>0)
        {
            $variation_notes = get_post_meta($post_id,'lp-variation-notes-'.$_GET['clone'], true);
        }

        if (!$variation_notes&&isset($_REQUEST['post']))
        {
            get_post_meta($_REQUEST['post'],'lp-variation-notes', true);
        }

        return $variation_notes;
    }

    add_filter('lp_selected_template_id','lp_ab_testing_prepare_id');//prepare name id for hidden selected template input
    add_filter('lp_display_headline_input_id','lp_ab_testing_prepare_id');//prepare id for main headline in template customizer mode
    add_filter('lp_display_notes_input_id','lp_ab_testing_prepare_id');//prepare id for main headline in template customizer mode
    function lp_ab_testing_prepare_id($id)
    {
        $current_variation_id = lp_ab_testing_get_current_variation_id();

        //check if variation clone is initiated
        if (isset($_GET['new_meta_key']))
            $current_variation_id = $_GET['new_meta_key'];

        if ($current_variation_id>0)
        {
            $id = $id.'-'.$current_variation_id;
        }

        return $id;
    }

    //prepare id for wp_editor in template customizer
    add_filter('lp_wp_editor_id','lp_ab_testing_prepare_wysiwyg_editor_id');
    function lp_ab_testing_prepare_wysiwyg_editor_id($id)
    {
        $current_variation_id = lp_ab_testing_get_current_variation_id();
        //echo $current_variation_id;exit;
        if ($current_variation_id>0)
        {
            switch ($id) {
                case "wp_content":
                    $id = 'content-'.$current_variation_id;
                    break;
                case "lp-conversion-area":
                    $id = 'landing-page-myeditor-'.$current_variation_id;
                    break;
                default:
                    $id = $id.'-'.$current_variation_id;
            }

        }

        return $id;
    }


    add_filter('lp_show_metabox','lp_ab_testing_admin_prepare_meta_ids', 5, 2);
    function lp_ab_testing_admin_prepare_meta_ids($lp_custom_fields, $main_key)
    {
        if (isset($_REQUEST['new-variation'])&&!isset($_REQUEST['clone']))
        {
            return $lp_custom_fields;
        }

        $current_variation_id = lp_ab_testing_get_current_variation_id();

        if (isset($_GET['clone'])) {
            $current_variation_id = $_GET['clone'];
        }

        if ($current_variation_id>0)
        {
            $post_id = $_GET['post'];
            foreach ($lp_custom_fields as $key=>$field)
            {
                $default = get_post_meta($post_id, $field['id'], true);

                $id = $field['id'];
                $field['id'] = $id.'-'.$current_variation_id ;

                if ($default) {
                    $field['default'] = $default;
                }

                $lp_custom_fields[$key] = $field;
            }
            return $lp_custom_fields;
        }

        //print_r($lp_custom_fields);exit;
        return $lp_custom_fields;
    }

    add_filter('lp_variation_selected_template','lp_ab_testing_lp_variation_selected_template', 10, 2);
    function lp_ab_testing_lp_variation_selected_template($selected_template, $post) {
        if (isset($_GET['new-variation']))
            return $selected_template;

        $current_variation_id = lp_ab_testing_get_current_variation_id();

        if ($current_variation_id>0) {
            $selected_template = get_post_meta( $post->ID , 'lp-selected-template-'.$current_variation_id , true);
        }

        //print_r($lp_custom_fields);exit;
        return $selected_template;
    }

    //add filter to modify thumbnail preview
    add_filter('lp_live_screenshot_url', 'lp_ab_testing_prepare_screenshot');
    function lp_ab_testing_prepare_screenshot($link) {
        $variation_id = lp_ab_testing_get_current_variation_id();
        $link = $link."?lp-variation-id=".$variation_id;
        return $link;
    }



    add_filter("post_type_link", "lp_ab_append_variation_id_to_adminbar_link", 10,2);
    function lp_ab_append_variation_id_to_adminbar_link($link, $post) {
        if( $post->post_type == 'landing-page' ) {
            $current_variation_id = lp_ab_testing_get_current_variation_id();

            if ($current_variation_id>0)
                $link = $link."?lp-variation-id=".$current_variation_id;
        }

        return $link;
    }

    if(!defined('AUTOSAVE_INTERVAL')) {
        define('AUTOSAVE_INTERVAL', 86400);
    }

    add_filter('wp_insert_post_data','lp_ab_testing_wp_insert_post_data',10,2);
    function lp_ab_testing_wp_insert_post_data($data,$postarr) {

        //exit;
        //$variation_id = lp_ab_testing_get_current_variation_id();
        //echo $variation_id;exit;
        if (isset($postarr['lp-variation-id'])&&$postarr['lp-variation-id']>0) {
            $postarr = array();
            $data = array();

            remove_action('save_post','lp_save_meta',10);
            remove_action('save_post','lp_ab_testing_save_post',10);

            $postID = $_POST['post_ID'];
            if($parent_id = wp_is_post_revision($_POST['post_ID'])) {
                $postID = $parent_id;
            }

            lp_ab_testing_save_post($postID);

        } else {
            //echo "here";exit;
            //$this_data = json_encode($data);
            //mail('hudson.atwell@gmail.com','test2',$this_data);
        }

        if (count($data)>1)
            return $data;
    }

    add_action('save_post','lp_ab_testing_save_post');
    function lp_ab_testing_save_post($postID) {
        global $post;

        $var_final = (isset($_POST['lp-variation-id'])) ? $_POST['lp-variation-id'] : '0';
        if ( isset($_POST['post_type']) && $_POST['post_type']=='landing-page') {

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ||$_POST['post_type']=='revision') {
                return;
            }

            if($parent_id = wp_is_post_revision($postID)) {
                $postID = $parent_id;
            }


            $this_variation = $var_final;
            //echo $this_variation;
            //print_r($_POST);exit;

            //first add to varation list if not present.
            $variations = get_post_meta($postID,'lp-ab-variations', true);
            if ($variations) {
                $array_variations = explode(',',$variations);
                if (!in_array($this_variation,$array_variations)) {
                    $array_variations[] = $this_variation;
                }
            } else {
                if  ($this_variation>0) {
                    $array_variations[] = 0;
                    $array_variations[] = $this_variation;
                } else {
                    $array_variations[] = $this_variation;
                }
            }

            //print_r($array_variations);exit;
            //update_post_meta($postID,'lp-ab-variations', "");
            update_post_meta($postID,'lp-ab-variations', implode(',',$array_variations));
            //add_post_meta($postID, 'lp_ab_variation_status-'.$this_variation , 1);

            //echo $this_variation;exit;
            if ($this_variation==0) {
                return;
            }
            //echo $this_variation;exit;
            //print_r($_POST);

            //next alter all custom fields to store correct varation and create custom fields for special inputs
            $ignore_list = array('post_status','post_type','tax_input','post_author','user_ID','post_ID','catslist','post_title','samplepermalinknonce',
                'autosavenonce','action','autosave','mm','jj','aa','hh','mn','ss','_wp_http_referer','lp-variation-id','_wpnonce','originalaction','original_post_status',
                'referredby','_wp_original_http_referer','meta-box-order-nonce','closedpostboxesnonce','hidden_post_status','hidden_post_password','hidden_post_visibility','visibility',
                'post_password','hidden_mm','cur_mm','hidden_jj','cur_jj','hidden_aa','cur_aa','hidden_hh','cur_hh','hidden_mn','cur_mn','original_publish','save','newlanding_page_category','newlanding_page_category_parent',
                '_ajax_nonce-add-landing_page_category','lp_lp_custom_fields_nonce','lp-selected-template','post_mime_type','ID','comment_status','ping_status');

            //$special_list = array('content','post-content');
            //print_r($_POST);exit;
            //echo $this_variation;exit;
            foreach ($_POST as $key=>$value) {
                //echo $key." : -{$this_variation} : $value<br>";
                if (!in_array($key,$ignore_list)&&!strstr($key,'nonce')) {
                    if ($key=='post_content') {
                        $key = 'content';
                    }

                    if (!strstr($key,"-{$this_variation}")) {
                        $new_array[$key.'-'.$this_variation] = $value;
                    } else {
                        //echo $key." : -{$this_variation}<br>";
                        $new_array[$key] = $value;
                    }
                }
                //echo $key." : -{$this_variation} : $value<br>";
            }

            //print_r($new_array);exit;

            foreach($new_array as $key => $val) {
                $old = get_post_meta($postID, $key, true);
                $new = $val;
                //echo "$key  : $old v. $new <br>";
                //if (isset($new) && $new != $old ) {
                update_post_meta($postID, $key, $new);
                //} elseif ('' == $new && $old) {
                //delete_post_meta($postID, $key, $old);
                //}
            }

        }
    }

}

/* PERFORM FRONT-END ONLY ACTIONS */
else
{

    //prepare customizer meta data for ab varations
    add_filter('lp_get_value','lp_ab_testing_prepare_variation_meta', 1 , 4);
    function lp_ab_testing_prepare_variation_meta($return, $post, $key, $id)
    {
        if (isset($_REQUEST['lp-variation-id'])||isset($_COOKIE['lp-variation-id']))
        {
            (isset($_REQUEST['lp-variation-id'])) ? $variation_id = $_REQUEST['lp-variation-id'] : $variation_id = $_COOKIE['lp-variation-id'];
            if ($variation_id>0)
                return do_shortcode(get_post_meta($post->ID, $key.'-'.$id. '-' .$variation_id , true));
            else
                return $return;
        }
        else
        {
            return $return;
        }
    }

    //prepare customizer, admin, and preview links for variations
    add_filter('lp_customizer_customizer_link', 'lp_ab_append_variation_id_to_link');
    add_filter('lp_customizer_admin_bar_link', 'lp_ab_append_variation_id_to_link');
    add_filter('lp_customizer_preview_link','lp_ab_append_variation_id_to_link');

    function lp_ab_append_variation_id_to_link($link)
    {

        $current_variation_id = lp_ab_testing_get_current_variation_id();

        if ($current_variation_id>0)
            $link = $link."&lp-variation-id=".$current_variation_id;

        return $link;
    }

}

/*PERFORM ACTIONS REQUIRED ON BOTH FRONT AND BACKEND */

add_filter('lp_content_area','lp_ab_testing_alter_content_area_admin', 10, 2);
function lp_ab_testing_alter_content_area_admin($content) {
    global $post;

    $variation_id = lp_ab_testing_get_current_variation_id();

    if ($variation_id>0) {
        $content = get_post_meta($post->ID,'content-'.$variation_id, true);
        if ( !is_admin() ) {
            $content = wpautop($content);
            $content = do_shortcode($content);
        }
    }

    return $content;
}

/* RETURN LETTER FROM ARRAY KEY */
function lp_ab_key_to_letter($key) {
    $alphabet = array( 'A', 'B', 'C', 'D', 'E',
        'F', 'G', 'H', 'I', 'J',
        'K', 'L', 'M', 'N', 'O',
        'P', 'Q', 'R', 'S', 'T',
        'U', 'V', 'W', 'X', 'Y',
        'Z'
    );

    if (isset($alphabet[$key])) {
        return $alphabet[$key];
    }
}

/* GET CURRENT VARIATION ID */
function lp_ab_testing_get_current_variation_id() {
    if ( isset($_GET['ab-action']) && is_admin()) {
        return $_SESSION['lp_ab_test_open_variation'];
    }

    if (!isset($_SESSION['lp_ab_test_open_variation'])&&!isset($_REQUEST['lp-variation-id'])) {
        $current_variation_id = 0;
    }
    //echo $_REQUEST['lp-variation-id'];
    if (isset($_REQUEST['lp-variation-id'])) {
        $_SESSION['lp_ab_test_open_variation'] = $_REQUEST['lp-variation-id'];
        $current_variation_id = $_REQUEST['lp-variation-id'];
        //echo "setting session $current_variation_id";
    }

    if (isset($_GET['message'])&&$_GET['message']==1&&isset( $_SESSION['lp_ab_test_open_variation'] )) {
        $current_variation_id = $_SESSION['lp_ab_test_open_variation'];

        //echo "here:".$_SESSION['lp_ab_test_open_variation'];
    }

    if (isset($_GET['ab-action'])&&$_GET['ab-action']=='delete-variation') {
        $current_variation_id = 0;
        $_SESSION['lp_ab_test_open_variation'] = 0;
    }

    if (!isset($current_variation_id))
        $current_variation_id = 0 ;

    return $current_variation_id;
}

//ready conversion area for displaying ab variations
add_filter('lp_conversion_area_pre_standardize','lp_ab_testing_prepare_conversion_area' , 10 , 2 );
function lp_ab_testing_prepare_conversion_area($content,$post=null) {
    $current_variation_id = lp_ab_testing_get_current_variation_id();

    if (isset($post)) {
        $post_id = $post->ID;
    } else if (isset($_REQUEST['post'])) {
        $post_id = $_REQUEST['post'];
    } else if (isset($_REQUEST['lp_id'])) {
        $post_id = $_REQUEST['lp_id'];
    }

    if ($current_variation_id>0)
        $content = get_post_meta($post_id,'landing-page-myeditor-'.$current_variation_id, true);

    return $content;
}

//ready conversion area for displaying ab variations
add_filter('lp_conversion_area_position','lp_ab_testing_lp_conversion_area_position' , 10 , 2 );
function lp_ab_testing_lp_conversion_area_position($position, $post = null, $key = 'default') {

    $current_variation_id = lp_ab_testing_get_current_variation_id();

    if (isset($post)) {
        $post_id = $post->ID;
    }
    else if (isset($_REQUEST['post'])) {
        $post_id = $_REQUEST['post'];
    }
    else if (isset($_REQUEST['lp_id'])) {
        $post_id = $_REQUEST['lp_id'];
    }

    if ($current_variation_id>0)
        $position = get_post_meta($post->ID, "{$key}-conversion-area-placement-".$current_variation_id, true);

    return $position;
}


add_filter('lp_main_headline','lp_ab_testing_prepare_headline', 10, 2);
function lp_ab_testing_prepare_headline($main_headline, $post = null) {

    $current_variation_id = lp_ab_testing_get_current_variation_id();

    if (isset($post)) {
        $post_id = $post->ID;
    } else if (isset($_REQUEST['post'])) {
        $post_id = $_REQUEST['post'];
    } else if (isset($_REQUEST['lp_id'])) {
        $post_id = $_REQUEST['lp_id'];
    } else if (isset($_REQUEST['post_id'])) {
        $post_id = $_REQUEST['post_id'];
    }

    if ($current_variation_id>0)
        $main_headline = get_post_meta($post_id,'lp-main-headline-'.$current_variation_id, true);

    if (!$main_headline) {
        get_post_meta($post_id,'lp-main-headline', true);
    }

    return $main_headline;
}

add_action('init','lp_ab_testing_add_rewrite_rules');
function lp_ab_testing_add_rewrite_rules() {
    $this_path = LANDINGPAGES_PATH;
    $this_path = explode('wp-content',$this_path);
    $this_path = "wp-content".$this_path[1];

    $slug = get_option( 'lp-main-landing-page-permalink-prefix', 'go' );
    //echo $slug;exit;
    $ab_testing = get_option( 'lp-main-landing-page-disable-turn-off-ab', "0");
    if($ab_testing === "0") {
        add_rewrite_rule("$slug/([^/]*)/([0-9]+)/", "$slug/$1?lp-variation-id=$2",'top');
        add_rewrite_rule("$slug/([^/]*)?", $this_path."modules/module.redirect-ab-testing.php?permalink_name=$1 ",'top');
        add_rewrite_rule("landing-page=([^/]*)?", $this_path.'modules/module.redirect-ab-testing.php?permalink_name=$1','top');
    }
    add_filter('mod_rewrite_rules', 'lp_ab_testing_modify_rules', 1);
    function lp_ab_testing_modify_rules($rules) {
        if (!stristr($rules,'RewriteCond %{QUERY_STRING} !lp-variation-id')) {
            $rules_array = preg_split ('/$\R?^/m', $rules);
            if (count($rules_array)<3) {
                $rules_array = explode("\n", $rules);
                $rules_array = array_filter($rules_array);
            }

            //print_r($rules_array);exit;

            $this_path = LANDINGPAGES_PATH;
            $this_path = explode('wp-content',$this_path);
            $this_path = "wp-content".$this_path[1];
            $slug = get_option( 'lp-main-landing-page-permalink-prefix', 'go' );

            $i = 0;
            foreach ($rules_array as $key=>$val) {

                if ( stristr($val,"RewriteRule ^{$slug}/([^/]*)? ") ||  stristr($val,"RewriteRule ^{$slug}/([^/]*)/([0-9]+)/ ") ) {
                    $new_val = "RewriteCond %{QUERY_STRING} !lp-variation-id";
                    $rules_array[$i] = $new_val;
                    $i++;
                    $rules_array[$i] = $val;
                    $i++;
                } else {
                    $rules_array[$i] = $val;
                    $i++;
                }
            }

            $rules = implode("\r\n", $rules_array);
        }

        return $rules;
    }

}


add_filter('lp_selected_template','lp_ab_testing_get_selected_template');//get correct selected template for each variation
function lp_ab_testing_get_selected_template($template) {
    global $post;

    $current_variation_id = lp_ab_testing_get_current_variation_id();

    if ($current_variation_id>0) {
        $new_template = get_post_meta($post->ID, 'lp-selected-template-'.$current_variation_id, true);
        if ($new_template) {
            $template = $new_template;
        }
    }

    return $template;
}

//prepare custom js and css for
add_filter('lp_custom_js_name','lp_ab_testing_prepare_name');
add_filter('lp_custom_css_name','lp_ab_testing_prepare_name');
function lp_ab_testing_prepare_name($id) {
    $current_variation_id = lp_ab_testing_get_current_variation_id();
    //echo $current_variation_id;exit;
    if ($current_variation_id>0) {
        $id = $id.'-'.$current_variation_id;
    }

    return $id;
}

add_action('wp_ajax_lp_ab_testing_prepare_variation', 'lp_ab_testing_prepare_variation_callback');
add_action('wp_ajax_nopriv_lp_ab_testing_prepare_variation', 'lp_ab_testing_prepare_variation_callback');

function lp_ab_testing_prepare_variation_callback() {

    $page_id = lp_url_to_postid( trim($_POST['current_url']) );

    $variations = get_post_meta($page_id,'lp-ab-variations', true);
    $marker = get_post_meta($page_id,'lp-ab-variations-marker', true);
    if (!is_numeric($marker)) {
        $marker = 0;
    }

    if ($variations) {
        //echo $variations;
        $variations = explode(',',$variations);
        //print_r($variations);

        $variation_id = $variations[$marker];

        $marker++;

        if ($marker>=count($variations)) {
            //echo "here";
            $marker = 0;
        }

        update_post_meta($page_id, 'lp-ab-variations-marker', $marker);

        echo $variation_id;
        die();
    }


}


add_filter('the_content','lp_ab_testing_alter_content_area', 10, 2);
add_filter('get_the_content','lp_ab_testing_alter_content_area', 10, 2);
function lp_ab_testing_alter_content_area($content) {
    global $post;

    if ( !isset($post) || $post->post_type != 'landing-page' ) {
        return $content;
    }

    $variation_id = lp_ab_testing_get_current_variation_id();

    if ($variation_id>0) {
        $content = do_shortcode(get_post_meta($post->ID,'content-'.$variation_id, true));
    }

    return $content;
}

add_filter('wp_title','lp_ab_testing_alter_title_area', 9, 2);
add_filter('the_title','lp_ab_testing_alter_title_area', 10, 2);
add_filter('get_the_title','lp_ab_testing_alter_title_area', 10, 2);
function lp_ab_testing_alter_title_area( $content , $id = null)
{
    global $post;

    if (!isset($post))
        return $content;

    if ( ( $post->post_type!='landing-page'||is_admin()) || $id != $post->ID)
        return $content;

    return lp_main_headline($post, null, true);
}

add_action('lp_record_impression','lp_ab_testing_record_impression', 10, 3 );
function lp_ab_testing_record_impression($post_id, $post_type = 'landing-page' , $variation_id = 0 ) {

    if ( $post_type == 'landing-page' ) {
        /* If Landing Page Post Type */
        $meta_key = 'lp-ab-variation-impressions-'.$variation_id;
    } else  {
        /* If Non Landing Page Post Type */
        $meta_key = '_inbound_impressions_count';
    }

    $impressions = get_post_meta($post_id, $meta_key , true);

    if (!is_numeric($impressions)) {
        $impressions = 1;
    } else {
        $impressions++;
    }

    update_post_meta($post_id, $meta_key , $impressions);
}


add_action('lp_launch_customizer_pre','lp_ab_testing_customizer_enqueue');
function lp_ab_testing_customizer_enqueue($post) {

    $permalink = get_permalink( $post->ID );
    $randomstring = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);

    wp_enqueue_script( 'lp_ab_testing_customizer_js', LANDINGPAGES_URLPATH . 'js/customizer.ab-testing.js', array( 'jquery' ) );
    wp_localize_script( 'lp_ab_testing_customizer_js', 'ab_customizer', array( 'lp_id' => $post->ID ,'permalink' => $permalink , 'randomstring' => $randomstring));
    wp_enqueue_style('lp_ab_testing_customizer_css', LANDINGPAGES_URLPATH . 'css/customizer-ab-testing.css');
}