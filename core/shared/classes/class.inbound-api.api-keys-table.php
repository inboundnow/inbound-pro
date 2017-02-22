<?php
/**
 * API Key Table Class
 *
 * @package     Leads
 * @subpackage  Inbound API
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Load WP_List_Table if not loaded */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if (!class_exists('Inbound_API_Keys_Table')) {

	/**
	 * Inbound_API_Keys_Table Class
	 *
	 * Renders the API Keys table
	 *
	 */
	class Inbound_API_Keys_Table extends WP_List_Table {

		/**
		 * @var int Number of items per page
		 */
		public $per_page = 30;

		/**
		 * @var object Query results
		 */
		static $keys;

		/**
		 * Get things started
		 *
		 * @see WP_List_Table::__construct()
		 */
		public function __construct() {
			global $status, $page;

			/* Set parent defaults */
			parent::__construct( array(
				'singular'  => __( 'API Key', 'inbound-pro' ),     /* Singular name of the listed records */
				'plural'    => __( 'API Keys', 'inbound-pro' ),    /* Plural name of the listed records */
				'ajax'      => false                       /* Does this table support ajax? */
			) );

			$this->inline_js();
			$this->query();
		}

		/**
		 *  Renders JS used to support API key actions
		 */
		public static function inline_js() {
			?>
			<script type="text/javascript">
				var Inbound_API_Actions = {

					init : function() {
						this.revoke_api_key();
						this.regenerate_api_key();
					},

					revoke_api_key : function() {
						jQuery( 'body' ).on( 'click', '.inbound-revoke-api-keys', function( e ) {
							return confirm( '<?php _e('Are you sure you want to revoke permissions for this API Key?', 'inbound-pro' ); ?> ');
						} );
					},
					regenerate_api_key : function() {
						jQuery( 'body' ).on( 'click', '.inbound-regenerate-api-keys', function( e ) {
							return confirm( '<?php _e('Are you sure you want to regenerate API Keys for this user?', 'inbound-pro' ); ?>  ');
						} );
					},
				};
				Inbound_API_Actions.init();
			</script>
			<?php
		}

		/**
		 * This function renders most of the columns in the list table.
		 *
		 * @access public
		 *
		 * @param array $item Contains all the data of the keys
		 * @param string $column_name The name of the column
		 *
		 * @return string Column Name
		 */
		public function column_default( $item, $column_name ) {
			return $item[ $column_name ];
		}

		/**
		 * Renders the column for the user field
		 *
		 * @access public
		 * @return void
		 */
		public function column_user( $item ) {

			$actions = array();

			/*
			if( apply_filters( 'inbound_api_log_requests', true ) ) {
				$actions['view'] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( add_query_arg( array( 'view' => 'api_requests', 'post_type' => 'download', 'page' => 'inbound-reports', 'tab' => 'logs', 's' => $item['email'] ), 'edit.php' ) ),
					__( 'View API Log', 'inbound-pro' )
				);
			}
			*/

			$actions['reissue'] = sprintf(
				'<a href="%s" class="inbound-regenerate-api-keys">%s</a>',
				esc_url( add_query_arg( array( 'user_id' => $item['id'], 'inbound_action' => 'regenerate-api-keys' ) ) ),
				__( 'Reissue', 'inbound-pro' )
			);
			$actions['revoke'] = sprintf(
				'<a href="%s" class="inbound-revoke-api-keys inbound-delete">%s</a>',
				esc_url( add_query_arg( array( 'user_id' => $item['id'], 'inbound_action' => 'revoke-api-keys' ) ) ),
				__( 'Revoke', 'inbound-pro' )
			);

			$actions = apply_filters( 'inbound_api_row_actions', array_filter( $actions ) );

			return sprintf('%1$s %2$s', $item['user'], $this->row_actions( $actions ) );
		}

		/**
		 * Retrieve the table columns
		 *
		 * @access public
		 * @since 2.0
		 * @return array $columns Array of all the list table columns
		 */
		public function get_columns() {
			$columns = array(
				'user'         => __( 'Username', 'inbound-pro' ),
				'key'          => __( 'Public Key', 'inbound-pro' ),
				'secret'       => __( 'Secret Key', 'inbound-pro' ),
				'token'        => __( 'Token', 'inbound-pro' )
			);

			return $columns;
		}

		/**
		 * Display the key generation form
		 *
		 * @access public
		 * @return void
		 */
		public function bulk_actions( $which = '' ) {


		}

		/**
		 * Display the key generation form
		 *
		 * @access public
		 * @return void
		 */
		public function display_controls( $which = '' ) {

			/* These aren't really bulk actions but this outputs the markup in the right place */
			static $inbound_api_is_bottom;

			if( $inbound_api_is_bottom ) {
				return;
			}

			$user = wp_get_current_user();

			?>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=wp-lead&page=wpleads_global_settings&tab=tabs-wpleads-apikeys' ); ?>">
				<input type="hidden" name="inbound_action" value="generate-api-keys" />
				<input type='text' name="user_id" placeholder="<?php _e( 'Enter User ID', 'inbound-pro' ); ?>" title="Your Current ID is <?php echo $user->ID; ?> " value="<?php echo $user->ID; ?>">
				<?php submit_button( __( 'Generate New API Keys', 'inbound-pro' ), 'secondary', 'submit', false ); ?>
				&nbsp;<a class='button button-primary' href='http://docs.inboundnow.com/guide/lead-api-documentation-v1/' target='_blank'><?php _e('View Documentation', 'inbound-pro' ); ?></a>
			</form>
			<?php
			$inbound_api_is_bottom = true;
		}

		/**
		 * Retrieve the current page number
		 *
		 * @access public
		 * @since 2.0
		 * @return int Current page number
		 */
		public function get_paged() {
			return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		}

		/**
		 * Performs the key query
		 *
		 * @access public
		 * @since 2.0
		 * @return void
		 */
		public function query() {
			$users    = get_users( array(
				'meta_key' => 'inbound_user_secret_key',
				'number'   => $this->per_page,
				'offset'   => $this->per_page * ( $this->get_paged() - 1 )
			) );

			$keys     = array();

			foreach( $users as $user ) {
				$keys[$user->ID]['id']     = $user->ID;
				$keys[$user->ID]['email']  = $user->user_email;
				$keys[$user->ID]['user']   = '<a href="' . add_query_arg( 'user_id', $user->ID, 'user-edit.php' ) . '"><strong>' . $user->user_login . '</strong></a>';

				$keys[$user->ID]['key']    = get_user_meta( $user->ID, 'inbound_user_public_key', true );
				$keys[$user->ID]['secret'] = get_user_meta( $user->ID, 'inbound_user_secret_key', true );
				$keys[$user->ID]['token']  = hash( 'md5', get_user_meta( $user->ID, 'inbound_user_secret_key', true ) . get_user_meta( $user->ID, 'inbound_user_public_key', true ) );
			}

			return $keys;
		}



		/**
		 * Retrieve count of total users with keys
		 *
		 * @access public
		 * @since 2.0
		 * @return int
		 */
		public function total_items() {
			global $wpdb;

			if( ! get_transient( 'inbound_total_api_keys' ) ) {
				$total_items = $wpdb->get_var( "SELECT count(user_id) FROM $wpdb->usermeta WHERE meta_key='inbound_user_secret_key'" );

				set_transient( 'inbound_total_api_keys', $total_items, 60 * 60 );
			}

			return get_transient( 'inbound_total_api_keys' );
		}

		/**
		 * Setup the final data for the table
		 *
		 * @access public
		 * @return void
		 */
		public function prepare_items() {
			$columns = $this->get_columns();

			$hidden = array(); /* No hidden columns */
			$sortable = array(); /* Not sortable... for now */

			$this->_column_headers = array( $columns, $hidden, $sortable );

			$data = $this->query();

			$total_items = $this->total_items();

			$this->items = $data;

			$this->set_pagination_args( array(
					'total_items' => $total_items,
					'per_page'    => $this->per_page,
					'total_pages' => ceil( $total_items / $this->per_page )
				)
			);
		}
	}
}