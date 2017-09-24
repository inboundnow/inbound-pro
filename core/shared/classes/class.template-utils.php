<?php

/**
 * Class Inbound_Template_Utils provides developer tools for generating landing page boilerplates from ACF import data
 * @package ACF
 */


/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Inbound_Template_Utils {

    static $activate_msg = '<h1>Hello and welcome to Inbound Now dev tools</h1>
                <p>Dev tools were created to help you, the developer, quickly create templates for all of inbound now plugins.</p>
                <p>You need will need <strong>inbound now pro</strong> activated to use this screen.</p>';

    public function __construct() {
        self::init();
    }

    public function init() {
        /* add extra menu items */
        add_action('admin_menu', array( __CLASS__ , 'add_screen' ) );

        //if (isset($_GET['inbound-template-gen'])) {
            //add_action( 'admin_head', array(__CLASS__, 'html'));
        //}

    }

    static function add_screen() {

        if (!function_exists('acf_get_field_groups')) {
            return;
        }

        add_submenu_page(
            'edit.php?post_type=landing-page',
            __( 'Developer Tools' , 'inbound-pro' ),
            __( 'Developer Tools' , 'inbound-pro' ),
            'manage_options',
            'template_utils',
            array( __CLASS__ , 'html' )
        );
    }

    static function get_json() {

        if (!function_exists('acf_get_field_group')) {
            echo self::$activate_msg;
            exit;
        }
        $keys = (isset($_GET['generate-template-id'])) ? array(sanitize_text_field($_GET['generate-template-id'])) : array();
        //print_r($keys);
        //exit;
        //$keys = $_GET['acf_export_keys'];
        //$keys = array('group_55e23ad63ecc3');
        //$keys = array('group_55d38b033048e');
        //$keys = array('group_55d26a506a990');

        // validate
        if( empty($keys) ) {

            return false;

        }


        // vars
        $json = array();


        // construct JSON
        foreach( $keys as $key ) {

            // load field group
            $field_group = acf_get_field_group( $key );


            // validate field group
            if( empty($field_group) ) {

                continue;

            }


            // load fields
            $field_group['fields'] = acf_get_fields( $field_group );


            // prepare fields
            $field_group['fields'] = acf_prepare_fields_for_export( $field_group['fields'] );


            // extract field group ID
            $id = acf_extract_var( $field_group, 'ID' );


            // add to json array
            $json[] = $field_group;

        }


        // return
        return $json;

    }
    /*
     There are two places the marketing button renders:
     in normal WP editors and via JS for ACF normal
     */
    static function tabs($count) {
        $tabs = "";
        for ($i=0; $i < $count; $i++) {
            $tabs .= "\t";
        }
        return $tabs;
    }
    static function inbound_repeater_output($field, $indent = 0, $wrap = true){
        $sp = $indent;
        $output = "";
        if($wrap) {
            $output = self::tabs(1 + $sp) . "<?php"."\r\n";
        }
        $output .= self::tabs(1 + $sp) ."/* Start ".$field['name']." Repeater Output */" ."\r\n";
        $output .= self::tabs(1 + $sp) .'if ( have_rows( "'.$field['name'].'" ) )  { ?>'. "\r\n\r\n";
        $output .= self::tabs(2 + $sp) .'<?php while ( have_rows( "'.$field['name'].'" ) ) : the_row();' . "\r\n";

        foreach ($field['sub_fields'] as $sub) {
            $output .= self::tabs(4 + $sp) ."$".$sub['name']. " = " . "get_sub_field(\"".$sub['name']."\");"."\r\n";
        }

        $output .= self::tabs(2 + $sp) .'?>'."\r\n\r\n";
        $output .= self::tabs(2 + $sp) .'<!-- your markup here -->'."\r\n\r\n";
        $output .= self::tabs(2 + $sp) .'<?php endwhile; ?>'."\r\n\r\n";
        $output .= self::tabs(1 + $sp) .'<?php } /* end if have_rows('.$field['name'].') */'."\r\n";
        $output .= self::tabs(1 + $sp) ."/* End ".$field['name']." Repeater Output */" ."\r\n";
        if($wrap) {
        $output .= self::tabs(1 + $sp) ."?>" ."\r\n\r\n";
        }

        return $output;
    }
    static function html($args) {
        //print_r($_POST);
        if(isset($_POST) && !empty($_POST)) {
            return;
        }

        /* Todo intercept and update the special key here */
        //print_r($json); exit;
        ?>
        <div class="wrap acf-settings-wrap">

            <h2><?php _e('Import / Export', 'acf'); ?></h2>

            <div class="acf-box">
                <div class="title">
                    <h3><?php _e('Generate Your Template Output', 'inboundnow'); ?></h3>
                </div>

                <div class="inner">
                <script type="text/javascript">
                function replaceUrlParam(url, paramName, paramValue){
                    var pattern = new RegExp('('+paramName+'=).*?(&|$)')
                    var newUrl=url
                    if(url.search(pattern)>=0){
                        newUrl = url.replace(pattern,'$1' + paramValue + '$2');
                    }
                    else{
                        newUrl = newUrl + (newUrl.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue
                    }
                    return newUrl
                }
                jQuery(document).ready(function($) {
                   // put all your jQuery goodness in here.
                    jQuery("#generate_template").on('change', function () {
                        var val = jQuery(this).val();
                        var newUrl = replaceUrlParam(window.location.href, 'generate-template-id', val);
                       window.location.href = newUrl;
                    });
                 });

                </script>
                <div id="options-available">
                <?php
                $choices = array('none' => "Choose template");
                $field_groups_ids = acf_get_field_groups();

                // populate choices
                if( !empty($field_groups_ids) ) {
                    foreach( $field_groups_ids as $field_group ) {
                        //print_r($field_group);
                        $choices[ $field_group['key'] ] = $field_group['title'];
                    }
                }
                echo "<label>Select the ACF options you wish to generate markup for</label>";
                // render field
                $acf_id = (isset($_GET['generate-template-id'])) ? sanitize_text_field($_GET['generate-template-id']) : false;
                $template_name = (isset($_GET['template-name'])) ? sanitize_text_field($_GET['template-name']) : '';
                acf_render_field(array(
                    'type'      => 'select',
                    'name'      => 'generate_template',
                    'prefix'    => false,
                    'value'     => $acf_id,
                    'toggle'    => true,
                    'choices'   => $choices,
                ));

                acf_render_field(array(
                    'type'      => 'text',
                    'name'      => 'template_name',
                    'prefix'    => false,
                    'value'     => $template_name,
                    'placeholder' => "Template Name"
                )); ?>



                </div>
                <p>This page is for helping developing templating super simple.</p>

                <p>This is generated output from your landing page options to copy/paste into your index.php</p>

<?php
/**
 * Generate the template here
 */
/* get the data */
$json = self::get_json();
//print_r($json);

// validate
if( $json === false || empty($json)) {

    acf_add_admin_notice( __("No field groups selected", 'acf') , 'error');
    exit;

}

// vars
$field_groups = $json;
?>

<textarea style="width:100%; height:500px;"  class="pre" readonly="true">
<?php echo "<?php
/**
* Template Name: __TEMPLATE_NAME__
* @package  WordPress Landing Pages
* @author   Inbound Template Generator
*/

/* Declare Template Key */
\$key = basename(dirname(__FILE__));

/* discover the absolute path of where this template is located. Core templates are loacted in /wp-content/plugins/landing-pages/templates/ while custom templates belong in /wp-content/uploads/landing-pages/tempaltes/ */
\$path = (preg_match(\"/uploads/\", dirname(__FILE__))) ? LANDINGPAGES_UPLOADS_PATH . \$key .'/' : LANDINGPAGES_PATH.'templates/'.\$key.'/';

\$urlpath = (preg_match(\"/uploads/\", dirname(__FILE__))) ? LANDINGPAGES_UPLOADS_URLPATH . \$key .'/' : LANDINGPAGES_URLPATH.'templates/'.\$key.'/';

/* Include ACF Field Definitions  */
include_once(\$path .'config.php');

/* Define Landing Pages's custom pre-load hook for 3rd party plugin integration */
do_action('wp_head');
\$post_id = get_the_ID(); ";?>
?>

<?php

echo '<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>  <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>  <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->

<head>
    <!--  Define page title -->
    <title><?php wp_title(); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- include your assets -->
    <!-- <link rel="stylesheet" href="<?php echo $urlpath; ?>css/css_file_name.css"> -->
    <!-- <script src="<?php echo $urlpath; ?>js/js_file_name.js"></script> -->

    <!-- Load Normal WordPress wp_head() function -->
    <footer>
    <?php do_action("wp_footer"); ?>

    <!-- Load Landing Pages\'s custom pre-load hook for 3rd party plugin integration -->
    <?php do_action("lp_head"); ?>

    </footer>
</head>'. "\r\n\r\n".
'<body>'. "\r\n\r\n";
 //print_r($field_groups); exit;



if(isset($field_groups)) {
echo "<?php ". "\r\n\r\n";
foreach( $field_groups as $field_group ) {


    foreach( $field_group['fields'] as $field ) {

        if($field['type'] === "repeater") {
            $repeater = self::inbound_repeater_output($field);
            echo $repeater;
        } else if($field['type'] === "flexible_content") {
            echo "/* Start ".$field['name']." Flexible Content Area Output */" ."\r\n";
            echo "\tif(function_exists('have_rows')) :" ."\r\n";
            echo "\t\tif(have_rows('".$field['name']."')) :" ."\r\n";
            echo "\t\t\t while(have_rows('".$field['name']."')) : the_row();" ."\r\n";
            echo "\t\t\t\t switch(get_sub_field('acf_fc_layout')) :" ."\r\n";
            foreach ($field['layouts'] as $layout) {
                //print_r($layout);
                echo "\t\t\t\t/* start layout ".$layout['name']." */"."\r\n";
                echo "\t\t\t\t case '".$layout['name']."' : " ."\r\n";

                foreach ($layout['sub_fields'] as $layout_subfield) {
                    if($layout_subfield['type'] ==='repeater') {
                        $test = self::inbound_repeater_output($layout_subfield, 4, false);
                        echo $test;
                    } else {
        echo "\t\t\t\t\t$".$layout_subfield['name']. " = " . "get_sub_field(\"".$layout_subfield['name']."\");"."\r\n";
                    }


                }
                echo "\t\t\t?>"."\r\n\r\n";
                echo "\t\t\t<!-- your markup here -->"."\r\n\r\n";
                echo "\t\t\t<?php break;". "\r\n";
                //echo "\t\t\t\t/* end layout ".$layout['name']." */"."\r\n";

            }
            echo "\t\t\t\tendswitch; /* end switch statement */ "."\r\n";
            echo "\t\t\tendwhile; /* end while statement */"."\r\n";
            echo "\t\t endif; /* end have_rows */"."\r\n";
            echo "\tendif;  /* end function_exists */"."\r\n";
            echo "/* End ".$field['name']." Flexible Content Area Output */" ."\r\n\r\n";

        } else {
            if($field['name']) {
                echo "\t$".$field['name']. " = " . "get_field(\"".$field['name']."\", \$post_id);"."\r\n";
            }

        }
    }


}
echo "?>"."\r\n\r\n";

/* break; endwhile; endif; */
echo "<?php "."\r\n";
echo "do_action('lp_footer');"."\r\n";
echo "do_action('wp_footer');"."\r\n";
echo "?>"."\r\n";
echo "</body>"."\r\n";
echo "</html>"."\r\n";
}
?>
</textarea>
<p>This is the config.php file</p>

                    <?php /* TODO: add config begging output */ ?>
                    <textarea class="pre" readonly="true"><?php

                    echo "if( function_exists('acf_add_local_field_group') ):" . "\r\n" . "\r\n";

                    foreach( $field_groups as $field_group ) {

                        // code
                        $code = var_export($field_group, true);

                        // change double spaces to tabs
                        $code = str_replace("  ", "\t", $code);

                        // correctly formats "=> array("
                        $code = preg_replace('/([\t\r\n]+?)array/', 'array', $code);

                        // Remove number keys from array
                        $code = preg_replace('/[0-9]+ => array/', 'array', $code);

                        // echo
                        echo "acf_add_local_field_group({$code});" . "\r\n" . "\r\n";

                    }

                    echo "endif;";

                    ?></textarea>

                </div>

            </div>

        </div>
        <div class="acf-hidden">
            <style type="text/css">
                textarea.pre {
                    width: 100%;
                    padding: 15px;
                    font-size: 14px;
                    line-height: 1.5em;
                    resize: none;
                }
            </style>
            <script type="text/javascript">
            (function($){

                var i = 0;

                $(document).on('click', 'textarea.pre', function(){

                    if( i == 0 )
                    {
                        i++;

                        $(this).focus().select();

                        return false;
                    }

                });

                $(document).on('keyup', 'textarea.pre', function(){

                    $(this).height( 0 );
                    $(this).height( this.scrollHeight );

                });

                $(document).ready(function(){

                    $('textarea.pre').trigger('keyup');

                });

            })(jQuery);
            </script>
        </div>

    <?php

    }
}
$Inbound_Template_Utils = new Inbound_Template_Utils();