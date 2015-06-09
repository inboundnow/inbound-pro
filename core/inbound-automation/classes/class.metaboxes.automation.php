<?php

if ( !class_exists( 'Inbound_Metaboxes_Automation' ) ) {

    class Inbound_Metaboxes_Automation {


        static $post_type;

        static $rule_trigger;
        static $rule_trigger_evaluate;
        static $rule_trigger_filters;
        static $rule_action_blocks;

        static $Inbound_Automation;
        static $rule; /* rule dataset */
        static $triggers;
        static $argument_filters;
        static $actions;
        static $db_lookup_filters;

        public function __construct() {
            self::$post_type = 'automation';
            self::load_hooks();
        }

        public static function load_hooks() {
            /* Setup Variables */
            add_action( 'posts_selection' , array( __CLASS__ , 'load_rule') );

            /* Add Metaboxes */
            add_action( 'add_meta_boxes' , array( __CLASS__ , 'define_metaboxes') );

            /* Replace Default Title Text */
            add_filter( 'enter_title_here' , array( __CLASS__ , 'change_title_text' ) , 10, 2 );

            /* Add Save Actions */
            add_action( 'save_post' , array( __CLASS__ , 'save_automation' ) );

            /* Enqueue JS */
            add_action( 'admin_enqueue_scripts', array( __CLASS__ , 'enqueue_admin_scripts' ) );
            add_action( 'admin_print_footer_scripts', array( __CLASS__ , 'print_admin_scripts' ) );

            /* Setup Ajax Listeners - Get Trigger Argument Filters*/
            add_action( 'wp_ajax_nopriv_automation_get_trigger_arguments', array( __CLASS__ , 'ajax_load_trigger_arguments' ) );
            add_action( 'wp_ajax_automation_get_trigger_arguments', array( __CLASS__ , 'ajax_load_trigger_arguments' ) );

            /* Setup Ajax Listeners - Get Action DB Lookup Filters*/
            add_action( 'wp_ajax_nopriv_automation_get_db_lookup_filters', array( __CLASS__ , 'ajax_load_db_lookup_filters' ) );
            add_action( 'wp_ajax_automation_get_db_lookup_filters', array( __CLASS__ , 'ajax_load_db_lookup_filters' ) );

            /* Setup Ajax Listeners - Get Actions */
            add_action( 'wp_ajax_nopriv_automation_get_actions', array( __CLASS__ , 'ajax_load_action_definitions' ) );
            add_action( 'wp_ajax_automation_get_actions', array( __CLASS__ , 'ajax_load_action_definitions' ) );

            /* Setup Ajax Listeners - Build Trigger Filter Settings*/
            add_action( 'wp_ajax_nopriv_automation_build_trigger_filter', array( __CLASS__ , 'ajax_build_trigger_filter' ) );
            add_action( 'wp_ajax_automation_build_trigger_filter', array( __CLASS__ , 'ajax_build_trigger_filter' ) );

            /* Setup Ajax Listeners - Build DB Lookup Filter Settings*/
            add_action( 'wp_ajax_nopriv_automation_build_db_lookup_filter', array( __CLASS__ , 'ajax_build_db_lookup_filter' ) );
            add_action( 'wp_ajax_automation_build_db_lookup_filter', array( __CLASS__ , 'ajax_build_db_lookup_filter' ) );

            /* Setup Ajax Listeners - Build Action Settings*/
            add_action( 'wp_ajax_nopriv_automation_build_action', array( __CLASS__ , 'ajax_build_action' ) );
            add_action( 'wp_ajax_automation_build_action', array( __CLASS__ , 'ajax_build_action' ) );

            /* Setup Ajax Listeners - Build Action Block*/
            add_action( 'wp_ajax_nopriv_automation_build_action_block', array( __CLASS__ , 'ajax_build_action_block' ) );
            add_action( 'wp_ajax_automation_build_action_block', array( __CLASS__ , 'ajax_build_action_block' ) );

        }

        /**
         *	Load automation definitions
         */
        public static function load_definitions() {

            /* prevent repeat calls */
            if (isset(self::$Inbound_Automation)) {
                return;
            }


            self::$Inbound_Automation = inbound_automation_load_definitions();
            self::$triggers = self::$Inbound_Automation->triggers;
            self::$argument_filters = self::$Inbound_Automation->argument_filters;
            self::$actions = self::$Inbound_Automation->actions;
            self::$db_lookup_filters = self::$Inbound_Automation->db_lookup_filters;

        }

        /**
         *	Load rule settings iinto static variables
         */
        public static function load_rule() {
            global $post;

            if ( !isset($post) || $post->post_type != self::$post_type || !isset( $_GET['post'] )) {
                return;
            }

            self::$rule = get_post_meta( $post->ID , 'inbound_rule', true );

            self::$rule_trigger =	( isset( self::$rule['trigger']) ) ?	self::$rule['trigger'] : '';
            self::$rule_trigger_evaluate =	( isset( self::$rule['trigger_filters_evaluate']) ) ?	self::$rule['trigger_filters_evaluate'] : '';
            self::$rule_trigger_filters =	( isset( self::$rule['trigger_filters']) ) ?	self::$rule['trigger_filters'] : array();
            self::$rule_action_blocks =	( isset( self::$rule['action_blocks']) ) ?	self::$rule['action_blocks'] : array();

        }

        public static function define_metaboxes() {
            global $post;

            if ( $post->post_type != self::$post_type ) {
                return;
            }

            /* Rule Settings */
            add_meta_box(
                'inbound_automation_settings', // $id
                __( 'Rule Settings', 'inbound-pro' ),
                array( __CLASS__ , 'display_container' ), // $callback
                self::$post_type ,
                'normal',
                'high'
            );


            /* Load Action Blocks If Available */
            if ( !isset(self::$rule_action_blocks) ) {
                return;
            }

            foreach ( self::$rule_action_blocks as $block_id => $block ) {

                /* Rule Settings */
                add_meta_box(
                    'inbound_automation_actions_' . $block_id, // $id
                    __( 'Action Block', 'inbound-pro' ),
                    array( __CLASS__ , 'print_actions' ), // $callback
                    self::$post_type ,
                    'normal',
                    'high',
                    array (
                        'block_id'=>$block_id,
                        'block' =>$block
                    )
                );
            }
        }

        /**
         *
         */
        public static function display_container() {
            global $post;

            self::load_definitions();
            self::print_nav();
            self::print_trigger_container();
            self::print_actions_container();
            self::print_logs_container();

        }



        /**
         *
         */
        public static function print_nav() {

            $nav_elements = array(
                array(
                    'id' => 'trigger-container',
                    'label' => 'Trigger',
                    'class' => 'nav_trigger',
                    'default' => true
                ),
                array(
                    'id' => 'actions-container',
                    'label' => 'Actions',
                    'class' => 'nav_actions',
                ),
                array(
                    'id' => 'logs-container',
                    'label' => 'Logs',
                    'class' => 'nav_logs',
                )
            );

            $nav_elements = apply_filters( 'inbound_automation_nav_elements' , $nav_elements );

            echo '<ul class="nav nav-pills">';

            foreach ($nav_elements as $nav) {
                echo '<li class="'.( isset($nav['default']) && $nav['default']	? 'active' : '' ).' '.( isset($nav['class']) ? $nav['class'] : '' ) .'" >';
                echo '<a href="#" class="navlink" id="'.$nav['id'].'">' . $nav['label'] . '</a>';
                echo '</li>';
            }

            echo '</ul>';
        }

        public static function print_trigger_container() {

            ?>
            <div class='nav-container nav-reveal trigger-container' id='trigger-container'>
                <table class='table-trigger-container'>
                    <tr class='tr-trigger-select'>
                        <td>
                            <h2>Define Trigger</h2>
                            <select class='trigger-dropdown form-control' id='trigger-dropdown' name='trigger'>
                                <?php
                                echo '<option value="-1" class="">Select Trigger</option>';
                                foreach ( self::$triggers as $hook => $trigger ) {
                                    echo '<option
											value="'.$hook.'"
											class="'.( isset($trigger['icon_class']) ? $trigger['icon_class'] : '' ) .'"
											'.( isset(self::$rule_trigger) && self::$rule_trigger == $hook ? 'selected="selected"' : '' ) .'
											>'.$trigger['label'].'</option>';
                                }

                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="tr-filter-select">
                        <td class='td-filter-add-dropdown' id='argument-filters-container'>
                            <h2>Define Conditions</h2>
                            <div class='trigger-filter-evaluate <?php if ( !isset( self::$rule_trigger_filters ) ||	count(self::$rule_trigger_filters) < 1 ) { echo 'nav-hide'; } ?>'>
                                <div class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-default	<?php if (	!self::$rule_trigger_evaluate || self::$rule_trigger_evaluate == 'match-all' ){ echo 'active'; } ?>">
                                        <input type='radio' name='trigger_filters_evaluate' value='match-all' <?php if (	!self::$rule_trigger_evaluate || self::$rule_trigger_evaluate == 'match-all' ){ echo 'checked="checked"'; } ?>> Match All
                                    </label>
                                    <label class="btn btn-default	<?php if ( self::$rule_trigger_evaluate == 'match-any' ){ echo 'active'; } ?>">
                                        <input type='radio' name='trigger_filters_evaluate' value='match-any' <?php if ( self::$rule_trigger_evaluate == 'match-any' ){ echo 'checked="checked"'; } ?>> Match Any
                                    </label>
                                    <label class="btn btn-default	<?php if ( self::$rule_trigger_evaluate == 'match-none' ){ echo 'active'; } ?>">
                                        <input type='radio' name='trigger_filters_evaluate' value='match-none' <?php if ( self::$rule_trigger_evaluate == 'match-none' ) { echo 'checked="checked"'; } ?>> Match None
                                    </label>
                                </div>
                                <div class="btn-group add-filter">
                                    <button type="button" class="btn btn-warning dropdown-toggle ladda-button" data-style="expand-right" data-toggle="dropdown" id='add-trigger-filter-button'>
                                        <?php _e('Add Filter' , 'inbound-mailer'); ?><span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu trigger-filters" role="menu" >
                                        <li><a><?php _e(' Select a filter...' , 'inbound-mailer' ); ?></a></li>
                                    </ul>
                                </div>


                                <?php
                                /* Load Trigger Filters if available */
                                if ( isset( self::$rule_trigger_filters ) ) {
                                    foreach (self::$rule_trigger_filters as $child_id => $filter) {

                                        $args = array(
                                            'trigger_id' => self::$rule_trigger,
                                            'trigger_filter_id' => $filter['trigger_filter_id'],
                                            'child_id' => $child_id,
                                            'defaults' => $filter
                                        );

                                        $html = self::ajax_build_trigger_filter( $args );
                                        echo $html;

                                    }

                                }
                                ?>

                        </td>
                    </tr>
                </table>
            </div>
        <?php
        }

        /**
         *
         */
        public static function print_actions_container() {

            ?>
            <div class='nav-container nav-hide actions-container' id='actions-container' >
                <!-- Split button -->
                <div class="btn-group btn-group-justified add-actions">
                    <div class="btn-group open">
                        <a type="button" class="btn btn-default ladda-button" data-style="expand-down" data-spinner-color="#000000" data-toggle="dropdown" id='add-action-block-button'>
                            Add Actions <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a	id="if-then-else" class="add-action-block"><?php _e( 'Create Action Set' , 'inbound-pro' ); ?></a></li>
                        </ul>
                    </div>
                </div>
                <br>
                <br>

            </div>
             <?php

        }

        /**
         *
         */
        public static function print_actions( $post, $args ) {
            global $post;

            $html = self::ajax_build_action_block( $args['args']['block_id'] , $action_priority = null, $args['args']['block'] );
            echo $html;
        }

        public static function print_logs_container() {
            global $inbound_automation_logs, $post;

            $logs = array_reverse( $inbound_automation_logs->get_logs( $post->ID ) , true );

            ?>
            <style>
                .tr-log-entry-content {
                    display:none;
                }

                #th-log-id, #th-log-expand {
                    width:50px;
                }

                .tablesorter {
                    table-layout:fixed;
                    background:#fff;
                    max-width:100%;
                    border-spacing:0;
                    width:100%;
                    margin:10px 0;
                    border:1px solid #ddd;
                    border-collapse:separate;
                    *border-collapse:collapsed;
                    -webkit-box-shadow:0 0 4px rgba(0,0,0,0.10);
                    -moz-box-shadow:0 0 4px rgba(0,0,0,0.10);
                    box-shadow:0 0 4px rgba(0,0,0,0.10);
                }
                .tablesorter th, .tablesorter td {
                    padding:8px;
                    line-height:18px;
                    text-align:left;
                    border-top:1px solid #ddd;
                    word-wrap:break-word;
                }
                .tablesorter th {
                    background:#eee;
                    background:-webkit-gradient(linear, left top, left bottom, from(#f6f6f6), to(#eee));
                    background:-moz-linear-gradient(top, #f6f6f6, #eee);
                    text-shadow:0 1px 0 #fff;
                    font-weight:bold;
                    vertical-align:bottom;
                }
                .tablesorter td {
                    vertical-align:top;
                }
                .tablesorter thead:first-child tr th,
                .tablesorter thead:first-child tr td {
                    border-top:0;
                }

                .tablesorter tbody + tbody {
                    border-top:2px solid #ddd;
                }
                .tablesorter th + th,
                .tablesorter td + td,
                .tablesorter th + td,
                .tablesorter td + th {
                    border-left:1px solid #ddd;
                }
                .tablesorter thead:first-child tr:first-child th,
                .tablesorter tbody:first-child tr:first-child th,
                .tablesorter tbody:first-child tr:first-child td {
                    border-top:0;
                }
            </style>
            <div class='nav-container nav-hide logs-container' id='logs-container' >
                <table class='tablesorter'>
                    <tr>
                        <th class=" sort-header" id='th-log-id'><?php _e( 'Log ID' , 'inbound-pro' ); ?></th>
                        <th class=" sort-header" id='th-log-title'><?php _e( 'Log Title' , 'inbound-pro' ); ?></th>
                        <th class=" sort-header" id='th-log-date'><?php _e( 'Log Date' , 'inbound-pro' ); ?></th>
                        <th class=" sort-header" id='th-log-date'><?php _e( 'Job ID' , 'inbound-pro' ); ?></th>
                        <th class=" sort-header" id='th-log-date'><?php _e( 'Log Type' , 'inbound-pro' ); ?></th>
                        <th class=" sort-header" id='th-log-expand'><?php _e( 'Expand' , 'inbound-pro' ); ?></th>
                    </tr>
                    <?php
                    $i=0;
                    foreach ($logs as $key => $log) {

                        if ($i>50) {
                            break;
                        }
                        echo '<tr class="tr-log-entry"	data-id="'.$key.'">';
                        echo '	<td class="td-log log-title">'.$key.'</td>';
                        echo '	<td class="td-log log-title">'.$log['log_title'].'</td>';
                        echo '	<td class="td-log log-datetime">'.$log['log_datetime'].'</td>';
                        echo '	<td class="td-log log-datetime">'.$log['job_id'].'</td>';
                        echo '	<td class="td-log log-datetime">'.$log['log_type'].'</td>';
                        echo '	<td class="td-log log-datetime"><a href="#" class="toggle-log-content" data-id="'.$key.'">+/-</a></td>';
                        echo '</tr>';
                        echo '<tr class="tr-log-entry-content" id="log-content-'.$key.'" data-id="'.$key.'">';
                        echo '	<td colspan=6 class="td-log-entry-content"data-id="'.$key.'" >';
                        echo 		stripslashes(base64_decode($log['log_content']));
                        echo '	</td>';
                        echo '</td>';
                        echo '</tr>';
                        $i++;
                    }

                    ?>
                </table>
            </div>
        <?php
        }

        /**
         *	Constructs the rule data set and saves it in post meta
         *	@param INT $post_id
         */
        public static function save_automation( $post_id )
        {
            global $post;

            if ( !isset( $post ) ) {
                return;
            }

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
                return;
            }

            if ( ! current_user_can( 'edit_post', $post_id ) ){
                return;
            }

            if ($post->post_type!=self::$post_type)	{
                return;
            }

            /* load definitions */
            self::load_definitions();

            $rule = array();

            /* Save trigger */
            $rule['trigger'] = $_POST['trigger'];

            /* Save Trigger Filters */
            if ( isset ( $_POST[ 'trigger_filter_value' ] ) ) {

                $filters = array();

                foreach ( $_POST['trigger_filter_key'] as $id => $value ) {

                    ( isset( $_POST['trigger_filter_id'][$id] ) ) ? $filters[$id]['trigger_filter_id'] = $_POST['trigger_filter_id'][$id] : $filters[$id]['trigger_filter_id'] = null;
                    ( isset( $_POST['trigger_filter_key'][$id] ) ) ? $filters[$id]['trigger_filter_key'] = $_POST['trigger_filter_key'][$id] : $filters[$id]['key'] = null;
                    ( isset( $_POST['trigger_filter_compare'][$id] ) ) ? $filters[$id]['trigger_filter_compare'] = $_POST['trigger_filter_compare'][$id] : $filters[$id]['trigger_filter_compare'] = null;
                    ( isset( $_POST['trigger_filter_value'][$id] ) ) ? $filters[$id]['trigger_filter_value'] = $_POST['trigger_filter_value'][$id] : $filters[$id]['value'] = null;

                }

                $rule[ 'trigger_filters'] =	$filters;

            }

            /* Save Trigger Filter Evaulation Nature */
            if ( isset ( $_POST[ 'trigger_filters_evaluate' ] ) ) {
                $rule[ 'trigger_filters_evaluate' ] =	$_POST[ 'trigger_filters_evaluate' ] ;
            }


            /* Save Action Blocks */
            if ( isset ( $_POST['action_block_id'] ) ) {

                $action_blocks = array();

                foreach ( $_POST['action_block_id'] as $block_id ) {


                    if ( isset( $_POST['action_block_type'][$block_id] ) ) {
                        $action_blocks[$block_id]['action_block_id'] = $_POST['action_block_id'][$block_id];
                        $action_blocks[$block_id]['action_block_type'] = $_POST['action_block_type'][$block_id];
                        if (isset($_POST['action_block_filters_evaluate'][$block_id])) {
                            $action_blocks[$block_id]['action_block_filters_evaluate'] = $_POST['action_block_filters_evaluate'][$block_id];
                        }
                    }

                    /* Get Action Filter Conditions for this block if they exist */
                    $filters = array();

                    if ( isset( $_POST['action_filter_value'][$block_id] ) ) {

                        foreach ( $_POST['action_filter_value'][$block_id] as $id => $filter_value ) {

                            ( isset( $_POST['action_filter_id'][$block_id][$id] ) ) ? $filters[$id]['action_filter_id'] = $_POST['action_filter_id'][$block_id][$id] : $filters[$id]['action_filter_id'] = null;
                            ( isset( $_POST['action_filter_key'][$block_id][$id] ) ) ? $filters[$id]['action_filter_key'] = $_POST['action_filter_key'][$block_id][$id] : $filters[$id]['key'] = null;
                            ( isset( $_POST['action_filter_compare'][$block_id][$id] ) ) ? $filters[$id]['action_filter_compare'] = $_POST['action_filter_compare'][$block_id][$id] : $filters[$id]['action_filter_compare'] = null;
                            ( isset( $_POST['action_filter_value'][$block_id][$id] ) ) ? $filters[$id]['action_filter_value'] = $_POST['action_filter_value'][$block_id][$id] : $filters[$id]['value'] = null;

                        }

                    }

                    /* Add Filters to $action_blocks */
                    $action_blocks[$block_id]['filters'] = $filters;

                    /* Get Then Actions For This Block If They Exist */
                    $actions = array();
                    if ( isset( $_POST['action_name'][$block_id]['then'] ) ) {

                        foreach ( $_POST['action_name'][$block_id]['then'] as $child_id => $action_name ) {

                            $this_action = self::$actions[ $action_name ];

                            if ( !isset( $this_action['settings']) ) {
                                continue;
                            }

                            $actions[$child_id]['action_name'] = $action_name;
                            $actions[$child_id]['action_class_name'] = $this_action['class_name'];

                            foreach ( $this_action['settings'] as $setting ) {

                                if ( isset( $_POST[$setting['id']][$block_id]['then'][$child_id] ) ) {
                                    $actions[$child_id][$setting['id']] = $_POST[$setting['id']][$block_id]['then'][$child_id];
                                }

                            }

                        }

                    }

                    /* Add Actions to Action Block */
                    $action_blocks[$block_id]['actions']['then'] = $actions;


                    /* Get Else Actions For This Block If They Exist */
                    $actions = array();
                    if ( isset( $_POST['action_name'][$block_id]['else'] ) ) {

                        /* Get Action Definitions */
                        foreach (	$_POST['action_name'][$block_id]['else'] as $child_id => $action_name ) {

                            $this_action = self::$actions[ $action_name ];

                            if ( !isset( $this_action['settings']) ) {
                                continue;
                            }

                            $actions[$child_id]['action_name'] = $action_name;

                            foreach ( $this_action['settings'] as $setting ) {

                                if ( isset( $_POST[$setting['id']][$block_id]['else'][$child_id] ) ) {
                                    $actions[$child_id][$setting['id']] = $_POST[$setting['id']][$block_id]['else'][$child_id];
                                }

                            }
                        }
                    }

                    /* Add Actions to Action Block */
                    if ($actions) {
                        $action_blocks[$block_id]['actions']['else'] = $actions;
                    }
                }


                /* Save Actions */
                $rule['action_blocks'] = $action_blocks;

            }

            //print_r($rule);exit;
            /* Save rule into post meta */
            update_post_meta( $post_id , 'inbound_rule' , $rule );


        }


        public static function change_title_text( $text, $post ) {
            if ($post->post_type==self::$post_type) {
                return __( 'Enter Rule Name Here' , 'inbound-pro' );
            } else {
                return $text;
            }
        }


        /* Enqueue Admin Scripts */
        public static function enqueue_admin_scripts( $hook ) {
            global $post;

            if ( !isset($post) || $post->post_type != self::$post_type ) {
                return;
            }

            if ( $hook == 'post-new.php' ) {
            }

            if ( $hook == 'post.php' ) {
            }

            if ($hook == 'post-new.php' || $hook == 'post.php') {

                /* disable heartbeat */
                wp_deregister_script( 'heartbeat' );

                /* disable compression test */
                wp_deregister_script( 'wp-compression-test');

                wp_enqueue_script('jquery-ui-core');

                //wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script( 'jquery-effects-core' );
                wp_enqueue_script( 'jquery-effects-highlight' );

                wp_enqueue_style( 'inbound_automation_admin_css' ,	INBOUND_AUTOMATION_URLPATH . 'assets/css/admin.post-edit.css' );
                wp_enqueue_style('inbound-automation-admin-jquery-ui-css',	INBOUND_AUTOMATION_URLPATH . 'assets/css/jquery-ui.css');

                /* load BootStrap */
                wp_register_script( 'bootstrap-js' , INBOUND_AUTOMATION_URLPATH .'assets/libraries/BootStrap/js/bootstrap.min.js');
                wp_enqueue_script( 'bootstrap-js' );

                /* BootStrap CSS */
                wp_register_style( 'bootstrap-css' , INBOUND_AUTOMATION_URLPATH . 'assets/libraries/BootStrap/css/bootstrap.css');
                wp_enqueue_style( 'bootstrap-css' );

                /* Spin.min.js - For button loading effect */
                wp_register_script( 'spin-js' , INBOUND_AUTOMATION_URLPATH .'assets/libraries/Ladda/spin.min.js');
                wp_enqueue_script( 'spin-js' );

                /* Ladda.min.js - For button loading effect*/
                wp_register_script( 'ladda-js' , INBOUND_AUTOMATION_URLPATH .'assets/libraries/Ladda/ladda.min.js');
                wp_enqueue_script( 'ladda-js' );

                /* Load ladda css */
                wp_register_style( 'ladda-css' , INBOUND_AUTOMATION_URLPATH . 'assets/libraries/Ladda/ladda-themeless.min.css');
                wp_enqueue_style( 'ladda-css' );

                /* Load FontAwesome */
                wp_register_style( 'font-awesome' , INBOUND_AUTOMATION_URLPATH . 'assets/libraries/FontAwesome/css/font-awesome.min.css');
                wp_enqueue_style( 'font-awesome' );

                /* Load Main JS File */
                wp_register_script( 'inbound-rules-js' , INBOUND_AUTOMATION_URLPATH .'assets/js/admin.rule-setup.js');
                wp_enqueue_script( 'inbound-rules-js' );

                /* Enqueue Select2 */
                wp_register_script( 'select2' , INBOUND_AUTOMATION_URLPATH .'assets/libraries/Select2/selecct2.min.js');
                wp_enqueue_script( 'select2' );

                /* load Sweet Alert */
                wp_enqueue_script('sweetalert', INBOUND_AUTOMATION_URLPATH . 'assets/libraries/SweetAlert/dist/sweetalert.min.js');
                wp_enqueue_style('sweetalert', INBOUND_AUTOMATION_URLPATH . 'assets/libraries/SweetAlert/dist/sweetalert.css');

            }
        }

        /**
         *  	Print Admin Scripts
         */
        public static function print_admin_scripts() {
            global $post;

            if ( !isset($post) || $post->post_type != self::$post_type ) {
                return;
            }

            ?>
            <script>


            </script>
        <?php

        }

        /* Builds Action Block HTML
        * @param action_block_type STRING
        * @param action_block_id STRING Which Action Block Placement Spot Is Next In Line
        * @param action_priority STRING Which Action Queue placement spot is next in line within the action block
        * @param block ARRAY data for generating historic action blocks
        *
        * @returns html STRING constructed html string
        */
        public static function ajax_build_action_block( $action_block_id = null , $action_priority = null, $block = null ) {

            /* loads automation definitions */
            self::load_definitions();

            ( isset($_REQUEST['action_block_id']) ) ? $action_block_id = $_REQUEST['action_block_id'] : $action_block_id;
            ( isset($_REQUEST['action_block_priority']) ) ? $action_block_id = $_REQUEST['action_block_priority'] : $action_block_id;

            $html = '';
            $html .= '<div class="action-block-wrapper" data-action-block="'. $action_block_id .'">';
            //$html .= '<div class="action-block-delete"><button class="btn btn-default btn-small delete-action-block" title="'.__('Delete Action Block' , 'inbound-pro' ) .'" data-action-block="'. $action_block_id .'"><i class="fa fa-times"></i></button></div>';
            $html .= "<div class='action-block' data-block-id='".$action_block_id."' >";
            $html .= "<div class='action-block-div' data-action-block-priority='".$action_priority."'>";
            $html .= "	    <input type='hidden' name='action_block_id[".$action_block_id."]' value='".$action_block_id."'>";

            /* Action Filters */
            $html .= "		<div id='action-block-filters' class='action-block-conditions'>";
            $html .= "            <h2>".__('Additional Conditions' , 'inbound-pro') ."</h2>";

            /* Add Action Filters */
            $html .= '            <div class="dropdown-add-filters" data-block-id="'.$action_block_id.'">';
            $html .= '                <span class="dropdown-toggle add-action-filters" data-block-id="'.$action_block_id.'" data-action-type="then" type="button"  data-toggle="dropdown">';
            $html .= '                   <i class="fa fa-plus-circle"></i>';
            $html .= '                </span>';
            $html .= '                <ul class="dropdown-menu action-filter-options" role="menu" aria-labelledby="menu1" >';
            $html .= '               </ul>';
            $html .= '            </div>';

            /* Add Action Filter Evaluation Nature */
            $html .= "            <div class='action-block-filter-evaluate' style='display:inline;'>";
            $html .= '                <div class="btn-group" data-toggle="buttons">';
            $html .= '                    <label class="btn btn-default	active">';
            $html .= '                      <input type="radio" name="action_block_filters_evaluate['.$action_block_id.']" value="match-all" checked="checked"> '. __( 'Match All' , 'inbound-pro' );
            $html .= '                    </label>';
            $html .= '                    <label class="btn btn-default	">';
            $html .= '                          <input type="radio" name="action_block_filters_evaluate['.$action_block_id.']" value="match-any"> ' .  __( 'Match Any' , 'inbound-pro' );
            $html .= '                    </label>';
            $html .= '                    <label class="btn btn-default">';
            $html .= '                         <input type="radio" name="action_block_filters_evaluate['.$action_block_id.']" value="match-none"> ' .__( 'Match None' , 'inbound-pro' );
            $html .= '                    </label>';
            $html .= '                </div>';
            $html .= "            </div>";

            /* Add Action Filters Here */
            $html .= '            <div class="action-block-filters-container" data-action-block-id="'.$action_block_id.'" >';


            /* Prepare Filters if Action Block Manually Evoked */
            if ( isset( $block['filters'] ) ) {

                //print_r($block);
                foreach ( $block['filters'] as $child_id => $filter ) {

                    $args = array(
                        'action_filter_id' => $filter['action_filter_id'],
                        'action_block_id' => $action_block_id,
                        'child_id' => $child_id,
                        'defaults' => $filter
                    );

                    $html .= self::ajax_build_db_lookup_filter( $args );

                }
            }

            $html .= "				</div>";
            $html .= "		</div>";

            /* Action Then Actions */
            $html .= "		<div class='action-block-actions'>";
            $html .= "			<h2>".__( 'Actions to perform if conditions are met' , 'inbound-pro' ) ."</h2>";

            /* Add Action Buttons */
            $html .= '            <div class="dropdown-add-actions" data-block-id="'.$action_block_id.'"  data-action-type="then" >';
            $html .= '                <span class="dropdown-toggle add-actions" data-block-id="'.$action_block_id.'"  data-action-type="then"  type="button"  data-toggle="dropdown">';
            $html .= '                   <i class="fa fa-plus-circle"></i>';
            $html .= '                </span>';
            $html .= '                <ul class="dropdown-menu action-options" role="menu" aria-labelledby="menu1" >';
            $html .= '               </ul>';
            $html .= '            </div>';

            /* Add Then Actions Here */
            $html .= "            <div class='action-block-actions-container'  >";

            /* Prepare Actions if Action Block Manually Evoked */
            if ( isset( $block['actions']['then'] ) ) {

                //print_r($block);
                foreach ( $block['actions']['then'] as $child_id => $action ) {

                    $args = array(
                        'action_name' => $action['action_name'],
                        'action_type' => 'then',
                        'action_block_id' => $action_block_id,
                        'child_id' => $child_id,
                        'input_action_name_name' => 'action_name',
                        'defaults' => $action
                    );

                    $html .= self::ajax_build_action( $args );

                }
            }

            $html .= "				</div>";
            $html .= "		</div>";
            $html .= "		<div class='action-block-actions'>";
            $html .= "			<h2>".__( 'Actions to perform if conditions are not met' , 'inbound-pro' ) ."</h2>";
            $html .= "				<div class='action-block-else-actions-container'>";

            /* Prepare Actions if Action Block Manually Evoked */
            if ( isset( $block['actions']['else'] ) ) {

                //print_r($block);
                foreach ( $block['actions']['else'] as $child_id => $action ) {

                    $args = array(
                        'action_name' => $action['action_name'],
                        'action_type' => 'else',
                        'action_block_id' => $action_block_id,
                        'child_id' => $child_id,
                        'input_action_name_name' => 'action_name',
                        'defaults' => $action
                    );

                    $html .= self::ajax_build_action( $args );

                }
            }

            $html .= "				</div>";
            $html .= "		</div>";
            $html .= "</div>";
            $html .= "</div></div>";


            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                echo $html;
                die();
            } else {
                return $html;
            }
        }

        /***
         * Ajax listener - build the html for a trigger filter
         */
        public static function ajax_build_trigger_filter( $args ) {

            /* loads automation definitions */
            self::load_definitions();

            /* Get Parameters */
            ( isset( $args['trigger_id'] ) ) ? $args['trigger_id'] :$args['trigger_id'] = $_REQUEST['trigger_id'];
            ( isset( $args['trigger_filter_id'] ) ) ? $args['trigger_filter_id'] :	$args['trigger_filter_id'] = $_REQUEST['trigger_filter_id'];
            ( isset( $args['child_id'] ) ) ?	$args['child_id'] :	$args['child_id'] = $_REQUEST['child_id'];
            ( isset( $args['defaults'] ) ) ? $args['defaults'] : $args['defaults'] = $_REQUEST['defaults'];


            /* Die If No Filter Selected */
            if ( $args['trigger_filter_id'] == '-1'	&&	defined( 'DOING_AJAX' ) && DOING_AJAX	) {
                die();
            } else if ($args['trigger_filter_id'] == '-1' ) {
                return '';
            }

            $key_args = array(
                'name' => 'trigger_filter_key',
                'type' => self::$argument_filters[ $args['trigger_id'] ][ $args['trigger_filter_id'] ]['key_input_type'],
                'options' => self::$argument_filters[ $args['trigger_id'] ][ $args['trigger_filter_id'] ]['keys'],
                'child_id' => $args['child_id'],
                'default' => $args['defaults'],
                'class' => 'trigger-filter-key'
            );


            $compare_args = array(
                'name' => 'trigger_filter_compare',
                'type' => 'dropdown',
                'options' => self::$argument_filters[ $args['trigger_id'] ][ $args['trigger_filter_id'] ]['compare'],
                'child_id' => $args['child_id'],
                'default' => $args['defaults'],
                'class' => 'trigger-filter-compare'
            );

            $value_args = array(
                'name' => 'trigger_filter_value' ,
                'type' => self::$argument_filters[ $args['trigger_id'] ][ $args['trigger_filter_id'] ]['value_input_type'],
                'options' => self::$argument_filters[ $args['trigger_id'] ][ $args['trigger_filter_id'] ]['values'],
                'child_id' => $args['child_id'],
                'default' => $args['defaults'],
                'class' => 'trigger-filter-value'
            );

            $key_input = self::build_input( $key_args );
            $compare_input = self::build_input( $compare_args );
            $value_input = self::build_input( $value_args );
            $header_name = str_replace('_', ' ', $args['trigger_filter_id']);
            $header_name = ucfirst(strtolower($header_name));

            $html = "<div class='filter-container'>";
            $html .= "<div><table class='table-filter' data-child-id='".$args['child_id']."'>";
            $html .= "	<tr class='tr-filter'>";
            $html .= "		<td class='td-filter-key'>";
            $html .= "			<input type='hidden' name='trigger_filter_id[".$args['child_id']."]' value='".$args['trigger_filter_id']."'>";
            $html .= 			$key_input;
            $html .= "		</td>";
            $html .= "		<td class='td-filter-compare'>";
            $html .= 			$compare_input;
            $html .= "		</td>";
            $html .= "		<td class='td-filter-value'>";
            $html .= 			$value_input;
            $html .= "		</td>";
            $html .= "		<td class='td-filter-delete'>";
            $html .= '			<span class="btn btn-default btn-mini delete-filter" title="'. __('Delete Trigger Filter' , 'inbound-pro' ).'" id="bigtest"><i class="fa fa-times"></i></span>';
            $html .= "		</td>";
            $html .= "	</tr>";
            $html .= "</table>";
            $html .= "</div></div>";

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                echo $html;
                die();
            } else {
                return $html;
            }
        }

        /* AJAX - Build DB Lookup Filter HTML */
        public static function ajax_build_db_lookup_filter( $args ) {

            /* loads automation definitions */
            self::load_definitions();

            /* Get Parameters */
            ( isset( $args['action_filter_id'] ) ) ? $args['action_filter_id'] :	$args['action_filter_id'] = $_REQUEST['action_filter_id'];
            ( isset( $args['action_block_id'] ) ) ?	$args['action_block_id'] :	$args['action_block_id'] = $_REQUEST['action_block_id'];
            ( isset( $args['child_id'] ) ) ?	$args['child_id'] :	$args['child_id'] = $_REQUEST['child_id'];
            ( isset( $args['defaults'] ) ) ? $args['defaults'] : $args['defaults'] = $_REQUEST['defaults'];

            /* Get Filter Definitions */
            $this_filter =	$args['action_filter_id'];

            /* Die If No Filter Selected */
            if ( $this_filter == '-1'	&&	defined( 'DOING_AJAX' ) && DOING_AJAX	) {
                die();
            } else if ($this_filter == '-1' ) {
                return '';
            }

            $key_args = array(
                'name' => 'action_filter_key',
                'type' => self::$db_lookup_filters[$this_filter]['key_input_type'],
                'options' => self::$db_lookup_filters[$this_filter]['keys'],
                'action_block_id' => $args['action_block_id'],
                'child_id' => $args['child_id'],
                'default' => $args['defaults'],
                'class' => 'action-filter-key'
            );

            $compare_args = array(
                'name' => 'action_filter_compare',
                'type' => 'dropdown',
                'options' => self::$db_lookup_filters[$this_filter]['compare'],
                'action_block_id' => $args['action_block_id'],
                'child_id' => $args['child_id'],
                'default' => $args['defaults'],
                'class' => 'action-filter-compare'
            );

            $value_args = array(
                'name' => 'action_filter_value',
                'type' => self::$db_lookup_filters[$this_filter]['value_input_type'],
                'options' => self::$db_lookup_filters[$this_filter]['values'],
                'action_block_id' => $args['action_block_id'],
                'child_id' => $args['child_id'],
                'default' => $args['defaults'],
                'class' => 'action-filter-value'
            );

            $key_input = self::build_input( $key_args );
            $compare_input = self::build_input( $compare_args );
            $value_input = self::build_input( $value_args );
            $header_name = str_replace('_', ' ', $this_filter);
            $header_name = ucfirst(strtolower($header_name));

            $html = "<div class='filter-container'>";
            $html .= '<table class="table-filter" data-child-id="'.$args['child_id'].'">';
            $html .= "	<tr class='tr-filter'>";
            $html .= "		<td class='td-filter-type'>";
            $html .= "			<input type='hidden' name='action_filter_id". ( isset($args['action_block_id'] ) && $args['action_block_id'] ? '['.$args['action_block_id'].']' : '' ) . "[".$args['child_id']."]' value='".$this_filter."'>";
            $html .= 			$header_name .':';
            $html .= "		</td>";
            $html .= "		<td class='td-filter-key'>";
            $html .= 			$key_input;
            $html .= "		</td>";
            $html .= "		<td class='td-filter-compare'>";
            $html .= 			$compare_input;
            $html .= "		</td>";
            $html .= "		<td class='td-filter-value'>";
            $html .= 			$value_input;
            $html .= "		</td>";
            $html .= "		<td class='td-filter-delete'>";
            $html .= '	        <div class="action-filter-delete">';
            $html .= '              <span class="delete-filter" title="'. __('Delete Action Filter' ,'inbound-pro' ) .'" data-block-id="'.$args['action_block_id'].'" data-child-id="'.$args['child_id'].'">';
            $html .= '                  <i class="fa fa-times"></i>';
            $html .= '              </span>';
            $html .= '          </div>';
            $html .= "		</td>";
            $html .= "	</tr>";
            $html .= "</table>";
            $html .= "</div>";

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                echo $html;
                die();
            } else {
                return $html;
            }
        }


        /**
         * AJAX - Build Action HTML
         */
        public static function ajax_build_action( $args ) {

            /* loads automation definitions */
            self::load_definitions();

            /* Get Parameters */
            ( isset( $args['action_name'] ) ) ? $args['action_name'] :	$args['action_name'] = $_REQUEST['action_name'];
            ( isset( $args['action_type'] ) ) ? $args['action_type'] :	$args['action_type'] = $_REQUEST['action_type'];
            ( isset( $args['action_block_id'] ) ) ?	$args['action_block_id'] :	$args['action_block_id'] = $_REQUEST['action_block_id'];
            ( isset( $args['child_id'] ) ) ?	$args['child_id'] :	$args['child_id'] = $_REQUEST['child_id'];
            ( isset( $args['defaults'] ) ) ? $args['defaults'] : $args['defaults'] = $_REQUEST['defaults'];

            $this_action =	$args['action_name'];

            /* Die If No Action Selected */
            if ( $this_action == '-1'	&&	defined( 'DOING_AJAX' ) && DOING_AJAX	) {
                die();
            } else if ($this_action == '-1' ) {
                return '';
            }

            $header_name = str_replace('_', ' ', $this_action);
            $header_name = ucfirst(strtolower($header_name));

            $html = "<div class='action-sub-wrapper' data-action-type='".$args['action_type']."' data-block-id='".$args['action_block_id']."'  data-child-id='".$args['child_id']."'>";
            $html .= "<h4>";
            $html .=		$header_name;
            $html .= '        <div class="action-buttons">';
            $html .= '          <span class="up-action-order" title=" '. __( 'Move up' , 'inbound-pro' ) .'"  data-block-id="'.$args['action_block_id'].'"  data-child-id="'.$args['child_id'].'"><i class="fa fa-arrow-up"></i> </span>';
            $html .= '          <span class="down-action-order" title=" '. __( 'Move up' , 'inbound-pro' ) .'"  data-block-id="'.$args['action_block_id'].'"  data-child-id="'.$args['child_id'].'"><i class="fa fa-arrow-down"></i> </span>';
            $html .= '          <span class="delete-action" title=" '. __( 'Delete Action Block' , 'inbound-pro' ) .'"  data-block-id="'.$args['action_block_id'].'"  data-child-id="'.$args['child_id'].'"><i class="fa fa-times"></i> </span>';
            $html .= '        </div>';
            $html .= '</h4>';
            $html .= '<div class="action-container">';
            $html .= "  <table class='table-action' data-child-id='".$args['child_id']."'>";
            $html .= "	    <tr class='tr-action'>";
            $html .= "		    <td class='td-action-setting-label' colspan=2>";
            $html .= "			    <input type='hidden' name='action_name[".$args['action_block_id']."][".$args['action_type']."][".$args['child_id']."]' value='".$this_action."' data-id='action_name'>";
            $html .= "		    </td>";
            $html .= "	    </tr>";

            /* Build Settings for this Action */
            foreach ( self::$actions[ $this_action ]['settings'] as $setting ) {

                /* Map Arguments for Generating Action Setting */
                $setting_args = array(
                    'name' => $setting['id'],
                    'action_name' => $args['action_name'],
                    'action_type' => $args['action_type'],
                    'action_block_id' => $args['action_block_id'],
                    'type' => $setting['type'],
                    'child_id' => $args['child_id'],
                    'class' => $setting['id']
                );

                /* Setup Default Values */
                if ( isset( $args['defaults'][$setting['id']] ) ) {	/* If Generating From History Use Historic Value */

                    $setting_args['default'][$setting['id']] = $args['defaults'][$setting['id']];

                } else if ( isset( $setting['default'] ) ) { /* Else Use Default Action Value as defined in action definition If Available */

                    $setting_args['default'][$setting['id']] = $setting['default'];
                }

                /* Set Options if Available */
                ( isset( $setting['options'] ) && is_array( $setting['options'] ) ) ? $setting_args['options'] = $setting['options'] : $settings_args['options'] = null;

                /* Generate Action Setting HTML */
                $setting_html = self::build_input( $setting_args );

                /* prepare hidden fields */
                $class = '';
                if (isset($setting['hidden']) && $setting['hidden']) {
                    $class = 'hide-field';
                }

                /* prepare reveal rules */
                $extras = '';
                if (isset($setting['reveal']) && is_array($setting['reveal'])) {
                    $extras = ' data-reveal-selector="'.$setting['reveal']['selector'].'" data-reveal-value="'.$setting['reveal']['value'].'" ';
                }

                /* Print Action Setting Label and Input */
                $html .= "	<tr class='tr-action-child {$class}' ".$extras." >";
                $html .= "		<td class='td-action-setting-label'>";
                $html .= "			".$setting['label'];
                $html .= "		</td>";
                $html .= "		<td class='td-action-setting-value'>";
                $html .= "			".$setting_html;
                $html .= "		</td>";
                $html .= "	</tr>";

            }


            $html .= "</table>";
            $html .= "</div></div>";

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                echo $html;
                die();
            } else {
                return $html;
            }
        }

        /**
         *	Ajax Listener - Get's arguments associated with trigger as defined in trigger definition file
         */
        public static function ajax_load_trigger_arguments() {

            $target_trigger = $_REQUEST['trigger'];

            /* loads automation definitions */
            self::load_definitions();

            $filter_whitelist = array();

            if ( !isset(self::$triggers[$target_trigger]['arguments']) ) {
                echo json_encode( array( array( 'id' => '0' , 'label' => 'Error: No Filters for Selected Trigger' ) ) );
                exit;
            }

            foreach (self::$triggers[$target_trigger]['arguments'] as $filter) {
                $filter_whitelist[] = array(
                    'id' => $filter['id'],
                    'label' => $filter['label']
                );
            }

            echo json_encode( $filter_whitelist );
            die();
        }

        /**
         *	AJAX - Get DB Lookup Filters by Selected Trigger
         */
        public static function ajax_load_db_lookup_filters() {

            if ( !isset($_REQUEST['trigger']) ) {
                exit;
            }

            /* loads automation definitions */
            self::load_definitions();

            $target_trigger = $_REQUEST['trigger'];

            $filter_whitelist = array();


            if ( !isset(self::$triggers[$target_trigger]['db_lookup_filters']) ) {
                echo json_encode( array( array( 'id' => '0' , 'label' => 'Error: No Filters for Selected Trigger' ) ) );
                exit;
            }

            foreach (self::$triggers[$target_trigger]['db_lookup_filters'] as $filter) {
                $filter_whitelist[] = array(
                    'id' => $filter['id'],
                    'label' => $filter['label']
                );
            }

            echo json_encode( $filter_whitelist );
            die();
        }



        /**
         *	AJAX - Get Actions by Selected Trigger
         */
        public static function ajax_load_action_definitions() {

            /* loads automation definitions */
            self::load_definitions();

            $target_trigger = $_REQUEST['trigger'];

            $action_whitelist = array();

            if ( !isset(self::$triggers[$target_trigger]['actions']) ) {
                echo json_encode( array( array( 'id' => '0' , 'label' => 'Error: No Filters for Selected Trigger' ) ) );
                exit;
            }

            foreach (self::$actions as $action) {
                if ( in_array( $action['id'] , self::$triggers[$target_trigger]['actions'] ) ) {
                    $action_whitelist[] = array(
                        'id' => $action['id'],
                        'label' => $action['label']
                    );
                }
            }

            echo json_encode( $action_whitelist );
            die();
        }

        /**
         *	Build Filter Input HTML - Generates ( Key , Compare , Value )
         */
        public static function build_input( $args ) {
            $html = '';

            $block_id = ( isset($args['action_block_id']) ) ? $args['action_block_id'] : '';
            $child_id = ( isset($args['child_id']) ) ? $args['child_id'] : '';
            $action_type = ( isset($args['action_type']) ) ? $args['action_type'] : '';

            /* construct input name */
            $name = $args['name']
                . ( $block_id ? '['.$block_id.']' : '' )
                . ( $action_type ? '['. $action_type.']' : '' )
                . '['.$args['child_id'].']';


            /* setup default */
            $default = (isset($args['default'][$args['name']])) ? $args['default'][$args['name']] : '';

            switch ($args['type']) {
                case 'dropdown' :
                    //echo $args['name'].':'.$args['default'][$args['name']].'<br>';
                    $html .= '<select name="'. $name .'" class="'.$args['class'].'" data-block-id="'.$block_id.'" data-child-id="'.$child_id.'" data-id="'.$args['name'].'">';
                    foreach ($args['options'] as $id => $label) {
                        $html .= '<option value="'.$id.'" '. ( $default == $id ? 'selected="selected"' : '' ) .'>'.$label.'</option>';
                    }
                    $html .= '</select>';
                    break;
                case 'select2' :


                    if (!$default) {
                        $default = array();
                    }

                    //echo $args['name'].':'.$args['default'][$args['name']].'<br>';
                    $html .= '<select name="'. $name .'[]" class="'.$args['class'].' select2" data-block-id="'.$block_id.'" data-child-id="'.$child_id.'"  data-id="'.$args['name'].'"  multiple>';
                    foreach ($args['options'] as $id => $label) {
                        $html .= '<option value="'.$id.'" '. ( in_array( $id , $default ) ? 'selected="selected"' : '' ) .'>'.$label.'</option>';
                    }
                    $html .= '</select>';
                    break;
                case 'text':
                    $html .= '<input type="text" name="'.$name.'" value="'.$default.'" data-block-id="'.$block_id.'" data-child-id="'.$child_id.'"  data-id="'.$args['name'].'">';
                    break;
                case 'checkbox':

                    $html .=	'<table >';

                    foreach ($args['options'] as $id=>$label) {
                        $html .= '<tr>';
                        $html .= '<td data-field-type="checkbox">';
                        $html .=	'<input type="checkbox"	name="'.$name.'[]" class="'.$args['class'].'"  data-id="'.$args['name'].'" value="'.$id.'" '. ( isset($args['default'][$args['name']]) && in_array( $id , $args['default'][$args['name']] ) ? 'checked="checked"' : '' ) .'>';
                        $html .=	'<label for="'.$id.'">&nbsp;&nbsp;'.$label.'</label>';
                        $html .=	'</td>';
                        $html .=	"</tr>";
                    }

                    $html .=	"</table>";

                    //$html .=	'<div class="lp_tooltip tool_checkbox" title="'.$field['description'].'"></div>';
                    break;
            }

            return $html;
        }
    }



    /**
     *	Hook metaboxes into admin_init
     */
    function inbound_automation_metaboxes() {
        $GLOBALS['Inbound_Metaboxes_Automation'] = new Inbound_Metaboxes_Automation;
    }
    add_action('admin_init' , 'inbound_automation_metaboxes' , 21 );
}