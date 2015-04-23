<?php
/**
*
* Pro welcome screen
*
*/

class Inbound_Pro_Settings {
	static $tab; /* placeholder for page currently opened */
	static $settings_fields; /* configuration dataset */
	static $settings_values; /* configuration dataset */

	/**
	*	Load hooks and listners
	*/
	public static function init() {
		self::$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'inbound-pro-setup';
		self::activation_redirect();
		self::add_hooks();
	}


	/**
	*	Loads hooks and filters
	*/
	public static function add_hooks() {

		/* enqueue js and css */
		add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_scripts' ) );

		/* add ajax listener for setting saves */
		add_action( 'wp_ajax_inbound_pro_update_setting' , array( __CLASS__ , 'ajax_update_settings' ) );

		/* add ajax listener for custom fields setting saves */
		add_action( 'wp_ajax_inbound_pro_update_custom_fields' , array( __CLASS__ , 'ajax_update_custom_fields' ) );

		/* add ajax listener for IP Addresses setting saves */
		add_action( 'wp_ajax_inbound_pro_update_ip_addresses' , array( __CLASS__ , 'ajax_update_ip_addresses' ) );

	}

	/**
	*	Enqueue scripts & stylesheets
	*/
	public static function enqueue_scripts() {

		$screen = get_current_screen();

		/* Load assets for inbound pro page */
		if (isset($screen) && $screen->base != 'toplevel_page_inbound-pro' ){
			return;
		}

		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js', array('jquery'), '1.8.6');
		wp_enqueue_script('modernizr');
		wp_enqueue_script('underscore');
		add_thickbox();

		/* load shuffle.js */
		wp_enqueue_script('shuffle', INBOUND_PRO_URLPATH . 'assets/libraries/Shuffle/jquery.shuffle.modernizr.min.js' , array( 'jquery') );

		/* load custom CSS & JS for inbound pro welcome */
		wp_enqueue_style('inbound-settings', INBOUND_PRO_URLPATH . 'assets/css/admin/settings.css');
		wp_enqueue_script('inbound-settings', INBOUND_PRO_URLPATH . 'assets/js/admin/settings.js' );

		/* load Ink */
		wp_enqueue_script('Ink-holder', INBOUND_PRO_URLPATH . 'assets/libraries/Ink/js/holder.js' );
		wp_enqueue_script('Ink-all', INBOUND_PRO_URLPATH . 'assets/libraries/Ink/js/ink-all.min.js' );
		wp_enqueue_script('Ink-autoload', INBOUND_PRO_URLPATH . 'assets/libraries/Ink/js/autoload.min.js' );
		wp_enqueue_style('Ink', INBOUND_PRO_URLPATH . 'assets/libraries/Ink/css/ink-flex.min.css');

		/* load fontawesome */
		wp_enqueue_style('fontawesome', INBOUND_PRO_URLPATH . 'assets/libraries/FontAwesome/css/font-awesome.min.css');
	}

	/**
	*	Listens for the _inbound_pro_welcome transient and if it exists then redirect to the welcome page
	*/
	public static function activation_redirect() {

		if ( get_transient('_inbound_pro_welcome') ) {
			$redirect = admin_url('admin.php?page=inbound-pro&tab=inbound-pro-welcome');
			header('Location: ' . $redirect);
			delete_transient( '_inbound_pro_welcome' );
			exit;
		}
	}

	/**
	*  Setup Core Settings
	*/
	public static function extend_settings() {

		self::$settings_fields = array(
			'inbound-pro-setup' => array(
				/* add license key group to setup page */
				array(
					'group_name' => 'license-key',
					'keywords' => __('license key' , 'inbound-pro'),
					'fields' => array (
						array (
							'id'	=> 'license-key',
							'type'	=> 'license-key',
							'default'	=> '',
							'placeholder'	=> __( 'Enter license key here' , 'inbound-pro' ),
							'options' => null,
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
					),
				),
				/* add custom lead fields field group to setup page */
				array(
					'group_name' => 'leads-custom-fields',
					'keywords' => __('leads, field mapping, custom fields' , 'inbound-pro'),
					'fields' => array (
						array (
							'id'	=> 'leads-custom-fields',
							'type'	=> 'custom-fields-repeater',
							'default'	=> '',
							'placeholder'	=> null,
							'options' => null,
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
					),
				),
				/* add Analytics exclusion options */
				array(
					'group_name' => 'inbound-analytics-rules',
					'keywords' => __('analytics,tracking,ipaddress,ip address,admin tracking' , 'inbound-pro'),
					'fields' => array (
						array (
							'id'	=> 'exclude-admin',
							'type'	=> 'header',
							'default'	=> __( 'Analytics' , 'inbound-pro' ),
							'placeholder'	=> null,
							'options' => false,
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
						array (
							'id'	=> 'exclude-admin',
							'type'	=> 'radio',
							'label'	=> __( 'Disable Admin Tracking' , 'inbound-pro' ),
							'description'	=> __( 'Toggle this to on to prevent impression/conversion tracking for logged in administrators.' , 'inbound-pro' ),
							'default'	=> 'off',
							'placeholder'	=> null,
							'options' => array(
								'off' => __( 'Off' , 'inbound-pro' ),
								'on' => __( 'On' , 'inbound-pro' ),
							),
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
						array (
							'id'	=> 'exclude-ip-addresses',
							'type'	=> 'ip-address-repeater',
							'default'	=> '',
							'placeholder'	=> null,
							'options' => null,
							'hidden' => false,
							'reveal' => array(
								'selector' => null ,
								'value' => null
							)
						),
					),
				)
			)
		);

		self::$settings_fields = apply_filters( 'inbound_settings/extend' , self::$settings_fields );

	}

	/**
	*	Render Pro Display
	*/
	public static function display() {

		?>

		<div class="ink-grid">


			<?php
			self::display_nav_menu();

			echo '<section class="column-group gutters article">';

			switch ( self::$tab ) {
				case 'inbound-pro-welcome':
					self::display_welcome();
					BREAK;
				case 'inbound-pro-settings':
					self::display_settings();
					self::display_sidebar();
					BREAK;
				case 'inbound-pro-setup':
					self::display_setup();
					self::display_sidebar();
					BREAK;
			}

			self::display_footer();
			?>
		</div>
		<?php
	}

	/**
	*  Display Pro Welcome Screen
	*/
	public static function display_welcome() {
		self::extend_settings();
		self::$settings_values = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );

		?>
		<div class="xlarge-70 large-70 medium-60 small-100 tiny-100">
			<?php _e(' Wlecome to Inbound Pro' , 'inbound-pro' ); ?>
		<?php
			//self::render_fields( 'inbound-pro-welcome' );
		?>
		</div>
		<?php
	}

	/**
	*	Display Inbound Pro Setup page
	*/
	public static function display_setup() {
		self::extend_settings();
		self::$settings_values = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
		//print_r(self::$settings_values);
		?>
		<div class="xlarge-80 large-80 medium-80 small-100 tiny-100">
		<?php
			self::render_fields( 'inbound-pro-setup' );
		?>
		</div>
		<?php
	}

	/**
	*	Display Inbound Pro Settings page
	*/
	public static function display_settings() {
		self::extend_settings();
		self::$settings_values = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
		?>
		<div class="xlarge-80 large-80 medium-70 small-100 tiny-100">
		<?php
			self::render_fields( 'inbound-pro-settings' );
		?>
		</div>
		<?php
	}


	/**
	*	Display Sidebar
	*/
	public static function display_sidebar() {

		$blogs = Inbound_API_Wrapper::get_blog_posts();

		?>
		<section class="xlarge-20 large-20 medium-30 small-100 tiny-100">

				<ul class="unstyled">
					<!--- Show blog posts --->
					<?php
					$i=0;
					$limit = 20;
					foreach ($blogs as $item) {
						if ($i>5) {
							break;
						}

						$excerpt = explode('The post' ,  $item['description']);
						$excerpt = $excerpt[0];

						?>
						<div class="all-80 small-50 tiny-50">
							<h6 class='sidebar-h6'><?php echo $item['title']; ?></h6>
							<!--<img class="half-bottom-space" src="holder.js/1200x600/auto/ink" alt="">-->
							<p><a href='<?php echo $item['guid']; ?>' target='_blank'><?php _e( 'Read more &#8594;' , 'inbound-pro'); ?></a></p>
						</div>
						<?php
						$i++;
					}
					?>
				</ul>
				<hr>
				<h2 style='font-size:12px;'>
				</h2>
				<iframe src='//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Finboundnow&amp;width=234&amp;height=65&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=false&amp;appId=364256913591848' scrolling='no' frameborder='0' style='border:none; overflow:hidden; width:234px; height:65px;' allowTransparency='true'>
				</iframe>
			</section>
		</section>
		<?php
	}

	/**
	*	Display Footer
	*/
	public static function display_footer() {
		$docs = Inbound_API_Wrapper::get_docs();
		if (!$docs) {
			$docs = array();
		}
		?>

		<table>
			<tr>
				<td class='footer-left' style='vertical-align:top;'>
					<section class="column-group gutters">
						<!--- Show Twitter Timeline --->
						<div class="xlarge-100 large-100 all-100">
							<a class="twitter-timeline" href="https://twitter.com/InboundNow" data-widget-id="577529141597216768"><?php _e('Tweets by @InboundNow' , 'inbound-pro'); ?></a>
							<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
						</div>
					</section>
				</td>
				<td class='footer-right' style='vertical-align:top;'>
					<h2 class='footer-right-h2'><?php _e('Latest Inbound Now Docs:' , 'inbound-pro' ); ?></h2>
					<section class="column-group gutters">
						<!--- Show docs --->
						<?php
						$i=0;
						$limit = 8;
						foreach ($docs as $item) {
							if ($i>5) {
								break;
							}

							$excerpt = explode('The post' ,  $item['description']);
							$excerpt = $excerpt[0];
							$excerpt = str_replace( '<a ' , '<a target="_blank" ' , $excerpt );

							?>
							<div class="all-50 large-30 small-50 tiny-50">
								<h4><?php echo $item['title']; ?></h4>
								<!--<img class="half-bottom-space" src="holder.js/1200x600/auto/ink" alt="">-->
								<p><?php echo $excerpt; ?></p>
							</div>
							<?php
							$i++;
						}
						?>
					</section>
				</td>
			</tr>
		</table>
		<footer class="clearfix pro-footer">
            <div class="ink-grid">
                <ul class="unstyled inline half-vertical-space">
                    <li class="active"><a href="http://www.inboundnow.com" target='_blank'><?php _e( 'Inbound Now' , 'inbound-pro' ); ?></a></li>
                    <li class="active"><a href="http://www.twitter.com/inboundnow" target='_blank'><?php _e( 'Twitter' , 'inbound-pro' ); ?></a></li>
                    <li class="active"><a href="http://www.github.com/inboundnow" target='_blank'><?php _e( 'GitHub' , 'inbound-pro' ); ?></a></li>
                    <li class="active"><a href="http://support.inboundnow.com" target='_blank'><?php _e( 'Support' , 'inbound-pro' ); ?></a></li>
                    <li class="active"><a href="http://docs.inboundnow.com" target='_blank'><?php _e( 'Documentation' , 'inbound-pro' ); ?></a></li>
                    <li class="active"><a href="http://www.inboundnow.com/translate-inbound-now/" target='_blank'><?php _e( 'Translations' , 'inbound-pro' ); ?></a></li>
                </ul>
            </div>
        </footer>
		<?php
	}

	/**
	*	Render About InboundNow Nav
	*/
	static function display_nav_menu() {

		$pages_array = array(
			'inbound-pro-setup' => __( 'Core Settings' , 'inbound-pro' ),
			'inbound-pro-settings' => __( 'Extension Settings' , 'inbound-pro' ),
			'inbound-pro-welcome' => __( 'Quick Start' , 'inbound-pro' )
		);

		$pages_array = apply_filters( 'inbound_pro_nav' , $pages_array );

		echo '<header class="vertical-space">';
		echo '	<h1><img src="'. INBOUND_PRO_URLPATH . 'assets/images/logos/inbound-now-logo.png" style="width:262px;" title="' . __( 'Inbound Now Professional Suite' , 'inbound-pro' ) .'"></h1>';
		echo ' 	<nav class="ink-navigation">';
		echo ' 		<ul class="menu horizontal black">';

		foreach ($pages_array as $key => $value) {
			$active = ( self::$tab === $key) ? 'active' : '';
			echo '<li class="'.$active.'"><a href="'.esc_url(admin_url(add_query_arg( array( 'tab' => $key , 'page' => 'inbound-pro' ), 'admin.php' ) ) ).'">';
			echo $value;
			echo '</a>';
		}

		echo '		</ul>';
		echo '	</nav>';
		echo '</header>';

	}

	/**
	*  Displays search filter
	*/
	public static function display_search() {
		?>
		<div class="">
				<div class='templates-filter-group'>
					<input class="filter-search" type="search" placeholder="<?php _e(' Filter Options... ' , 'inbound-pro' ); ?>">
				</div>
			</div>

		<?php
	}

	/**
	*  Renders settings given a fields dataset
	*  @param STRING $fieldgroup_key identification key for which field group to render
	*/
	public static function render_fields( $page ) {
		echo '<div class="wrap">';
		/* render search filter */
		self::display_search();

		echo '	<div id="grid" class="container-fluid">';

		self::$settings_fields[ $page ] = (isset(self::$settings_fields[ $page ])) ? self::$settings_fields[ $page ] : array();

		foreach( self::$settings_fields[ $page ] as $priority => $group ) {
			echo '<div class="inbound-settings-group " data-keywords="'.$group['keywords'].','.$group['group_name'].'" data-group-name="'.$group['group_name'].'">';
			foreach( $group['fields'] as $field ) {

				/* get value if available else set to default */
				$field['default'] =  (isset($field['default'])) ? $field['default'] : '';
				$field['class'] =  (isset($field['class'])) ? $field['class'] : '';
				$field['value'] = (isset(self::$settings_values[ $group['group_name'] ][ $field['id'] ])) ? self::$settings_values[ $group['group_name'] ][ $field['id'] ] : $field['default'];

				/* rend field */
				self::render_field( $field , $group );

			}

			echo '</div>';
		}
		echo '  </div>';
		echo '</div>';
	}

	/**
	*  Renders label and input given field dataset
	*  @param ARRAY $field dataset containing field information
	*  @param ARRAY $group dataset containing fieldgroup information
	*/
	public static function render_field( $field , $group ) {

		if (isset($field['reveal']) && is_array($field['reveal'])) {
			$data = ' data-reveal-selector="'.$field['reveal']['selector'].'" data-reveal-value="'.$field['reveal']['value'].'" ';
		} else {
			$data = '';
		}

		echo '<div class="inbound-setting '.$field['class'].' " '.$data.' data-field-id="'.$field['id'].'" id="field-'.$field['id'].'">';
		switch($field['type']) {
			// text
			case 'license-key':

				echo '<div class="license-key">';
				echo '	<label>'.__('Inbound API Key:' , 'inbound-pro' ) .'</label>';
				echo '		<input type="text" class="license" name="'.$field['id'].'" id="'.$field['id'].'" placeholder="'.$field['placeholder'].'" value="'.$field['value'].'" data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'"  data-special-handler="true"/>';
				echo '</div>';
				break;
			case 'header':
				$extra = (isset($field['default'])) ? $field['default'] : '';
				echo '<h3 class="inbound-header">'.$extra.'</h3>';
				break;
			case 'oauth-button':
				$data = '';
				$oauth_class = '';
				$unauth_class = '';

				/* build data attriubtes */
				foreach ( $field['oauth_map'] as $key => $selector ) {
					$data .= " data-".$key."='".$selector."'";
				}

				/* discover if access key and access token already exists */
				if (isset(self::$settings_values[ $group['group_name'] ][ 'oauth' ])) {
					$params['action'] = 'revoke_oauth_tokens';
					$params['group'] = $group['group_name'];
					$oauth_class = "hidden";
					$unauth_class = "";
				} else {
					$oauth_class = "";
					$unauth_class = "hidden";
				}
				echo '<button class="ink-button orange unauth thickbox '.$unauth_class.'" id="'.$field['id'].'" data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'" '.$data.' >'.__( 'Un-Authorize' , 'inbound-pro' ).'</button>';
					$class = 'hidden';

				$params['action'] = 'request_access_token';
				$params['group'] = $group['group_name'];
				$params['oauth_urls'] = $field['oauth_urls'];
				$params['oauth_map'] = $field['oauth_map'];
				$params['TB_iframe'] = 'true';
				$params['width'] = '800';
				$params['height'] = '500';

				echo '<a href="'. admin_url( add_query_arg( $params , 'admin.php' ) ) .'"  class="ink-button green oauth thickbox '.$oauth_class.'" id="'.$field['id'].'" data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'" '.$data.' >'.__( 'Authorize' , 'inbound-pro' ).'</a>';


				break;
			case 'text':
				echo '<div class="inbound-field">';
				echo '	<div class="inbound-label-field">';
				echo '		<label>'.$field['label'] .'</label>';
				echo '	</div>';
				echo '	<div class="inbound-text-field">';
				echo '		<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" placeholder="'.( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ) .'"  value="'.$field['value'].'" size="30"  data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'"/>';
				echo '	</div>';
				echo '	<div class="inbound-tooltip-field">';
				echo '		<i class="tooltip fa fa-question-circle tool_text" title="'.$field['description'].'"></i>';
				echo '	</div>';
				echo '</div>';
				BREAK;
			// ol
			case 'ol':
				echo '<div class="inbound-field">';
				echo '	<div class="inbound-label-field">';
				echo '		<label><strong>'.$field['label'] .'</strong></label>';
				echo '	</div><br>';
				echo '	<div class="inbound-ol-field">';
				echo '		<ol>';
				foreach ($field['options'] as $option) {
					echo '		<li>'. $option. '</li>';
				}
				echo '		</ol>';
				echo '	</div>';
				echo '</div>';
				BREAK;
			// ul
			case 'ul':
				echo '<div class="inbound-field">';
				echo '	<div class="inbound-label-field">';
				echo '		<label><strong>'.$field['label'] .'</strong></label>';
				echo '	</div><br>';
				echo '	<div class="inbound-ul-field">';
				echo '		<ul>';
				foreach ($field['options'] as $option) {
					echo '		<li>'. $option. '</li>';
				}
				echo '		</ul>';
				echo '	</div>';
				echo '</div>';
				BREAK;
			// textarea
			case 'textarea':
				echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="106" rows="6"  data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'">'.$field['value'].'</textarea>
						<i class="tooltip fa-question-circle tool_textarea" title="'.$field['description'].'"></i>';
				break;
			// wysiwyg
			case 'wysiwyg':
				wp_editor( $field['value'], $field['id'], $settings = array() );
				echo	'<span class="description">'.$field['description'].'</span><br><br>';
				break;
			// media
				case 'media':

				echo '<label for="upload_image">';
				echo '<input name="'.$field['id'].'"	id="'.$field['id'].'" type="text" size="36" name="upload_image" value="'.$field['value'].'"  data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'"/>';
				echo '<input class="upload_image_button" id="uploader_'.$field['id'].'" type="button" value="Upload Image" />';
				echo '<br /><i class="tooltip fa-question-circle tool_media" title="'.$field['description'].'"></i>';
				break;
			// checkbox
			case 'checkbox':
				$i = 1;
				echo "<table>";
				if (!isset($field['value'])){$field['value']=array();}
				elseif (!is_array($field['value'])){
					$field['value'] = array($field['value']);
				}
				foreach ($field['options'] as $value=>$label) {
					if ($i==5||$i==1)
					{
						echo "<tr>";
						$i=1;
					}
						echo '<td><input type="checkbox" name="'.$field['id'].'[]" id="'.$field['id'].'" value="'.$value.'" ',in_array($value,$field['value']) ? ' checked="checked"' : '','  data-field-type="'.$field['type'].'"  data-field-group="'.$group['group_name'].'"/>';
						echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label></td>';
					if ($i==4)
					{
						echo "</tr>";
					}
					$i++;
				}
				echo "</table>";
				echo '<br><i class="tooltip fa-question-circle tool_checkbox" title="'.$field['description'].'"></i>';
			break;
			// radio
			case 'radio':

				echo '<div class="inbound-field">';
				echo '	<div class="inbound-label-field">';
				echo '		<label>'.$field['label'] .'</label>';
				echo '	</div>';

				echo '	<div class="inbound-radio-field">';
				foreach ($field['options'] as $value=>$label) {
					//echo $meta.":".$field['id'];
					//echo "<br>";
					echo '<input type="radio" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$value.'" ',$field['value']==$value ? ' checked="checked"' : '','  data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'"/>';
					echo '<label for="'.$value.'">&nbsp;&nbsp;'.$label.'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
				}
				echo '	</div>';

				echo '	<div class="inbound-tooltip-field">';
				echo '		<br /><i class="tooltip fa fa-question-circle tool_dropdown" title="'.$field['description'].'"></i>';
				echo '	</div>';
				echo '</div>';


			break;
			// select
			case 'dropdown':
				echo '<div class="inbound-field">';
				echo '	<div class="inbound-label-field">';
				echo '		<label>'.$field['label'] .'</label>';
				echo '	</div>';
				echo '	<div class="inbound-dropdown-field">';

				echo '		<select name="'.$field['id'].'" id="'.$field['id'].'"  data-field-type="'.$field['type'].'" data-field-group="'.$group['group_name'].'">';

							foreach ($field['options'] as $value=>$label) {
								echo '		<option', $field['value'] == $value ? ' selected="selected"' : '', ' value="'.$value.'">'.$label.'</option>';
							}

				echo '		</select>';
				echo '	</div>';
				echo '	<div class="inbound-tooltip-field">';
				echo '		<br /><i class="tooltip fa fa-question-circle tool_dropdown" title="'.$field['description'].'"></i>';
				echo '	</div>';
				echo '</div>';
			break;
			case 'html':
				//print_r($field);
				echo $field['value'];
				echo '<br /><i class="tooltip fa-question-circle tool_dropdown" title="'. ( isset($field['description'] ) ? $field['description'] : '' ) .'"></i>';
			break;
			case 'custom-fields-repeater':
				$fields = Leads_Field_Map::get_lead_fields();
				$fields = Leads_Field_Map::prioritize_lead_fields( $fields );

				$field_types = Leads_Field_Map::build_field_types_array();

				echo '<div class="repeater-custom-fields">';
				echo '	<h4>'.__('Custom Lead Fields:' , 'inbound-pro' ) .'</h4>';

				echo '		<div class="map-row-headers column-group">';
				echo '			<div class="map-key-header all-5">';
				echo '				<th> </th>';
				echo '			</div>';
				echo '			<div class="map-key-header all-25">';
				echo '				<th>' . __( 'Field Key' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo '			<div class="map-key-header all-30">';
				echo '			<th>' . __( 'Field Label' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo '			<div class="map-key-header all-20">';
				echo '			<th>' . __( 'Field Type' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo '			<div class="map-key-header all-15">';
				echo '			<th>' . __( 'Action' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo ' 		</div>';

				echo ' 	<form data="'.$field['type'].'" id="custom-fields-form">';
				echo ' 	<ul class="field-map" id="field-map">';


				foreach( $fields as $key => $field ) {

					echo '	<li class="map-row column-group"  data-priority="'.$key.'">';
					echo '		<div class="map-handle all-5">';
					echo '			<span class="drag-handle">';
					echo '				<i class="fa fa-arrows"></i>';
					echo '			</span>';
					echo '		</div>';
					echo '		<div class="map-key all-25">';
					echo '				<input type="hidden" class="field-priority" name="fields['.$field['key'].'][priority]" value="'.$key.'">';
					echo '				<input type="text" class="field-key" data-special-handler="true" data-field-type="mapped-field" name="fields['.$field['key'].'][key]" value="'.$field['key'].'" '. ( isset($field['nature']) && $field['nature'] == 'core' ? 'disabled' : '' ) .' required>';
					echo '		</div>';
					echo '		<div class="map-label all-30">';
					echo '				<input type="text" class="field-label" data-special-handler="true" data-field-type="mapped-field"  name="fields['.$field['key'].'][label]" value="'.$field['label'].'" required>';
					echo '		</div>';
					echo '		<div class="map-label all-20">';
					echo '				<select type="text" class="field-type" data-special-handler="true" data-field-type="mapped-field"  name="fields['.$field['key'].'][type]">';

					foreach ( $field_types as $type => $label ) {
						echo 				'<option value="'.$type.'" '.( isset($field['type']) && $field['type'] == $type ? 'selected="selected"' : '' ).'>'.$label.'</option>';
					}

					echo '				</select>';
					echo '		</div>';
					echo '		<div class="map-actions all-20">';

					echo '			<div class="edit-btn-group ">';
					echo '				<span class="ink-button red delete-custom-field '.( !isset($field['nature']) || $field['nature'] != 'core'  ? '' : 'hidden' ).'" id="remove-field">'.__( 'remove' , ' inbound-pro' ).'</span>';
					echo '			</div>';
					echo '			<div class="edit-btn-group ">';
					echo '				<span class="ink-button red delete-custom-field-confirm hidden" id="remove-field-confirm">'.__( 'confirm removal' , ' inbound-pro' ).'</span>';
					echo '			</div>';

					echo '		</div>';
					echo '	</li>';
				}

				echo '	</ul>';
				echo '	</form>';
				echo '	<form id="add-new-custom-field-form">';
				echo '	<div class="map-row-addnew column-group">';
				echo '		<div class="map-handle all-5 ">';
				echo '			<span class="drag-handle">';
				echo '				<i class="fa fa-arrows"></i>';
				echo '			</span>';

				echo '		</div>';
				echo '		<div class="map-key all-25">';
				echo '				<input type="text"  name="fields[new][key]" data-special-handler="true" id="new-key" placeholder="'.__('Enter field key here' , 'inbound-pro' ).'" required>';
				echo '		</div>';
				echo '		<div class="map-label all-30">';
				echo '				<input type="text" name="fields[new][label]" data-special-handler="true" id="new-label" placeholder="'.__('Enter field label here' , 'inbound-pro' ).'" required>';
				echo '		</div>';

				echo '		<div class="map-label all-20">';
				echo '				<select type="text" class="field-type" data-special-handler="true" id="new-type"  name="fields[new][type]">';
				foreach ( $field_types as $type => $label ) {
					echo 				'<option value="'.$type.'">'.$label.'</option>';
				}
				echo '				</select>';
				echo '		</div>';
				echo '		<div class="map-actions all-20">';
				echo '			<div class="edit-btn-group">';
				echo '				<button type="submit" class="ink-button blue" id="add-custom-field">'.__( 'add new field' , ' inbound-pro' ).'</button>';
				echo '			</div>';
				echo '		</div>';
				echo '	</div>';
				echo '	</form>';
				echo '</div>';
				BREAK;

			case 'ip-address-repeater':
				self::$settings_values = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
				$ip_addresses = ( isset(self::$settings_values['inbound-analytics-rules']['ip-addresses']) && self::$settings_values['inbound-analytics-rules']['ip-addresses'] ) ? self::$settings_values['inbound-analytics-rules']['ip-addresses'] : array('') ;

				echo '<div class="repeater-ip-addresses">';
				echo '	<h4>'.__('Exclude IPs from Tracking:' , 'inbound-pro' ) .'</h4>';
				echo '		<div class="ip-address-row-headers column-group">';
				echo '			<div class="ip-address-header all-80">';
				echo '			<th>' . __( 'IP Address' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo '			<div class="ip-address-action-header all-20">';
				echo '			<th>' . __( 'Action' , 'inbound-pro' ) .'</th>';
				echo '			</div>';
				echo ' 		</div>';

				echo ' 	<form data="'.$field['type'].'" id="ip-addresses-form">';
				echo ' 		<ul class="field-ip-addresses" id="field-ip-address">';


				foreach( $ip_addresses as $key => $ip_address ) {

					echo '		<li class="ip-address-row column-group '. ( !$ip_address ? 'hidden' : '' ) .'"  data-priority="'.$key.'">';
					echo '			<div class="ip-address all-80">';
					echo '				<input type="ip-address" class="field-ip-address" data-special-handler="true" data-field-type="ip-address" name="ip-addresses[]" value="'.$ip_address.'" required>';
					echo '			</div>';
					echo '			<div class="ip-address-actions all-20">';

					echo '				<div class="edit-btn-group ">';
					echo '					<span class="ink-button red delete-ip-address '.( !isset($field['nature']) || $field['nature'] != 'core'  ? '' : 'hidden' ).'" id="remove-field">'.__( 'remove' , ' inbound-pro' ).'</span>';
					echo '				</div>';
					echo '				<div class="edit-btn-group ">';
					echo '					<span class="ink-button red delete-ip-address-confirm hidden" id="remove-ip-confirm">'.__( 'confirm removal' , ' inbound-pro' ).'</span>';
					echo '				</div>';

					echo '			</div>';
					echo '		</li>';
				}

				echo '		</ul>';
				echo '	</form>';

				/* add new ip address html */
				echo '	<form id="add-new-ip-address-form">';
				echo '		<div class="ip-address-row-addnew column-group">';
				echo '			<div class="ip-address all-80">';
				echo '					<input type="text"  name="ip-addresses" data-special-handler="true" id="new-ip-address" placeholder="'.__('Enter IP Address ' , 'inbound-pro' ).'" required>';
				echo '			</div>';
				echo '			<div class="ip-address-actions all-20">';
				echo '				<div class="edit-btn-group">';
				echo '					<button type="submit" class="ink-button blue" id="add-ip-address">'.__( 'add new ip address' , ' inbound-pro' ).'</button>';
				echo '				</div>';
				echo '			</div>';
				echo '		</div>';
				echo '	</form>';

				echo '</div>';
			break;
		} //end switch
		echo '</div>';

	}

	/**
	*  Ajax listener for saving updated field data
	*/
	public static function ajax_update_settings() {
		/* parse string */
		parse_str($_POST['input'] , $data );

		//error_log(print_r($data,true));

		/* Update Setting */
		$settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
		$settings[ $data['fieldGroup'] ][ $data['name'] ] = $data['value'];

		Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $settings );
	}

	/**
	*  Ajax listener for saving updated custom field data
	*/
	public static function ajax_update_custom_fields() {
		/* parse string */
		parse_str($_POST['input'] , $data );

		//error_log(print_r($data,true));

		/* Update Setting */
		$settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
		$settings[ 'leads-custom-fields' ] =  $data;

		Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $settings );
	}

	/**
	*  Ajax listener for saving updated ip addresses to not track
	*/
	public static function ajax_update_ip_addresses() {
		/* parse string */
		parse_str($_POST['input'] , $data );
		//error_log(print_r($data,true));

		$ip_addresses = array_filter($data['ip-addresses']);
		$ip_addresses = array_map('trim',$ip_addresses);

		/* Update Setting */
		$settings = Inbound_Options_API::get_option( 'inbound-pro' , 'settings' , array() );
		$settings['inbound-analytics-rules'][ 'ip-addresses' ] = $ip_addresses;

		Inbound_Options_API::update_option( 'inbound-pro' , 'settings' , $settings );
	}
}

Inbound_Pro_Settings::init();