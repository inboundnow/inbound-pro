<?php

/**
 *
 *	Manage Inbound Extras
 *
*/


class Inbound_Pro_Downloads {

	static $management_mode; /* UI mode toggle */
	static $items; /* dataset of remotly available items based on page being loaded */
	static $downloads; /* improved dataset of loaded downloads */
	static $download; /* focus dataset being processed */
	static $headline; /* UI headline */

	/**
	*	Initializes class
	*/
	public function __construct() {
		self::add_hooks();
	}

	/**
	*	Loads hooks and filters
	*/
	public static function add_hooks() {
		/* Listen for management REQUEST calls  */
		add_action( 'admin_init' , array( __CLASS__ , 'run_management_actions' ) );

		/* enqueue js and css */
		add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_scripts' ) );

		/* load inline js & css */
		add_action( 'admin_footer' , array( __CLASS__ , 'load_inline_js_css' ) );

	}


	/**
	*	Load CSS & JS
	*/
	public static function enqueue_scripts() {
		$screen = get_current_screen();

		/* Load assets for upload page */
		if ( ( isset($screen) && ( $screen->base != 'inbound-pro_page_inbound-manage-templates' && $screen->base != 'inbound-pro_page_inbound-manage-extensions' ) ) ){
			return;
		}

		wp_enqueue_script('jquery');
		wp_enqueue_script('underscore');

		wp_enqueue_script('shuffle', INBOUND_PRO_URLPATH . 'assets/libraries/Shuffle/jquery.shuffle.modernizr.min.js' , array( 'jquery') );

		wp_enqueue_script('bootstrap', INBOUND_PRO_URLPATH . 'assets/libraries/BootStrap/js/bootstrap.min.js');
		wp_enqueue_style('bootstrap', INBOUND_PRO_URLPATH . 'assets/libraries/BootStrap/css/bootstrap.min.css');
		wp_enqueue_style('manage-downloads', INBOUND_PRO_URLPATH . 'assets/css/admin/manage-downloads.css');
		wp_enqueue_style('fontawesome', INBOUND_PRO_URLPATH . 'assets/libraries/FontAwesome/css/font-awesome.min.css');

		/* load cusotm js */
		wp_enqueue_script('manage-downloads', INBOUND_PRO_URLPATH . 'assets/js/admin/manage-downloads.js' );

		/* localize script */
		$memory = Inbound_Options_API::get_option( 'inbound-pro' , 'memory' , array() );
		$meta_filter = ( isset( $memory[ 'meta_filter'] ) ) ? $memory[ 'meta_filter'] : 'uninstalled' ;

		/* get pro templates dataset */
		self::build_main_dataset();

		wp_localize_script( 'manage-downloads', 'downloads' , array( 'meta_filter' => $meta_filter , 'plugin_url_path' => INBOUND_PRO_URLPATH , 'dataset' => self::$downloads , 'current_page' => $_GET['page'] ) );
	}



	/**
	*  Listens for POSTED actions
	*/
	public static function run_management_actions() {

		if (!isset($_REQUEST['action']) || !current_user_can('activate_plugins') ) {
			return;
		}

		switch ($_REQUEST['action']):
			case 'install':
				self::run_installation();
				break;
			case 'upgrade':

				break;
			case 'uninstall':
				self::run_uninstallation();
				break;
		endswitch;

	}


	/**
	*  Runs upload commands
	*/
	public static function run_installation() {

		if ( ! current_user_can('delete_plugins') ) {
			wp_die( __('You do not have sufficient permissions to delete plugins for this site.') );
		}

		/* load pclzip */
		include_once( ABSPATH . '/wp-admin/includes/class-pclzip.php');

		/* get zip URL from api server */
		$download_location = Inbound_API_Wrapper::get_download_zip( $_REQUEST['filename'] );

		/* get downloads dataset */
		self::build_main_dataset();

		/* get download array from */
		self::$download = self::$downloads[ $_REQUEST['download'] ];
		
		echo "from node api_:   " . $download_location . "<br>";
		echo "from php script: "; print_r(self::$download['fileserver']);
		exit;
		/* get upload path from download data */
		$extraction_path = self::get_upload_path( self::$download );

		/* delete download folder if there */
		self::delete_download_folder( $extraction_path );

		/* create temp file */
		$temp_file = tempnam('/tmp', 'TEMPPLUGIN' );

		/* get zip file contents from svn */
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $file); // remove self::$download['fileserver'];
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$file = curl_exec($ch);
		curl_close($ch);

		/* write zip file to temp file */
		$handle = fopen($temp_file, "w");
		fwrite($handle, $file);
		fclose($handle);

		/* extract temp file to plugins direction */
		$archive = new PclZip($temp_file);
		$result = $archive->extract( PCLZIP_OPT_REMOVE_PATH, self::$download['zip_filename'] , PCLZIP_OPT_PATH, $extraction_path , PCLZIP_OPT_REPLACE_NEWER );
		if ($result == 0) {
			die("Error : ".$archive->errorInfo(true));
		}

		/* delete templ file */
		unlink($temp_file);

		/* add notification */
		add_action( 'admin_notices', function() {
			?>
			<br>
			<div class="updated">
				<p><?php _e( self::$download[ 'post_title' ] . ' has been installed!', 'inbound-pro' ); ?></p>
			</div>
			<?php
		} );

		/* update configuration dataset & mark installed */
		self::update_configuration_memory('installed');

		/* switch downloads filter to 'installed' */
		$memory = Inbound_Options_API::get_option( 'inbound-pro' , 'memory' , array() );
		$memory['meta_filter'] = 'installed';
		Inbound_Options_API::update_option( 'inbound-pro' , 'memory' , $memory );

	}


	/**
	*  Runs upload commands
	*/
	public static function run_uninstallation() {

		if ( ! current_user_can('delete_plugins') ) {
			wp_die( __('You do not have sufficient permissions to delete plugins for this site.') );
		}

		/* get pro templates dataset */
		self::build_main_dataset();

		/* get download array from */
		self::$download = self::$downloads[ $_REQUEST['download'] ];

		/* get upload path from download data */
		$extraction_path = self::get_upload_path( self::$download );

		/* delete templ file */
		self::delete_download_folder( $extraction_path );

		/* add notification */
		add_action( 'admin_notices', function() {
			?>
			<br>
			<div class="updated">
				<p><?php _e( self::$download[ 'post_title' ] . ' has been uninstalled!', 'inbound-pro' ); ?></p>
			</div>
			<?php
		} );



		/* update configuration dataset & mark installed */
		self::update_configuration_memory('uninstalled');

		/* switch downloads filter to 'installed' */
		$memory = Inbound_Options_API::get_option( 'inbound-pro' , 'memory' , array() );
		$memory['meta_filter'] = 'uninstalled';
		Inbound_Options_API::update_option( 'inbound-pro' , 'memory' , $memory );

	}


	/**
	* deletes plugin folder
	* @param STRING $dirPath
	*/
	public static function delete_download_folder( $dirPath ) {

		if ( $dirPath && is_dir($dirPath)) {
			$objects = scandir($dirPath);
			foreach ($objects as $object) {
				if ($object != "." && $object !="..") {
					if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
						self::delete_download_folder($dirPath . DIRECTORY_SEPARATOR . $object);
					} else {
						unlink($dirPath . DIRECTORY_SEPARATOR . $object);
					}
				}
			}
			reset($objects);
			rmdir($dirPath);
		}

	}


	/**
	*  Build a stronger dataset from dataset imported from api
	*/
	public static function build_main_dataset() {

		/* load management static vars based on page being loaded */
		self::load_management_vars();

		/* get install configuaration dataset from db */
		$configuration = Inbound_Options_API::get_option( 'inbound-pro' , 'configuration' , array() );

		/* loop through relevant items and build a more robost dataset */
		$i=0;
		foreach (self::$items as $key => $download ) {

			self::$downloads[ $download['post_name']  ] = $download;

			/* discover applicable plugins */
			$download['plugins'][] = 'all';
			self::$downloads[ $download['post_name'] ]['plugins'] = $download['plugins'];

			/* discover installed condition */
			self::$downloads[ $download['post_name'] ]['status'][] = ( isset($configuration[ $download['post_name'] ]['status'] ) ) ? $configuration[ $download['post_name'] ]['status'] : 'uninstalled' ;

			/* Discover current version */
			self::$downloads[ $download['post_name'] ]['current_version'] = ( isset($configuration[ $download['post_name'] ]['current_version'] ) ) ? $configuration[ $download['post_name'] ]['current_version'] : '' ;

			/* set next key inline */
			$prev = $i-1;
			$next = $i+1;

			self::$downloads[ $download['post_name'] ]['previous_post_name'] = (isset(self::$items[$prev]['post_name'])) ? self::$items[$prev]['post_name'] : self::$items[0]['post_name'];
			self::$downloads[ $download['post_name'] ]['next_post_name'] = (isset(self::$items[$next]['post_name'])) ? self::$items[$next]['post_name'] : self::$items[0]['post_name'];

			$i++;
		}

		//print_r(self::$downloads);exit;
	}

	/**
	*  Update downloads configuration file with new status & version
	*  @param STRING $status
	*/
	public static function update_configuration_memory( $status ) {

		/* get install configuaration dataset */
		$configuration = Inbound_Options_API::get_option( 'inbound-pro' , 'configuration' , array() );

		$configuration[ self::$download['post_name'] ]['status'] = $status;

		/* update current version to server_version */
		if ($status == 'installed') {
			$configuration[ self::$download['post_name'] ]['download_type'] = self::$download['download_type'];
			$configuration[ self::$download['post_name'] ]['current_version'] = self::$download['server_version'];
			$configuration[ self::$download['post_name'] ]['upload_path'] = self::get_upload_path( self::$download );
			$configuration[ self::$download['post_name'] ]['zip_filename'] = self::$download['zip_filename'];

		}

		/* now update configuaration dataset in db */
		Inbound_Options_API::update_option( 'inbound-pro' , 'configuration' , $configuration , 'yes' );

	}



	/**
	*  Loads all UI elements
	*/
	public static function display_ui() {

		/* get download data */
		self::build_main_dataset();

		/* displays filters */
		self::display_filters( );

		/* display grid items */
		self::display_grid_items();
	}

	/**
	*  Prints filters
	*/
	public static function display_filters() {
		?>
		<div class="filter-options row">

			<div class="col-xs-12 col-md-12">
			  <div class="templates-filter-group left-group">
				<h5 class="filter-label"><?php echo self::$headline; ?></h5>
				<button class="btn btn-go" data-filter-value="all"><span class="visuallyhidden"><?php _e( 'All' , 'inbound-pro' ); ?></span></button>
				<button class="btn btn-primary" data-filter-value="landing-pages"><span class="visuallyhidden"><?php _e( 'Landing Pages' , 'inbound-pro' ); ?></span></button>
				<button class="btn btn-warning" data-filter-value="calls-to-action"><span class="visuallyhidden"><?php _e( 'Calls to Action' , 'inbound-pro' ); ?></span></button>
				<?php
				/* if extension management page then show more core components */
				if ( self::$management_mode == 'extensions' ) {
					?>
					<button class="btn btn-success" data-filter-value="leads"><span class="visuallyhidden"><?php _e( 'Leads' , 'inbound-pro' ); ?></span></button>
					<button class="btn btn-danger" data-filter-value="automation"><span class="visuallyhidden"><?php _e( 'Automation' , 'inbound-pro' ); ?></span></button>

					<?php
				}
				?>
				<button class="btn btn-info" data-filter-value="email"><span class="visuallyhidden"><?php _e( 'Email' , 'inbound-pro' ); ?></span></button>

			  </div>
			</div>


			<div class="col-xs-12 col-md-12 right-group">
				<div class='templates-filter-group align-left '>
					<input class="filter-search" type="search" placeholder="<?php _e(' Search... ' , 'inbound-pro' ); ?>">
					<div class='radio-filters'>
						<span class="ib">
							<label class='filter-label' for="installed"><input type="radio" name="meta" value="installed" class='radio-filter'> <?php _e( 'Installed' , 'inbound-pro' ); ?></label>
						</span>
						<span class="ib">
							<label  class='filter-label' for="uninstalled"><input type="radio" name="meta" value="uninstalled"  class='radio-filter'> <?php _e( 'Uninstalled' , 'inbound-pro' ); ?></label>
						</span>
						<span class="ib">
							<label class='filter-label' for="needs-update"><input type="radio" name="meta" value="needs-update" class='radio-filter'> <?php _e( 'Needs Update' , 'inbound-pro' ); ?></label>
						</span>
					</div>
				</div>
			</div>

		</div>
		<?php
	}

	/**
	*  Loop through downloads datset and display grid items
	*/
	public static function display_grid_items() {
		?>

		<div class="wrap">
			<div id="grid" class="container-fluid">
				<?php $count = 1;


				foreach (self::$downloads as $download) {

					?>
					<?php if ($count % 3 == 0) {
						echo "<div></div>";
					}
					   $count++;
					?>
					<div class="row col-md-2 col-xs-2 download-item " data-plugins='<?php echo json_encode( $download['plugins'] );	?>' data-meta='<?php echo json_encode( $download['status'] ); ?>'  >

						<?php
						/* build ribbons */
						$ribbons = array();
						if ( in_array( 'landing-pages' , $download['plugins'] ) ) {
							$ribbons['landing-pages']['ribbon'] = 'lp-ribbon';
							$ribbons['landing-pages']['fa'] = 'fa-file-text-o';
							$ribbons['landing-pages']['title'] = __( 'Landing Page' , 'inbound-pro' );
						}

						if ( in_array( 'calls-to-action' , $download['plugins'] ) ) {
							$ribbons['calls-to-action']['ribbon'] = 'cta-ribbon';
							$ribbons['calls-to-action']['fa'] = 'fa-crosshairs';
							$ribbons['calls-to-action']['title'] = __( 'Call to Action' , 'inbound-pro' );
						}

						if ( in_array( 'leads' , $download['plugins'] ) ) {
							$ribbons['leads']['ribbon'] = 'leads-ribbon';
							$ribbons['leads']['fa'] = 'fa-users';
							$ribbons['leads']['title'] = __( 'Leads' , 'inbound-pro' );
						}

						if ( in_array( 'email' , $download['plugins'] ) ) {
							$ribbons['email']['ribbon'] = 'cta-ribbon';
							$ribbons['email']['fa'] = 'fa-envelope-o';
							$ribbons['email']['title'] = __( 'Email' , 'inbound-pro' );
						}

						if ( in_array( 'automation' , $download['plugins'] ) ) {
							$ribbons['automation']['ribbon'] = 'automation-ribbon';
							$ribbons['automation']['fa'] = 'fa-cog';
							$ribbons['automation']['title'] = __( 'Email' , 'inbound-pro' );
						}

						/* print ribbons */
						$i = 0;
						foreach ($ribbons as $key => $ribbon) {
							echo '<div class="download-ribbon '.$ribbon['ribbon'].' ribbon-'.$i.'"  title="'.$ribbon['title'].'" data-toggle="tooltip" data-placement="left" >
								<div class="border-ribbon"></div>
								<a class="fa '.$ribbon['fa'].'"></a>
							 </div>';
							$i++;
						}
						?>

						<div class="download-image" style='background-image:url(<?php echo INBOUND_PRO_URLPATH. 'assets/images/downloads/'.$download['post_name'].'.jpg'; ?>);' >
						</div>

						<div class="col-template-content more-details" data-download='<?php echo $download['post_name']; ?>' data-toggle="tooltip" data-placement="top" data-original-title='<?php _e( 'View Details' , 'inbound-pro' ); ?>'>
							<div class="col-template-info-title"><?php echo $download['post_title']; ?></div>
						</div>

						<div class="col-template-actions">
							<?php
							if ( in_array( 'uninstalled' , $download['status'] ) ) {
								?>
								<div class="action-install">
									<a  href="admin.php?page=<?php echo $_GET['page']; ?>&action=install&download=<?php echo $download['post_name']; ?>&filename=<?php echo $download['zip_filename']; ?>" class="power-toggle power-is-off fa fa-power-off"  data-toggle="tooltip" id='<?php echo $download['post_name']; ?>' title='<?php _e( 'Turn On' , 'inbound-pro' ); ?>'></a>
								</div>
								<?php
							}
							if ( in_array( 'installed' , $download['status'] ) ) {
								?>
								<div class="action-uninstall">
									<a href="admin.php?page=<?php echo $_GET['page']; ?>&action=uninstall&download=<?php echo $download['post_name']; ?>" class="power-toggle power-is-on fa fa-power-off"  data-toggle="tooltip" id='<?php echo $download['post_name']; ?>' title='<?php _e( 'Turn Off' , 'inbound-pro' ); ?>'></a>
								</div>
								<?php
								if ($download['download_type'] == 'extension' ) {
									$settings_url = apply_filters(
										'inbound-pro/download-setting-url' ,
										add_query_arg( array( 'page'=>'inbound-pro' , 'tab' =>  'inbound-pro-settings' , 'setting' => $download['zip_filename'] ) , admin_url( 'admin.php' ) )
										, $download );
									?>
									<div class="action-settings">
										<a target="_blank" href="<?php echo $settings_url; ?>" class="fa fa-cog"  data-toggle="tooltip" id='<?php echo $download['post_name']; ?>' title='<?php _e( 'View Settings' , 'inbound-pro' ); ?>'></a>
									</div>
									<?php
								}
							}
							?>
						</div>
					</div>

					<?php
				}
				?>
			</div>
		</div>
		<?php
	}


	/**
	*  Discover weather download is installed or uninstalled and weather it needs an upgrade or not
	*/
	public static function get_download_status() {


	}


	/**
	*  Determines uploads location
	*/
	public static function get_upload_path( $download ) {

		/* get zip file name */
		$folder_name =  ( isset($download['zip_filename']) ) ? $download['zip_filename'] :  $download['post_name'];

		if ( $download['download_type'] == 'extension' ) {
			return 	INBOUND_PRO_UPLOADS_PATH . 'extensions/'. $folder_name;
		}

		if ( in_array( 'calls-to-action' , $download[ 'plugins' ] ) ) {
			return WP_CTA_UPLOADS_PATH . $folder_name . '/';
		}

		if ( in_array( 'landing-pages' , $download[ 'plugins' ] ) ) {
			return LANDINGPAGES_UPLOADS_PATH . $folder_name . '/';
		}

		if ( in_array( 'mailer' , $download[ 'plugins' ] ) ) {
			return INBOUND_EMAIL_UPLOADS_PATH . $folder_name . '/';
		}

	}

	/**
	*  Display management page
	*/
	public static function load_management_vars() {

		switch( $_REQUEST['page'] ) {
			case 'inbound-manage-templates':

				/* set mode to templates */
				self::$management_mode = 'templates';

				/* set headline */
				self::$headline = __( 'Manage Templates' , 'inbound-pro' );

				/* set pre-processed download items */
				self::$items = Inbound_API_Wrapper::get_pro_templates();

				break;
			case 'inbound-manage-extensions':

				/* set mode to extensions */
				self::$management_mode = 'extensions';

				/* set headline */
				self::$headline = __( 'Manage Extensions' , 'inbound-pro' );

				/* set pre-processed download items */
				self::$items = Inbound_API_Wrapper::get_pro_extensions();

				break;
		}
	}


	/**
	*  Loads inline JS & CSS
	*/
	public static function load_inline_js_css() {
		$screen = get_current_screen();

		if ( $screen->base != 'inbound-pro_page_inbound-manage-templates' ) {
			return;
		}

		/* get filter memory */
		$memory = Inbound_Options_API::get_option( 'inbound-pro' , 'memory' , array() );
		$meta_filter = ( isset( $memory[ 'meta_filter'] ) ) ? $memory[ 'meta_filter'] : 'uninstalled' ;

		?>
		<script>
		jQuery(document).ready(function() {

		});
		</script>
		<?php
	}

}



/**
*	Loads Inbound_Pro_Downloads on admin_init
*/
function load_Inbound_Pro_Downloads() {
	$Inbound_Pro_Downloads = new Inbound_Pro_Downloads;
}
add_action( 'init' , 'load_Inbound_Pro_Downloads' );



