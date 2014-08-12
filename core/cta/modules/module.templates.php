<?php

if (isset($_GET['page']) && $_GET['page']=='wp_cta_templates_upload' || isset($_GET['page']) && $_GET['page']=='wp_cta_templates_update'|| isset($_GET['page']) && $_GET['page']=='wp_cta_templates_search') 
{
	add_action('admin_enqueue_scripts','wp_cta_templates_admin_enqueue');
	function wp_cta_templates_admin_enqueue() {
		wp_enqueue_script('wp-cta-js-templates-upload', WP_CTA_URLPATH . 'js/admin/admin.templates-upload.js');
	}

	include_once(WP_CTA_PATH.'modules/module.templates-upload.php');

} else if (isset($_GET['page'])&&$_GET['page']=='wp_cta_manage_templates') {

	add_action('admin_enqueue_scripts','wp_cta_templates_admin_enqueue');
	function wp_cta_templates_admin_enqueue() {
		wp_enqueue_style('wp-cta-css-templates', WP_CTA_URLPATH . 'css/admin-templates.css');
		wp_enqueue_script('wp-cta-js-templates', WP_CTA_URLPATH . 'js/admin/admin.templates.js');
 	}


	if( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

	class CTA_Manage_Uploaded_Templates extends WP_List_Table {
		private $template_data;
		private $singular;
		private $plural;

		function __construct() {
			$CTA_Load_Extensions = CTA_Load_Extensions();
			$wp_cta_data = $CTA_Load_Extensions->template_definitions;
			$final_data = array();
			
			foreach ($wp_cta_data as $key=>$data)
			{
				$array_core_templates = array('auto-focus' , 'breathing' , 'clean-cta' , 'blank-template','call-out-box','cta-one','demo', 'flat-cta', 'peek-a-boo', 'popup-ebook', 'facebook-like-button', 'facebook-like-to-download', 'feedburner-subscribe-to-download', 'linkedin-share-to-download', 'tweet-to-download', 'follow-to-download', 'ebook-call-out');

				if ($key == 'wp-cta' || substr($key,0,4) == 'ext-' )
					continue;

				if (isset($data['info']['data_type']) && $data['info']['data_type']=='metabox')
					continue;

				if (in_array($key,$array_core_templates))
					continue;

				//if (stristr($data['category'],'Theme Integrated'))
					//continue;

				//echo "<br>";
				if (isset($_POST['s'])&&!empty($_POST['s'])) {
					if (!stristr($data['info']['label'],$_POST['s'])) {
						continue;
					}
				}

				if (isset($data['thumbnail'])) {
					$thumbnail = $data['thumbnail'];
				} else if ($key=='default') {
					$thumbnail =  get_bloginfo('template_directory')."/screenshot.png";
				} else {
					$thumbnail = WP_CTA_UPLOADS_URLPATH.$key."/thumbnail.png";
				}
				
				//echo $thumbnail;

				$this_data['ID']  = $key;
				$this_data['template']  = $key;
				( array_key_exists('info',$data) ) ? $this_data['name'] = $data['info']['label'] :  $this_data['name'] = $data['label'];
				( array_key_exists('info',$data) ) ? $this_data['category'] = $data['info']['category'] :  $this_data['category'] = $data['category'];
				( array_key_exists('info',$data) ) ? $this_data['description'] = $data['info']['description'] :  $this_data['description'] = $data['description'];

				$this_data['thumbnail']  = $thumbnail;

				if (isset($data['version'])&&!empty($data['info']['version']))
				{
					$this_data['version']  = $data['info']['version'];
				}
				else
				{
					$this_data['version'] = "1.0.1";
				}

				$final_data[] = $this_data;
			}

			//print_r($this_data);exit;
			$this->template_data = $final_data;
			//$this->_args = array();

			$this->singular = 'ID';
			$this->plural = 'ID';

			$args = $this->_args;
			//print_r($args);exit;
			$args['plural'] = sanitize_key( '' );
			$args['singular'] = sanitize_key( '' );

			$this->_args = $args;
		}

		function get_columns()
		{
			$columns = array(
			'cb'        => '<input type="checkbox" />',
			'template' => 'Template',
			'description' => 'Description',
			'category' => 'Category',
			'version' => 'Current Version'

			);
			return $columns;
		}

		function column_cb($item)
		{
			return sprintf(
				'<input type="checkbox" name="template[]" value="%s" />', $item['ID']
			);
		}

		function get_sortable_columns()
		{
			$sortable_columns = array(
				'template'  => array('template',false),
				'category' => array('category',false),
				'version'   => array('version',false)
			);

			return $sortable_columns;
		}

		function usort_reorder( $a, $b )
		{
			// If no sort, default to title
			$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'template';
			// If no order, default to asc
			$order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
			// Determine sort order
			$result = strcmp( $a[$orderby], $b[$orderby] );
			// Send final sort direction to usort
			//print_r($b);exit;
			//echo $order;exit;
			return ( $order === 'asc' ) ? $result : -$result;
		}

		function prepare_items() {
			$columns  = $this->get_columns();

			$hidden = array('ID');
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );
			if(is_array($this->template_data))
			{
				usort( $this->template_data, array( &$this, 'usort_reorder' ) );
			}

			$per_page = 25;
			$current_page = $this->get_pagenum();

			$total_items = count( $this->template_data );

			if (is_array($this->template_data))
			{
				$this->found_data = array_slice( $this->template_data,( ( $current_page-1 )* $per_page ), $per_page );
			}

			$this->set_pagination_args( array(
				'total_items' => $total_items,                  //WE have to calculate the total number of items
				'per_page'    => $per_page                     //WE have to determine how many items to show on a page
			) );


			$this->items = $this->found_data;
		}

		function column_default( $item, $column_name ) {
			//echo $item;exit;
			switch( $column_name )
			{
				case 'template':
					return '<div class="capty-wrapper" style="overflow: hidden; position: relative; "><div class="capty-image"><img src="'.$item[ 'thumbnail' ].'" class="template-thumbnail" alt="'.$item['name'].'" id="id_'.$item['ID'].'" title="'.$item['name'].'">
					</div><div class="capty-caption" style="text-align:center;width:158px;margin-left:-6px;height: 20px; opacity: 0.7; top:-82px;position: relative;">'.$item['name'].'</div></div>';
				case 'category':
					return '<span class="post-state">
							<span class="pending states">'.$item[ $column_name ].'</span>
							</span>';
				case 'description':
					return $item[ $column_name ];
				case 'version':
					echo wp_cta_templates_check_for_update($item);
					return;
				case 'actions':
					echo wp_cta_templates_print_delete_button($item);

					return;
				default:
					return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
			}
		}

		function admin_header() {
			$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;

			if( 'wp_cta_manage_templates' != $page )
			return;
		}

		function no_items() {
			_e( 'No premium templates installed. Templates included in the Call to Action core plugin will not be listed here.' );
		}

		function get_bulk_actions() {
			$actions = array(

				'upgrade'    => 'Upgrade',
				'delete'    => 'Delete',

			);

			return $actions;
		}

	}



	function wp_cta_manage_templates() {
		wp_cta_manage_templates_actions_check();
		$title = __('Manage Templates');
		echo '<div class="wrap">';
		screen_icon();
		?>

		<h2><?php echo esc_html( $title );	?>
		 <a href="edit.php?post_type=wp-call-to-action&page=wp_cta_templates_upload" class="add-new-h2"><?php echo esc_html_x('Add New Template', 'template'); ?></a>
		</h2>
		<?php

		$myListTable = new CTA_Manage_Uploaded_Templates();
		$myListTable->prepare_items();
		?>
		<form method="post" >
		  <input type="hidden" name="page" value="my_list_test" />
		  <?php $myListTable->search_box('search', 'search_id'); ?>
		</form>
		<form method="post" id='bulk_actions'>

		<?php
		$myListTable->display();

		echo '</div></form>';

	}



	function wp_cta_manage_templates_actions_check()
	{
		if (isset($_REQUEST['action']))
		{
			switch ($_REQUEST['action']):
				case 'upgrade':
					if (count($_REQUEST['template'])>0)
					{
						foreach ($_REQUEST['template'] as $key=>$slug)
						{
							wp_cta_templates_upgrade_template($slug);
						}
					}
					break;
				case 'delete':
					if (count($_REQUEST['template'])>0)
					{
						foreach ($_REQUEST['template'] as $key=>$slug)
						{
							wp_cta_templates_delete_dir(WP_CTA_PATH.'templates/'.$slug, $slug);
						}
					}
					break;
			endswitch;


			echo('<meta http-equiv="refresh" content="0;url=edit.php?post_type=wp-call-to-action&page=wp_cta_manage_templates">');
			exit;
		}
	}



	function wp_cta_templates_upgrade_template($slug) {
		global $wp_cta_data;
		$data = $wp_cta_data[$slug];

		$item['ID']  = $slug;
		$item['template']  = $slug;
		$item['name']  = $data['label'];
		$item['category']  = $data['category'];
		$item['description']  = $data['description'];

		//print_r($item);exit;

		$response = wp_cta_template_api_request( $item );
		$package = $response['package'];
		IF (!isset($package)||empty($package)) return;
		//echo $package;exit;
		$zip_array = wp_remote_get($package,null);
		($zip_array['response']['code']==200) ? $zip = $zip_array['body'] : die("<div class='error'><p>{$slug}: Invalid download location (Version control not provided).</p></div>");

		$uploads = wp_upload_dir();
		$uploads_dir = $uploads['path'];

		$temp = ini_get('upload_tmp_dir');
		if (empty($temp))
		{
			$temp = "/tmp";
		}

		$file_path = $temp . "/".$slug.".zip";

		//$file_path = WP_CTA_PATH."templates/here.zip";
		//echo $file_path;
		////echo $zip;exit;

		file_put_contents($file_path, $zip);

		//echo $file_path;exit;
		include_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');

		$zip = new PclZip( $file_path );
		//echo is_writable(WP_CTA_PATH.'templates/'.$slug);exit;
		$uploads = wp_upload_dir();
		$uploads_path = $uploads['basedir'];
		$extended_path = $uploads_path.'/wp-call-to-actions/templates/';


		if (!is_dir($extended_path))
		{
			wp_mkdir_p( $extended_path );
		}

		$result = $zip->extract(PCLZIP_OPT_PATH, $extended_path , PCLZIP_OPT_REPLACE_NEWER );

		if (!$result)
		{
			die("There was a problem. Please try again!");
		}
		else
		{
			//print_r($result);exit;
			unlink($file_path);
			echo '<div class="updated"><p>'.$data['label'].' upgraded successfully!</div>';
		}
	}



	function wp_cta_templates_check_for_update($item) {
		$version = $item['version'];
		$api_response = wp_cta_template_api_request( $item );
		//print_r($api_response);
		if( false !== $api_response ) {
			if( version_compare( $version, $api_response['new_version'], '<' ) ) {
				$template_page = WP_CTA_STORE_URL."/downloads/".$item['ID']."/";
				$html = '<div class="update-message">'.$item['version'].' &nbsp;&nbsp; <font class="update-available">Version '.$api_response['new_version'].' available.</font><br> <a title="'.$item['name'].'" class="thickbox" href="'.$template_page.'" target="_blank">View template details</a> ';
				$html .= 'or <a href="?post_type=wp-call-to-action&page=wp_cta_manage_templates&action=upgrade&template%5B%5D='.$item['ID'].'">update now</a>.</div>';
				return $html;
			} else {
				return $item['version'];
			}
		} else {
			return $item['version'];
		}
	}

	function  wp_cta_template_api_request( $item ) {
		$api_params = array(
			'edd_action' 	=> 'get_version',
			'license' 		=> '',
			'name' 			=> $item['name'],
			'slug' 			=> $item['ID'],
			'nature' 			=> 'template',
		);

		$request = wp_remote_post( WP_CTA_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
		//print_r($request);exit;
		if ( !is_wp_error( $request ) ):
			$request = json_decode( wp_remote_retrieve_body( $request ), true );
			if( $request )
				$request['sections'] = maybe_unserialize( $request['sections'] );
			return $request;
		else:
			return false;
		endif;
	}

	function wp_cta_templates_delete_dir($dir,$slug) {
		global $wp_cta_data;
		$data = $wp_cta_data[$slug];

		if (!file_exists($dir)) return true;
		if (!is_dir($dir) || is_link($dir)) return unlink($dir);
		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') continue;
			if (!wp_cta_templates_delete_dir($dir . "/" . $item , $slug)) {
				chmod($dir . "/" . $item, 0777);
				if (!wp_cta_templates_delete_dir($dir . "/" . $item , $slug)) return false;
			};
		}
		return rmdir($dir);


		echo '<div class="updated"><p>'.$data['label'].' deleted successfully!</div>';
	}
}

//create hidden pages for template upload management
add_action('admin_menu', 'wp_cta_templates_add_menu');


function wp_cta_templates_add_menu() {
	if (current_user_can('manage_options'))
	{
		global $_registered_pages;

		$hookname = get_plugin_page_hookname('wp_cta_templates_upload', 'edit.php?post_type=wp-call-to-action');
		if (!empty($hookname)) {
			add_action($hookname, 'wp_cta_templates_upload');
		}
		$_registered_pages[$hookname] = true;

		$hookname = get_plugin_page_hookname('wp_cta_templates_search', 'edit.php?post_type=wp-call-to-action');
		//echo $hookname;exit;
		if (!empty($hookname)) {
			add_action($hookname, 'wp_cta_templates_search');
		}
		$_registered_pages[$hookname] = true;
	}
}