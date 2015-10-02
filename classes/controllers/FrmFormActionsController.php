<?php

class FrmFormActionsController {
    public static $action_post_type = 'frm_form_actions';
    public static $registered_actions;

    public static function register_post_types() {
        register_post_type( self::$action_post_type, array(
            'label' => __( 'Form Actions', 'formidable' ),
            'description' => '',
            'public' => false,
            'show_ui' => false,
            'exclude_from_search' => true,
            'show_in_nav_menus' => false,
            'show_in_menu' => true,
            'capability_type' => 'page',
            'supports' => array(
				'title', 'editor', 'excerpt', 'custom-fields',
				'page-attributes',
            ),
            'has_archive' => false,
        ) );

        /**
         * post_content: json settings
         * menu_order: form id
         * post_excerpt: action type
         */

        self::actions_init();
    }

    public static function actions_init() {
        self::$registered_actions = new Frm_Form_Action_Factory();
        self::register_actions();
        do_action( 'frm_form_actions_init' );
    }

    public static function register_actions() {
        $action_classes = apply_filters( 'frm_registered_form_actions', array(
            'email'     => 'FrmEmailAction',
            'wppost'    => 'FrmDefPostAction',
            'register'  => 'FrmDefRegAction',
            'paypal'    => 'FrmDefPayPalAction',
            //'aweber'    => 'FrmDefAweberAction',
            'mailchimp' => 'FrmDefMlcmpAction',
            'twilio'    => 'FrmDefTwilioAction',
            'highrise'  => 'FrmDefHrsAction',
        ) );

        include_once(FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/email_action.php');
        include_once(FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/default_actions.php');

        foreach ( $action_classes as $action_class ) {
            self::$registered_actions->register($action_class);
        }
    }

	public static function get_form_actions( $action = 'all' ) {
        $temp_actions = self::$registered_actions;
        if ( empty($temp_actions) ) {
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
        unset( $temp_actions, $a );

        $action_limit = 10;
        if ( count( $actions ) <= $action_limit ) {
            return $actions;
        }

        // remove the last few inactive icons if there are too many
        $temp_actions = $actions;
        arsort( $temp_actions );
        foreach ( $temp_actions as $type => $a ) {
            if ( ! isset( $a->action_options['active'] ) || empty( $a->action_options['active'] ) ) {
				unset( $actions[ $type ] );
                if ( count( $actions ) <= $action_limit ) {
                    break;
                }
            }
            unset( $type, $a );
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
		 * use this hook to migrate old settings into a new action
		 * @since 2.0
		 */
		do_action( 'frm_before_list_actions', $form );

		$form_actions = FrmFormAction::get_action_for_form( $form->id );

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
        $action_control->_set($action_key);
        include(FrmAppHelper::plugin_path() .'/classes/views/frm-form-actions/form_action.php');
    }

    public static function add_form_action() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

        global $frm_vars;

		$action_key = absint( $_POST['list_id'] );
        $action_type = sanitize_text_field( $_POST['type'] );

        $action_control = self::get_form_actions( $action_type );
        $action_control->_set($action_key);

        $form_id = absint( $_POST['form_id'] );

        $form_action = $action_control->prepare_new($form_id);

        $values = array();
        $form = self::fields_to_values($form_id, $values);

        include(FrmAppHelper::plugin_path() .'/classes/views/frm-form-actions/form_action.php');
        wp_die();
    }

    public static function fill_action() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

        $action_key = absint( $_POST['action_id'] );
        $action_type = sanitize_text_field( $_POST['action_type'] );

        $action_control = self::get_form_actions( $action_type );
        if ( empty($action_control) ) {
            wp_die();
        }

        $form_action = $action_control->get_single_action( $action_key );

        $values = array();
        $form = self::fields_to_values($form_action->menu_order, $values);

        include(FrmAppHelper::plugin_path() .'/classes/views/frm-form-actions/_action_inside.php');
        wp_die();
    }

	private static function fields_to_values( $form_id, array &$values ) {
        $form = FrmForm::getOne($form_id);

		$values = array( 'fields' => array(), 'id' => $form->id );

        $fields = FrmField::get_all_for_form($form->id);
        foreach ( $fields as $k => $f ) {
            $f = (array) $f;
            $opts = (array) $f['field_options'];
            $f = array_merge($opts, $f);
            if ( ! isset( $f['post_field'] ) ) {
                $f['post_field'] = '';
            }
            $values['fields'][] = $f;
            unset($k, $f);
        }

        return $form;
    }

	public static function update_settings( $form_id ) {
        global $wpdb;

        $registered_actions = self::$registered_actions->actions;

		$old_actions = FrmDb::get_col( $wpdb->posts, array( 'post_type' => self::$action_post_type, 'menu_order' => $form_id ), 'ID' );
        $new_actions = array();

        foreach ( $registered_actions as $registered_action ) {
            $action_ids = $registered_action->update_callback($form_id);
            if ( ! empty( $action_ids ) ) {
                $new_actions[] = $action_ids;
            }
        }

        //Only use array_merge if there are new actions
        if ( ! empty( $new_actions ) ) {
            $new_actions = call_user_func_array( 'array_merge', $new_actions );
        }
        $old_actions = array_diff( $old_actions, $new_actions );

		self::delete_missing_actions( $old_actions );
    }

	public static function delete_missing_actions( $old_actions ) {
		if ( ! empty( $old_actions ) ) {
			foreach ( $old_actions as $old_id ) {
				wp_delete_post( $old_id );
			}
			FrmAppHelper::cache_delete_group( 'frm_actions' );
		}
	}

	public static function trigger_create_actions( $entry_id, $form_id, $args = array() ) {
		self::trigger_actions( 'create', $form_id, $entry_id, 'all', $args );
	}

    /**
     * @param string $event
     */
	public static function trigger_actions( $event, $form, $entry, $type = 'all', $args = array() ) {
		$form_actions = FrmFormAction::get_action_for_form( ( is_object( $form ) ? $form->id : $form ), $type );

		if ( empty( $form_actions ) ) {
            return;
        }

		FrmForm::maybe_get_form( $form );

        $link_settings = self::get_form_actions( $type );
        if ( 'all' != $type ) {
            $link_settings = array( $type => $link_settings );
        }

        $stored_actions = $action_priority = array();

		$importing = in_array( $event, array( 'create', 'update' ) ) && defined( 'WP_IMPORTING' ) && WP_IMPORTING;

        foreach ( $form_actions as $action ) {
			$trigger_on_import = $importing && in_array( 'import', $action->post_content['event'] );
			if ( ! in_array( $event, $action->post_content['event'] ) && ! $trigger_on_import ) {
                continue;
            }

            if ( ! is_object( $entry ) ) {
                $entry = FrmEntry::getOne( $entry, true );
            }

			if ( empty( $entry ) || $entry->is_draft ) {
				continue;
			}

			$child_entry = ( ( $form && is_numeric( $form->parent_form_id ) && $form->parent_form_id ) || ( $entry && ( $entry->form_id != $form->id || $entry->parent_item_id ) ) || ( isset( $args['is_child'] ) && $args['is_child'] ) );

			if ( $child_entry ) {
                //don't trigger actions for sub forms
                continue;
            }

            // check conditional logic
			$stop = FrmFormAction::action_conditions_met( $action, $entry );
            if ( $stop ) {
                continue;
            }

            // store actions so they can be triggered with the correct priority
            $stored_actions[ $action->ID ] = $action;
            $action_priority[ $action->ID ] = $link_settings[ $action->post_excerpt ]->action_options['priority'];

            unset($action);
        }

        if ( ! empty( $stored_actions ) ) {
            asort($action_priority);

            // make sure hooks are loaded
            new FrmNotification();

            foreach ( $action_priority as $action_id => $priority ) {
                $action = $stored_actions[ $action_id ];
                do_action('frm_trigger_'. $action->post_excerpt .'_action', $action, $entry, $form, $event);
                do_action('frm_trigger_'. $action->post_excerpt .'_'. $event .'_action', $action, $entry, $form);

                // If post is created, get updated $entry object
                if ( $action->post_excerpt == 'wppost' && $event == 'create' ) {
                    $entry = FrmEntry::getOne($entry->id, true);
                }
            }
        }
    }

	public static function duplicate_form_actions( $form_id, $values, $args = array() ) {
        if ( ! isset($args['old_id']) || empty($args['old_id']) ) {
            // continue if we know which actions to copy
            return;
        }

        $action_controls = self::get_form_actions( );

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
			unset($this->actions[ $action_class ]);
		}
	}

	public function _register_actions() {
		$keys = array_keys($this->actions);

		foreach ( $keys as $key ) {
			// don't register new action if old action with the same id is already registered
			if ( ! isset( $this->actions[ $key ] ) ) {
			    $this->actions[ $key ]->_register();
			}
		}
	}
}
