<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormActionsController {
	public static $action_post_type = 'frm_form_actions';
	public static $registered_actions;

	/**
	 * Variables saved in the post:
	 * post_content: json settings
	 * menu_order: form id
	 * post_excerpt: action type
	 */
	public static function register_post_types() {
		register_post_type(
			self::$action_post_type,
			array(
				'label'               => __( 'Form Actions', 'formidable' ),
				'description'         => '',
				'public'              => false,
				'show_ui'             => false,
				'exclude_from_search' => true,
				'show_in_nav_menus'   => false,
				'show_in_menu'        => true,
				'capability_type'     => 'page',
				'supports'            => array( 'title', 'editor', 'excerpt', 'custom-fields', 'page-attributes' ),
				'has_archive'         => false,
			)
		);

		self::actions_init();
	}

	public static function actions_init() {
		self::$registered_actions = new Frm_Form_Action_Factory();
		self::register_actions();
		do_action( 'frm_form_actions_init' );
	}

	/**
	 * @return void
	 */
	public static function register_actions() {
		$action_classes = array(
			'on_submit'         => 'FrmOnSubmitAction',
			'email'             => 'FrmEmailAction',
			'wppost'            => 'FrmDefPostAction',
			'register'          => 'FrmDefRegAction',
			'paypal'            => 'FrmDefPayPalAction',
			'payment'           => 'FrmTransLiteAction',
			'quiz'              => 'FrmDefQuizAction',
			'quiz_outcome'      => 'FrmDefQuizOutcomeAction',
			'mailchimp'         => 'FrmDefMlcmpAction',
			'api'               => 'FrmDefApiAction',
			'salesforce'        => 'FrmDefSalesforceAction',
			'activecampaign'    => 'FrmDefActiveCampaignAction',
			'constantcontact'   => 'FrmDefConstContactAction',
			'getresponse'       => 'FrmDefGetResponseAction',
			'hubspot'           => 'FrmDefHubspotAction',
			'zapier'            => 'FrmDefZapierAction',
			'twilio'            => 'FrmDefTwilioAction',
			'highrise'          => 'FrmDefHighriseAction',
			'mailpoet'          => 'FrmDefMailpoetAction',
			'aweber'            => 'FrmDefAweberAction',
			'googlespreadsheet' => 'FrmDefGoogleSpreadsheetAction',
		);

		$action_classes = apply_filters( 'frm_registered_form_actions', $action_classes );

		include_once FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/email_action.php';
		include_once FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/default_actions.php';

		foreach ( $action_classes as $action_class ) {
			self::$registered_actions->register( $action_class );
		}
	}

	/**
	 * @since 4.0
	 *
	 * @param array $values
	 */
	public static function email_settings( $values ) {
		$form   = FrmForm::getOne( $values['id'] );
		$groups = self::form_action_groups();

		$action_controls = self::get_form_actions();
		self::maybe_add_action_to_group( $action_controls, $groups );

		$allowed = self::active_actions( $action_controls );

		include FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/settings.php';
	}

	/**
	 * Add unknown actions to a group.
	 *
	 * @since 4.0
	 */
	private static function maybe_add_action_to_group( $action_controls, &$groups ) {
		$grouped = array();
		foreach ( $groups as $group ) {
			if ( isset( $group['actions'] ) ) {
				$grouped = array_merge( $grouped, $group['actions'] );
			}
		}

		foreach ( $action_controls as $action ) {
			if ( isset( $groups[ $action->id_base ] ) || in_array( $action->id_base, $grouped ) ) {
				continue;
			}

			$this_group = $action->action_options['group'];
			if ( ! isset( $groups[ $this_group ] ) ) {
				$this_group = 'misc';
			}

			if ( ! isset( $groups[ $this_group ]['actions'] ) ) {
				$groups[ $this_group ]['actions'] = array();
			}
			$groups[ $this_group ]['actions'][] = $action->id_base;

			unset( $action );
		}
	}

	/**
	 * @since 4.0
	 *
	 * @return array
	 */
	public static function form_action_groups() {
		$groups = array(
			'misc'      => array(
				'name'    => '',
				'icon'    => 'frm_icon_font frm_shuffle_icon',
				'actions' => array(
					'email',
					'wppost',
					'register',
					'quiz',
					'quiz_outcome',
					'twilio',
				),
			),
			'payment'   => array(
				'name'    => __( 'eCommerce', 'formidable' ),
				'icon'    => 'frm_icon_font frm_credit_card_alt_icon',
				'actions' => array(
					'paypal',
					'payment',
				),
			),
			'marketing' => array(
				'name'    => __( 'Email Marketing', 'formidable' ),
				'icon'    => 'frm_icon_font frm_mail_bulk_icon',
				'actions' => array(
					'mailchimp',
					'activecampaign',
					'constantcontact',
					'getresponse',
					'aweber',
					'mailpoet',
				),
			),
			'crm'       => array(
				'name'    => __( 'CRM', 'formidable' ),
				'icon'    => 'frm_icon_font frm_address_card_icon',
				'actions' => array(
					'salesforce',
					'hubspot',
					'highrise',
				),
			),
		);

		return apply_filters( 'frm_action_groups', $groups );
	}

	/**
	 * Get the number of currently active form actions.
	 *
	 * @since 4.0
	 *
	 * @return array
	 */
	private static function active_actions( $action_controls ) {
		$allowed = array();
		foreach ( $action_controls as $action_control ) {
			if ( isset( $action_control->action_options['active'] ) && $action_control->action_options['active'] ) {
				$allowed[] = $action_control->id_base;
			}
		}
		return $allowed;
	}

	/**
	 * For each add-on, add an li, class, and javascript function. If active, add an additional class.
	 *
	 * @since 4.0
	 * @param object $action_control
	 * @param array  $allowed
	 */
	public static function show_action_icon_link( $action_control, $allowed ) {
		$data    = array();
		$classes = ' frm_' . $action_control->id_base . '_action frm_single_action';

		$group_class = ' frm-group-' . $action_control->action_options['group'];

		/* translators: %s: Name of form action */
		$upgrade_label = sprintf( esc_html__( '%s form actions', 'formidable' ), $action_control->action_options['tooltip'] );

		$default_shown    = array( 'wppost', 'register', 'payment', 'quiz', 'hubspot' );
		$default_shown    = array_values( array_diff( $default_shown, $allowed ) );
		$default_position = array_search( $action_control->id_base, $default_shown );
		$allowed_count    = count( $allowed );

		if ( isset( $action_control->action_options['active'] ) && $action_control->action_options['active'] ) {
			$classes .= ' frm_active_action';
		} else {
			$classes .= ' frm_inactive_action';
			if ( $default_position !== false && ( $allowed_count + $default_position ) < 6 ) {
				$group_class .= ' frm-default-show';
			}

			$data['data-upgrade'] = $upgrade_label;
			$data['data-medium']  = 'settings-' . $action_control->id_base;

			$upgrading = FrmAddonsController::install_link( $action_control->action_options['plugin'] );
			if ( isset( $upgrading['url'] ) ) {
				$data['data-oneclick'] = json_encode( $upgrading );
			}

			if ( isset( $action_control->action_options['message'] ) ) {
				$data['data-message'] = $action_control->action_options['message'];
			}

			$requires = FrmFormsHelper::get_plan_required( $upgrading );
			if ( $requires && 'free' !== $requires ) {
				$data['data-requires'] = $requires;
			}
		}//end if

		// HTML to include on the icon.
		$icon_atts = array();
		if ( $action_control->action_options['color'] !== 'var(--primary-700)' ) {
			$icon_atts = array(
				'style' => '--primary-700:' . $action_control->action_options['color'],
			);
		}

		include FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/_action_icon.php';
	}

	public static function get_form_actions( $action = 'all' ) {
		$temp_actions = self::$registered_actions;
		if ( empty( $temp_actions ) ) {
			self::actions_init();
			$temp_actions = self::$registered_actions->actions;
		} else {
			$temp_actions = $temp_actions->actions;
		}

		$actions = array();

		foreach ( $temp_actions as $a ) {
			if ( 'all' != $action && $a->id_base == $action ) {
				return $a;
			}

			$actions[ $a->id_base ] = $a;
		}

		return $actions;
	}

	/**
	 * @since 2.0
	 */
	public static function list_actions( $form, $values ) {
		if ( empty( $form ) ) {
			return;
		}

		/**
		 * Use this hook to migrate old settings into a new action
		 *
		 * @since 2.0
		 */
		do_action( 'frm_before_list_actions', $form );

		$filters      = array(
			'post_status' => 'all',
		);
		$form_actions = FrmFormAction::get_action_for_form( $form->id, 'all', $filters );

		$action_controls = self::get_form_actions();

		$action_map = array();

		foreach ( $action_controls as $key => $control ) {
			$action_map[ $control->id_base ] = $key;
		}

		foreach ( $form_actions as $action ) {
			if ( ! isset( $action_map[ $action->post_excerpt ] ) ) {
				// don't try and show settings if action no longer exists
				continue;
			}

			self::action_control( $action, $form, $action->ID, $action_controls[ $action_map[ $action->post_excerpt ] ], $values );
		}
	}

	public static function action_control( $form_action, $form, $action_key, $action_control, $values ) {
		$action_control->_set( $action_key );

		$use_logging = self::should_show_log_message( $form_action->post_excerpt );

		include FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/form_action.php';
	}

	public static function add_form_action() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		global $frm_vars;

		$action_key  = FrmAppHelper::get_param( 'list_id', '', 'post', 'absint' );
		$action_type = FrmAppHelper::get_param( 'type', '', 'post', 'sanitize_text_field' );

		$action_control = self::get_form_actions( $action_type );
		$action_control->_set( $action_key );

		$form_id = FrmAppHelper::get_param( 'form_id', '', 'post', 'absint' );

		$form_action = $action_control->prepare_new( $form_id );
		$use_logging = self::should_show_log_message( $action_type );

		$values = array();
		$form   = self::fields_to_values( $form_id, $values );

		include FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/form_action.php';
		wp_die();
	}

	public static function fill_action() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$action_key  = FrmAppHelper::get_param( 'action_id', '', 'post', 'absint' );
		$action_type = FrmAppHelper::get_param( 'action_type', '', 'post', 'sanitize_text_field' );

		$action_control = self::get_form_actions( $action_type );
		if ( empty( $action_control ) ) {
			wp_die();
		}

		$form_action = $action_control->get_single_action( $action_key );

		$values = array();
		$form   = self::fields_to_values( $form_action->menu_order, $values );

		$use_logging = self::should_show_log_message( $action_type );

		include FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/_action_inside.php';
		wp_die();
	}

	/**
	 * @since 3.06.04
	 * @return bool
	 */
	private static function should_show_log_message( $action_type ) {
		$logging = array( 'api', 'salesforce', 'constantcontact', 'activecampaign' );
		return in_array( $action_type, $logging, true ) && ! function_exists( 'frm_log_autoloader' );
	}

	private static function fields_to_values( $form_id, array &$values ) {
		$form = FrmForm::getOne( $form_id );

		$values = array(
			'fields' => array(),
			'id'     => $form->id,
		);

		$fields = FrmField::get_all_for_form( $form->id );
		foreach ( $fields as $k => $f ) {
			$f    = (array) $f;
			$opts = (array) $f['field_options'];
			$f    = array_merge( $opts, $f );
			if ( ! isset( $f['post_field'] ) ) {
				$f['post_field'] = '';
			}
			$values['fields'][] = $f;
			unset( $k, $f );
		}

		return $form;
	}

	/**
	 * @param int $form_id
	 * @return void
	 */
	public static function update_settings( $form_id ) {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		$process_form = FrmAppHelper::get_post_param( 'process_form', '', 'sanitize_text_field' );
		if ( ! wp_verify_nonce( $process_form, 'process_form_nonce' ) ) {
			$frm_settings = FrmAppHelper::get_settings();
			$error_args   = array(
				'title'      => __( 'Verification failed', 'formidable' ),
				'body'       => $frm_settings->admin_permission,
				'cancel_url' => add_query_arg(
					array(
						'page'       => 'formidable',
						'frm_action' => 'settings',
						'id'         => $form_id,
					),
					admin_url( 'admin.php?' )
				),
			);
			FrmAppController::show_error_modal( $error_args );
			return;
		}

		global $wpdb;

		$registered_actions = self::$registered_actions->actions;

		$old_actions = FrmDb::get_col(
			$wpdb->posts,
			array(
				'post_type'  => self::$action_post_type,
				'menu_order' => $form_id,
			),
			'ID'
		);
		$new_actions = array();

		foreach ( $registered_actions as $registered_action ) {
			$action_ids = $registered_action->update_callback( $form_id );
			if ( ! empty( $action_ids ) ) {
				$new_actions[] = $action_ids;
			}
		}

		// Only use array_merge if there are new actions.
		if ( ! empty( $new_actions ) ) {
			$new_actions = call_user_func_array( 'array_merge', $new_actions );
		}
		$old_actions = array_diff( $old_actions, $new_actions );

		self::delete_missing_actions( $old_actions );

		FrmOnSubmitHelper::save_on_submit_settings( $form_id );
	}

	public static function delete_missing_actions( $old_actions ) {
		if ( ! empty( $old_actions ) ) {
			foreach ( $old_actions as $old_id ) {
				wp_delete_post( $old_id );
			}
			FrmDb::cache_delete_group( 'frm_actions' );
		}
	}

	public static function trigger_create_actions( $entry_id, $form_id, $args = array() ) {
		$filter_args             = $args;
		$filter_args['entry_id'] = $entry_id;
		$filter_args['form_id']  = $form_id;

		$event = apply_filters( 'frm_trigger_create_action', 'create', $args );

		self::trigger_actions( $event, $form_id, $entry_id, 'all', $args );
	}

	/**
	 * @param string $event
	 */
	public static function trigger_actions( $event, $form, $entry, $type = 'all', $args = array() ) {
		$action_status = array(
			'post_status' => 'publish',
		);
		$form_actions  = FrmFormAction::get_action_for_form( ( is_object( $form ) ? $form->id : $form ), $type, $action_status );

		if ( empty( $form_actions ) ) {
			return;
		}

		FrmForm::maybe_get_form( $form );

		$link_settings = self::get_form_actions( $type );
		if ( 'all' != $type ) {
			$link_settings = array( $type => $link_settings );
		}

		$stored_actions  = array();
		$action_priority = array();

		if ( in_array( $event, array( 'create', 'update' ), true ) && defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
			$this_event = 'import';
		} else {
			$this_event = $event;
		}

		foreach ( $form_actions as $action ) {

			$skip_this_action = ! in_array( $this_event, $action->post_content['event'], true ) || FrmOnSubmitAction::$slug === $action->post_excerpt;
			$skip_this_action = apply_filters( 'frm_skip_form_action', $skip_this_action, compact( 'action', 'entry', 'form', 'event' ) );
			if ( $skip_this_action ) {
				continue;
			}

			if ( ! is_object( $entry ) ) {
				$entry = FrmEntry::getOne( $entry, true );
			}

			if ( empty( $entry ) || ( FrmEntriesHelper::DRAFT_ENTRY_STATUS === (int) $entry->is_draft && 'draft' !== $event ) ) {
				continue;
			}

			$child_entry = ( ( is_object( $form ) && is_numeric( $form->parent_form_id ) && $form->parent_form_id ) || ( $entry && ( $entry->form_id != $form->id || $entry->parent_item_id ) ) || ( isset( $args['is_child'] ) && $args['is_child'] ) );

			if ( $child_entry ) {
				// maybe trigger actions for sub forms
				$trigger_children = apply_filters( 'frm_use_embedded_form_actions', false, compact( 'form', 'entry' ) );
				if ( ! $trigger_children ) {
					continue;
				}
			}

			// Check conditional logic.
			$stop = FrmFormAction::action_conditions_met( $action, $entry );
			if ( $stop ) {
				continue;
			}

			// Store actions so they can be triggered with the correct priority.
			$stored_actions[ $action->ID ]  = $action;
			$action_priority[ $action->ID ] = $link_settings[ $action->post_excerpt ]->action_options['priority'];

			unset( $action );
		}//end foreach

		if ( ! empty( $stored_actions ) ) {
			asort( $action_priority );

			// Make sure hooks are loaded.
			new FrmNotification();

			foreach ( $action_priority as $action_id => $priority ) {
				$action = $stored_actions[ $action_id ];
				do_action( 'frm_trigger_' . $action->post_excerpt . '_action', $action, $entry, $form, $event );
				do_action( 'frm_trigger_' . $action->post_excerpt . '_' . $event . '_action', $action, $entry, $form );

				// If post is created, get updated $entry object.
				if ( $action->post_excerpt === 'wppost' && $event === 'create' ) {
					$entry = FrmEntry::getOne( $entry->id, true );
				}
			}
		}
	}

	public static function duplicate_form_actions( $form_id, $values, $args = array() ) {
		if ( empty( $args['old_id'] ) ) {
			// Continue if we know which actions to copy.
			return;
		}

		$action_controls = self::get_form_actions();

		foreach ( $action_controls as $action_control ) {
			$action_control->duplicate_form_actions( $form_id, $args['old_id'] );
			unset( $action_control );
		}
	}

	public static function limit_by_type( $where ) {
		global $frm_vars, $wpdb;

		if ( ! isset( $frm_vars['action_type'] ) ) {
			return $where;
		}

		$where .= $wpdb->prepare( ' AND post_excerpt = %s ', $frm_vars['action_type'] );

		return $where;
	}
}

class Frm_Form_Action_Factory {
	public $actions = array();

	public function __construct() {
		add_action( 'frm_form_actions_init', array( $this, '_register_actions' ), 100 );
	}

	public function register( $action_class ) {
		$this->actions[ $action_class ] = new $action_class();
	}

	public function unregister( $action_class ) {
		if ( isset( $this->actions[ $action_class ] ) ) {
			unset( $this->actions[ $action_class ] );
		}
	}

	public function _register_actions() {
		$keys = array_keys( $this->actions );

		foreach ( $keys as $key ) {
			// don't register new action if old action with the same id is already registered
			if ( ! isset( $this->actions[ $key ] ) ) {
				$this->actions[ $key ]->_register();
			}
		}
	}
}
