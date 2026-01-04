<?php
namespace Cbx\Petition;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

use Cbx\Petition\Helpers\PetitionHelper;

class PetitionSignListTable extends \WP_List_Table {

	function __construct() {

		//Set parent defaults
		parent::__construct( [
			'singular' => 'cbxpetitionsign',     //singular name of the listed records
			'plural'   => 'cbxpetitionsigns',    //plural name of the listed records
			'ajax'     => false,                 //does this table support ajax?
			'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
		] );
	}//end constructor

	/**
	 * Callback for column 'State'
	 *
	 * @param  array  $item
	 *
	 * @return string
	 */
	function column_state( $item ) {
		$state_key = esc_attr( $item['state'] );

		//return '<span class="cbxpetition_signature_status cbxpetition_signature_status_' . esc_attr( $state_key ) . '">' . PetitionHelper::getPetitionSignState( $state_key ) . '</span>';
		return '<span class="component_status component_status_icon component_status_petition component_status_' . esc_attr( $state_key ) . '">' . PetitionHelper::getPetitionSignState( $state_key ) . '</span>';
	}//end method column_state

	/**
	 * Callback for column 'User Email'
	 *
	 * @param  array  $item
	 *
	 * @return string
	 */

	function column_email( $item ) {
		$user_email = sanitize_email( $item['email'] );

		$user_info = get_user_by( 'email', $item['email'] );

		if ( $user_info !== false ) {
			if ( current_user_can( 'edit_user', $user_info->ID ) ) {
				$user_email = '<a title="' . esc_html__( 'View User',
						'cbxpetition' ) . '" href="' . esc_url( get_edit_user_link( $user_info->ID ) ) . '">' . esc_attr( $user_email ) . '</a>';
			}
		} else {
			$user_email = $user_email . esc_html__( '(Guest)', 'cbxpetition' );
		}

		return $user_email;
	}//end method column_email

	/**
	 * Callback for column 'Sign Comment'
	 *
	 * @param  array  $item
	 *
	 * @return string
	 */

	function column_comment( $item ) {
		$comment = wp_unslash( sanitize_textarea_field( $item['comment'] ) );
		if ( strlen( $comment ) > 25 ) {
			$comment = substr( $comment, 0, 25 ) . '...';
		}

		return '<p class="cbxpetition-comment-expand">' . $comment . '</p>';

	}//end method column_comment

	/**
	 * Callback for column 'petition_id'
	 *
	 * @param  array  $item
	 *
	 * @return string
	 */
	function column_petition_id( $item ) {
		$petition_id = absint( $item['petition_id'] );

		$output = '<a href="' . esc_url( get_permalink( $petition_id ) ) . '">' . get_the_title( $petition_id ) . '</a>';
		if ( current_user_can( 'edit_post', $petition_id ) ) {
			$edit_url = esc_url( get_edit_post_link( $petition_id ) );
			$output   .= '<a href="' . esc_url( $edit_url ) . '" title="' . esc_html__( 'Edit Petition',
					'cbxpetition' ) . '">' . esc_html__( ' (Edit)', 'cbxpetition' ) . '</a>';
		}

		return $output;
	}//end method column_petition_id

	/**
	 * Callback for column 'action'
	 *
	 * @param  array  $item
	 *
	 * @return string
	 */
	function column_action( $item ) {
		$save_svg   = cbxpetition_esc_svg( cbxpetition_load_svg( 'icon_save' ) );
		$delete_svg = cbxpetition_esc_svg( cbxpetition_load_svg( 'icon_delete' ) );
		$more_v_svg = cbxpetition_esc_svg( cbxpetition_load_svg( 'icon_more_v' ) );

		$signature_id = absint( $item['id'] );
		$petition_id  = absint( $item['petition_id'] );

		$signature_list_url = admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-signatures' );
		$edit_url           = add_query_arg( [
			'view' => 'addedit',
			'id'   => $signature_id
		], $signature_list_url );


		$output = '<div class="button_actions button_actions_signatures">';
		$output .= '<a class="button secondary icon icon-inline small" href="' . esc_url( $edit_url ) . '"  title="' . esc_html__( 'Edit Signature',
				'cbxpetition' ) . '"><i class="cbx-icon">' . $save_svg . '</i><span class="button-label">' . esc_html__( 'Edit', 'cbxpetition' ) . '</span></a>';
		$output .= '<details class="dropdown dropdown-menu ml-10">';
		$output .= '<summary class="button icon icon-only outline primary icon-inline"><i class="cbx-icon">' . $more_v_svg . '</i></summary>';
		$output .= '<div class="card card-menu card-menu-right">';
		$output .= '<ul>';
		$output .= '<li><a data-petition-id="' . absint( $petition_id ) . '" data-signature-id="' . absint( $signature_id ) . '" class="button error small icon icon-inline petition-signature-delete" href="#"><i class="cbx-icon">' . $delete_svg . '</i><span class="button-label">' . esc_html__( 'Delete', 'cbxpetition' ) . '</span></a></li>';
		$output .= '</ul>';
		$output .= '</div>';
		$output .= '</details>';
		$output .= '</div>';

		return $output;
	}//end method column_action

	/**
	 * Callback for column 'ID'
	 *
	 * @param  array  $item
	 *
	 * @return string
	 */
	function column_id( $item ) {
		$signature_id = absint( $item['id'] );

		/*$signature_list_url = admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-signatures' );
		$edit_url           = add_query_arg( [
			'view' => 'addedit',
			'id'   => $signature_id
		], $signature_list_url );*/

		//return $item['id'];

		$comment = sanitize_textarea_field( wp_unslash($item['comment']) );
		if ( strlen( $comment ) > 25 ) {
			$comment = substr( $comment, 0, 25 ) . '...';
		}

		return '<p class="cbxpetition-comment-expand">' . $comment . '</p>';
	}//end method column_id

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			$this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/
			$item['id']                //The value of the checkbox should be the record's id
		);
	}//end method column_cb

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
				return $item[ $column_name ];
			case 'petition_id':
				return $item[ $column_name ];
			case 'f_name':
				return $item[ $column_name ];
			case 'l_name':
				return $item[ $column_name ];
			case 'email':
				return $item[ $column_name ];
			/*case 'comment':
				return $item[ $column_name ];*/
			case 'state':
				return $item[ $column_name ];
			default:
				return ''; //print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}//end method column_default

	/**
	 * Add extra markup in the toolbars before or after the list
	 *
	 * @param  string  $which  , helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	function extra_tablenav( $which ) {
		if ( $which == "top" ) {
			//$petition_id = isset( $_REQUEST['petition_id'] ) ? intval( $_REQUEST['petition_id'] ) : 0;

			//get the petitions
			//$all_petitions = PetitionHelper::getAllPetitions();


			?>

            <!-- petition dropdown filter -->
            <!--<div class="alignleft actions">
					<label for="petition_id"
						   class="screen-reader-text"><?php /*esc_html_e( 'Filter by Petition', 'cbxpetition' ) */ ?></label>
					<select class="form-control form" name="petition_id" id="petition_id">
						<option <?php /*echo ( $petition_id == 0 ) ? ' selected="selected" ' : ''; */ ?>
							value="0"><?php /*esc_html_e( 'Select Petition', 'cbxpetition' ); */ ?></option>
						<?php
			/*							foreach ( $all_petitions as $post_id => $post_title ):
											$selected = ( ( $petition_id > 0 && $petition_id == $post_id ) ? ' selected="selected" ' : '' );
											echo '<option  ' . $selected . ' value="' . $post_id . '">' . stripslashes( $post_title) . ' (' . esc_html__( 'ID:', 'cbxpetition' ) . $post_id . ')</option>';
											*/ ?>
							<?php /*endforeach; */ ?>
					</select>
					<input type="submit" name="filter_action" id="post-query-submit" class="button"
						   value="<?php /*esc_html_e( 'Filter', 'cbxpetition' ) */ ?>" />
				</div>-->

            <!-- log export view through hook -->
			<?php
			do_action( 'cbxpetition_sign_log_filter_extra' );
		}
	}

	function get_columns() {
		$columns = [
			'cb'          => '<input type="checkbox" />', //Render a checkbox instead of text
			'id'          => esc_html__( 'Comment', 'cbxpetition' ),
			//'comment'     => esc_html__( 'Comment', 'cbxpetition' ),
			'f_name'      => esc_html__( 'First Name', 'cbxpetition' ),
			'l_name'      => esc_html__( 'Last Name', 'cbxpetition' ),
			'email'       => esc_html__( 'Email', 'cbxpetition' ),
			'petition_id' => esc_html__( 'Petition', 'cbxpetition' ),
			'state'       => esc_html__( 'Status', 'cbxpetition' ),
			'action'      => esc_html__( 'Actions', 'cbxpetition' ),
		];

		return apply_filters( 'cbxpetition_signature_listing_columns', $columns );
	}//end method get_columns

	function get_sortable_columns() {
		$sortable_columns = [
			'id'          => [ 'id', false ], //true means it's already sorted
			'petition_id' => [ 'petition_id', false ],
			'f_name'      => [ 'f_name', false ],
			'l_name'      => [ 'l_name', false ],
			'email'       => [ 'email', false ],
			'state'       => [ 'state', false ],
		];

		return apply_filters( 'cbxpetition_signature_listing_sortable_columns', $sortable_columns );;
	}//end method get_sortable_columns

	/**
	 * Get sortable/order by keys
	 *
	 * @return mixed|null
	 */
	function get_sortable_keys() {
		return cbxpetition_signature_get_sortable_keys();
	}//end method get_sortable_keys

	/**
	 * Petition Bulk actions
	 *
	 * @return array|mixed|void
	 */
	function get_bulk_actions() {
		$bulk_actions           = PetitionHelper::getPetitionSignStates();
		$bulk_actions['delete'] = esc_html__( 'Delete', 'cbxpetition' );

		return apply_filters( 'cbxpetition_sign_state_bulk_action', $bulk_actions );
	}//end method get_bulk_actions

	function process_bulk_action() {
		global $wpdb;
		$settings = new CBXSetting();

		$new_status = $current_action = $this->current_action();

		if ( $new_status == - 1 ) {
			return;
		}

		if ( ! empty( $_REQUEST['cbxpetitionsign'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended 
			$signature_table = esc_sql($wpdb->prefix . 'cbxpetition_signs');
			$state_arr       = array_keys( PetitionHelper::getPetitionSignStates() );

			$current_user = wp_get_current_user();
			$user_id      = $current_user->ID;


			$results = wp_unslash( $_REQUEST['cbxpetitionsign'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			foreach ( $results as $signature_id ) {
				$signature_id = (int) $signature_id;

				//at first keep the log record

				$signature   = PetitionHelper::petitionSignInfo( $signature_id );
				$petition_id = absint( $signature['petition_id'] );

				if ( 'delete' === $current_action ) {
					do_action( 'cbxpetition_sign_delete_before', $signature, $signature_id, $petition_id );

					if ( $signature !== null && sizeof( $signature ) > 0 ) {
						//now delete
						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared 
						$sql = $wpdb->prepare( "DELETE FROM {$signature_table} WHERE id=%d", $signature_id );
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						$delete_status = $wpdb->query( $sql );

						if ( $delete_status !== false ) {
							do_action( 'cbxpetition_sign_delete_after', $signature, $signature_id, $petition_id );
						}

					}
				} else {
					if ( ! in_array( $new_status, $state_arr ) ) {
						break;
					}


					$old_status = esc_attr( $signature['state'] );
					if ( ! is_null( $signature ) && $new_status != $old_status ) {

						$signature_data = [
							'state'    => $new_status,
							'mod_by'   => $user_id,
							'mod_date' => current_time( 'mysql' ),
						];

                        if($new_status != 'unverified'){
	                        $signature_data['activation'] = '';
                        }

                        $signature_format = [
	                        '%s', //status
	                        '%d', //mod_by
	                        '%s'  //mod_date
                        ];

						if($new_status != 'unverified'){
							$signature_format[] = '%s';
                        }

						$update = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$signature_table,
							$signature_data,
							[ 'id' => $signature_id ],
							$signature_format,
							[ '%d' ]
						);

						if ( $update !== false ) {
							$signature['state']    = $new_status;
							$signature['mod_by']   = $user_id;
							$signature['mod_date'] = current_time( 'mysql' );

							do_action( 'cbxpetition_sign_status_to_' . $new_status, $signature, $old_status, $new_status );
							do_action( 'cbxpetition_sign_status_from_' . $old_status . '_to_' . $new_status, $signature, $old_status, $new_status );
						}


						//signature approve event special care
						if ( $old_status != $new_status && $new_status == 'approved' ) {
							do_action( 'cbxpetition_sign_approved', $signature, $old_status, $new_status );
						}
					}
				}//end else status update
			}

			do_action( 'cbxpetition_sign_state_action', $new_status );
		}
	}//end method process_bulk_action

	function prepare_items() {
		global $wpdb; //This is used only if making any database queries

		$user   = get_current_user_id();
		$screen = get_current_screen();

		$current_page = $this->get_pagenum();

		$option_name = $screen->get_option( 'per_page', 'option' ); //the core class name is WP_Screen


		$per_page = intval( get_user_meta( $user, $option_name, true ) );

		if ( $per_page == 0 ) {
			$per_page = intval( $screen->get_option( 'per_page', 'default' ) );
		}

		$columns  = $this->get_columns();
		$hidden   = get_hidden_columns( $this->screen );
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$this->process_bulk_action();

		$search      = ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '' ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';                     // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order       = ( isset( $_REQUEST['order'] ) && $_REQUEST['order'] != '' ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'DESC';     // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_by    = ( isset( $_REQUEST['orderby'] ) && $_REQUEST['orderby'] != '' ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'id'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended 
		$petition_id = isset( $_REQUEST['petition_id'] ) ? absint( $_REQUEST['petition_id'] ) : 0;                                                         // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status      = isset( $_REQUEST['sign_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sign_status'] ) ) : 'all';                          // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$order = strtoupper( $order );

		$data = PetitionHelper::getSignListingData( $search,
			$petition_id,
			0,
			$status,
			$order,
			$order_by,
			$per_page,
			$current_page );

		$total_items = intval( PetitionHelper::getSignListingDataCount( $search,
			$petition_id,
			0,
			$status,
			$per_page,
			$current_page ) );

		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( [
			'total_items' => $total_items,
			//WE have to calculate the total number of items
			'per_page'    => $per_page,
			//WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $per_page )
			//WE have to calculate the total number of pages
		] );

	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @param  object  $item  The current item
	 *
	 * @since  3.1.0
	 * @access public
	 *
	 */
	public function single_row( $item ) {
		$row_class = 'cbxpetition_sign_row';
		$row_class = apply_filters( 'cbxpetition_row_class', $row_class, $item );
		echo '<tr id="cbxpetition_sign_row_' . esc_attr( $item['id'] ) . '" class="' . esc_attr( $row_class ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}//end method single_row

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since  3.1.0
	 * @access public
	 */
	public function no_items() {
		echo '<div class="notice notice-warning inline "><p>' . esc_html__( 'No petition sign found. Please change your search criteria for better result.',
				'cbxpetition' ) . '</p></div>';
	}//end method no_items

	/**
	 * Pagination
	 *
	 * @param  string  $which
	 */
	protected function pagination( $which ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items     = $this->_pagination_args['total_items'];
		$total_pages     = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		if ( 'top' === $which && $total_pages > 1 ) {
			$this->screen->render_screen_reader_content( 'heading_pagination' );
		}

		/* translators: %s: number of items */
		$output = sprintf( _n( '%s item', '%s items', $total_items, 'cbxpetition' ), number_format_i18n( $total_items ) );
		$output = '<span class="displaying-num">' . $output . '</span>';

		$current              = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();

		$current_url = set_url_scheme( 'http://' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ?? '' ) ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ) );

		$current_url = remove_query_arg( $removable_query_args, $current_url );

		$page_links = [];

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span></span>';

		$disable_first = $disable_last = $disable_prev = $disable_next = false;

		if ( $current == 1 ) {
			$disable_first = true;
			$disable_prev  = true;
		}
		if ( $current == 2 ) {
			$disable_first = true;
		}
		if ( $current == $total_pages ) {
			$disable_last = true;
			$disable_next = true;
		}
		if ( $current == $total_pages - 1 ) {
			$disable_last = true;
		}

		$pagination_params = [];

		$search = isset( $_REQUEST['s'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended 
		//$logdate = ( isset( $_REQUEST['logdate'] ) && $_REQUEST['logdate'] != '' ) ? sanitize_text_field( $_REQUEST['logdate'] ) : '';

		if ( $search != '' ) {
			$pagination_params['s'] = $search;
		}

		/*if ($logdate != '') {
			$pagination_params['logdate'] = $logdate;
		}*/


		$pagination_params = apply_filters( 'cbxpetition_pagination_log_params', $pagination_params );

		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				__( 'First page', 'cbxpetition' ),
				'&laquo;'
			);
		}

		if ( $disable_prev ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
		} else {
			$pagination_params['paged'] = max( 1, $current - 1 );

			$page_links[] = sprintf( "<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( $pagination_params, $current_url ) ),
				__( 'Previous page', 'cbxpetition' ),
				'&lsaquo;'
			);
		}

		if ( 'bottom' === $which ) {
			$html_current_page  = $current;
			$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page', 'cbxpetition' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
		} else {
			$html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page', 'cbxpetition' ) . '</label>',
				$current,
				strlen( $total_pages )
			);
		}
		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		/* translators: %1$s: current page , %2$s: total page number  */
		$page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging', 'cbxpetition' ), $html_current_page, $html_total_pages ) . $total_pages_after;

		if ( $disable_next ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
		} else {
			$pagination_params['paged'] = min( $total_pages, $current + 1 );

			$page_links[] = sprintf( "<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( $pagination_params, $current_url ) ),
				__( 'Next page', 'cbxpetition' ),
				'&rsaquo;'
			);
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
		} else {
			$pagination_params['paged'] = $total_pages;

			$page_links[] = sprintf( "<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( $pagination_params, $current_url ) ),
				__( 'Last page', 'cbxpetition' ),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class = ' hide-if-js';
		}
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			//$page_class = ' no-pages';
			$page_class = ' ';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end pagination

	/**
	 * Displays the search box.
	 *
	 * @param  string  $text  The 'submit' button label.
	 * @param  string  $input_id  ID attribute value for the search input field.
	 *
	 * @since 3.1.0
	 *
	 */
	public function search_box( $text, $input_id ) {
		/*if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}*/

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {                                                                                                 // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) . '" />'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( ! empty( $_REQUEST['order'] ) ) {                                                                                               // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) . '" />'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {                                                                                                        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['post_mime_type'] ) ) ) . '" />'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {                                                                                                  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<input type="hidden" name="detached" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['detached'] ) ) ) . '" />'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		?>
        <div class="search-box pull-right">
            <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
            <input placeholder="<?php esc_attr_e( 'Search keyword', 'cbxpetition' ); ?>" type="search"
                   id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, '', '', false, [ 'id' => 'search-submit' ] ); ?>
        </div>
		<?php
	}//end method search_box
}//end method PetitionSignListTable