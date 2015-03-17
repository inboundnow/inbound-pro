<?php
/**
* WordPress: WP Calls To Action Template Config File
* Template Name:  Flat CTA
* @package  WordPress Calls to Action
* @author 	InboundNow
*/

do_action('wp_cta_global_config'); // The wp_cta_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files
$key = basename(dirname(__FILE__));
$this_path = WP_CTA_PATH.'templates/'.$key.'/';
$url_path = WP_CTA_URLPATH.'templates/'.$key.'/';

$wp_cta_data[$key]['info'] =
array(
    'data_type' => 'template', // Template Data Type
    'version' => "1.0", // Version Number
    'label' => "Clean CTA", // Nice Name
    'category' => 'Minimal', // Template Category
    'demo' => '', // Demo Link
    'description'  => 'Clean call to action', // template description
	'path' => $this_path, //path to template folder
	'urlpath' => $url_path //urlpath to template folder
);


/* Define Meta Options for template */
$wp_cta_data[$key]['settings'] =
array(
   array(
       'label' => 'Instructions', // Name of field
       'description' => "Fill in the below fields to configure the clean CTA. Make sure to insert the height and width of your call to action. Use the visual editor to see live changes and for faster editing", // what field does
       'id' => 'description', // metakey. $key Prefix is appended from parent in array loop
       'type'  => 'description-block', // metafield type
       'default'  => '', // default content
       'context'  => 'normal' // Context in screen (advanced layouts in future)
       ),

   array(
         'label' => 'Header Background Image',
         'description' => "Header Image",
         'id'  => 'header-image',
         'type'  => 'media',
         'default'  => $url_path . 'bg.jpg',
         'context'  => 'normal'
         ),
   array(
         'label' => 'Custom Header Height',
         'description' => "enter height in px",
         'id'  => 'header-height',
         'type'  => 'text',
         'default'  => '',
         'context'  => 'normal'
         ),
   array(
         'label' => 'Header Text',
         'description' => "Header Text",
         'id'  => 'header-text',
         'type'  => 'text',
         'default'  => 'Snappy Headline',
         'context'  => 'normal'
         ),
   array(
       'label' => 'Header Text Color',
       'description' => "Use this setting to change headline color",
       'id'  => 'header-text-color',
       'type'  => 'colorpicker',
       'default'  => '000000',
       'context'  => 'normal'
       ),
      array(
         'label' => 'Sub Header Text',
         'description' => "Sub Header Text",
         'id'  => 'sub-header-text',
         'type'  => 'wysiwyg',
         'default'  => '<h3>Awesome Subheadline Text Goes here</h3>
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae mauris arcu, eu pretium nisi. Praesent fringilla ornare ullamcorper. Pellentesque diam orci, sodales in blandit ut, placerat quis felis. Vestibulum at sem massa, in tempus nisi. Vivamus ut fermentum odio. Etiam porttitor faucibus volutpat. Vivamus vitae mi ligula, non hendrerit urna. Suspendisse potenti. Quisque eget massa a massa semper mollis.',
         'context'  => 'normal'
         ),
     array(
         'label' => 'Sub Header Text Color',
         'description' => "Use this setting to change headline color",
         'id'  => 'sub-header-text-color',
         'type'  => 'colorpicker',
         'default'  => '000000',
         'context'  => 'normal'
         ),
     array(
         'label' => 'Background Color',
         'description' => "Changes background color",
         'id'  => 'content-color',
         'type'  => 'colorpicker',
         'default'  => 'ed1a4b',
         'context'  => 'normal'
         ),
     array(
         'label' => 'Bottom Text Option',
         'description' => "Content below button",
         'id'  => 'bottom-text',
         'type'  => 'wysiwyg',
         'default'  => '',
         'context'  => 'normal'
         ),
     array(
         'label' => 'Bottom Text Option (optional)',
         'description' => "Use this setting to change headline color",
         'id'  => 'bottom-text-color',
         'type'  => 'colorpicker',
         'default'  => '000000',
         'context'  => 'normal'
         ),
      array(
         'label' => 'Submit Button Color',
         'description' => "Use this setting to change the template's submit button color.",
         'id'  => 'submit-button-color',
         'type'  => 'colorpicker',
         'default'  => '69c773'
         ),
      array(
         'label' => 'Submit Button Text',
         'description' => "Text on the button.",
         'id'  => 'submit-button-text',
         'type'  => 'text',
         'default'  => 'Download Now'
         ),
      array(
         'label' => 'Submit Button Text Color',
         'description' => "Text on the button.",
         'id'  => 'submit-button-text-color',
         'type'  => 'colorpicker',
         'default'  => 'ffffff'
         ),
       array(
         'label' => 'Destination Link',
         'description' => "Where do you want to link people to?",
         'id'  => 'link_url',
         'type'  => 'text',
         'default'  => 'http://www.inboundnow.com'
         ),
       array(
         'label' => 'turn-off-editor',
         'description' => "Turn off editor",
         'id'  => 'turn-off-editor',
         'type'  => 'custom-css',
         'default'  => '#postdivrich {display:none !important;}'
         )
   );


/* define dynamic template markup */
$wp_cta_data[$key]['markup'] = '
<style type="text/css">
#inbound-wrapper-clean h1, #inbound-wrapper-clean p {
  margin-bottom: 20px;
  color: #333;
}
#inbound-wrapper-clean h1 {
  margin-top: 2.5rem;
  margin-bottom: 0;
  text-align: center;
}
#inbound-wrapper-clean p {
  margin: 0 auto 0.5rem;
  text-align: center;
}
#inbound-wrapper-clean.card {

 -webkit-border-radius: 4px;
 -moz-border-radius: 4px;
  border-radius: 4px;
  box-shadow: 0 2px 0 rgba(0, 0, 0, 0.03);
}
.button-list {
  text-align: center;
  list-style: none;
  line-height: 2.5rem;
  padding-bottom: 10px;
  padding-top: 5px;
}
.button-item {
  display: inline-block;
  margin: 0 0.25em;
  height: 2.5rem;
}
.button {
  display: inline-block;
  height: inherit;
}
.cover {
  overflow: hidden;
  -webkit-border-radius: 4px 4px 0px 0px;
  -moz-border-radius: 4px 4px 0px 0px;
  border-radius: 4px 4px 0px 0px;
  padding: 0 0 1.5rem;
  background: #666 url(\'{{header-image}}\') center center no-repeat;
  background-size: cover;
  background-position: top;
  height: {{header-height}};
}
#inbound-wrapper-clean .cover h1 {
  color: {{header-text-color|color}};
  text-shadow: 1px 1px 0px rgba(150, 150, 150, 0.59);
}
.cover #clean-sub-head-text, #clean-sub-head-text h1,#clean-sub-head-text h2,#clean-sub-head-text h3,#clean-sub-head-text h4,#clean-sub-head-text h5,#clean-sub-head-text h6 {
  color: {{sub-header-text-color|color}};
}
.clean-button {
  display:block;
  margin: auto;
  text-align: center;
  border-radius:4px;
  text-decoration: none;
  font-family:\'Lucida Grande\',helvetica;
  background: {{submit-button-color|color}};
  -webkit-box-shadow: 0 4px 0 0 {{submit-button-color|brightness(70)}};
  -moz-box-shadow: 0 4px 0 0 {{submit-button-color|brightness(70)}};
  box-shadow: 0 4px 0 0 {{submit-button-color|brightness(70)}};
  font-size: 20px !important;
  padding: 15px 20px;
  width: 250px;
  color: {{submit-button-text-color|color}};
}
#cta-button.clean-button:hover { background: {{submit-button-color|brightness(80)}}; }
#inbound-wrapper-clean {background-color: {{content-color|color}};}
#inbound-wrapper-clean .btn { border: 3px solid {{submit-button-color|color}}; color: {{submit-button-color|color}};}
#inbound-wrapper-clean .btn:hover, .inbound-wrapper-clean .btn:active { color: #{{content-color}}; background: {{submit-button-color|color}};}
#inbound-content p {color:{{content-text-color|color}};}
#clean-bottom-text {
  padding: 10px;
  font-size: 14px;
  text-align: left;
  color: #{{bottom-text-color}};
}
#clean-sub-head-text {
  text-align: center;
  padding: 10px;
  padding-left: 30px;
  padding-right: 30px;
}

#cta-button {
  border: none;
}

</style>
<div id="inbound-wrapper-clean" class="card">
  <div class="cover">

    <h1>{{ header-text }}</h1>

    <div id="clean-sub-head-text">{{sub-header-text}}</div>
  </div>
  <div class="button-list">
    <a href="{{link_url}}" id="cta-button" class="clean-button" href="#">{{submit-button-text}}</a>
  </div>
</div>
<div id="clean-bottom-text">{{bottom-text}}</div>
';
