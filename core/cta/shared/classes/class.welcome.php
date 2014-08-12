<?php
/**
 * Inbound Now Weclome Page Class
 *
 * @package     Landing Pages
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2014, David Wells
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 * Forked from pippin's https://easydigitaldownloads.com/
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
if(!defined('INBOUND_NOW_PATH')) { define('INBOUND_NOW_PATH', WP_PLUGIN_DIR . '/inbound-now-pro'); }
/**
 * Inbound_Now_Welcome Class
 *
 * A general class for About and Credits page.
 *
 * @since 1.4
 */

class Inbound_Now_Welcome {

	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_options';
	private $welcome_folder_exists = false;
	private $plugin_type = 'addon';

	/* Get things started */
	public function __construct($plugin_slug, $plugin_name, $type) {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );

		/* Auto set class vars. Works but is lazy coding
		$arguments = func_get_args();
        if(!empty($arguments)) {
            foreach($arguments[0] as $key => $property) {
                if(property_exists($this, $key)) {
                    $this->{$key} = $property;
                }
            }
        }
        Person(array('isFat' => true, 'canRunFast' => false))
        */
	    if (preg_match('/components/', $type)) {
	    	$this->plugin_type = 'pro'; // check if loaded from pro or from addon plugin
	    }

        $this->plugin_slug = $plugin_slug;
        $this->plugin_name = $plugin_name;

    	// Set Variable if welcome folder exists
    	$dir = INBOUND_NOW_PATH . '/components/'. $plugin_slug . '/welcome/';
    	$plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
		if(file_exists($dir)) {
			$this->welcome_folder_exists = true;
		} elseif (file_exists($plugin_dir)) {
			$this->welcome_folder_exists = true;
		}

	}

	/* Register the Dashboard Pages which are later hidden but these pages are used to render the Welcome pages. */
	public function admin_menus() {
		if ( !$this->welcome_folder_exists ) {
			return;
		}
		// Add menu page
		add_dashboard_page(
			__( 'Welcome to', $this->plugin_name),
			__( 'Welcome to', $this->plugin_name),
			$this->minimum_capability,
			$this->plugin_slug . '-welcome',
			array( $this, 'quick_start_screen' )
		);

	}

	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/* Hide Individual Dashboard Pages */
	public function admin_head() {
		$plugin = $this->plugin_slug;
		remove_submenu_page( 'index.php', $plugin . '-welcome' );
		// GET style.css from /welcome folder of target
	}
	/* Render About Welcome Nav */
	public function render_nav_menu() {
		$current_view = $_GET['page'];
		$plugin_slug = $this->plugin_slug;
		$plugin_name = $this->plugin_name;
		$page_array = array($plugin_slug .'-welcome' => "Welcome to " . $plugin_name);
		echo '<h2 class="nav-tab-wrapper" style="margin-left: -40px; padding-left: 40px;">';
		foreach ($page_array as $key => $value) {
			$active = ($current_view === $key) ? 'nav-tab-active' : '';

		echo '<a class="nav-tab '.$active.'" href="'.esc_url( admin_url( add_query_arg( array( 'page' => $key ), 'index.php' ) ) ).'">';
		echo _e( $value, 'inbound-now');
		echo '</a>';

		}
		echo '</h2>';


	}
	static function check_for_welcome() {

	}
	/* Render About Screen */
	public function quick_start_screen() {
		// If no /welcome folder exists exit class
		if ( !$this->welcome_folder_exists ) {
			return;
		}
		$display_version = '1.1.1.1.1'; // Parse main file for version #;
		$plugin_name = $this->plugin_name;
		$plugin_slug = $this->plugin_slug;
		$Recommended = "";
		$cta_install = "";
		$leads_install = "";
		$rec_end = "";
		//$test = get_transient('_inboundnow_zapier_activation_redirect', true, 30 );

		$current_view = $_GET['page'];
		if (function_exists( 'is_plugin_active' ) && is_plugin_active('inbound-now-pro/inbound-now-pro.php')) {
			//echo 'Pro on';
			$dir = INBOUND_NOW_PATH . '/components/'. $plugin_slug . '/welcome/';

		} else if (function_exists( 'is_plugin_active' ) && is_plugin_active($plugin_slug .'/'. $plugin_slug . '.php')) {
			//echo 'Pro off';
			$dir = WP_PLUGIN_DIR . '/' . $plugin_slug . '/welcome/';
		}

		if(file_exists($dir)) {
			//$contents = file_get_contents($file);
			$results = scandir($dir);
			//print_r($results);
			$contents = '';
			$nav_items = '<h2 class="nav-tab-wrapper" style="margin-left: -40px; padding-left: 40px;">';
			foreach ($results as $name) {
				if($name != '.' && $name != '..' && $name != 'index.php') {
					$clean_name = trim(substr($name, 2));
					$clean_id_name = trim(ucwords(str_replace(array('.php', "_"), "-", $clean_name)));
					$clean_tab_name = trim(ucwords(str_replace(array('.php', '-', "_"), " ", $clean_name)));
					$active = ($current_view === $name) ? 'nav-tab-active' : '';
					$nav_items .= '<a class="nav-tab '.$active.'" id="tab-'.$clean_id_name.'">';
					$nav_items .= __( $clean_tab_name, 'inbound-now');
					$nav_items .= '</a>';
					$contents .= '<div id="content-'.$clean_id_name.'">';
					$contents .= file_get_contents($dir . $name);
					$contents .= '</div>';
				}
			}
			$nav_items .= '<h2>';
		}

		?>

		<div class="wrap about-wrap" id="inbound-plugins">
			<h1><?php printf( __( 'Welcome to '.$plugin_name.' %s', 'inbound-now'), $display_version ); ?></h1>
			<div class="about-text" id="in-sub-head"><?php printf( __( 'Thank you for updating to the latest version! '.$plugin_name.' %s is help you customize your site!', 'inbound-now'), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'inbound-now'), $display_version ); ?></div>

			<?php echo $nav_items; ?>
			<div id="inbound-welcome-wrapper">
			<?php echo $contents; ?>
			</div>
		</div>
		<?php
	}

	/* Sends user to the Welcome page on first activation of plugin as well as each time plugin is upgraded to a new version */
	public function welcome() {
		$slug = self::get_plugin_slug();
		$transient = "_" . str_replace('-', "_", $slug) . '_activation_redirect';

		// Bail if no activation redirect
		if ( ! get_transient( $transient ) )
			return;

		// Delete the redirect transient
		delete_transient( $transient );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		if ($this->plugin_type != 'pro') {
		   wp_safe_redirect( admin_url( 'index.php?page='.$slug . '-welcome' ) ); exit;
		} else {
		   wp_safe_redirect( admin_url( 'index.php?page=inbound-now-welcome' ) ); exit;
		}

	}
}