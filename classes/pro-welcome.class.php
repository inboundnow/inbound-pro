<?php
/**
*
* Pro welcome screen
*
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Inbound_Now_Pro_Welcome {

	public $minimum_capability = 'manage_options';

	/* Get things started */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
	}

	/* Register the Dashboard Pages */
	public function admin_menus() {
		// About Page
		add_dashboard_page(
			__( 'Inbound Now', 'inbound-now'),
			__( 'Inbound Now', 'inbound-now'),
			$this->minimum_capability,
			'inbound-now-welcome',
			array( $this, 'inbound_pro_start_screen' )
		);
	}

	/* Render About InboundNow Nav */
	static function render_nav_menu() {
		$current_view = $_GET['page'];
		$page_array = array('inbound-now-welcome' => "Quick Start Guide",
							'available-tools' => "Configure Your Settings",
							'inbound-developers' => 'Developers & Designers'
							);
		echo '<h2 class="nav-tab-wrapper" style="margin-left: -40px; padding-left: 40px;">';
		foreach ($page_array as $key => $value) {
			$active = ($current_view === $key) ? 'nav-tab-active' : '';
		echo '<a class="nav-tab '.$active.'" href="'.esc_url(admin_url(add_query_arg( array( 'page' => $key ), 'index.php' ) ) ).'">';
		echo $value;
		echo '</a>';
		}
		echo '</h2>';
	}
	/* Render About Screen */
	public function inbound_pro_start_screen() {
		list( $display_version ) = explode( '-', INBOUND_NOW_CURRENT_VERSION );
		$Recommended = "";
		$cta_install = "";
		$leads_install = "";
		$rec_end = "";
		if (!is_plugin_active('cta/wordpress-cta.php')) {
			$Recommended = "<div id='recommended-other-plugins'><h4>Recommended Other Plugins</h4>";
		 	$cta_install = "<a href='".esc_url( admin_url( add_query_arg( array( 'tab' => 'search', 's' => 'wordpress+call+to+action' ), 'plugin-install.php' ) ) )."'><img src='".LANDINGPAGES_URLPATH."images/cta-install.png'></a>";
		 	$rec_end = "</div>";

		}
		if (!is_plugin_active('leads/wordpress-leads.php')) {
			$Recommended = "<div id='recommended-other-plugins'><h4>Install Recommended Plugins</h4>";
			$leads_install = "<a href='".esc_url( admin_url( add_query_arg( array( 'tab' => 'search', 's' => 'WordPress%20Leads' ), 'plugin-install.php' ) ) )."'><img src='".LANDINGPAGES_URLPATH."images/leads-install.png'></a>";
			$rec_end = "</div>";

		}
		?>
		<style type="text/css">
		.about-text {
		font-size: 19px;
			}</style>
		<div class="wrap about-wrap" id="inbound-plugins">
			<h1><?php printf( __( 'Welcome to Inbound Now Pro %s', 'inbound-now'), $display_version ); ?></h1>
			<div class="about-text" id="in-sub-head"><?php printf( __( 'Thank you for updating to the latest version! WordPress Landing Pages %s is help you convert more leads!', 'inbound-now'), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'inbound-now'), $display_version ); ?></div>

			<?php self::render_nav_menu();?>
			<div class="row">
			<div class='grid two-third'>
			<div id="creating-landing-page">
				<h4><?php _e( 'Create Your First Landing Page', 'inbound-now');?></h4>
				<iframe width="640" height="360" src="//www.youtube.com/embed/-VuaBUc_yfk" frameborder="0" allowfullscreen></iframe>
			</div>
			<div id="creating-landing-page">
				<h4><?php _e( 'How to Create Forms', 'inbound-now');?></h4>
				<iframe width="640" height="360" src="//www.youtube.com/embed/Y4M_g9wkRXw" frameborder="0" allowfullscreen></iframe>
			</div>
			<div id="creating-landing-page">
			<h4><?php _e( 'Creating Landing Pages with your Current Theme Template', 'inbound-now');?></h4>
			<iframe width="640" height="360" src="//www.youtube.com/embed/pQzmx4ooL1M?list=UUCqiE-EcfDjaKGXSxtegcyg" frameborder="0" allowfullscreen></iframe>
			</div>
			<div id="creating-landing-page">
			<!-- Creating Landing Page with current active Theme -->
			</div>
			<div id="creating-landing-page">

			</div>

			</div>
			<div class='grid one-third'>

				<?php echo $Recommended . $leads_install . $cta_install . $rec_end; ?>

			<h4>Quick Links</h4>
			<ul id="inbound-setting-links">
				<li>
					<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'landing-page', 'page' => 'lp_global_settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to WordPress Landing Pages Settings', 'inbound-now'); ?></a>
				</li>
				<li>
					<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'landing-page', 'page' => 'lp_manage_templates' ), 'edit.php' ) ) ); ?>"><?php _e( 'Manage Your Landing Page Templates', 'inbound-now'); ?></a>
				</li>
				<li>
					<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'landing-page' ), 'post-new.php' ) ) ); ?>"><?php _e( 'Create New Landing Page', 'inbound-now'); ?></a>
				</li>
				<li>
					<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'landing-page', 'page' => 'inbound-forms-redirect' ), 'edit.php' ) ) ); ?>"><?php _e( 'Create Landing Page Forms', 'inbound-now'); ?></a>
				</li>
			</ul>
			</div>
			</div> <!-- end row -->
		</div>
		<?php
	}

}
new Inbound_Now_Pro_Welcome();
