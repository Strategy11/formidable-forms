<?php
/**
 * Native forms and Views setup shared by optional solution add-ons.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSolutionFormsHelper {
	/** @var array Installed solution definitions. */
	private static $solutions = array();

	/** @var array Resource locks held through native validation and persistence. */
	private static $locks = array();

	/**
	 * Keep a capacity decision locked through the native entry write.
	 *
	 * @since x.x
	 *
	 * @param string $resource Stable solution resource identity.
	 *
	 * @return bool
	 */
	public static function lock( $resource ) {
		global $wpdb;
		$name = 'frm_solution_' . md5( $wpdb->prefix . ':' . $resource );

		if ( isset( self::$locks[ $name ] ) ) {
			return true;
		}

		if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 5)', $name ) ) ) {
			return false;
		}

		if ( ! self::$locks ) {
			register_shutdown_function( array( self::class, 'release_locks' ) );
		}
		self::$locks[ $name ] = true;
		return true;
	}

	/**
	 * Release only locks acquired by this helper.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function release_locks() {
		global $wpdb;

		foreach ( array_keys( self::$locks ) as $name ) {
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $name ) );
		}
		self::$locks = array();
	}

	/**
	 * Normalize a native date field using Formidable Pro's configured format.
	 *
	 * @since x.x
	 *
	 * @param string $value Submitted or stored date value.
	 *
	 * @return string
	 */
	public static function date( $value ) {
		if ( preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $value ) ) {
			$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, wp_timezone() );
			return $date && $date->format( 'Y-m-d' ) === $value ? $value : '';
		}

		if ( ! class_exists( 'FrmProAppHelper' ) ) {
			return '';
		}

		$settings = FrmProAppHelper::get_settings();
		$date     = FrmProAppHelper::convert_date( $value, $settings->date_format, 'Y-m-d' );
		return is_string( $date ) ? $date : '';
	}

	/**
	 * Register an optional solution without creating any site content.
	 *
	 * @since x.x
	 *
	 * @param string $slug Stable add-on identity.
	 * @param array  $definition Labels, forms, screens and domain callback.
	 *
	 * @return void
	 */
	public static function register( $slug, $definition ) {
		if ( ! self::$solutions ) {
			add_action( 'admin_menu', array( self::class, 'menu' ) );
			add_action( 'admin_post_frm_solution_setup', array( self::class, 'handle_setup' ) );
			add_filter( 'frm_views_types', array( self::class, 'types' ) );
			add_filter( 'frm_views_setup_solutions', array( self::class, 'destinations' ) );
			add_filter( 'frm_validate_entry', array( self::class, 'validate' ), 1000, 2 );
			add_filter( 'frm_pre_create_entry', array( self::class, 'prepare' ) );
			add_action( 'frm_after_create_entry', array( self::class, 'changed' ), 15, 2 );
			add_action( 'frm_after_update_entry', array( self::class, 'changed' ), 5, 2 );
			add_filter( 'frm_setup_new_fields_vars', array( self::class, 'choices' ), 20, 2 );
			add_filter( 'frm_setup_edit_fields_vars', array( self::class, 'choices' ), 20, 2 );
			add_filter( 'frm_can_access_protected_file', array( self::class, 'file_access' ), 20, 2 );
		}
		self::$solutions[ $slug ] = $definition;
	}

	/**
	 * Resolve the configured native form for a solution source.
	 *
	 * @since x.x
	 *
	 * @param string $slug Solution identity.
	 * @param string $source Source identity.
	 *
	 * @return int
	 */
	public static function form( $slug, $source ) {
		return FrmForm::get_id_by_key( 'frm_' . $slug . '_' . $source );
	}

	/**
	 * Resolve a source field using its stable native field key.
	 *
	 * @since x.x
	 *
	 * @param string $slug Solution identity.
	 * @param string $source Source identity.
	 * @param string $name Field role.
	 *
	 * @return int
	 */
	public static function field( $slug, $source, $name ) {
		return FrmField::get_id_by_key( 'frm_' . $slug . '_' . $source . '_' . $name );
	}

	/**
	 * Read a native record only from the intended source.
	 *
	 * @since x.x
	 *
	 * @param string $slug Solution identity.
	 * @param string $source Source identity.
	 * @param int    $id Native entry ID.
	 *
	 * @return array
	 */
	public static function record( $slug, $source, $id ) {
		$entry = FrmEntry::getOne( $id, true );

		if ( ! $entry || $entry->is_draft || (int) $entry->form_id !== self::form( $slug, $source ) ) {
			return array();
		}

		$row = array(
			'id'    => (int) $entry->id,
			'owner' => (int) $entry->user_id,
		);

		foreach ( self::$solutions[ $slug ]['forms'][ $source ]['fields'] as $name => $field ) {
			$row[ $name ] = $entry->metas[ self::field( $slug, $source, $name ) ] ?? '';
		}

		return $row;
	}

	/**
	 * Load matching native entries from one form.
	 *
	 * @since x.x
	 *
	 * @param string $slug Solution identity.
	 * @param string $source Source identity.
	 * @param array  $filters Exact field values.
	 *
	 * @return array
	 */
	public static function records( $slug, $source, $filters = array() ) {
		$form = self::form( $slug, $source );

		if ( ! $form ) {
			return array();
		}

		$entries = FrmEntry::getAll(
			array(
				'it.form_id'        => $form,
				'it.is_draft'       => 0,
				'it.parent_item_id' => 0,
			),
			' ORDER BY it.id ASC',
			'',
			true,
			false
		);
		$rows    = array();

		foreach ( $entries as $entry ) {
			$row     = self::record( $slug, $source, $entry->id );
			$matches = true;

			foreach ( $filters as $key => $value ) {
				if ( (string) ( $row[ $key ] ?? '' ) !== (string) $value ) {
					$matches = false;
					break;
				}
			}

			if ( $matches ) {
				$rows[] = $row;
			}
		}

		return $rows;
	}

	/**
	 * Update a computed field through Formidable's native metadata API.
	 *
	 * @since x.x
	 *
	 * @param string $slug Solution identity.
	 * @param string $source Source identity.
	 * @param int    $id Native entry ID.
	 * @param string $name Field role.
	 * @param mixed  $value Computed value.
	 *
	 * @return bool
	 */
	public static function set( $slug, $source, $id, $name, $value ) {
		$entry = FrmEntry::getOne( $id, true );
		$field = self::field( $slug, $source, $name );

		if ( ! $entry || ! $field || (int) $entry->form_id !== self::form( $slug, $source ) ) {
			return false;
		}

		if ( array_key_exists( $field, $entry->metas ) ) {
			return false !== FrmEntryMeta::update_entry_meta( $id, $field, '', $value );
		}

		return false !== FrmEntryMeta::add_entry_meta( $id, $field, '', $value );
	}

	/**
	 * Register discoverable types, keeping source forms fixed after setup.
	 *
	 * @since x.x
	 *
	 * @param array $types Existing View types.
	 *
	 * @return array
	 */
	public static function types( $types ) {
		if ( ! class_exists( 'FrmViewsRecordScreenHelper' ) ) {
			return $types;
		}

		foreach ( self::$solutions as $slug => $definition ) {
			$screens = $definition['screens'];

			foreach ( $screens as $type => $screen ) {
				$screens[ $type ]['form_id']         = self::form( $slug, $screen['source'] );
				$screens[ $type ]['solution']        = $slug;
				$screens[ $type ]['create_callback'] = array( self::class, 'initialize_view' );

				if ( ! $screens[ $type ]['form_id'] ) {
					$screens[ $type ]['setup_url'] = admin_url( 'admin.php?page=formidable-' . $slug );
				}
			}

			$types = FrmViewsRecordScreenHelper::register(
				$types,
				$screens,
				array(
					'category'       => $slug,
					'category_label' => $definition['label'],
					'category_icon'  => 'frm_users_solid_icon',
					'icon'           => 'frm_users_solid_icon',
				)
			);
		}//end foreach

		return $types;
	}

	/**
	 * Give generated Views their native field mappings and access defaults.
	 *
	 * @since x.x
	 *
	 * @param int    $id Created View ID.
	 * @param string $type Registered type identity.
	 *
	 * @return void
	 */
	public static function initialize_view( $id, $type ) {
		foreach ( self::$solutions as $slug => $definition ) {
			if ( ! isset( $definition['screens'][ $type ] ) ) {
				continue;
			}

			$screen   = $definition['screens'][ $type ];
			$settings = array( 'publish_records' => 'public' === $screen['screen_access'] ? 1 : 0 );

			foreach ( $screen['screen_columns'] as $name => $label ) {
				$settings[ $name . '_field' ] = self::field( $slug, $screen['source'], $name );
			}

			if ( ! empty( $screen['screen_filter'] ) ) {
				$settings['filter_value']                        = $screen['status_value'];
				$settings[ $screen['screen_filter'] . '_field' ] = self::field( $slug, $screen['source'], $screen['screen_filter'] );
			}

			$state = (array) get_option( 'frm_solution_' . $slug, array() );

			if ( ! empty( $state['form_pages'][ $screen['source'] ] ) ) {
				$settings['edit_form_url'] = get_permalink( $state['form_pages'][ $screen['source'] ] );
			}

			if ( ! empty( $screen['link_source'] ) && ! empty( $state['form_pages'][ $screen['link_source'] ] ) ) {
				$settings['detail_url'] = get_permalink( $state['form_pages'][ $screen['link_source'] ] );
			}

			FrmViewsSetupHelper::initialize_view( $id, array( 'type_settings' => $settings ), array( 'form' => self::form( $slug, $screen['source'] ) ) );
		}//end foreach
	}

	/**
	 * Create native forms, fields, editable Views and draft pages explicitly.
	 *
	 * @since x.x
	 *
	 * @param string $slug Installed solution identity.
	 *
	 * @return array|WP_Error
	 */
	public static function setup( $slug ) {
		if ( ! self::permitted() || ! isset( self::$solutions[ $slug ] ) ) {
			return new WP_Error( 'permission', __( 'Setup requires permission to manage forms, entries, Views and pages.', 'formidable' ) );
		}

		$definition = self::$solutions[ $slug ];

		foreach ( $definition['forms'] as $source => $configuration ) {
			$form = self::form( $slug, $source );

			if ( ! $form ) {
				$form = FrmForm::create(
					array(
						'name'        => $configuration['label'],
						'description' => '',
						'form_key'    => 'frm_' . $slug . '_' . $source,
						'status'      => 'published',
						'logged_in'   => 1,
						'editable'    => 1,
						'options'     => array(
							'editable'           => 1,
							'editable_role'      => '',
							'open_editable_role' => 'administrator',
							'protect_files'      => 1,
							'protect_files_role' => array( 'administrator' ),
							'noindex_files'      => 1,
							'submit_value'       => __( 'Submit', 'formidable' ),
							'success_msg'        => __( 'Your submission has been received.', 'formidable' ),
						),
					)
				);
			}//end if

			if ( ! $form ) {
				return new WP_Error( 'form', __( 'A starter form could not be created. Retry setup to resume.', 'formidable' ) );
			}

			foreach ( $configuration['fields'] as $name => $field ) {
				if ( self::field( $slug, $source, $name ) ) {
					continue;
				}

				$values                  = FrmFieldsHelper::setup_new_vars( $field['type'], $form );
				$values['name']          = $field['label'];
				$values['field_key']     = 'frm_' . $slug . '_' . $source . '_' . $name;
				$values['required']      = ! empty( $field['required'] );
				$values['default_value'] = $field['default'] ?? '';

				if ( isset( $field['choices'] ) ) {
					$values['options']                         = $field['choices'];
					$values['field_options']['separate_value'] = 1;
				}

				if ( ! empty( $field['managed'] ) ) {
					$values['field_options']['read_only'] = 1;
				}

				if ( ! FrmField::create( $values ) ) {
					return new WP_Error( 'field', __( 'A starter field could not be created. Retry setup to resume.', 'formidable' ) );
				}
			}//end foreach
		}//end foreach

		return self::setup_views( $slug );
	}

	/**
	 * Connect generated Views and draft pages without replacing customization.
	 *
	 * @since x.x
	 *
	 * @param string $slug Installed solution identity.
	 *
	 * @return array|WP_Error
	 */
	private static function setup_views( $slug ) {
		$state = (array) get_option( 'frm_solution_' . $slug, array() );

		foreach ( self::$solutions[ $slug ]['forms'] as $source => $configuration ) {
			if ( ! empty( $configuration['staff'] )
				|| ( ! empty( $state['form_pages'][ $source ] )
				&& 'page' === get_post_type( $state['form_pages'][ $source ] )
				&& 'trash' !== get_post_status( $state['form_pages'][ $source ] ) ) ) {
				continue;
			}

			$page = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'draft',
					'post_title'   => $configuration['label'],
					'post_content' => '[formidable id="' . self::form( $slug, $source ) . '"]',
				),
				true
			);

			if ( is_wp_error( $page ) ) {
				return $page;
			}
			$state['form_pages'][ $source ] = $page;
			update_option( 'frm_solution_' . $slug, $state, false );
		}//end foreach

		FrmViewsTypeHelper::reset_cache();

		foreach ( self::$solutions[ $slug ]['screens'] as $type => $screen ) {
			$id = $state['views'][ $type ] ?? 0;

			if ( ! $id || ! get_post_status( $id ) || 'trash' === get_post_status( $id ) ) {
				$id = FrmViewsDisplay::create( self::form( $slug, $screen['source'] ), $type, $screen['label'] );

				if ( ! $id ) {
					return new WP_Error( 'view', __( 'A View could not be created. Retry setup to resume.', 'formidable' ) );
				}
				$state['views'][ $type ] = $id;
			}

			$page = $state['pages'][ $type ] ?? 0;

			if ( ! $page || ! get_post_status( $page ) || 'trash' === get_post_status( $page ) ) {
				$page = wp_insert_post(
					array(
						'post_type'    => 'page',
						'post_status'  => 'draft',
						'post_title'   => $screen['label'],
						'post_content' => FrmViewsSetupHelper::view_block( $id ),
					),
					true
				);

				if ( is_wp_error( $page ) ) {
					return $page;
				}
				$state['pages'][ $type ] = $page;
			}

			update_option( 'frm_solution_' . $slug, $state, false );
		}//end foreach

		return $state;
	}

	/**
	 * Check the native setup capabilities and optional Views API.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	private static function permitted() {
		return FrmAppHelper::current_user_can( 'frm_edit_forms' )
		&& FrmAppHelper::current_user_can( 'frm_edit_entries' )
		&& FrmAppHelper::current_user_can( 'frm_edit_displays' )
		&& current_user_can( 'edit_pages' )
		&& class_exists( 'FrmViewsTypeHelper' )
		&& class_exists( 'FrmViewsSetupHelper' );
	}

	/**
	 * Match a native form to its installed solution definition.
	 *
	 * @since x.x
	 *
	 * @param int $form Native form ID.
	 *
	 * @return array
	 */
	private static function identify( $form ) {
		$form = absint( $form );

		foreach ( self::$solutions as $slug => $definition ) {
			foreach ( $definition['forms'] as $source => $configuration ) {
				if ( self::form( $slug, $source ) === $form ) {
					return array( $slug, $source, $configuration );
				}
			}
		}

		return array();
	}

	/**
	 * Enforce source ownership, managed fields and source-bound references.
	 *
	 * @since x.x
	 *
	 * @param array $errors Existing native validation errors.
	 * @param array $values Submitted native entry.
	 *
	 * @return array
	 */
	public static function validate( $errors, $values ) {
		$identity = self::identify( absint( $values['form_id'] ?? 0 ) );

		if ( ! $identity ) {
			return $errors;
		}
		list( $slug, $source, $configuration ) = $identity;
		$id                                    = absint( $values['id'] ?? 0 );
		$previous                              = $id ? self::record( $slug, $source, $id ) : array();
		$manager                               = FrmAppHelper::current_user_can( 'frm_edit_entries' );

		if ( ! is_user_logged_in()
			|| ( ! empty( $configuration['staff'] )
			&& ! $manager )
			|| ( $id
			&& ( ! $previous
			|| ( ! $manager
			&& $previous['owner'] !== get_current_user_id() ) ) ) ) {
			$errors['solution'] = __( 'You do not have permission to change this record.', 'formidable' );
			return $errors;
		}

		$row = array();

		foreach ( $configuration['fields'] as $name => $field ) {
			$field_id     = self::field( $slug, $source, $name );
			$value        = $values['item_meta'][ $field_id ] ?? '';
			$row[ $name ] = $value;

			if ( 'file' !== $field['type'] && ! is_scalar( $value ) ) {
				$errors[ 'field' . $field_id ] = __( 'Enter a single valid value.', 'formidable' );
				$row[ $name ]                  = '';
				continue;
			}

			if ( isset( $field['choices'] ) ) {
				$allowed = array_column( $field['choices'], 'value' );

				if ( ! is_scalar( $value ) || ! in_array( (string) $value, $allowed, true ) ) {
					$errors[ 'field' . $field_id ] = __( 'Choose one of the configured options.', 'formidable' );
				}
			}

			if ( ! empty( $field['immutable'] ) && $previous && (string) $value !== (string) $previous[ $name ] ) {
				$errors[ 'field' . $field_id ] = __( 'This record is connected to its original selection. Create a new record to choose another.', 'formidable' );
			}

			if ( ! empty( $field['managed'] ) && ! $manager && (string) $value !== (string) ( $previous[ $name ] ?? ( $field['default'] ?? '' ) ) ) {
				$errors[ 'field' . $field_id ] = __( 'This value is managed by your organization.', 'formidable' );
			}

			if ( ! empty( $field['reference'] ) && $value && ! self::record( $slug, $field['reference'], absint( $value ) ) ) {
				$errors[ 'field' . $field_id ] = __( 'Choose a record from the configured source.', 'formidable' );
			}
		}//end foreach

		$domain = self::$solutions[ $slug ]['handler'];
		$result = call_user_func( array( $domain, 'validate' ), $errors, $source, $row, $previous );
		return is_array( $result ) ? $result : array_merge( $errors, array( 'solution' => __( 'This submission could not be validated. Please try again.', 'formidable' ) ) );
	}

	/**
	 * Preserve the selected employee ownership only on staff-authored records.
	 *
	 * @since x.x
	 *
	 * @param array $values Native entry values.
	 *
	 * @return array
	 */
	public static function prepare( $values ) {
		$identity = self::identify( absint( $values['form_id'] ?? 0 ) );

		if ( ! $identity ) {
			return $values;
		}
		list( $slug, $source, $configuration ) = $identity;
		$owner_field                           = $configuration['owner_field'] ?? '';
		$values['frm_user_id']                 = get_current_user_id();

		if ( $owner_field && FrmAppHelper::current_user_can( 'frm_edit_entries' ) ) {
			$owner = absint( $values['item_meta'][ self::field( $slug, $source, $owner_field ) ] ?? 0 );

			if ( get_userdata( $owner ) ) {
				$values['frm_user_id'] = $owner;
			}
		}

		return $values;
	}

	/**
	 * Run domain transitions after the native entry is stored.
	 *
	 * @since x.x
	 *
	 * @param int $id Stored native entry ID.
	 * @param int $form Source form ID.
	 *
	 * @return void
	 */
	public static function changed( $id, $form ) {
		$identity = self::identify( $form );

		if ( ! $identity ) {
			return;
		}

		list( $slug, $source ) = $identity;
		call_user_func( array( self::$solutions[ $slug ]['handler'], 'changed' ), $source, self::record( $slug, $source, $id ) );
	}

	/**
	 * Populate native select choices from the solution's configured source form.
	 *
	 * @since x.x
	 *
	 * @param array  $values Native field presentation values.
	 * @param object $field Native field definition.
	 *
	 * @return array
	 */
	public static function choices( $values, $field ) {
		$identity = self::identify( $field->form_id );

		if ( ! $identity ) {
			return $values;
		}
		list( $slug, $source, $configuration ) = $identity;

		foreach ( $configuration['fields'] as $name => $definition ) {
			if ( ! empty( $definition['user_choices'] ) && self::field( $slug, $source, $name ) === (int) $field->id ) {
				$values['options'] = array();
				$users             = FrmAppHelper::current_user_can( 'frm_edit_entries' ) ? get_users( array( 'number' => 1000 ) ) : array( wp_get_current_user() );

				foreach ( $users as $user ) {
					$values['options'][ (string) $user->ID ] = array(
						'label' => $user->display_name,
						'value' => (string) $user->ID,
					);
				}

				$values['separate_value'] = 1;
				continue;
			}

			if ( empty( $definition['reference'] ) || self::field( $slug, $source, $name ) !== (int) $field->id ) {
				continue;
			}

			$values['options']     = array();
			$values['options'][''] = '';

			$filters = FrmAppHelper::current_user_can( 'frm_edit_entries' ) ? array() : ( $definition['reference_filters'] ?? array() );

			foreach ( self::records( $slug, $definition['reference'], $filters ) as $row ) {
				$values['options'][ (string) $row['id'] ] = array(
					'label' => $row['title'],
					'value' => (string) $row['id'],
				);
			}

			$values['separate_value'] = 1;
		}//end foreach

		return $values;
	}

	/**
	 * Grant native protected downloads to the record owner or entry managers.
	 *
	 * @since x.x
	 *
	 * @param bool $allowed Earlier protected-file access decision.
	 * @param int  $attachment Native attachment ID.
	 *
	 * @return bool
	 */
	public static function file_access( $allowed, $attachment ) {
		$attachment = absint( $attachment );

		if ( ! class_exists( 'FrmProFileField' ) ) {
			return $allowed;
		}

		$identity = self::identify( FrmProFileField::get_form_id_from_file_id( $attachment ) );

		if ( ! $identity ) {
			return $allowed;
		}

		if ( ! is_user_logged_in() ) {
			return false;
		}
		list( $slug, $source, $configuration ) = $identity;

		foreach ( self::records( $slug, $source ) as $row ) {
			if ( get_current_user_id() !== $row['owner'] && ! FrmAppHelper::current_user_can( 'frm_edit_entries' ) ) {
				continue;
			}

			foreach ( $configuration['fields'] as $name => $definition ) {
				if ( 'file' === $definition['type'] && in_array( $attachment, wp_parse_id_list( $row[ $name ] ), true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Add installed solutions to the native Formidable admin menu.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function menu() {
		foreach ( self::$solutions as $slug => $definition ) {
			add_submenu_page( 'formidable', $definition['label'], $definition['label'], 'frm_edit_forms', 'formidable-' . $slug, array( self::class, 'admin' ) );
		}
	}

	/**
	 * Share explicit setup destinations with the Views hub.
	 *
	 * @since x.x
	 *
	 * @param array $destinations Existing destinations.
	 *
	 * @return array
	 */
	public static function destinations( $destinations ) {
		foreach ( self::$solutions as $slug => $definition ) {
			$destinations[ 'native-' . $slug ] = array( $definition['label'], 'formidable-' . $slug, true );
		}
		return $destinations;
	}

	/**
	 * Render setup and direct links to native forms, entries and draft pages.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function admin() {
		$slug = str_replace( 'formidable-', '', FrmAppHelper::simple_get( 'page', 'sanitize_key', '' ) );

		if ( ! isset( self::$solutions[ $slug ] ) || ! FrmAppHelper::current_user_can( 'frm_edit_forms' ) ) {
			return;
		}

		$definition = self::$solutions[ $slug ];
		$state      = get_option( 'frm_solution_' . $slug, array() );
		require __DIR__ . '/../views/shared/solution-setup.php';
	}

	/**
	 * Handle explicit setup through native nonce and capability checks.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function handle_setup() {
		check_admin_referer( 'frm_solution_setup' );
		$slug   = FrmAppHelper::get_post_param( 'solution', '', 'sanitize_key' );
		$result = self::setup( $slug );

		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=formidable-' . $slug ) );
		exit;
	}
}
