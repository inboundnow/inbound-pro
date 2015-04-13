<?php

if ( !class_exists('Inbound_Branching')	) {

	class Inbound_Branching {
		
		static $plugins; /* placeholder for dataset of plugins to apply developer mode too */
		static $plugin; /* placeholder for plugin being processed */
		static $branch; /* placeholder for current loaded branch */
		static $plugin_data; /* array version of api response containing plugin data */
		
		/**
		* Load class instance
		*/
		public function __construct() {
			self::load_static_vars();
			self::load_hooks();
		}

		/**
		*  Load static vars
		*/
		public static function load_static_vars() {
			self::$plugins = apply_filters( 'inbound_plugin_branches' , array() );
		}
		/**
		* Load hooks and filters
		*
		*/
		private static function load_hooks() {
			/* add controls */
			add_filter('plugin_action_links', array( __CLASS__ ,  'add_plugin_options' ) , 20 , 2); 
			
			/* enqueue js includes */
			add_action( 'admin_enqueue_scripts', array( __CLASS__ , 'enqueue_admin_scripts' ) );
			
			/* add js listeners */
			add_action( 'admin_print_footer_scripts', array( __CLASS__ , 'print_js_css' ) );
			
			/* Adds listener to save email data */
			add_action( 'wp_ajax_inbound_toggle_branch', array( __CLASS__ , 'toggle_branch' ) );
		}
		
		

		/**
		*  Adds version control options to plugin links
		*/
		public static function add_plugin_options( $links, $plugin ) {
			/* get plugin slug */
			$parts = explode( '/', $plugin );
			
			/* set current plugin being processed */
			self::$plugin = $parts[0];
			
			/* if array is not in our processing queue then skip */
			if ( !array_key_exists( self::$plugin , self::$plugins ) ) {
				return $links;
			}			
			
			/* determine if plugin is currently github branch or svn branch */
			self::get_current_branch();
			
			/* generate branch toggle button */
			$links['toggle'] =  self::generate_version_toggle();
			
			//echo self::$plugin;
			return $links;
		}

		
		/**
		*  Look in in options api to determine current branch
		*/
		public static function get_current_branch() {
			self::$branch = Inbound_Options_API::get_option( 'inbound-branching' ,  self::$plugin , 'svn' );
		}
		
		/**
		*  Store current branch URL in options api
		*/
		public static function set_current_branch() {
			self::$branch = Inbound_Options_API::update_option( 'inbound-branching' ,  self::$plugin , self::$branch );
		}
		
		
		/**
		*  Generate switch version dropdown button
		*/
		public static function generate_version_toggle() {
			if ( self::$branch == 'svn' ) {
				$class = "switch-versions";
				$switch_to = "git";
				$title = __( 'Switch to lastest development release. Warning this should not be performed on a live site.' , 'inbound-pro' );
				$button_text = __( 'Switch to latest git' , 'inbound-pro' );
			} else {
				$class = "switch-versions";
				$switch_to = "svn";
				$title = __( 'Switch to latest stable release.' , 'inbound-pro' );
				$button_text = __( 'Switch to latest svn' , 'inbound-pro' );
			}
			return '<a href="#" class="'.$class.'" id="'.self::$plugin.'-toggle" data-branch="'.$switch_to.'" data-plugin="'.self::$plugin.'" title="'.$title.'">'. $button_text .'</a> <div class="spinner" id="spinner-'.self::$plugin.'-toggle"></span></div>';
		}
		
		/**
		*  Enqueues JS
		*/
		public static function enqueue_admin_scripts() {
			
			$screen = get_current_screen();
			
			if ( $screen->base != 'plugins' ) {
				return;
			}
			
			
		}
		
		/**
		*  Print JS Listners for Switching Plugins
		*/
		public static function print_js_css() {
			
			if ( ! function_exists( 'get_current_screen' ) ) {
				return;
			}
			
			$screen = get_current_screen();
			
			if ( $screen->base != 'plugins' ) {
				return;
			}

			
			?>
			<script>
			jQuery( 'document' ).ready( function() {
				
				jQuery( '.switch-versions' ).on( 'click' , function() {
					
					
					/* get download url */
					var branch = jQuery( '#' + this.id ).data( 'branch' );
					var plugin = jQuery( '#' + this.id ).data( 'plugin' );

					var result = confirm("<?php _e('Switching branches on a live site should be avoided. Are you sure you would like to switch filesets?' , 'inbound-pro' ); ?>");
					
					if (!result) {
						return;
					}
					
					
					/* toggle spinner */
					jQuery('#spinner-'+this.id).show();
					
					/* run ajax to replace plugin */
					jQuery.ajax({
						type: "POST",
						url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
						data: {
							action: 'inbound_toggle_branch',
							plugin: plugin,
							branch : branch
						},
						dataType: 'html',
						timeout: 200000,
						success: function (response) {
							if ( response == 1 ) {
								
								/* toggle spinner */
								jQuery('#spinner-'+this.id).show();
					
								/* reload page */
								location.reload();
								
							} else {
								alert( response );								
								
								/* toggle spinner */
								jQuery('#spinner-'+this.id).show();
							}
						},
						error: function(request, status, err) {
							alert(status);
						}
					});


				});
			
			});
			</script>
			<style>
			.row-actions .version-dropdown {
				font-size:10px;
				height:19px;
			}
			
			body .toggle .switch-versions {
				
			}
			</style>
			<?php
		
		}
		
		/**
		*  Ajax listener to delete current plugin and replace it's files with selected branch.
		*/
		public static function toggle_branch() {
			if ( ! current_user_can('delete_plugins') ) {
				wp_die(__('You do not have sufficient permissions to delete plugins for this site.'));
			}

			/* load plugins */
			self::load_static_vars();
			
			/* load pclzip */
			include_once( ABSPATH . '/wp-admin/includes/class-pclzip.php');

			self::$branch = $_POST['branch'];
			self::$plugin = $_POST['plugin'];
			$branch_url = self::$plugins[ self::$plugin ][ self::$branch ];
			
			
			/* get plugin path */
			$plugin_path = WP_PLUGIN_DIR . '/' . self::$plugin;

			/* get files in plugin directory currently */
			self::delete_plugin_folder( $plugin_path );

			/* create temp file */
			$temp_file = tempnam('/tmp', 'TEMPPLUGIN' );

			/* get zip file contents from svn */
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $branch_url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
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
			if (self::$branch == 'git') {
				$result = $archive->extract( PCLZIP_OPT_REMOVE_PATH, self::$plugin.'-master' , PCLZIP_OPT_PATH, $plugin_path , PCLZIP_OPT_REPLACE_NEWER );
			} else {
				$result = $archive->extract( PCLZIP_OPT_PATH, WP_PLUGIN_DIR , PCLZIP_OPT_REPLACE_NEWER );
			}
			
			if ($result == 0) {
				die("Error : ".$archive->errorInfo(true));
			}

			/* delete templ file */
			unlink($temp_file);

			/* set current branch into memory */
			self::set_current_branch( self::$branch );
			
			header('HTTP/1.1 200 OK');
			echo 1;
			exit;
		}

		/**
		*	deletes plugin folder
		*/
		public static function delete_plugin_folder($dirPath) {
			if (is_dir($dirPath)) {
				$objects = scandir($dirPath);
				foreach ($objects as $object) {
					if ($object != "." && $object !="..") {
						if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
							self::delete_plugin_folder($dirPath . DIRECTORY_SEPARATOR . $object);
						} else {
							unlink($dirPath . DIRECTORY_SEPARATOR . $object);
						}
					}
				}
				reset($objects);
				rmdir($dirPath);
			}

		}
	}

	$GLOBALS['Inbound_Branching'] = new Inbound_Branching;
}
