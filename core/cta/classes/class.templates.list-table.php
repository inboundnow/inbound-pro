<?php

/**
 * List table for visualizing 4rd party installed templates
 *
 * @package	Calls To Action
 * @subpackage	Templates
*/


if ( !class_exists('CTA_Template_Manager_List') ) {

	if( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

	class CTA_Template_Manager_List extends WP_List_Table {
		private $template_data;
		private $singular;
		private $plural;

		function __construct() {
			
			$CTA_Load_Extensions = CTA_Load_Extensions();
			$wp_cta_data = $CTA_Load_Extensions->template_definitions;
			
			$final_data = array();
			
			foreach ($wp_cta_data as $key=>$data)
			{
				$array_core_templates = array('auto-focus' , 'thumbnail-cta' , 'breathing' , 'clean-cta' , 'blank-template','call-out-box','cta-one','demo', 'flat-cta', 'peek-a-boo', 'popup-ebook', 'facebook-like-button', 'facebook-like-to-download', 'feedburner-subscribe-to-download', 'linkedin-share-to-download', 'tweet-to-download', 'follow-to-download', 'ebook-call-out');

				if ($key == 'wp-cta' || substr($key,0,4) == 'ext-' )
					continue;

				if (isset($data['info']['data_type']) && $data['info']['data_type']=='metabox') {
					continue;
				}

				if (in_array($key,$array_core_templates)) {
					continue;
				}

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

			$this->template_data = $final_data;


			$this->singular = 'ID';
			$this->plural = 'ID';
			$this->_actions = array();

			$args = $this->_args;

			$args['plural'] = sanitize_key( '' );
			$args['singular'] = sanitize_key( '' );

			$this->_args = $args;
		}

		function get_columns() {
			$columns = array(
			'cb'        => '<input type="checkbox" />',
			'template' => 'Template',
			'description' => 'Description',
			'category' => 'Category',
			'version' => 'Current Version'

			);
			return $columns;
		}

		function column_cb($item){
			return sprintf(
				'<input type="checkbox" name="template[]" value="%s" />', $item['ID']
			);
		}

		function get_sortable_columns()	{
			$sortable_columns = array(
				'template'  => array('template',false),
				'category' => array('category',false),
				'version'   => array('version',false)
			);

			return $sortable_columns;
		}

		function usort_reorder( $a, $b ){
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
					echo CTA_Template_Manager::check_template_for_update($item);
					return;
				case 'actions':
					//echo wp_cta_templates_print_delete_button($item);

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





}