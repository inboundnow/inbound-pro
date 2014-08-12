<?php
/**
* Template Name: aspire
* @package	WordPress Landing Pages
* @author	Inbound Template Generator!
* WordPress Landing Page Config File
*/
do_action('lp_global_config');
$key = lp_get_parent_directory(dirname(__FILE__));


/* Configures Template Information */
$lp_data[$key]['info'] = array(
	'data_type' => 'template',
	'version' => '2.0.0',
	'label' => 'aspire',
	'category' => 'responsive',
	'demo' => '',
	'description' => 'This is an auto generated template from Inbound Now'
);


/* Configures Template Editor Options */
$lp_data[$key]['settings'] = array(
	array(
		'label' => 'turn-off-editor', /* Turns off main content */
		'description' => 'Turn off editor',
		'id'	=> 'turn-off-editor',
		'type'	=> 'custom-css',
		'default'	=> '#postdivrich, #lp_2_form_content {display:none !important;}'
		),	
     array(
           'label' => __( 'Call to Action Content' , 'landing-pages' ),
           'description' => __( 'Place your call to action here.' , 'landing-page' ),
           'id' => "conversion-area-content",
           'type' => "wysiwyg",
           'default' => ''
         ),
	array(
		'label' => "Logo Image",
		'description' => "Logo Image",
		'id' => "logo-image",
		'type' => "media",
		'default' => "/wp-content/uploads/landing-pages/templates/aspire/images/logo-wide-3.png",
		'selector' => ".navbar-header .navbar-brand",
	),
	array(
		'label' => 'Color Scheme',
		'description' => 'Use this setting to change the templates color scheme',
		'id'	=> 'scheme',
		'type'	=> 'colorpicker',
		'default'	=> '74cfae',
		'context'	=> 'normal'
		),
	array(
		'label' => "Top right links",
		'description' => "Copyright Text",
		'id' => "top-right-link",
		'type' => "wysiwyg",
		'default' => '<ul class="nav navbar-nav navbar-right">
				<li class="active"><a href="http://www.inboundnow.com">Contact Us</a></li>
				</ul>',
		'selector' => "#f .row p",
	),
	array(
		'label' => "Sub headline",
		'description' => "Sub headline",
		'id' => "sub-headline",
		'type' => "textarea",
		'default' => "Sub-headline goes in here",
		'selector' => "#hello .col-lg-8.col-lg-offset-2.centered h2",
	),
	array(
		'label' => 'Submit Button Background Color',
		'description' => '',
		'id'	=> 'submit-bg-color',
		'type'	=> 'colorpicker',
		'default'	=> '69c773',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Submit Button Text Color',
		'description' => '',
		'id'	=> 'submit-text-color',
		'type'	=> 'colorpicker',
		'default'	=> 'ffffff',
		'context'	=> 'normal'
		),
	array(
		'label' => "Middle Left Content",
		'description' => "Middle Left Content",
		'id' => "middle-left-content",
		'type' => "wysiwyg",
		'default' => "<img src='http://www.fillmurray.com/600/400/' alt='' class=''>",
		'selector' => "#green .row .col-lg-5.centered",
	),
	array(
		'label' => "Middle Right Content",
		'description' => "Middle Right Content",
		'id' => "middle-right-content",
		'type' => "wysiwyg",
		'default' => "
						<h3 class=''>Headline 2</h3>
						<p class=''>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
					",
		'selector' => "#green .row .col-lg-7.centered",
	),
	array(
		'label' => "Full Width Bottom Content 1",
		'description' => "Full Width Bottom Content 1",
		'id' => "full-width-bottom-content-1",
		'type' => "wysiwyg",
		'default' => "


			<h3 class=''>Headline 3</h3>
				<div class='col-lg-7 col-lg-offset-1 mt'>
						<p class='lead'>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever.</p>
				</div>

				<div class='col-lg-3 mt'>
					<p><button type='button' class='btn btn-theme btn-lg'>Email Me!</button></p>
				</div>

		",
		'selector' => "body .container:eq(3)",
	),
	array(
		'label' => "Full Width Bottom Content 2",
		'description' => "Full Width Bottom Content 2",
		'id' => "full-width-bottom-content-2",
		'type' => "wysiwyg",
		'default' => "<h3 class=''>Headline 3</h3>
					<div class='col-lg-7 col-lg-offset-1 mt'>
							<p class='lead'>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever.</p>
					</div>

					<div class='col-lg-3 mt'>
						<p><button type='button' class='btn btn-theme btn-lg'>Email Me!</button></p>
					</div>

		",
		'selector' => "#skills",
	),
	array(
		'label' => "Full Width Bottom Content 3",
		'description' => "Full Width Bottom Content 3",
		'id' => "full-width-bottom-content-3",
		'type' => "wysiwyg",
		'default' => '<iframe width="853" height="480" src="//www.youtube.com/embed/Y4M_g9wkRXw?list=UUCqiE-EcfDjaKGXSxtegcyg" frameborder="0" allowfullscreen></iframe>',
		'selector' => "#social",
	),
	array(
		'label' => "Copyright Text",
		'description' => "Copyright Text",
		'id' => "copyright-text",
		'type' => "textarea",
		'default' => "Your Company Inc.",
		'selector' => "#f .row p",
	),
	array(
	'label' => 'Background Settings',
			'description' => 'Set the template\'s background',
			'id'	=> 'background-style',
			'type'	=> 'dropdown',
			'default'	=> 'color',
			'options' => array('fullscreen'=>'Fullscreen Image', 'tile'=>'Tile Background Image', 'color' => 'Solid Color', 'repeat-x' => 'Repeat Image Horizontally', 'repeat-y' => 'Repeat Image Vertically', 'custom' => 'Custom CSS'),
			'context'	=> 'normal'
			),
		array(
			'label' => 'Background Image',
			'description' => 'Enter an URL or upload an image for the banner.',
			'id'	=> 'background-image',
			'type'	=> 'media',
			'default'	=> '/wp-content/uploads/landing-pages/templates/aspire/images/bg-2.jpeg',
			'context'	=> 'normal'
			),
		array(
			'label' => 'Background Color',
			'description' => 'Use this setting to change the templates background color',
			'id'	=> 'background-color',
			'type'	=> 'colorpicker',
			'default'	=> 'f2f2f2',
			'context'	=> 'normal'
			),

);
