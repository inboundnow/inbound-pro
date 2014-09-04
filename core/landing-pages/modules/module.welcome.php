<?php
/**
 * Weclome Page Class
 *
 * @package     Landing Pages
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 * Forked from pippin's WordPress Landing Pages! https://easydigitaldownloads.com/
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * LandingPages_Welcome Class
 *
 * A general class for About and Credits page.
 *
 * @since 1.4
 */
class LandingPages_Welcome {

	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Get things started
	 *
	 * @since 1.4
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	/**
	 * Register the Dashboard Pages which are later hidden but these pages
	 * are used to render the Welcome and Credits pages.
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_menus() {
		// About Page
		add_dashboard_page(
			__( 'Welcome to WordPress Landing Pages', 'landing-pages'),
			__( 'Welcome to WordPress Landing Pages', 'landing-pages'),
			$this->minimum_capability,
			'lp-quick-start',
			array( $this, 'quick_start_screen' )
		);
		// About InboundNow Page
		add_dashboard_page(
			__( 'About the Inbound Now Marketing Platform', 'landing-pages'),
			__( 'About the Inbound Now Marketing Platform', 'landing-pages'),
			$this->minimum_capability,
			'about-inboundnow',
			array( $this, 'about_inboundnow_screen' )
		);
		// Developer Page
		add_dashboard_page(
			__( 'Developers and Designers', 'landing-pages'),
			__( 'Developers and Designers', 'landing-pages'),
			$this->minimum_capability,
			'inbound-developers',
			array( $this, 'dev_designer_screen' )
		);

	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'lp-quick-start' );
		remove_submenu_page( 'index.php', 'about-inboundnow' );
		remove_submenu_page( 'index.php', 'inbound-developers' );

		// Badge for welcome page
		$badge_url = WP_PLUGIN_DIR . 'assets/images/edd-badge.png';
		?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/
		.edd-badge {
			padding-top: 130px;
			height: 52px;
			width: 185px;
			color: #666;
			font-weight: bold;
			font-size: 14px;
			text-align: center;
			text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8);
			margin: 0 -5px;
			background: url('<?php echo $badge_url; ?>') no-repeat;
		}

		.about-wrap .edd-badge {
			position: absolute;
			top: 0;
			right: 0;
		}

		.edd-welcome-screenshots {
			float: right;
			margin-left: 10px!important;
		}
		#inbound-plugins .grid.one-third:first-child{
			margin-left: 0px;
			padding-left: 0px;
		}
		#inbound-plugins .grid.one-third {
		width: 31.333333%;
		}
		#inbound-plugins .grid.two-third {
		width: 64.333333%;
		}
		#inbound-plugins h3 {
			padding-top: 0px;
			font-size: 22px;
			margin-top: 0px;
			text-align: center;
		}
		#inbound-plugins .dl-button {
			text-align: center;

		}
		.inbound-button-holder {

		}

		#inbound-plugins .in-button {
		background: #94BA65;
		border: 1px solid rgba(0, 0, 0, 0.15);
		-webkit-border-radius: 2px;
		-moz-border-radius: 2px;
		border-radius: 2px;
		-webkit-box-shadow: 0 2px 3px rgba(0, 0, 0, 0.15),inset 1px 1px 1px rgba(255, 255, 255, 0.2);
		-moz-box-shadow: 0 2px 3px rgba(0,0,0,.15),inset 1px 1px 1px rgba(255,255,255,.2);
		box-shadow: 0 2px 3px rgba(0, 0, 0, 0.15),inset 1px 1px 1px rgba(255, 255, 255, 0.2);
		color: #FFF;
		cursor: pointer;
		display: inline-block;
		font-family: inherit;
		font-size: 20px;
		font-weight: 500;
		text-align: center;
		padding: 8px 20px;
		text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.15);
		text-decoration: none;
		}
		#inbound-plugins .content-box.default p:first-child {
			margin-top: 10px;
		}
		#inbound-plugins .grid {
		float: left;
		min-height: 1px;
		padding-left: 10px;
		padding-right: 10px;}
		#inbound-plugins .content-box {
		background: #F2F2F2 ;
		border: 1px solid #EBEBEA;
		-webkit-box-shadow: inset 1px 1px 1px rgba(255, 255, 255, 0.5);
		-moz-box-shadow: inset 1px 1px 1px rgba(255,255,255,0.5);
		box-shadow: inset 1px 1px 1px rgba(255, 255, 255, 0.5);
		margin: 0px 0px 20px;
		padding: 20px 15px 20px;
		position: relative;
		text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.5);
		min-height: 245px;
		}
		#in-sub-head {
			margin: 0px 135px 0px 0;
		}
		.grid.one-third p:last-of-type {
			padding-bottom: 24px;
		}
		.grid.one-third:nth-last-of-type(1) p:last-of-type  {
			padding-bottom: 0px;
		}
		#inbound-plugins .grid.one-third:nth-last-of-type(1) {
			padding-right: 0px;
		}
		#recommended-other-plugins {
			background: #F5F5F5;
			background-image: -webkit-gradient(linear,left bottom,left top,from(#F5F5F5),to(#F9F9F9));
			background-image: -webkit-linear-gradient(bottom,#F5F5F5,#F9F9F9);
			background-image: -moz-linear-gradient(bottom,#f5f5f5,#f9f9f9);
			background-image: -o-linear-gradient(bottom,#f5f5f5,#f9f9f9);
			background-image: linear-gradient(to top,#F5F5F5,#F9F9F9);
			border-color: #DFDFDF;
			-webkit-box-shadow: inset 0 1px 0 #FFF;
			box-shadow: inset 0 1px 0 #FFF;
			-webkit-border-radius: 3px;
			border-radius: 3px;
			margin-bottom: 20px;
			margin-top: 20px;
			padding: 0;
			border-width: 1px;
			border-style: solid;
			line-height: 1;
			padding-bottom: 10px;
		}
		#recommended-other-plugins img {
			padding-left: 10px;
			margin-top: 10px;
			width: 93%;
		}
		#recommended-other-plugins h4{
			margin-top: 0px;
			padding-top: 10px;
			padding-left: 10px;
			margin-bottom: 5px;
			font-size: 1.2em;

			text-decoration: underline;
		}
		#inbound-setting-links {
			list-style: disc;
			padding-left: 25px;
		}
		#inbound-contribute p {
			font-size: 31px; margin-top: 30px; line-height: 30px;margin-bottom: 15px;
		}
		#inbound-contribute a { color:#000; text-decoration: none; -webkit-transition:color .25s ease-in;
		   -moz-transition:color .25s ease-in;
		   -o-transition:color .25s ease-in;
		   transition:color .25s ease-in;}
		#inbound-contribute a:hover { color:#21759B;
			-webkit-transition:color .25s ease-in;
					   -moz-transition:color .25s ease-in;
					   -o-transition:color .25s ease-in;
					   transition:color .25s ease-in;  }
		/*]]>*/
		</style>
		<?php
	}
	/**
	 * Render About InboundNow Nav
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	static function render_nav_menu() {
		$current_view = $_GET['page'];
		$page_array = array('lp-quick-start' => "Quick Start Guide",
							'about-inboundnow' => "About the Platform",
							'inbound-developers' => 'Developers & Designers'
							);
		echo '<h2 class="nav-tab-wrapper" style="margin-left: -40px; padding-left: 40px;">';
		foreach ($page_array as $key => $value) {
			$active = ($current_view === $key) ? 'nav-tab-active' : '';

		echo '<a class="nav-tab '.$active.'" href="'.esc_url( admin_url( add_query_arg( array( 'page' => $key ), 'index.php' ) ) ).'">';
		echo _e( $value, 'landing-pages');
		echo '</a>';

		}
		echo '</h2>';


	}
	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function quick_start_screen() {
		list( $display_version ) = explode( '-', LANDINGPAGES_CURRENT_VERSION );
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
			<h1><?php printf( __( 'Welcome to WordPress Landing Pages %s', 'landing-pages'), $display_version ); ?></h1>
			<div class="about-text" id="in-sub-head"><?php printf( __( 'Thank you for updating to the latest version! WordPress Landing Pages %s is help you convert more leads!', 'landing-pages'), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'landing-pages'), $display_version ); ?></div>

			<?php self::render_nav_menu();?>
			<div class="row">
			<div class='grid two-third'>
			<div id="creating-landing-page">
				<h4><?php _e( 'Create Your First Landing Page', 'landing-pages');?></h4>
				<iframe width="640" height="360" src="//www.youtube.com/embed/-VuaBUc_yfk" frameborder="0" allowfullscreen></iframe>
			</div>
			<div id="creating-landing-page">
				<h4><?php _e( 'How to Create Forms', 'landing-pages');?></h4>
				<iframe width="640" height="360" src="//www.youtube.com/embed/Y4M_g9wkRXw" frameborder="0" allowfullscreen></iframe>
			</div>
			<div id="creating-landing-page">
			<h4><?php _e( 'Creating Landing Pages with your Current Theme Template', 'landing-pages');?></h4>
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
					<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'landing-page', 'page' => 'lp_global_settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to WordPress Landing Pages Settings', 'landing-pages'); ?></a>
				</li>
				<li>
					<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'landing-page', 'page' => 'lp_manage_templates' ), 'edit.php' ) ) ); ?>"><?php _e( 'Manage Your Landing Page Templates', 'landing-pages'); ?></a>
				</li>
				<li>
					<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'landing-page' ), 'post-new.php' ) ) ); ?>"><?php _e( 'Create New Landing Page', 'landing-pages'); ?></a>
				</li>
				<li>
					<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'landing-page', 'page' => 'inbound-forms-redirect' ), 'edit.php' ) ) ); ?>"><?php _e( 'Create Landing Page Forms', 'landing-pages'); ?></a>
				</li>
			</ul>
			</div>
			</div> <!-- end row -->




		</div>
		<?php
	}
	/**
	 * Render About InboundNow Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function about_inboundnow_screen() {
		list( $display_version ) = explode( '-', LANDINGPAGES_CURRENT_VERSION );
		$leads_active_class = "Download For Free";
		$lp_active_class = "Download For Free";
		$cta_active_class = "Download For Free";
		$active_class = "";
		$leadactive = "";
		$ctaactive = "";
		if (is_plugin_active('landing-pages/landing-pages.php')) {
		  $lp_active_class = "Already Installed!";
		  $lpactive = " plugin-active";
		}
		if (is_plugin_active('cta/wordpress-cta.php')) {
		 	$cta_active_class = "Already Installed!";
		 	$ctaactive = " plugin-active";
		}
		if (is_plugin_active('leads/wordpress-leads.php')) {
			$leads_active_class = "Already Installed!";
			$leadactive = " plugin-active";
		}

		?>
		<style type="text/css">
		#inbound-plugins h4 {
			text-align: center;
			font-weight: 200;
			margin-bottom: 0px;
			margin-top: 0px;
		}
		.inbound-check {
		color: #58D37B;
		padding-right: 0px;
		padding-top: 5px;
		display: inline-block;
		clear: both;
		vertical-align: top;
		}
		#inbound-plugins .in-button.plugin-active {
		background: #B9B9B9;}
		.intro-p {
			display: inline-block;
			width: 96%;
			vertical-align: top;
			margin-top: 0px;
			padding-top: 0px;
			margin-right: -20px;
			line-height: 1.4em;
		}
		.circle-wrap {
			float: left;
			margin-right: 0px;
			width: 25px;
			height: 25px;
			margin-left: -5px;
			margin-right: 6px;
			margin-top: 5px;
			border-radius: 50%;
			background: linear-gradient(to bottom, #FFF 0%, #F7F7F7 100%);
			box-shadow: 0 0 3px 0 rgba(0, 0, 0, 0.3), inset 0 2px 0 -1px rgba(98, 98, 98, 0.3), 0 3px 7px -3px rgba(0, 0, 0, 0.4);
			color: #ADADAD;
			text-align: center;
			font-size: 18px;
			line-height: 17px;
			cursor: default;
			transition: color .3s ease;
		}
		.inbound-button-holder {
			text-align: center;
		}
		</style>

		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Turbo Charge Your Marketing', 'landing-pages'), $display_version ); ?></h1>
			<div class="about-text" id="in-sub-head"><?php printf( __( 'WordPress Landing Pages is only one piece of Inbound Now\'s Marketing Platform', 'landing-pages'), $display_version ); ?></div>

			<?php self::render_nav_menu();?>


			<p class="about-description"><?php _e( 'To have an effective marketing strategy for your site you need to incorporate a comprehensive conversion strategy to capture visitors attention, get them clicking, and convert them on a web form or landing page.', 'landing-pages'); ?></p>

		<div class="row" id="inbound-plugins">
		    <div class="grid one-third">
		        <div class="content-box default">
		            <h4><?php _e('Capture visitor attention with' , 'landing-pages'); ?></h4>

		            <h3 style="text-align: center;">WordPress Calls to Action</h3>

		            <div class='circle-wrap'>
		                <span class="inbound-check">✔</span>
		            </div>

		            <p class="intro-p"><?php _e('<b>Convert more website traffic</b> with visually
		            appealing calls to action' , 'landing-pages'); ?></p>

		            <div class='circle-wrap'>
		                <span class="inbound-check">✔</span>
		            </div>

		            <p class="intro-p"><?php _e('A/B test your marketing tactics and <b>improve your
		            sites conversion rates' , 'landing-pages'); ?></b></p>

		            <div class="inbound-button-holder">
		                <div class='dl-button'>
		                    <a class="in-button<?php echo $ctaactive;?>" href=
		                    "http://wordpress.org/plugins/cta/"><i class=
		                    "icon-download"></i><?php echo $cta_active_class;?></a>
		                </div>
		            </div>
		        </div>
		    </div>

		    <div class="grid one-third">
		        <div class="content-box default">
		            <h4><?php _e( 'Convert website visitors with' , 'landing-pages'); ?></h4>

		            <h3>WordPress Landing Pages</h3>

		            <div class='circle-wrap'>
		                <span class="inbound-check">✔</span>
		            </div>

		            <p class="intro-p"><?php _e('<b>Generate more web leads</b> with pages specifically designed for conversion' , 'landing-pages'); ?></p>

		            <div class='circle-wrap'>
		                <span class="inbound-check">✔</span>
		            </div>

		            <p class="intro-p"><?php _e('A/B Test Landing Page effectiveness for <b>continual optimization & improvement</b>' , 'landing-pages'); ?></p>

		            <div class="inbound-button-holder">
		                <div class='dl-button'>
		                    <a class="in-button<?php echo $lpactive;?>" href=
		                    "http://wordpress.org/plugins/landing-pages/"><i class=
		                    "icon-download"></i><?php echo $lp_active_class;?></a>
		                </div>
		            </div>
		        </div>
		    </div>

		    <div class="grid one-third">
		        <div class="content-box default">
		            <h4><?php _e('Followup &amp; Close the deal with' , 'landing-pages'); ?></h4>

		            <h3>WordPress Leads</h3>

		            <div class='circle-wrap'>
		                <span class="inbound-check">✔</span>
		            </div>

		            <p class="intro-p"><?php _e('Gather & leverage lead intelligence on
		            visitors to <b>close more deals</b>' , 'landing-pages'); ?></p>

		            <div class='circle-wrap'>
		                <span class="inbound-check">✔</span>
		            </div>

		            <p class="intro-p"><?php _e('Track page views, site conversions,
		            demographics, geolocation, social media data and more.' , 'landing-pages'); ?></p>

					<p class="intro-p"><?php _e('Know everything a lead has seen and done on your site before you contact them' , 'landing-pages'); ?></p>

		            <div class="inbound-button-holder">
		                <div class='dl-button'>
		                    <a class="in-button<?php echo $leadactive;?>" href=
		                    "http://wordpress.org/plugins/leads/"><i class=
		                    "icon-download"></i><?php echo $leads_active_class;?></a>
		                </div>
		            </div>
		        </div>
		    </div>
		</div>


		</div>
		<?php
	}

	/**
	 * Render Developers/Designer Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function dev_designer_screen() {
		list( $display_version ) = explode( '-', LANDINGPAGES_CURRENT_VERSION );
		?>
		<style type="text/css">
		#create-templates {
			text-decoration: none;
			text-align: center;
			font-size: 38px;
			padding: 38px;
			vertical-align: top;
			padding-top: 24px;
			padding-bottom: 46px;
			color: #21759B;
			}</style>
		<div class="wrap about-wrap" id="inbound-plugins">
			<h1><?php printf( __( 'Welcome to WordPress Landing Pages %s', 'landing-pages'), $display_version ); ?></h1>
			<div class="about-text" id="in-sub-head"><?php printf( __( 'Learn How to Build Custom Templates & Add Value to Your Clients', 'landing-pages'), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'landing-pages'), $display_version ); ?></div>

			<?php self::render_nav_menu();?>
			<div class="row">
			<div class='grid two-third'>
			<p class="about-description"><?php _e( 'WordPress Landing Pages was built as a platform to allow anyone to create and use their own landing page designs.', 'landing-pages'); ?></p>

			<p class="about-description"><?php _e( 'Infuse your designs with powerful A/B testing functionality and give clients the ability to edit options on the backend with ease.', 'landing-pages'); ?></p>

			<h1 style="text-decoration: none; text-align: center;"><a target="_blank" id="create-templates" class='button' href="http://docs.inboundnow.com/guide/creating-landing-page-templates/">Create Your Own Templates</a></h1>

			<p class="about-description">WordPress Landing Pages is third party extendable. We have a variety of <a href="http://docs.inboundnow.com/landing-pages/dev/core-hooks-filters/hooks">hooks</a>, actions, and filters to add your own functionality</p>
			</div>
		<div class='grid one-third' id="inbound-contribute" style="text-align: center;">
		<p class="about-description" style=""><a href="https://github.com/inboundnow/landing-pages" target="_blank"><b>Contribute Code</b> + <span style="font-size:21px"><b>Submit Feature Requests</b></span></a></p>
		<a href="https://github.com/inboundnow/landing-pages"  target="_blank"><img src="<?php echo LANDINGPAGES_URLPATH;?>images/github-help.jpg"></a>
		</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Sends user to the Welcome page on first activation of EDD as well as each
	 * time EDD is upgraded to a new version
	 *
	 * @access public
	 * @since 1.4
	 * @global $edd_options Array of all the EDD Options
	 * @return void
	 */
	public function welcome() {


		// Bail if no activation redirect
		if ( ! get_transient( '_landing_page_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_landing_page_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		wp_safe_redirect( admin_url( 'index.php?page=lp-quick-start' ) ); exit;
	}
}
new LandingPages_Welcome();
