<?php

/**
 *
 * Class for managing external package retrieveal, installation, and deletion. (Extensions & Templates)
 * @package     InboundPro
 * @subpackage  Installer
 */


class Inbound_Pro_Downloads {

	static $management_mode; /* UI mode toggle */
	static $items; /* dataset of remotly available items based on page being loaded */
	static $downloads; /* improved dataset of loaded downloads */
	static $download; /* focus dataset being processed */
	static $headline; /* UI headline */
	static $customer; /* sets customer status */
	static $access_level; /* sets customer status */

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

		add_action( 'inbound-pro/check-for-updates' , array( __CLASS__ , 'cron_check_for_updates' ) );

		/* add open_base_dir check */
		add_action('admin_notices', array( __CLASS__ , 'check_open_base_dir' ) );

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

		wp_enqueue_script('bootstrap', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/BootStrap/js/bootstrap.min.js');
		wp_enqueue_style('bootstrap', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/BootStrap/css/bootstrap.min.css');
		wp_enqueue_style('manage-downloads', INBOUND_PRO_URLPATH . 'assets/css/admin/manage-downloads.css');
		wp_enqueue_style('fontawesome', INBOUNDNOW_SHARED_URLPATH . 'assets/fonts/fontawesome/css/font-awesome.min.css');

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
		global $download_name;

		if ( ! current_user_can('delete_plugins') ) {
			wp_die( __('You do not have sufficient permissions to delete plugins for this site.') );
		}

		/* preapre variables */
		$filename = ( isset($filename) && $filename ) ? $filename : $_REQUEST['filename'];
		$download_type = ( isset($download_type) && $download_type ) ? $download_type : $_REQUEST['filename'];

		/* get downloads dataset */
		self::build_main_dataset();

		/* get download array from */
		self::$download = self::$downloads[ $_REQUEST['download_name'] ];

		/* get zip URL from api server */
		self::$download['download_location'] = Inbound_API_Wrapper::get_download_zip( self::$download );

		/* get upload path from download data */
		self::$download['extraction_path'] = self::get_upload_path( self::$download );

		self::install_download( self::$download );

		/* create global for download name */
		$download_name = self::$download[ 'post_title' ];
		/* add notification */
		add_action( 'admin_notices', function( ) {
			global $download_name;
			?>
			<br>
			<div class="updated">
				<p><?php _e( $download_name . ' has been installed!', 'inbound-pro' ); ?></p>
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
		global $download_name;

		if ( ! current_user_can('delete_plugins') ) {
			wp_die( __('You do not have sufficient permissions to delete plugins for this site.') );
		}

		/* get pro templates dataset */
		self::build_main_dataset();

		/* get download array from */
		self::$download = self::$downloads[ $_REQUEST['download_name'] ];

		/* get upload path from download data */
		self::$download['extraction_path'] = self::get_upload_path( self::$download );

		/* delete download folder if there */
		self::delete_download_folder( self::$download['extraction_path'] );

		/* create global for download name */
		$download_name = self::$download[ 'post_title' ];

		/* add notification */
		add_action( 'admin_notices', function() {
			global $download_name;
			?>
			<br>
			<div class="updated">
				<p><?php _e( $download_name . ' has been uninstalled!', 'inbound-pro' ); ?></p>
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
	 * Install extension
	 *
	 */
	public static function install_download( $download ) {

		/* load pclzip */
		include_once( ABSPATH . '/wp-admin/includes/class-pclzip.php');

		/* delete download folder if there */
		self::delete_download_folder( $download['extraction_path'] );

		/* create temp file *///get_temp_dir()
		$temp_file = tempnam(sys_get_temp_dir(), 'TEMPPLUGIN' );

		/* get zip file contents from svn */
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $download['download_location'] );
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

		/* make sure we get a filename */
		if(!isset($download['filename']) || !$download['filename']) {
			$download['filename'] = $download['post_name'];
		} else {
			$download['filename'] = str_replace('.zip' , '' , $download['filename']);
		}

		/* extract temp file to plugins direction */
		$archive = new PclZip($temp_file);
		$result = $archive->extract( PCLZIP_OPT_REMOVE_PATH, $download['filename'] , PCLZIP_OPT_PATH, $download['extraction_path'] , PCLZIP_OPT_REPLACE_NEWER );
		if ($result == 0) {
			die("Error : ".$archive->errorInfo(true));
		}

		/* delete templ file */
		unlink($temp_file);
	}

	/**
	 * deletes download folder from uploads location
	 * @param STRING $dirPath
	 */
	public static function delete_download_folder( $dirPath ) {

		if ( !$dirPath || !is_dir($dirPath)) {
			return;
		}

		/* get all objects in folder */
		$objects = scandir($dirPath);

		/* if there is a .git directory assume local development and bail */
		if ( in_array( '.git' , $objects ) ) {
			return;
		}

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


	/**
	 *  Build a stronger dataset from dataset imported from api
	 */
	public static function build_main_dataset() {

		/* load management static vars based on page being loaded */
		self::load_management_vars();

		/* get install configuaration dataset from db */
		$configuration= Inbound_Options_API::get_option('inbound-pro', 'configuration', array());

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

		return self::$downloads;
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
			$configuration[ self::$download['post_name'] ]['filename'] = self::$download['filename'];

		}

		/* now update configuaration dataset in db */
		Inbound_Options_API::update_option( 'inbound-pro' , 'configuration' , $configuration , 'yes' );

	}

	/**
	 *  Loads all UI elements
	 */
	public static function test_ui() {

		/* get download data */
		self::build_main_dataset();
		self::grid_container();
	}

	/**
	 *
	 */
	public static function grid_container() {
		// Conditional showing of tabs
		$showAll = true;
		$showLP = false;
		$showCTA = false;
		$showLeads = false;
		$showEmail = false;
		$openTab = 0;

		if (isset($_GET['show'])) {
			$showAll = false;
			if( $_GET['show'] === "landing-pages" ) {
				$showLP = true;
				$openTab = 1;
			}  else if( $_GET['show'] === "leads" ) {
				$showLeads = true;
				$openTab = 2;
			} else if( $_GET['show'] === "cta" ) {
				$showCTA = true;
				$openTab = 3;
			} else if( $_GET['show'] === "email" ) {
				$showEmail = true;
				$openTab = 4;
			}
		}

		$AllTabClass = ($showAll) ? 'tab-current' : '';
		$allClass = ($showAll) ? 'content-current' : '';
		$leadsTabClass = ($showLeads) ? 'tab-current' : '';
		$leadsClass = ($showLeads) ? 'content-current' : '';
		$emailTabClass = ($showEmail) ? 'tab-current' : '';
		$emailClass = ($showEmail) ? 'content-current' : '';
		$ctaTabClass = ($showCTA) ? 'tab-current' : '';
		$ctaClass = ($showCTA) ? 'content-current' : '';
		$lpTabClass = ($showLP) ? 'tab-current' : '';
		$lpClass = ($showLP) ? 'content-current' : '';

		?>
		<link rel='stylesheet' id='edd-styles-css'  href='/wp-content/plugins/_inbound-pro/assets/css/store.css' type='text/css' media='all' />
		<!--START -->
		<div id="tabs" class="tabs">
			<nav>
				<ul>
					<li class="<?php echo $AllTabClass; ?>"><a href="#section-1" ><span>All</span></a></li>
					<li class="<?php echo $lpTabClass; ?>"><a href="#section-2" class="icon-article"><span>Landing Pages</span></a></li>
					<li class="<?php echo $leadsTabClass; ?>"><a href="#section-3" class="icon-user"><span>Leads</span></a></li>
					<li class="<?php echo $ctaTabClass; ?>"><a href="#section-4" class="icon-eye"><span>Calls to Action</span></a></li>
					<li class="<?php echo $emailTabClass;?>"><a href="#section-5" class="fa fa-envelope-o"><span>Email</span></a></li>

				</ul>


			</nav>
			<div class="content" id="market-content">
				<section id="section-1" class="<?php echo $allClass;?>">

					<div class="store-container">
						<div class="main">
							<span class="center-text area-desc">From email marketing to CRMs these add-ons will make your life easier</span>
							<div id="cbp-vm" class="cbp-vm-switcher cbp-vm-view-grid">

								<div class="cbp-vm-options">
									<span>Change View</span>
									<a href="#" class="cbp-vm-icon cbp-vm-grid cbp-vm-selected" data-view="cbp-vm-view-grid">Grid View</a>
									<a href="#" class="cbp-vm-icon cbp-vm-list" data-view="cbp-vm-view-list">List View</a>
								</div>

								<?php self::store_listings();?>
							</div>
						</div><!-- /main -->
					</div><!-- /store container -->
				</section>

			</div><!-- /content -->
		</div>
		<!-- END -->

	<?php }
	/* Prepping for new layout */
	public static function store_listings($slug = 'default') {

		?>

		<ul>
			<?php $count = 0;
			foreach (self::$downloads as $download) {
				$id = $download['ID'];
				$title = $download['post_title'];
				$title_class = (strlen($title) > 30 ) ? "long-title" : 'short-title';
				$exerpt = $download['post_excerpt'];
				$link = $download['permalink'];
				$terms = get_the_terms( $id, 'download_category' ); ?>

				<li class="">
					<?php $img = $download['featured_image'];
					if(!$img){
						$img = '<img src="http://inboundnew.dev/wp-content/uploads/2014/08/medium_wype-1.jpg">';
					}
					?>
					<a class="cbp-vm-image" href="<?php echo $link;?>"><img src="<?php echo $img;?>"/></a>

					<!--<div class="cbp-vm-price">$19.90</div>-->
					<div class="cbp-vm-details">
						<h3 class="cbp-vm-title <?php echo $title_class;?>"><a href="<?php echo $link;?>"><?php echo $title;?></a></h3>
						<span class='details-text'>
						<?php
						if( $exerpt != "" ) :
							echo $exerpt;
						else :
							echo "short description This is the headline text. This is the headline text. more text here. Testing testing";
						endif;
						?>
		                </span>
					</div>
					<a class="cbp-vm-icon cbp-vm-add" href="<?php echo $link; ?>">Add to cart</a>
				</li>
				<?php $count++; ?>

			<?php }

			?>

			<!--<div id="load_more">Load More</div> -->
		</ul>

	<?php
	}


	/**
	 *  Checks if open_base dir
	 */
	public static function check_open_base_dir() {
		global $post , $inbound_settings;


		if (!isset($_GET['page'])|| ( $_GET['page']!='inbound-manage-extensions' && $_GET['page']!='inbound-manage-templates' ) ){
			return false;
		}

		if ( !ini_get('open_basedir') ) {
			return;
		}

		?>
		<div class="updated">
			<p><?php _e( 'ATTENTION: open_basedir restriction in effect which will cause problems with installation.' , 'inbound-pro'); ?></p>
		</div>

		<?php
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
							<label  class='filter-label' for="uninstalled"><input type="radio" name="meta" value="uninstalled"  class='radio-filter'> <?php _e( 'Uninstalled' , 'inbound-pro' ); ?></label>
						</span>
						<span class="ib">
							<label class='filter-label' for="installed"><input type="radio" name="meta" value="installed" class='radio-filter'> <?php _e( 'Installed' , 'inbound-pro' ); ?></label>
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
		global $inbound_settings;

		$page = sanitize_text_field($_GET['page']);
		$inbound_settings['system']['counts']['needs-update'][self::$management_mode] = 0;
		?>

		<div class="wrap">
			<?php
			/*
			echo "<pre>";
			print_r(self::$downloads);exit;
			/**/
			?>
			<div id="grid" class="container-fluid">
				<?php $count = 1;

				/* determine permissions from customer access level*/
				$permitted = false;

				if (INBOUND_ACCESS_LEVEL > 0 &&  INBOUND_ACCESS_LEVEL != 9 ) {
					$permitted = true;
				}

				foreach (self::$downloads as $download) {


					if ($count % 3 == 0) {
						echo "<div></div>";
					}
					$count++;

					/* Determine if needs update */
					if ( version_compare( $download['current_version'] ,  $download['server_version']) == -1  && !in_array('uninstalled', $download['status']) )  {
						$download['status'][] = 'needs-update';
						$inbound_settings['system']['counts']['needs-update'][self::$management_mode]++;
					}


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
							$ribbons['email']['ribbon'] = 'mail-ribbon';
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

						<div class="download-image" style='background-image:url(<?php echo self::get_image_thumbnail($download); ?>);' >
						</div>

						<div class="col-template-content more-details" data-download='<?php echo $download['post_name']; ?>'  data-toggle="tooltip" data-placement="top" data-original-title='<?php _e( 'View Details' , 'inbound-pro' ); ?>'>
							<div class="col-template-info-title"><?php echo $download['post_title']; ?></div>
						</div>

						<div class="col-template-actions">
							<?php
							if ( in_array( 'uninstalled' , $download['status'] )&& $permitted ) {
								?>
								<div class="action-install">
									<a  href="admin.php?page=<?php echo $page; ?>&action=install&download_name=<?php echo $download['post_name']; ?>&download_type=<?php echo $download['download_type']; ?>&filename=<?php echo $download['filename']; ?>" class="power-toggle power-is-off fa fa-power-off"  data-toggle="tooltip" id='<?php echo $download['post_name']; ?>' title='<?php _e( 'Turn On' , 'inbound-pro' ); ?>'></a>
								</div>
								<?php
							}

							if ( in_array( 'installed' , $download['status'] ) && $permitted ) {
								?>
								<div class="action-uninstall">
									<a href="admin.php?page=<?php echo $page; ?>&action=uninstall&download_name=<?php echo $download['post_name']; ?>&filename=<?php echo $download['filename']; ?>" class="power-toggle power-is-on fa fa-power-off"  data-toggle="tooltip" id='<?php echo $download['post_name']; ?>' title='<?php _e( 'Turn Off' , 'inbound-pro' ); ?>'></a>
								</div>

								<?php
								if ( in_array( 'needs-update' , $download['status'] ) ) {
									?>
									<div class="action-update">
										<a href="admin.php?page=<?php echo $page; ?>&action=install&download_name=<?php echo $download['post_name']; ?>&download_type=<?php echo $download['download_type']; ?>&filename=<?php echo $download['filename']; ?>" class="fa fa-floppy-o"  data-toggle="tooltip" id='<?php echo $download['post_name']; ?>' title='<?php _e( 'Update' , 'inbound-pro' ); ?>'></a>
									</div>
									<?php
								}

								if ($download['download_type'] == 'extension' ) {
									$settings_url = apply_filters(
											'inbound-pro/download-setting-url' ,
											add_query_arg( array( 'page'=>'inbound-pro' , 'tab' =>  'inbound-pro-settings' , 'setting' => $download['filename'] ) , admin_url( 'admin.php' ) )
											, $download );
									?>
									<div class="action-settings">
										<a target="_blank" href="<?php echo $settings_url; ?>" class="fa fa-cog"  data-toggle="tooltip" id='<?php echo $download['post_name']; ?>' title='<?php _e( 'View Settings' , 'inbound-pro' ); ?>'></a>
									</div>
									<?php
								}
							}

							if (!$permitted) {
								?>
								<div class="action-locked">
									<i class="fa fa-lock"  data-toggle="tooltip" id='' title='<?php _e( 'Only available to subscribers.' , 'inbound-pro' ); ?>'></i>
								</div>
								<?php
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

		/* update needs update count */
		Inbound_Options_API::update_option('inbound-pro', 'settings', $inbound_settings);
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
		$folder_name =  ( isset($download['filename']) ) ? $download['filename'] :  $download['post_name'];
		$folder_name = str_replace('.zip' , '' , $folder_name );

		if ( $download['download_type'] == 'extension' ) {
			return 	INBOUND_PRO_UPLOADS_PATH . 'extensions/'. $folder_name;
		}

		if ( in_array( 'calls-to-action' , $download[ 'plugins' ] ) ) {
			return WP_CTA_UPLOADS_PATH . $folder_name . '/';
		}

		if ( in_array( 'landing-pages' , $download[ 'plugins' ] ) ) {
			return LANDINGPAGES_UPLOADS_PATH . $folder_name . '/';
		}

		if ( in_array( 'email' , $download[ 'plugins' ] ) ) {
			return INBOUND_EMAIL_UPLOADS_PATH . $folder_name . '/';
		}

	}

	public static function get_image_thumbnail($download) {
		global $inbound_paths_created;

		$path = INBOUND_PRO_UPLOADS_PATH . 'assets/images/';
		$url = INBOUND_PRO_UPLOADS_URLPATH . 'assets/images/';


		if(strstr( $download['featured_image'] , '.png')){
			$ext = '.png';
		}else if(strstr( $download['featured_image'] , '.jpg')){
			$ext = '.jpg';
		}

		if(file_exists( $path . $download['post_name'] . $ext)){
			return $url . $download['post_name'] .$ext;
		}

		$image_path_location = $path . $download['post_name'] . $ext;
		$image_url_location = $url . $download['post_name'] . $ext;

		/* create wp-content/inbound-pro/assets/images */
		if (!$inbound_paths_created) {
			$inbound_paths_created = true;
			Inbound_Pro_Activation::create_upload_folders();
		}


		$image_path_location = $path . $download['post_name'] . $ext;
		$image_url_location = $url . $download['post_name'] . $ext;

		/* create wp-content/inbound-pro/assets/images */
		if (!$inbound_paths_created) {
			$inbound_paths_created = true;
			Inbound_Pro_Activation::create_upload_folders();
		}

		/* get zip file contents from svn */
		$file = wp_remote_get($download['featured_image']);
		$image = wp_remote_retrieve_body($file);
		/* write zip file to temp file */
		$handle = fopen($image_path_location, "w");
		fwrite($handle, $image);
		fclose($handle);


		return $image_url_location;
	}

	/**
	 *  Display management page
	 */
	public static function load_management_vars( ) {

		$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 'inbound-manage-extensions';
		switch( $page ) {
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
			default:
				/* if not calling from the template or extension management page let's make sure our download dataset is primed and up to date */
				Inbound_API_Wrapper::get_downloads();
				self::$items = array();
				break;
		}


	}


	/**
	 *  Loads inline JS & CSS
	 */
	public static function load_inline_js_css() {
		$screen = get_current_screen();

		if ( $screen->base != 'inbound-pro_page_inbound-manage-templates' &&  $screen->base != 'inbound-pro_page_inbound-manage-extensions' ) {
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

		if ( intval(INBOUND_ACCESS_LEVEL) < 3) {
			?>
			<style type="text/css">
			body .details-install {
				display:none !important;
			}
			body .details-update {
				display:none !important;
			}

			<?php
			if ($screen->base == 'inbound-pro_page_inbound-manage-extensions') {
			?>

				body .details-preview {
					display: none !important;
				}

			<?php
			}
			?>
			</style>
			<?php
		}

	}

	public static function cron_check_for_updates() {
		global $inbound_settings;

		$configuration = Inbound_Options_API::get_option( 'inbound-pro' , 'configuration' , array() );


		$inbound_settings['system']['counts']['needs-update']['extensions'] = 0;
		$inbound_settings['system']['counts']['needs-update']['templates'] = 0;

		$data = Inbound_API_Wrapper::get_downloads();

		foreach ( $data as $key => $download ) {

			if (!isset($download->post_name)) {
				continue;
			}

			$status = ( isset($configuration[ $download->post_name ]['status'] ) ) ? $configuration[ $download->post_name ]['status'] : 'uninstalled' ;

			if($status=='uninstalled') {
				continue;
			}

			if ( isset($download->download_type) && $download->download_type == 'extension' ) {
				$extensions[] = (array) $download;
			}


			/* Determine if needs update */
			if ( version_compare( $configuration[ $download->post_name ]['current_version'] ,  $download->server_version) == -1   )  {
				$inbound_settings['system']['update-count'][self::$management_mode]++;
			}
		}

		/* update needs update count */
		Inbound_Options_API::update_option('inbound-pro', 'settings', $inbound_settings);
	}



}



/**
 *	Loads Inbound_Pro_Downloads on admin_init
 */
function load_Inbound_Pro_Downloads() {
	$Inbound_Pro_Downloads = new Inbound_Pro_Downloads;
}
add_action( 'init' , 'load_Inbound_Pro_Downloads' );
