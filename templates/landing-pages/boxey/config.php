<?php
/**
* Template Name: boxey
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
	'label' => 'boxey',
	'category' => 'custom',
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
			'label' => __( 'Main Content' , 'landing-pages' ) ,
			'description' => __( 'This is the default content from template.' , 'landing-pages' ),
			'id' => "main-content",
			'type' => "wysiwyg",
			'default' => '<img class="left" src="http://glocal.dev/wp-content/uploads/landing-pages/templates/boxey/images/pic1.jpg" width="130" height="160" alt="">This is the main content area. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mo.'
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
		'default' => "/wp-content/uploads/landing-pages/templates/boxey/images/logo-wide-3.png",
		'selector' => "#header a:eq(0)",
	),
	array(
		'label' => 'Top right nav text color',
		'description' => '',
		'id'	=> 'nav-color',
		'type'	=> 'colorpicker',
		'default'	=> '494949',
		'context'	=> 'normal'
		),
	array(
		'label' => "Top Right Navigation",
		'description' => "Top Right Navigation",
		'id' => "top-right-navigation",
		'type' => "wysiwyg",
		'default' => "<ul>
						<li class=''>
									<a href='#'>Learn More</a>
								</li>
								<li>
									<a href='#'>Contact Us Directly</a>
								</li>
							</ul>
							<br class='clear'>
						",
		'selector' => "#nav",
	),
	array(
		'label' => 'Headline Color & content text color',
		'description' => 'Use this setting to change the templates Sub Headline color',
		'id'	=> 'headline-color',
		'type'	=> 'colorpicker',
		'default'	=> '5e5e5e',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Content Area BG color',
		'description' => 'Use this setting to change the templates Sub Headline color',
		'id'	=> 'content-color',
		'type'	=> 'colorpicker',
		'default'	=> 'ffffff',
		'context'	=> 'normal'
		),
	array(
		'label' => "Sub Headline",
		'description' => "",
		'id' => "subheadline",
		'type' => "textarea",
		'default' => "This is the Sub-headline",
		'selector' => "#copyright",
	),
	array(
		'label' => 'Sub Headline Color',
		'description' => 'Use this setting to change the templates Sub Headline color',
		'id'	=> 'subheadline-color',
		'type'	=> 'colorpicker',
		'default'	=> '242424',
		'context'	=> 'normal'
		),
	array(
		'label' => "Bottom Left Column",
		'description' => "Bottom Left Column",
		'id' => "bottom-left-column",
		'type' => "wysiwyg",
		'default' => "<h3 class=''>
						Primis dolor fringilla porta
					</h3>
								<ul class='imageList'>
									<li class='first'>
										<img class='left' src='/wp-content/uploads/landing-pages/templates/boxey/images/pic2.jpg' width='80' height='80' alt=''> <span class=''>Quis faucibus mauris quis consectetur lobortis parturient sit turpis scelerisque neque aliquet.</span>
									</li>
									<li>
										<img class='left' src='/wp-content/uploads/landing-pages/templates/boxey/images/pic1.jpg' width='80' height='80' alt=''> <span>Lobortis malesuada penatibus porta varius ligula blandit sit dolor mattis morbi ullamcorper posuere tempus.</span>
									</li>
									<li class='last'>
										<img class='left' src='/wp-content/uploads/landing-pages/templates/boxey/images/pic2.jpg' width='80' height='80' alt=''> <span>Diam nunc turpis placerat imperdiet ac cras ac sociis aliquam sed lacinia augue suspendisse.</span>
									</li>
								</ul>
							",
		'selector' => "#box2",
	),
	array(
		'label' => "Bottom Right Column",
		'description' => "Bottom Right Column",
		'id' => "bottom-right-column",
		'type' => "wysiwyg",
		'default' => "
								<h3>
									Magnis felis
								</h3>
								<p class=''>
									Vulputate magna nibh augue. Rutrum nibh sodales porta etiam.
								</p>
								<ul class='linkedList'>
									<li class='first'>
										<a href='#' class=''>Lobortis fringilla dictum cras</a>
									</li>
									<li class=''>
										<a href='#'>Erat primis accumsan facilisis</a>
									</li>
									<li class=''>
										<a href='#'>Laoreet in in magna</a>
									</li>
									<li>
										<a href='#'>Porta a vulputate placerat</a>
									</li>
									<li class='last'>
										<a href='#'>Neque rhoncus rhoncus iaculis</a>
									</li>
								</ul>
							",
		'selector' => "#box3",
	),
	array(
		'label' => 'Conversion Area BG color',
		'description' => '',
		'id'	=> 'cv-bg-color',
		'type'	=> 'colorpicker',
		'default'	=> '32251b',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Conversion Area Text color',
		'description' => '',
		'id'	=> 'cv-text-color',
		'type'	=> 'colorpicker',
		'default'	=> 'ffffff',
		'context'	=> 'normal'
		),
	array(
		'label' => 'Submit Button Background Color',
		'description' => '',
		'id'	=> 'submit-bg-color',
		'type'	=> 'colorpicker',
		'default'	=> '3BAF3A',
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
		'label' => "Copyright bottom",
		'description' => "Copyright bottom",
		'id' => "copyright-bottom",
		'type' => "textarea",
		'default' => "© Your Site Name",
		'selector' => "#copyright",
	),
	array(
	'label' => 'Background Settings',
			'description' => 'Set the template\'s background',
			'id'	=> 'background-style',
			'type'	=> 'dropdown',
			'default'	=> 'tile',
			'options' => array('fullscreen'=>'Fullscreen Image', 'tile'=>'Tile Background Image', 'color' => 'Solid Color', 'repeat-x' => 'Repeat Image Horizontally', 'repeat-y' => 'Repeat Image Vertically', 'custom' => 'Custom CSS'),
			'context'	=> 'normal'
			),
		array(
			'label' => 'Background Image',
			'description' => 'Enter an URL or upload an image for the banner.',
			'id'	=> 'background-image',
			'type'	=> 'media',
			'default'	=> '/wp-content/uploads/landing-pages/templates/boxey/images/retina_wood.png',
			'context'	=> 'normal'
			),
		array(
			'label' => 'Background Color',
			'description' => 'Use this setting to change the templates background color',
			'id'	=> 'background-color',
			'type'	=> 'colorpicker',
			'default'	=> 'ce0cb7',
			'context'	=> 'normal'
			),
);
