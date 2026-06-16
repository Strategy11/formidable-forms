<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormsListHelper extends FrmListHelper {

	/**
	 * The transient name that stores data for which posts a form is embedded in.
	 *
	 * @since 6.32
	 *
	 * @var string
	 */
	private static $embed_posts_transient_name = 'frm_posts_contain_form';

	/**
	 * @var string
	 */
	public $status = '';

	public $total_items = 0;

	/**
	 * @param array $args
	 */
	public function __construct( $args ) {
		$this->status = self::get_param( array( 'param' => 'form_type' ) );

		parent::__construct( $args );
		$this->screen->set_screen_reader_content(
			array(
				'heading_list' => esc_html__( 'Forms list', 'formidable' ),
			)
		);
	}

	/**
	 * @return void
	 */
	public function prepare_items() {
		global $per_page, $mode;

		$page     = $this->get_pagenum();
		$per_page = $this->get_items_per_page( 'formidable_page_formidable_per_page' );

		$mode = self::get_param(
			array(
				'param'   => 'mode',
				'default' => 'list',
			)
		);

		$orderby = self::get_param(
			array(
				'param'   => 'orderby',
				'default' => 'name',
			)
		);
		$order   = self::get_param(
			array(
				'param'   => 'order',
				'default' => 'ASC',
			)
		);

		FrmAppController::apply_saved_sort_preference( $orderby, $order );

		$start = self::get_param(
			array(
				'param'   => 'start',
				'default' => ( $page - 1 ) * $per_page,
			)
		);

		$s_query = array(
			array(
				'or'               => 1,
				'parent_form_id'   => null,
				'parent_form_id <' => 1,
			),
		);
		switch ( $this->status ) {
			case 'draft':
				$s_query['is_template'] = 0;
				$s_query['status']      = 'draft';
				break;
			case 'trash':
				$s_query['status'] = 'trash';
				break;
			default:
				$s_query['is_template'] = 0;
				$s_query['status !']    = 'trash';
				break;
		}

		$s = self::get_param(
			array(
				'param'    => 's',
				'sanitize' => 'sanitize_text_field',
			)
		);

		if ( $s !== '' ) {
			preg_match_all( '/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $s, $matches );
			$search_terms = array_map( 'trim', $matches[0] );

			foreach ( $search_terms as $term ) {
				$s_query[] = array(
					'or'               => true,
					'name LIKE'        => $term,
					'description LIKE' => $term,
					'created_at LIKE'  => $term,
					'form_key LIKE'    => $term,
					'id'               => $term,
				);
				unset( $term );
			}
		}

		$this->items       = FrmForm::getAll( $s_query, $orderby . ' ' . $order, $start . ',' . $per_page );
		$total_items       = FrmDb::get_count( 'frm_forms', $s_query );
		$this->total_items = $total_items;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * @return void
	 */
	public function no_items() {
		if ( $this->status === 'trash' ) {
			echo '<p>';
			esc_html_e( 'No forms found in the trash.', 'formidable' );
			// phpcs:disable Generic.WhiteSpace.ScopeIndent
			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable' ) ); ?>">
				<?php esc_html_e( 'See all forms.', 'formidable' ); ?>
			</a>
			<?php
			// phpcs:enable Generic.WhiteSpace.ScopeIndent
			echo '</p>';
		} else {
			$title = __( 'No Forms Found', 'formidable' );
			include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/_no_forms.php';
		}
	}

	public function get_bulk_actions() {
		$actions = array();

		if ( 'trash' === $this->status ) {
			if ( current_user_can( 'frm_edit_forms' ) ) {
				$actions['bulk_untrash'] = __( 'Restore', 'formidable' );
			}

			if ( current_user_can( 'frm_delete_forms' ) ) {
				$actions['bulk_delete'] = __( 'Delete Permanently', 'formidable' );
			}
		} elseif ( EMPTY_TRASH_DAYS && current_user_can( 'frm_delete_forms' ) ) {
			$actions['bulk_trash'] = __( 'Move to Trash', 'formidable' );
		} elseif ( current_user_can( 'frm_delete_forms' ) ) {
			$actions['bulk_delete'] = __( 'Delete', 'formidable' );
		}

		return $actions;
	}

	/**
	 * @param string $which
	 *
	 * @return void
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		if ( 'trash' !== $this->status || ! current_user_can( 'frm_delete_forms' ) ) {
			return;
		}

		// phpcs:disable Generic.WhiteSpace.ScopeIndent
		?>
		<div class="alignleft actions frm_visible_overflow">
			<?php submit_button( __( 'Empty Trash', 'formidable' ), 'apply', 'delete_all', false ); ?>
		</div>
		<?php
		// phpcs:enable Generic.WhiteSpace.ScopeIndent
	}

	/**
	 * @return array
	 */
	public function get_views() {
		$statuses = array(
			'published' => __( 'My Forms', 'formidable' ),
			'draft'     => __( 'Drafts', 'formidable' ),
			'trash'     => __( 'Trash', 'formidable' ),
		);

		$links     = array();
		$counts    = FrmForm::get_count();
		$form_type = FrmAppHelper::simple_get( 'form_type', 'sanitize_title', 'published' );

		if ( isset( $statuses[ $form_type ] ) ) {
			$counts->$form_type = $this->total_items;
		}

		$form_type = self::get_param(
			array(
				'param'   => 'form_type',
				'default' => 'published',
			)
		);

		foreach ( $statuses as $status => $name ) {
			$class = $status == $form_type ? ' class="current"' : ''; // phpcs:ignore Universal.Operators.StrictComparisons

			if ( $counts->{$status} || 'draft' !== $status ) {
				/* translators: %1$s: Status, %2$s: Number of items */
				$links[ $status ] = '<a href="' . esc_url( '?page=formidable&form_type=' . $status ) . '" ' . $class . '>' . sprintf( __( '%1$s <span class="count">(%2$s)</span>', 'formidable' ), $name, number_format_i18n( $counts->{$status} ) ) . '</a>'; // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
			}

			unset( $status, $name );
		}

		return $links;
	}

	/**
	 * @param stdClass $item
	 * @param string   $style
	 *
	 * @return string
	 */
	public function single_row( $item, $style = '' ) {
		global $mode;

		// Set up the hover actions for this user
		$actions   = array();
		$edit_link = FrmForm::get_edit_link( $item->id );

		$this->get_actions( $actions, $item, $edit_link );

		$action_links = $this->row_actions( $actions );
		$checkbox     = $this->get_row_checkbox( $item );
		$r            = '<tr id="item-action-' . absint( $item->id ) . '"' . $style . '>';

		list( $columns, $hidden ) = $this->get_column_info();

		$format = 'Y/m/d';

		if ( 'list' !== $mode ) {
			$format .= ' \<\b\r \/\> g:i:s a';
		}

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = $column_name . ' column-' . $column_name . ( 'name' === $column_name ? ' post-title page-title column-title' : '' );
			$style = '';

			if ( in_array( $column_name, $hidden, true ) ) {
				$class .= ' hidden';
			}

			if ( $column_name === 'name' ) {
				$class .= ' column-primary';
			}

			$class      = 'class="' . esc_attr( $class ) . '"';
			$attributes = $class . $style . $this->get_column_data_attr( $column_name, $column_display_name );

			switch ( $column_name ) {
				case 'cb':
					$r .= '<th scope="row" class="check-column">' . $checkbox . '</th>';
					break;
				case 'id':
				case 'form_key':
					$val = $item->{$column_name};
					break;
				case 'name':
					$val  = $this->get_form_name( $item, $actions, $edit_link, $mode );
					$val .= $action_links;
					break;
				case 'created_at':
					$date = gmdate( $format, strtotime( $item->created_at ) );
					$val  = '<abbr title="' . esc_attr( gmdate( 'Y/m/d g:i:s A', strtotime( $item->created_at ) ) ) . '">' . $date . '</abbr>';
					break;
				case 'entries':
					$val = $this->get_entries_column_value( $item );
					break;
				default:
					if ( method_exists( $this, 'column_' . $column_name ) ) {
						$val = call_user_func( array( $this, 'column_' . $column_name ), $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					break;
			}//end switch

			if ( isset( $val ) ) {
				$r .= "<td $attributes>";
				$r .= $val;
				$r .= '</td>';
			}
			unset( $val );
		}//end foreach
		return $r . '</tr>';
	}

	/**
	 * @param string $column_name
	 * @param string $column_display_name
	 *
	 * @return string
	 */
	private function get_column_data_attr( $column_name, $column_display_name ) {
		if ( 'settings' === $column_name ) {
			return ' data-colname="' . esc_attr( trim( strip_tags( $column_display_name ) ) ) . '"';
		}
		return ' data-colname="' . esc_attr( $column_display_name ) . '"';
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	private function get_entries_column_value( $item ) {
		if ( ! empty( $item->options['no_save'] ) ) {
			return (string) FrmAppHelper::icon_by_class(
				'frmfont frm_forbid_icon frm_bstooltip',
				array(
					'title' => __( 'Saving entries is disabled for this form', 'formidable' ),
					'echo'  => false,
				)
			);
		}

		$text = intval( FrmEntry::getRecordCount( $item->id ) );
		return current_user_can( 'frm_view_entries' ) ? '<a href="' . esc_url( admin_url( 'admin.php?page=formidable-entries&form=' . $item->id ) ) . '">' . $text . '</a>' : (string) $text; // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	private function get_row_checkbox( $item ) {
		// Set up the checkbox (because the user is editable, otherwise it's empty).
		$checkbox            = '<input type="checkbox" name="item-action[]" id="cb-item-action-' . absint( $item->id ) . '" value="' . esc_attr( $item->id ) . '" />';
		$checkbox_label_text = sprintf(
			// translators: Form title
			__( 'Select %s', 'formidable' ),
			! empty( $item->name ) ? $item->name : FrmFormsHelper::get_no_title_text()
		);

		return $checkbox . '<label for="cb-item-action-' . absint( $item->id ) . '"><span class="screen-reader-text">' . esc_html( $checkbox_label_text ) . '</span></label>';
	}

	/**
	 * Get the HTML for the Actions column in the form list.
	 * This includes multiple icons for triggering the embed modal, the visual styler, and an active landing page.
	 *
	 * @since 6.0
	 * @deprecated 6.32 We moved these actions to other places. This column will show if there is a filter added to the hook.
	 *
	 * @param stdClass $form
	 *
	 * @return string
	 */
	protected function column_shortcode( $form ) {
		$val  = '<a href="#" class="frm-embed-form" role="button" aria-label="' . esc_attr__( 'Embed Form', 'formidable' ) . '">' . FrmAppHelper::icon_by_class( 'frmfont frm_code_icon', array( 'echo' => false ) ) . '</a>'; // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
		$val .= $this->column_style( $form );
		$val .= $this->column_views( $form );
		$val  = apply_filters( 'frm_form_list_actions', $val, array( 'form' => $form ) );
		// Remove the space hard coded in Landing pages.
		$val = str_replace( '&nbsp;', '', $val );
		return '<div>' . $val . '</div>';
	}

	/**
	 * Get the HTML for the Style column in the form list.
	 *
	 * @since 6.0
	 *
	 * @param stdClass $form
	 *
	 * @return string
	 */
	protected function column_style( $form ) {
		$style_setting = $form->options['custom_style'] ?? '';
		$frm_settings  = FrmAppHelper::get_settings();

		if ( $style_setting === '0' || 'none' === $frm_settings->load_style ) {
			// Don't show a link if styling is off.
			return '';
		}

		$style = FrmStylesController::get_form_style( $form );

		if ( ! $style ) {
			// Do a second pass to avoid null values.
			$frm_style = new FrmStyle( 'default' );
			$style     = $frm_style->get_one();
		}

		$href = FrmStylesHelper::get_edit_url( $style, $form->id );
		return '<a href="' . esc_url( $href ) . '" title="' . esc_attr( $style->post_title ) . '">' . FrmAppHelper::icon_by_class( 'frmfont frm_pallet_icon', array( 'echo' => false ) ) . '</a>'; // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
	}

	/**
	 * Generate the HTML for the form Views page.
	 *
	 * @since 6.19
	 *
	 * @param stdClass $form Form object.
	 *
	 * @return string
	 */
	protected function column_views( $form ) {
		$attributes = array(
			'href'   => admin_url( 'admin.php?page=formidable-views&form=' . absint( $form->id ) . '&show_nav=1' ),
			'title'  => __( 'Link to list of all views for this form.', 'formidable' ),
			'target' => '_blank',
		);

		if ( class_exists( 'FrmViewsDisplay' ) ) {
			$view_ids   = FrmViewsDisplay::get_display_ids_by_form( $form->id );
			$view_count = $view_ids ? count( $view_ids ) : 0;
		} else {
			$view_count = 0;
		}

		// phpcs:disable Generic.WhiteSpace.ScopeIndent
		return '<a ' . FrmAppHelper::array_to_html_params( $attributes ) . '>
					' . intval( $view_count ) .
				'</a>';
		// phpcs:enable Generic.WhiteSpace.ScopeIndent
	}

	/**
	 * Get the HTML for the Settings column in the form list.
	 *
	 * @since 6.32
	 *
	 * @return string
	 */
	protected function column_settings() {
		return '&nbsp;';
	}

	/**
	 * @param array  $actions
	 * @param object $item
	 * @param string $edit_link
	 *
	 * @return void
	 */
	private function get_actions( &$actions, $item, $edit_link ) {
		$new_actions = FrmFormsHelper::get_action_links( $item->id, $item );

		foreach ( $new_actions as $link => $action ) {
			$new_actions[ $link ] = FrmFormsHelper::format_link_html( $action, 'short' );
		}

		if ( 'trash' === $this->status ) {
			$actions = $new_actions;
			return;
		}

		if ( current_user_can( 'frm_edit_forms' ) ) {
			$actions['frm_edit']     = '<a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit', 'formidable' ) . '</a>';
			$actions['frm_settings'] = '<a href="' . esc_url( '?page=formidable&frm_action=settings&id=' . $item->id ) . '">' . esc_html__( 'Settings', 'formidable' ) . '</a>';
		}

		$actions          = array_merge( $actions, $new_actions );
		$actions['embed'] = '<a href="#" class="frm-embed-form" role="button" aria-label="' . esc_attr__( 'Embed Form', 'formidable' ) . '">' . esc_html__( 'Embed', 'formidable' ) . '</a>'; // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
		$actions['view']  = '<a href="' . esc_url( FrmFormsHelper::get_direct_link( $item->form_key, $item ) ) . '" target="_blank">' . esc_html__( 'Preview', 'formidable' ) . '</a>'; // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
	}

	/**
	 * @param object $item
	 * @param array  $actions
	 * @param string $edit_link
	 * @param string $mode
	 *
	 * @return string
	 */
	private function get_form_name( $item, $actions, $edit_link, $mode = 'list' ) {
		$form_name = $item->name;

		if ( is_null( $form_name ) || trim( $form_name ) === '' ) {
			$form_name = FrmFormsHelper::get_no_title_text();
		}

		$form_name = FrmAppHelper::kses( $form_name );

		if ( 'excerpt' !== $mode ) {
			$form_name = FrmAppHelper::truncate( $form_name, 50 );
		}

		$val = '<strong>';

		if ( 'trash' === $this->status ) {
			$val .= $form_name;
		} else {
			$val .= '<a href="' . esc_url( isset( $actions['frm_edit'] ) ? $edit_link : FrmFormsHelper::get_direct_link( $item->form_key, $item ) ) . '" class="row-title">' . FrmAppHelper::kses( $form_name ) . '</a> '; // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
		}

		$this->add_draft_label( $item, $val );
		$val .= '</strong>';

		$this->add_form_description( $item, $val );

		return $val;
	}

	/**
	 * @param object $item
	 * @param string $val
	 *
	 * @return void
	 */
	private function add_draft_label( $item, &$val ) {
		if ( 'draft' === $item->status && 'draft' !== $this->status ) {
			$val .= ' - <span class="post-state">' . esc_html__( 'Draft', 'formidable' ) . '</span>';
		}
	}

	/**
	 * @param object $item
	 * @param string $val
	 *
	 * @return void
	 */
	private function add_form_description( $item, &$val ) {
		global $mode;

		if ( 'excerpt' === $mode && ! is_null( $item->description ) ) {
			$val .= FrmAppHelper::truncate( strip_tags( $item->description ), 50 );
		}
	}

	/**
	 * @return string
	 */
	protected function confirm_bulk_delete() {
		return __( 'ALL selected forms and their entries will be permanently deleted. Want to proceed?', 'formidable' );
	}

	/**
	 * @param stdClass $form
	 *
	 * @return string
	 */
	public function column_embeds( $form ) {
		$posts = $this->get_posts_contain_form( $form );

		if ( ! $posts ) {
			return '<span class="frm-forms-list-embeds-zero">0</span>';
		}

		return FrmAppHelper::clip(
			function () use ( $posts ) {
				?>
				<a href="#" class="frm-forms-list-embeds-btn" data-posts="<?php echo esc_attr( wp_json_encode( $posts ) ); ?>">
					<?php
					FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown6_icon' );
					echo intval( count( $posts ) );
					?>
				</a>
				<?php
			}
		);
	}

	/**
	 * Gets posts or pages that contain the form shortcode.
	 *
	 * @since 6.32
	 *
	 * @param stdClass $form Form object.
	 *
	 * @return array
	 */
	private function get_posts_contain_form( $form ) {
		$cached_posts = get_transient( self::$embed_posts_transient_name );

		if ( isset( $cached_posts[ $form->id ] ) && is_array( $cached_posts[ $form->id ] ) ) {
			return $cached_posts[ $form->id ];
		}

		$posts = $this->query_posts_contain_form( $form );

		if ( ! is_array( $posts ) ) {
			return array();
		}

		foreach ( $posts as $post ) {
			if ( ! property_exists( $post, 'permalink' ) ) {
				$post->permalink = get_permalink( $post->ID );
			}

			if ( ! property_exists( $post, 'edit_link' ) ) {
				$post->edit_link = get_edit_post_link( $post->ID );
			}

			// Ensure post_name is not null or the string "null"
			if ( ! isset( $post->post_name ) ) {
				$post->post_name = '';
			}

			// Ensure post_title is not null or the string "null"
			if ( ! isset( $post->post_title ) ) {
				$post->post_title = '';
			}

			if ( '' === $post->post_title ) {
				$post->post_title = __( '(no title)', 'formidable' );
			}
		}//end foreach

		if ( ! is_array( $cached_posts ) ) {
			$cached_posts = array();
		}

		$cached_posts[ $form->id ] = $posts;
		set_transient( self::$embed_posts_transient_name, $cached_posts, DAY_IN_SECONDS );

		return $posts;
	}

	/**
	 * Gets search strings for a form inside a post.
	 *
	 * @since 6.32
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return string[]
	 */
	protected function get_search_strings_for_form( $form_id ) {
		return $this->get_base_search_strings_for_form( $form_id );
	}

	/**
	 * Gets the base search strings for a form inside a post.
	 *
	 * @since 6.32
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return string[]
	 */
	protected function get_base_search_strings_for_form( $form_id ) {
		return array(
			'[formidable id=' . $form_id . ']',
			'[formidable id=' . $form_id . ' ',
			'[formidable id="' . $form_id . '"',
			"[formidable id='" . $form_id . "'",
			'<!-- wp:formidable/simple-form {"formId":"' . $form_id . '"',
		);
	}

	/**
	 * Queries for posts that contain the form shortcode.
	 *
	 * @param stdClass $form Form object.
	 *
	 * @return array
	 */
	private function query_posts_contain_form( $form ) {
		$form_id = $form->id;
		global $wpdb;
		$query_strings = $this->get_search_strings_for_form( $form_id );
		$like_where    = array();

		foreach ( $query_strings as $query_string ) {
			$like_where[] = $wpdb->remove_placeholder_escape( $wpdb->prepare( 'post_content LIKE %s', '%' . $query_string . '%' ) );
		}

		$like_where = implode( ' OR ', $like_where );
		$where      = "post_type IN ('post', 'page') AND ($like_where)";

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$posts = $wpdb->get_results( "SELECT ID,post_title,post_name FROM $wpdb->posts WHERE $where" );

		if ( ! is_array( $posts ) ) {
			return array();
		}

		/**
		 * @since 6.32
		 *
		 * @param stdClass[] $posts
		 * @param array      $args
		 */
		$filtered_posts = apply_filters( 'frm_get_posts_contain_form', $posts, compact( 'form' ) );

		if ( ! is_array( $filtered_posts ) ) {
			_doing_it_wrong( 'frm_get_posts_contain_form', 'Filter should return an array.', '6.32' );
			return $posts;
		}

		return $filtered_posts;
	}

	/**
	 * Maybe clear the embed posts transient.
	 *
	 * @since 6.32
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 *
	 * @return void
	 */
	public static function maybe_clear_embed_posts_transient( $post_id, $post ) {
		if ( str_contains( $post->post_content, '[formidable ' ) || str_contains( $post->post_content, '<!-- wp:formidable/simple-form ' ) ) {
			// New post contains the form shortcode, so clear the embed posts transient.
			delete_transient( self::$embed_posts_transient_name );
			return;
		}

		$cached_posts = get_transient( self::$embed_posts_transient_name );

		if ( ! is_array( $cached_posts ) ) {
			return;
		}

		// If the new post data of a cached post doesn't contain the Formidable forms, clear the transient.
		foreach ( $cached_posts as $posts ) {
			foreach ( $posts as $post_data ) {
				if ( intval( $post_data->ID ) === intval( $post_id ) ) {
					// This post contains the form shortcode before updating, so clear the embed posts transient.
					delete_transient( self::$embed_posts_transient_name );
					return;
				}
			}
		}
	}
}
