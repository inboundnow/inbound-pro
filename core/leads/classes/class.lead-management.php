<?php

/**
 * Class adds 'Bulk Actions' section that provides for batch actions against lead searches. This class is meant to improve on the
 *
 * @package Leads
 * @subpackage BulkActions
 */


class Leads_Manager {

    static $relation;
    static $page;
    static $per_page;
    static $paged;
    static $order;
    static $orderby;
    static $orderbys;
    static $orderbys_flip;
    static $num; /* number of leads affected */
    static $on;
    static $what;
    static $tag;
    static $keyword;
    static $query; /* query object */
    static $taxonomies; /* array of wp-lead taxonomies */
    static $statuses; /* array of wp-lead taxonomies */

    /**
     *  Initiate class
     */
    public function __construct() {

        self::load_static_vars();
        self::load_hooks();

    }

    /**
     *  Load hooks and filters
     */
    public static function load_hooks() {

        /* load static vars */
        add_action('admin_init', array(__CLASS__, 'load_static_vars'));
        /* load admin scripts */
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts'));
        /* perform lead manage actions by ajax*/
        add_action('wp_ajax_perform_actions', array(__CLASS__, 'ajax_perform_actions'));
        /* ajax listener for loading more leads */
        add_action('wp_ajax_leads_ajax_load_more_leads', array(__CLASS__, 'ajax_load_more_leads'));
        /* ajax listener for deleting lead from list */
        add_action('wp_ajax_leads_delete_from_list', array(__CLASS__, 'ajax_delete_from_list'));
        /* ajax listener export opration */
        add_action('wp_ajax_leads_export_list', array(__CLASS__, 'ajax_leads_export_list'));


    }

    /**
     *  Load constants
     */
    public static function load_static_vars() {

        if (!isset($_REQUEST['page']) || $_REQUEST['page'] != 'lead_management') {
            return;
        }

        /* clean POST and REQUEST arrays of added slashes */
        $_POST = stripslashes_deep($_POST);
        $_REQUEST = stripslashes_deep($_REQUEST);

        /* set ordering & paging vars */
        self::$per_page = 60;
        self::$page = empty($_REQUEST['pull_page']) ? 1 : intval($_REQUEST['pull_page']);
        self::$paged = empty($_REQUEST['paged']) ? 1 : intval($_REQUEST['paged']);
        self::$orderby = (isset($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby']) : '';
        self::$order = (isset($_REQUEST['order'])) ? strtoupper(sanitize_text_field($_REQUEST['order'])) : 'ASC';

        /* set ordering vars */
        self::$orderbys = array(
            __('Date First Created', 'inbound-pro' ) => 'date',
            __('Date Last Modified', 'inbound-pro' ) => 'modified',
            __('Alphabetical Sort', 'inbound-pro' ) => 'title',
            __('Status', 'inbound-pro' ) => 'post_status'
        );

        /* set ordering vars */
        self::$orderbys_flip = array_flip(self::$orderbys);

        /* number of leads affected by action if any */
        self::$num = (isset($_REQUEST['num'])) ? intval($_REQUEST['num']) : 0;

        self::$what = (isset($_REQUEST['what'])) ? sanitize_text_field($_REQUEST['what']) : "";

        self::$relation = (isset($_REQUEST['relation'])) ? sanitize_text_field($_REQUEST['relation']) : "AND";
        self::$tag = (isset($_REQUEST['t'])) ? sanitize_text_field($_REQUEST['t']) : '';

        self::$keyword = (isset($_REQUEST['s'])) ? sanitize_text_field($_REQUEST['s']) : '';

        self::$taxonomies = get_object_taxonomies('wp-lead', 'objects');

        self::$statuses = Inbound_Leads::get_lead_statuses();

    }

    /**
     *  Enqueues admin scripts
     */
    public static function enqueue_admin_scripts() {
        $screen = get_current_screen();

        if ($screen->id != 'wp-lead_page_lead_management') {
            return;
        }

        wp_enqueue_script(array('jquery', 'jqueryui', 'jquery-ui-selectable', 'editor', 'thickbox', 'media-upload'));

        wp_enqueue_script('selectjs', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/Select2/select2.min.js', array() , null , false );
        wp_enqueue_style('selectjs', INBOUNDNOW_SHARED_URLPATH . 'assets/includes/Select2/select2.min.css');


        wp_enqueue_script('tablesort', WPL_URLPATH . 'assets/js/management/tablesort.min.js');

        wp_enqueue_script('light-table-filter', WPL_URLPATH . 'assets/js/management/light-table-filter.min.js');
        wp_enqueue_script('modernizr', WPL_URLPATH . 'assets/js/management/modernizr.custom.js');
        wp_enqueue_script('tablesort', WPL_URLPATH . 'assets/js/management/tablesort.min.js');
        wp_enqueue_script('jquery-dropdown', WPL_URLPATH . 'assets/js/management/jquery.dropdown.js');
        wp_enqueue_script('jquery-ui', WPL_URLPATH . 'assets/js/management/jquery-ui.js');
        wp_enqueue_script('bulk-manage-leads', WPL_URLPATH . 'assets/js/management/admin.js' );

        wp_localize_script('bulk-manage-leads', 'bulk_manage_leads', array('admin_url' => admin_url('admin-ajax.php'), 'taxonomies' => self::$taxonomies ));
        wp_enqueue_style('wpleads-list-css', WPL_URLPATH . '/assets/css/admin-management.css');
        wp_enqueue_style('jquery-ui-css', WPL_URLPATH . '/assets/css/jquery-ui.css');
        wp_admin_css('thickbox');
        add_thickbox();
    }

    /**
     *  Displays main UI container
     */
    public static function display_ui() {
        global $wpdb;

        /* listen for and display notications */
        self::display_notifications();
        /* display header */
        self::display_headers();
        /* display filters */
        self::display_filters();
        /* build query */
        self::build_query();
        /* display pagination if applicable */
        self::display_pagination();
        /* display query reseults messages */
        self::display_results_message();
        /* display results table */
        self::display_results_table();
        /* display actions */
        self::display_row_actions();

    }


    /**
     *  display notifications
     */
    public static function display_notifications() {

        // Deal with any update messages we might have:
        if (!isset($_REQUEST['done'])) {
            return;
        }

        switch ($_REQUEST['done']) {
            case 'add':
                $message = sprintf(__("Added %d posts to the list '%s'", 'inbound-pro' ), self::$num, self::$what);
                break;
            case 'remove':
                $message = sprintf(__("Removed %d posts from the list '%s'.", 'inbound-pro' ), self::$num, self::$what);
                break;
            case 'tag':
                $message = sprintf(__("Tagged %d posts with &ldquo; %s &rdquo;", 'inbound-pro' ), self::$num, self::$what);
                break;
            case 'untag':
                $message = sprintf(__("Untagged %d posts with '%s'", 'inbound-pro' ), self::$num, self::$what);
                break;
            case 'delete_leads':
                $message = sprintf(__("%d leads permanently deleted", 'inbound-pro' ), self::$num);
                break;
        }

        ?>
        <div id='message' class='updated'>
            <p><strong><?php echo $message; ?></strong></p>
        </div>
        <?php
    }

    /**
     *  display headers
     */
public static function display_headers() {

    ?>
    <div class="wrap">
        <h2><?php _e('Lead Bulk Management', 'inbound-pro' ); ?></h2>

        <?php

        /* echo starter text if search not being ran yet */
        if (!isset($_REQUEST['submit'])) {
            echo '<p class="starter-text">' . __('To get started, select the lead criteria below to see all matching leads.', 'inbound-pro' ) . '</p>';
        }

        /* hide current page div */
        echo "<div id='paged-current'>" . self::$paged . "</div>";

        }

        /**
         *  Display filters
         */
        public static function display_filters() {
            ?>
            <div id="filters" class="inbound-lead-filters">
                <form id="lead-management-form" method="get" action="edit.php">
                    <input type="hidden" name="page" value="lead_management"/>
                    <input type="hidden" name="post_type" value="wp-lead"/>

                    <div id="top-filters"><?php
                        foreach (self::$taxonomies as $key => $taxonomy) {
                            if (!$taxonomy->hierarchical) {
                                //continue;
                            }
                            ?>

                            <div id="inbound-filter">
                                <div class="filter-label">
                                    <label for="taxonomy"><?php _e(sprintf('Select by %s:', $taxonomy->labels->singular_name), 'inbound-pro' ); ?></label>
                                </div>
                                <?php echo self::build_taxonomy_select($taxonomy, 'multiple'); ?>
                            </div>
                            <?php
                        }
                        ?>
                        <div id="inbound-filter">
                            <div class="filter-label">
                                <label for="wp_lead_status"><?php _e('Select by Status:', 'inbound-pro' ); ?>
                                </label>
                            </div>
                            <?php echo self::build_lead_status_select(); ?>
                        </div>
                        <div id="inbound-filter">
                            <div class="filter-label">
                                <label for="orderby"><?php _e('Match Condition:', 'inbound-pro' ); ?></label></div>
                            <select name="relation" id="relation">
                                <option value="AND" <?php echo(self::$relation == 'AND' ? ' selected="selected"' : ''); ?>><?php _e('Match All', 'inbound-pro' ); ?></option>
                                <option value="OR" <?php echo(self::$relation == 'OR' ? ' selected="selected"' : 'test'); ?>><?php _e('Match Any', 'inbound-pro' ); ?></option>

                            </select>
                        </div>
                    </div>
                    <div id="bottom-filters">
                        <div class="filter" id="lead-sort-by">
                            <div class="filter-label"><label for="orderby"><?php _e('Sort by:', 'inbound-pro' ); ?></label>
                            </div>
                            <select name="orderby" id="orderby">
                                <?php
                                foreach (self::$orderbys as $title => $value) {
                                    $selected = (self::$orderby == $value) ? ' selected="selected"' : '';
                                    echo "<option value='$value'$selected>$title</option>\n";
                                }
                                ?>
                            </select>
                            <select name="order" id="order">
                                <option value="asc" <?php (self::$order == 'ASC' ? ' selected="selected"' : ''); ?>><?php _e('Ascending', 'inbound-pro' ); ?></option>
                                <option value="desc" <?php (self::$order == 'DESC' ? ' selected="selected"' : ''); ?>><?php _e('Descending', 'inbound-pro' ); ?></option>
                            </select>
                        </div>


                        <div class="filter" id="lead-keyword-filter">
                            <label for="s"><?php _e('Keyword:', 'inbound-pro' ); ?></label>
                            <input type="text" name="s" id="s" value="<?php echo htmlentities(self::$keyword); ?>" title="<?php _e('Use % for wildcards.', 'inbound-pro' ); ?>"/>
                        </div>


                        <div class="filter" id="lead-tag-filter">
                            <label for="s"><?php _e('Tag:', 'inbound-pro' ); ?></label>
                            <input type="text" name="t" id="t" value="<?php echo htmlentities(self::$tag); ?>" title="'foo, bar': posts tagged with 'foo' or 'bar'. 'foo+bar': posts tagged with both 'foo' and 'bar'"/>
                        </div>

                        <div class="filter">
                            <input type="submit" class="button-primary" value="<?php _e('Search Leads', 'inbound-pro' ); ?>" name="submit"/>
                        </div>

                    </div>
                </form>
            </div>
            <?php
        }

        /**
         *  Display hidden input fields
         */
        public static function display_hidden_action_fields() {

            wp_nonce_field('lead_management-edit');

            if (isset($_REQUEST['s']) && !empty($_REQUEST['s'])) {
                echo '<input type="hidden" name="s" value="' . urlencode($_REQUEST['s']) . '" />';
            }

            if (isset($_REQUEST['t']) && !empty($_REQUEST['t'])) {
                echo '<input type="hidden" name="t" value="' . urlencode($_REQUEST['t']) . '" />';
            }
        }

        /**
         *  Display pagination
         */
        public static function display_pagination() {

            $pagination = '';
            if (isset($query) && $query->max_num_pages > 1) {
                $current = preg_replace('/&?paged=[0-9]+/i', '', strip_tags($_SERVER['REQUEST_URI'])); // I'll happily take suggestions on a better way to do this, but it's 3am so

                $pagination .= "<div class='tablenav-pages'>";

                if (self::$paged > 1) {
                    $prev = self::$paged - 1;
                    $pagination .= "<a class='prev page-numbers' href='$current&amp;paged=$prev'>&laquo; " . __('Previous', 'inbound-pro' ) . "</a>";
                }

                for ($i = 1; $i <= $query->max_num_pages; $i++) {
                    if ($i == self::$paged) {
                        $pagination .= "<span class='page-numbers current'>$i</span>";
                    } else {
                        $pagination .= "<a class='page-numbers' href='$current&amp;paged=$i'>$i</a>";
                    }
                }

                if (self::$paged < $query->max_num_pages) {
                    $next = self::$paged + 1;
                    $pagination .= "<a class='next page-numbers' href='$current&amp;paged=$next'>" . __('Next', 'inbound-pro' ) . " &raquo;</a>";
                }

                $pagination .= "</div>";
            }

            echo $pagination;

        }

        /**
         *  Display results query
         */
        public static function display_results_message() {
            // Criteria were given, but no posts were matched.
            if (empty(self::$query->posts)) {
                echo '<p>' . __('No posts matched that criteria, sorry! Try again with something different.', 'inbound-pro' ) . '</p>';
                return;
            }

            echo '<div style="margin-top:20px;font-style:italic">';
            echo '		<div id="display-lead-total">';
            echo '			' . __('search returned ', 'inbound-pro' ) . '<strong><span id="lead-total-found">' . self::$query->found_posts . ' </span></strong>' . __('results', 'inbound-pro' );
            echo '		</div>';
            echo '		<div id="display-lead-count">';
            echo '			<i class="lead-spinner"></i>';
            echo '			<span id="lead-count-text">' . __('Grabbing Matching Leads', 'inbound-pro' ) . '</span>';
            echo '		</div>';
            echo '	<div class="table-search">';
            echo '		<input type="search" class="light-table-filter" data-table="widefat" placeholder="' . __('Filter Results Below', 'inbound-pro' ) . '" /><span id="search-icon"></span>';

            echo '	</div>';
            echo '</div>';

        }


        /**
         *  Display results table
         */
        public static function display_results_table() {

        if (!isset(self::$query->posts)) {
            return;
        }

        ?>
        <form method="post" id="man-table" action="<?php echo admin_url('admin.php'); ?>">
            <input type="hidden" name="action" value="lead_action"/>
            <?php
            if (isset($_GET['wplead_list_category'])){
                foreach($_GET['wplead_list_category'] as $list_id) {
                    echo '<input type="hidden" name="wplead_list_category[]" value="'.$list_id.'"/>';
                }
            }
            ?>
            <div id="posts">

                <table class="widefat" id="lead-manage-table">
                    <thead>
                    <tr>
                        <th class="checkbox-header no-sort" scope="col">
                            <input type="checkbox" id="toggle" title="Select all posts"/></th>
                        <th class="count-sort-header" scope="col">#</th>
                        <th scope="col"><?php _e('Date', 'inbound-pro' ); ?></th>
                        <th scope="col"><?php _e('Email', 'inbound-pro' ); ?></th>
                        <th scope="col"><?php _e('Current Lists', 'inbound-pro' ); ?></th>
                        <th scope="col"><?php _e('Current Tags', 'inbound-pro' ); ?></th>
                        <th scope="col" class="no-sort"><?php _e('View', 'inbound-pro' ); ?></th>
                        <th scope="col"><?php _e('ID', 'inbound-pro' ); ?></th>
                        <?php do_action('inbound_bulk_lead_action_list_header');?>
                    </tr>
                    </thead>
                    <tbody id="the-list">
                    <?php

                    $loop_count = 1;
                    $i = 0;

                    foreach (self::$query->posts as $post) {

                        echo '<tr' . ($i++ % 2 == 0 ? ' class="alternate"' : '') . '>';

                        /* show checkbox */
                        echo '<td><input class="lead-select-checkbox" type="checkbox" name="ids[]" value="' . $post->ID . '" /></td>';

                        /* show count */
                        echo '<td class="count-sort"><span>' . $loop_count . '</span></td>';

                        /* show publish date */
                        echo '<td>';
                        if ('0000-00-00 00:00:00' == $post->post_date) {
                            _e('Unpublished', 'inbound-pro' );
                        } else {
                            echo date(__('Y/m/d'), strtotime($post->post_date));
                        }
                        echo '</td>';

                        /* show email */
                        echo '<td>';
                        echo '	<span class="lead-email">' . $post->post_title . '</span>';
                        echo '</td>';

                        /* show lists */
                        echo '<td class="list-column-row">';
                        $terms = wp_get_post_terms($post->ID, 'wplead_list_category', 'id');
                        foreach ($terms as $term) {
                            echo '<span class="list-pill">' . $term->name . ' <i title="Remove This lead from the ' . $term->name . ' list" class="remove-from-list" data-lead-id="' . $post->ID . '" data-list-id="' . $term->term_id . '"></i></span> ';
                        }
                        echo '</td>';

                        /* show tags */
                        echo '<td class="tags-column-row">';
                        $tags = wp_get_post_terms($post->ID, 'lead-tags', 'id');

                        if ($tags) {
                            foreach ($tags as $tag) {
                                echo "<a title='Click to Edit Lead Tag Name' target='_blank' href='" . admin_url('edit-tags.php?action=edit&taxonomy=lead-tags&tag_ID=' . $tag->term_id . '&post_type=wp-lead') . "'>$tag->name</a>, ";
                            }
                        } else {
                            _e('No tags', 'inbound-pro' );
                        }
                        echo '</td>';

                        /* show link to lead */
                        echo '<td>';
                        echo '	<a class="thickbox inbound-thickbox" href="post.php?action=edit&post=' . $post->ID . '&amp;small_lead_preview=true&amp;TB_iframe=true&amp;width=1345&amp;height=244">' . __('View', 'inbound-pro' ) . '</a>';
                        echo '</td>';

                        /* show lead id */
                        echo '<td>' . $post->ID . '</td>';

                        /*add custom row content*/
                        do_action('inbound_bulk_lead_action_list_item', $post);

                        echo '</tr>';
                        $loop_count++;
                    }
                    echo '</tbody>';
                    echo '</table>';

                    }

                    /**
                     *  Display Row Actions
                     */
                    public static function display_row_actions() {
                    ?>
                    </tbody>
                </table>
            </div>



            <div id="all-actions" class="tablenav">

                <div id="inbound-lead-management">
                    <span class="lead-actions-title"><?php _e('What do you want to do with the selected leads?', 'inbound-pro' ); ?></span>

                    <div id="controls">
                        <?php
                        self::display_action_controls();
                        ?>
                    </div>
                    <div id="lead-action-triggers">
                        <div class="action" id="lead-export">
                            <a href="#lead-export-process" class="manage-remove button-primary button export-leads-csv button-primary button"  title="<?php _e('Exports selected leads into a CSV format.', 'inbound-pro' ); ?>"> <?php _e('Exports selected as CSV', 'inbound-pro' ); ?></a>

                            <a style="visibility: hidden;" id="export-leads" href="#lead-export-process"> <?php _e('Exports selected as CSV', 'inbound-pro' ); ?></a>
                        </div>
                        <div class="action" id="lead-update-lists">
                            <label for="lead-update-lists"><?php _e('Choose List:', 'inbound-pro' ); ?></label>
                            <?php

                            /* get available terms in taxonomy */
                            $terms = get_terms('wplead_list_category', array('hide_empty' => false));

                            /* setup the select */
                            echo '<select name="wplead_list_category_action">';

                            /* print the first option */
                            echo '<option class="" value="" selected="selected">' . __('Select lead list ', 'inbound-pro' ) . '</option>';

                            /* loop through terms and create options */
                            foreach ($terms as $term) {
                                echo '<option class="" value="' . $term->term_id . '" >' . $term->name . ' (' . $term->count . ')</option>';
                            }

                            /* end select input */
                            echo '</select>';

                            ?>
                            <input type="submit" class="button-primary button" name="add" value="<?php _e('Add to', 'inbound-pro' ) ?>" title="<?php _e('Add the selected posts to this category.', 'inbound-pro' ); ?>"/>
                            <input type="submit" class="manage-remove button-primary button" name="remove" value="<?php _e('Remove from', 'inbound-pro' ) ?>" title="<?php _e('Remove the selected posts from this category.', 'inbound-pro' ); ?>"/>
                        </div>

                        <div class="action" id="lead-update-tags">
                            <label for="lead-update-tags"><?php _e('Tags:', 'inbound-pro' ); ?></label>
                            <input type="text" id="inbound-lead-tags-input" name="tags" placeholder="<?php _e('Separate multiple tags with commas. ', 'inbound-pro' ); ?>" title="<?php _e('Separate multiple tags with commas.', 'inbound-pro' ); ?>"/>
                            <input type="submit" name="replace_tags" class="manage-tag-replace button-primary button" value="<?php _e('Replace', 'inbound-pro' ); ?>" title="<?php _e('Replace the selected leads\'s current tags with these ones. Warning this will delete current tags and replace them ', 'inbound-pro' ); ?>"/>
                            <input type="submit" name="tag" class="manage-tag-add button-primary button" value="<?php _e('Add', 'inbound-pro' ) ?>" title="<?php _e('Add tags to the selected leads without altering the leads\' existing tags', 'inbound-pro' ); ?>"/>
                            <input type="submit" name="untag" class="manage-remove button-primary button" value="<?php _e('Remove', 'inbound-pro' ) ?>" title="<?php _e('Remove these tags from the selected leads.', 'inbound-pro' ); ?>"/>
                        </div>

                        <div class="action" id="lead-update-meta">
                            <label for="lead-update-meta"><?php _e('Meta:', 'inbound-pro' ); ?></label>
                            <input type="text" name="meta_val" title="<?php _e('Separate multiple tags with commas.', 'inbound-pro' ); ?>"/>
                            <input type="submit" name="replace_meta" value="<?php _e('Replace', 'inbound-pro' ); ?>" title="<?php _e('Replace the selected posts\' current meta values with these ones.', 'inbound-pro' ); ?>"/>
                            <input type="submit" name="meta" value="<?php _e('Add', 'inbound-pro' ); ?>" title="<?php _e('Add these meta values to the selected posts without altering the posts\' existing tags.', 'inbound-pro' ); ?>"/>
                            <input type="submit" name="unmeta" value="<?php _e('Remove', 'inbound-pro' ); ?>" title="<?php _e('Remove these meta values from the selected posts.', 'inbound-pro' ); ?>"/>
                        </div>

                        <div class="action" id="lead-delete">
                            <label for="lead-delete" id="del-label"><span style="color:red;"><?php _e('Delete Selected Leads (Warning! There is no UNDO):', 'inbound-pro' ); ?></span></label>

                            <input type="submit" class="manage-remove button-primary button" name="delete_leads" value="<?php _e('Permanently Delete Selected Leads', 'inbound-pro' ) ?>" title="<?php _e('This will delete the selected leads from your database. There is no undo.', 'inbound-pro' ); ?>"/>

                        </div>
                        <?php do_action('inbound_bulk_lead_action_triggers');?>
                    </div>
                </div>

                <?php
                self::display_hidden_action_fields();
                ?>

            </div>
        </form>
        <div id="lead-export-process" style="display: none;">
            <table id="progress-table" class="widefat">
                <thead>
                <tr>
                    <th width="50%" scope="col" class="">Count</th>
                    <th width="50%" scope="col" class="">Progress</th>
                </tr>
                </thead>
                <tbody id="the-progress-list" class="ui-selectable">
                </tbody>
            </table>
            <div class="download-leads-csv"></div>
        </div>

    </div>




    <?php
}

    /**
     *  Display action controls
     */
    public static function display_action_controls() {
        ?>
        <section id="set-3">
            <div class="fleft">
                <select id="cd-dropdown" class="cd-select">
                    <option value="-1" selected class="db-drop-label"><?php _e('Choose action to apply to selected leads', 'inbound-pro' ); ?></option>
                    <option value="lead-export" class="action-symbol lead-export-symbol db-drop-label"><?php _e('Export Selected Leads as CSV', 'inbound-pro' ); ?></option>
                    <option value="lead-update-lists" class="action-symbol lead-update-lists-symbol db-drop-label"><?php _e('Add or Remove Selected Leads from Lists', 'inbound-pro' ); ?></option>
                    <option value="lead-update-tags" class="action-symbol lead-update-tags-symbol db-drop-label"><?php _e('Add or Remove Tags to Selected Leads', 'inbound-pro' ); ?></option>
                    <option value="lead-delete" class="action-symbol lead-update-delete-symbol db-drop-label"><?php _e('Permanently Delete Selected Leads', 'inbound-pro' ); ?></option>
                    <?php do_action('inbound_bulk_lead_action_controls');?>
                </select>
            </div>
        </section>
        <script>
            jQuery(document).ready(function ($) {
                jQuery(function () {
                    jQuery('#cd-dropdown').dropdown();
                });

                jQuery("body").on('click', '.cd-dropdown li', function () {
                    var value = jQuery(this).attr('data-value'); // get selected value
                    console.log(value);

                    if (value) { // require a URL
                        jQuery(".action").hide();
                        jQuery("#" + value).show();
                    }
                    return false;
                });
            });
        </script>
        <?php
    }

    /**
     *  Build query
     */
    public static function build_query() {
        if (!isset($_REQUEST['submit']) && !defined('DOING_AJAX')) {
            self::$query = null;
            return;
        }

        /* set default args */
        $args = array(
            'post_type' => 'wp-lead',
            'order' => self::$order,
            'orderby' => self::$orderby,
            'posts_per_page' => self::$per_page,
        );


        /* set tax_query_relation */
        $tax_query = array('relation' => $_REQUEST['relation']);

        /* loop through taxonomies and check for filter */
        foreach (self::$taxonomies as $key => $taxonomy) {
            if (!$taxonomy->hierarchical) {
                //continue;
            }

            if (!isset($_REQUEST[$taxonomy->name]) || !$_REQUEST[$taxonomy->name] || $_REQUEST[$taxonomy->name][0] == 'all') {
                continue;
            }

            /* build tax_query */

            foreach ($_REQUEST[$taxonomy->name] as $values) {
                $tax_query[] = array(
                    'taxonomy' => $taxonomy->name,
                    'field' => 'id',
                    'terms' => array($values)
                );
            }
        }

        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        /* Add tag to query */
        if ((isset($_REQUEST['t'])) && $_REQUEST['t'] != "") {
            $args['tag'] = $_REQUEST['t'];
        }

        /* set meta_query */
        if (isset($_REQUEST['wp_lead_status']) && $_REQUEST['wp_lead_status'] ) {
            $meta_query = array('relation' => $_REQUEST['relation']);
            foreach ($_REQUEST['wp_lead_status'] as $status) {

                if ($status == 'all') {
                   continue;
                } else {
                    $meta_query[] = array(
                        'key' => 'wp_lead_status',
                        'value' => $status,
                        'meta_compare' => '='
                    );
                }
            }

            $args['meta_query'] = $meta_query;
        }


        if ((isset($_REQUEST['paged'])) && $_REQUEST['paged'] != "1") {
            $args['paged'] = self::$paged;
        }

        self::$query = new WP_Query($args);

    }


    /**
     *  get taxnomy select
     */
    public static function build_taxonomy_select($taxonomy) {

        /* create the select input */
        echo '<select name="' . $taxonomy->name . '[]" id="' . $taxonomy->name . '" multiple class="select2 form-control">';

        /* get selected taxonomies */
        $list_array = (isset($_REQUEST[$taxonomy->name])) ? $_REQUEST[$taxonomy->name] : array();

        /* print the first option */
        echo '<option class="" value="all" ' . (isset($_REQUEST[$taxonomy->name]) && $_REQUEST[$taxonomy->name][0] === 'all' ? 'selected="selected"' : '') . '>' . __('All ', 'inbound-pro' ) . '</option>';

        /* get available terms in taxonomy */
        $terms = get_terms($taxonomy->name, array('hide_empty' => false));

        /* loop through terms and create options */

        foreach ($terms as $term) {
            echo '<option class="" value="' . $term->term_id . '" ' . (isset($_REQUEST[$taxonomy->name]) && in_array($term->term_id, $list_array) ? 'selected="selected"' : '') . '>' . $term->name . ' (' . $term->count . ')</option>';
        }

        /* end select input */
        echo '</select>';
        ?>
        <script type='text/javascript'>
            jQuery("#<?php echo $taxonomy->name; ?>").select2({
                allowClear: true,
                placeholder: '<?php _e(sprintf('Select %s From List', $taxonomy->labels->singular_name), 'inbound-pro' ); ?>'
            });

        </script>
        <?php

    }

    /**
     *  get status select html
     */
    public static function build_lead_status_select() {

        /* create the select input */
        echo '<select name="wp_lead_status[]" id="wp_lead_status" multiple class="select2 form-control">';

        /* get selected taxonomies */
        $status_array = (isset($_REQUEST['wp_lead_status'])) ? $_REQUEST['wp_lead_status'] : array();

        /* print the first option */
        echo '<option class="" value="all" ' . (isset($_REQUEST['wp_lead_status']) && $_REQUEST['wp_lead_status'][0] === 'all' ? 'selected="selected"' : '') . '>' . __('All ', 'inbound-pro' ) . '</option>';

        /* loop through terms and create options */
        foreach (self::$statuses as $key=>$status) {
            echo '<option class="" value="' . $key . '" ' . (isset($_REQUEST['wp_lead_status']) && in_array($key, $status_array) ? 'selected="selected"' : '') . '>' . $status['label'] . ' (' . Inbound_Leads::get_status_lead_count($key) . ')</option>';
        }

        /* end select input */
        echo '</select>';
        ?>
        <script type='text/javascript'>
            jQuery("#wp_lead_status").select2({
                allowClear: true,
                placeholder: '<?php _e('Select Status From List', 'inbound-pro' ); ?>'
            });

        </script>
        <?php

    }

    /**
     *  Perform lead actions
     */
    public static function ajax_perform_actions() {

        /*permission check*/
        if (!current_user_can('level_9')) {
            die (__('User does not have admin level permissions.'));
        }

        if(empty($_POST) || empty($_POST['data']['action'])){
            die();
        }

        /*assemble the vars*/
        $action       = $_POST['data']['action']; //what kind of lead action is being taken.
        $limit        = $_POST['data']['limit']; //limit of how  many leads are being processed. eg. 100   //will be incremented on each pass
        $offset       = $_POST['data']['offset'];//lead progress pointer
        $total        = $_POST['data']['total'];  //total leads being dealt with
        $ids          = json_decode(stripslashes($_POST['data']['ids']));//the lead ids
        $lead_list_id = $_POST['data']['lead_list_id'];//the id of the lead list where the actions are taking place
        $tags         = $_POST['data']['tags'];//tags to be added, removed or replaced


        /*find out what the action is...*/
        if($action == 'add'){

            for($offset; $offset < $limit; $offset++) {
                Inbound_Leads::add_lead_to_list(intval($ids[$offset]), $lead_list_id); // add to list
            }

        } elseif($action == 'remove'){

            for($offset; $offset < $limit; $offset++) {
                Inbound_Leads::remove_lead_from_list(intval($ids[$offset]), $lead_list_id);
            }

        } elseif($action == 'tag'){
            $tags = explode(',', $tags);

            for($offset; $offset < $limit; $offset++) {
                Inbound_Leads::add_tag_to_lead(intval($ids[$offset]), $tags);
            }

        } elseif($action == 'untag'){
            $tags = explode(',', $tags);

            for($offset; $offset < $limit; $offset++) {
                Inbound_Leads::remove_tag_from_lead(intval($ids[$offset]), $tags);
            }

        } elseif($action == 'replace_tags'){
            $tags = explode(',', $tags);

            for($offset; $offset < $limit; $offset++) {
                wp_set_object_terms($ids[$offset], $tags, 'lead-tags');
            }

        } elseif($action == 'delete_leads'){

            for($offset; $offset < $limit; $offset++) {
                wp_delete_post(intval($ids[$offset]), true);
            }

        } else{
            /*if it wasn't on the list... die*/
            die(__('ERROR: unknown action'));
        }

        $err = print_r(error_get_last(), true);
        echo json_encode($err);
        die();
    }

    public static function ajax_leads_export_list(){

        $returnArray = array();

        if(!isset($_POST) || empty($_POST)){
            $returnArray = array(
                'status' => 0,
                'error' => 'Empty post values!!.',
                'url' => ''
            );
            die(json_encode($returnArray));

        }
        if(empty($_POST['data']['ids'])){
            $returnArray = array(
                'status' => 0,
                'error' => 'Please select leads to export!!.',
                'url' => ''
            );
            die(json_encode($returnArray));

        }


        //handle posted data
        $ids      = json_decode(stripslashes($_POST['data']['ids']));
        $limit    = $_POST['data']['limit'];
        $offset   = $_POST['data']['offset'];
        $total    = $_POST['data']['total'];
        $is_first = (!isset($_POST['data']['is_first']) || !$_POST['data']['is_first'] ) ? 0 : 1;
        $fields = Leads_Field_Map::build_map_array();

        /* add lead status & date created */
        $fields['wp_lead_status'] = __("Lead Status","inbound-pro");
        $fields['wpleads_last_updated'] = __("Last Updated","inbound-pro");
        $fields['wpleads_date_created'] = __("Date Created","inbound-pro");
        $fields['sources'] = __("Sources","inbound-pro");

        $upload_dir = wp_upload_dir();
        $uploads_path = 'leads/csv';

        //GETTING CORRECT FILE PATH
        $path = $upload_dir['path'].'/'.$uploads_path.'/';
        $url = $upload_dir['url'].'/'.$uploads_path.'/';
        $blogtime = current_time( 'mysql' );
        $hash = md5(serialize($ids));
        $filename = date("m.d.y.") . $hash ;
        list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = preg_split( '([^0-9])', $blogtime );
        $path = str_replace($today_year.'/'.$today_month.'/','',$path);
        $url = str_replace($today_year.'/'.$today_month.'/','',$url);

        if(file_exists($path)){
            if($is_first == 1){
                @unlink($path.$filename.".csv");
            }
        } else {
            mkdir($path, 0755, true);
        }
        $exported = 0;

        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Description: File Transfer');
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=".$path."/".$filename.".csv");
        header("Expires: 0");
        header("Pragma: public");

        $file = @fopen($path.$filename.".csv","a");

        if(!$file){
            $returnArray = array(
                'status' => 0,
                'error' => 'Unable to create file. Please check you uploads folder permission!!.',
                'url' => ''
            );
            die(json_encode($returnArray));
        }


        if($is_first == 1){
            // Add a header row if it hasn't been added yet
            fputcsv($file, array_keys($fields));
            $headerDisplayed = true;
        }

        for($j = $offset;  $j < $limit; $j++)
        {
            unset($this_row_data);

            if (!isset($ids[$j])) {
                continue;
            }

            $lead = get_post($ids[$j]);
            $this_lead_data = get_post_custom($ids[$j]);


            foreach ($fields as $key => $val) {

                if (isset($this_lead_data[$key])) {
                    $val = $this_lead_data[$key];
                    if (is_array($val)) {
                        $val = implode(';', $val);
                    }
                } else {
                    $val = "";
                }

                /* account for date created */
                if ($key == 'wpleads_date_created') {
                    $val = $lead->post_date;
                } else if ($key == 'wpleads_last_updated') {
                    $val = $lead->post_modified;
                }

                $this_row_data[$key] = $val;
            }

            /* Add sources */
            $this_row_data['sources'] = json_encode(Inbound_Events::get_lead_sources($ids[$j]));
            fputcsv($file, $this_row_data);
            $exported++;
        }
        // Close the file
        fclose($file);
        if($limit >= $total){
            $url = $url.$filename.'.csv';
            $returnArray = array(
                'status' => 1,
                'error' => '',
                'url' => $url
            );
        }else{
            $returnArray = array(
                'status' => 1,
                'error' => '',
                'url' => ''
            );
        }

        die(json_encode($returnArray));
    }




    /**
     *  Ajax listener to load more leads
     */
    public static function ajax_load_more_leads() {

        /* build query */
        self::build_query();

        $i = 0;

        $loop_page = self::$paged - 1;
        $loop_count = $loop_page * 60;
        $loop_count = $loop_count + 1;

        foreach (self::$query->posts as $post) {

            echo '<tr' . ($i++ % 2 == 0 ? ' class="alternate"' : '') . '>';

            /* show checkbox */
            echo '<td><input class="lead-select-checkbox" type="checkbox" name="ids[]" value="' . $post->ID . '" /></td>';

            /* show count */
            echo '<td class="count-sort"><span>' . $loop_count . '</span></td>';

            /* show publish date */
            echo '<td>';
            if ('0000-00-00 00:00:00' == $post->post_date) {
                _e('Unpublished', 'inbound-pro' );
            } else {
                echo date(__('Y/m/d'), strtotime($post->post_date));
            }
            echo '</td>';

            /* show email */
            echo '<td>';
            echo '	<span class="lead-email">' . $post->post_title . '</span>';
            echo '</td>';

            /* show lists */
            echo '<td class="list-column-row">';
            $terms = wp_get_post_terms($post->ID, 'wplead_list_category', 'id');
            foreach ($terms as $term) {
                echo '<span class="list-pill">' . $term->name . ' <i title="Remove This lead from the ' . $term->name . ' list" class="remove-from-list" data-lead-id="' . $post->ID . '" data-list-id="' . $term->term_id . '"></i></span> ';
            }
            echo '</td>';

            /* show tags */
            echo '<td class="tags-column-row">';
            $_tags = wp_get_post_terms($post->ID, 'lead-tags', 'id');

            if ($_tags) {
                foreach ($_tags as $tag) {
                    echo "<a title='Click to Edit Lead Tag Name' target='_blank' href='" . admin_url('edit-tags.php?action=edit&taxonomy=lead-tags&tag_ID=' . $tag->term_id . '&post_type=wp-lead') . "'>$tag->name</a>, ";
                }
            } else {
                _e('No tags', 'inbound-pro' );
            }
            echo '</td>';

            /* show link to lead */
            echo '<td>';
            echo '	<a class="thickbox" href="post.php?action=edit&post=' . $post->ID . '&amp;small_lead_preview=true&amp;TB_iframe=true&amp;width=1345&amp;height=244">' . __('View', 'inbound-pro' ) . '</a>';
            echo '</td>';

            /* show lead id */
            echo '<td>' . $post->ID . '</td>';

            /*add custom row content*/
            do_action('inbound_bulk_lead_action_list_item', $post);

            echo '</tr>';
            $loop_count++;
        }

    }


    /**
     *  Ajax listener to delete lead from list
     */
    public static function ajax_delete_from_list() {

        $lead_id = (isset($_POST['lead_id'])) ? $_POST['lead_id'] : '';
        $list_id = (isset($_POST['list_id'])) ? $_POST['list_id'] : '';

        $id = $lead_id;

        $current_terms = wp_get_post_terms($id, 'wplead_list_category', 'id');
        $current_terms_count = count($terms);
        //print_r($current_terms);
        $all_remove_terms = '';
        foreach ($current_terms as $term) {
            $add = $term->term_id;
            $all_remove_terms .= $add . ' ,';
        }
        $final = explode(' ,', $all_remove_terms);

        $final = array_filter($final, 'strlen');

        if (in_array($list_id, $final)) {
            $new = array_flip($final);
            unset($new[$list_id]);
            $save = array_flip($new);
            wp_set_object_terms($id, $save, 'wplead_list_category');
        }


    }

}

add_action('init', 'inbound_load_lead_manager', 1);
function inbound_load_lead_manager() {
    new Leads_Manager;
}

