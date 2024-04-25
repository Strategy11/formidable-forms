<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

abstract class FrmFormMigrator {

	public $source_active;

	public $slug;
	public $path;
	public $name;

	public $response = array();
	public $tracking = 'frm_forms_imported';

	protected $fields_map          = array();
	protected $current_source_form;
	protected $current_section     = array();

	/**
	 * Define required properties.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$this->source_active = is_plugin_active( $this->path );
		if ( ! $this->source_active ) {
			// if source plugin is not installed, do nothing
			return;
		}

		$this->maybe_add_to_import_page();

		$this->response = array(
			'upgrade_omit' => array(),
			'unsupported'  => array(),
		);
	}

	private function maybe_add_to_import_page() {
		add_action( 'frm_import_settings', array( $this, 'import_page' ) );
		add_action( 'wp_ajax_frm_import_' . $this->slug, array( $this, 'import_forms' ) );
	}

	public function import_page() {
		?>
		<div class="wrap">
			<h2 class="frm-h2"><?php echo esc_html( $this->name ); ?> Importer</h2>
			<p class="howto">Import forms and settings automatically from <?php echo esc_html( $this->name ); ?>.</p>
			<div id="welcome-panel">
				<div style="margin-bottom:10px;">
					<p class="about-description">
						Select the forms to import.
					</p>
					<form class="frm_form_importer" method="post"
						action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
						<?php wp_nonce_field( 'nonce', 'frm_ajax' ); ?>
						<input type="hidden" name="slug" value="<?php echo esc_attr( $this->slug ); ?>" />
						<input type="hidden" name="action" value="frm_import_<?php echo esc_attr( $this->slug ); ?>" />
						<div style="max-width:400px;text-align:left;">
							<?php
							if ( empty( $this->get_forms() ) ) {
								esc_html_e( 'No Forms Found.', 'formidable' );
							}
							?>
							<?php foreach ( $this->get_forms() as $form_id => $name ) { ?>
								<p>
									<label>
										<input type="checkbox" name="form_id[]"
											value="<?php echo esc_attr( $form_id ); ?>" checked="checked" />
										<?php
										echo esc_html( $name );
										$new_form_id = $this->is_imported( $form_id );
										?>
										<?php if ( $new_form_id ) { ?>
											(<a href="<?php echo esc_url( FrmForm::get_edit_link( $new_form_id ) ); ?>">previously imported</a>)
										<?php } ?>
									</label>
								</p>
							<?php } ?>
						</div>
						<p class="submit"><button type="submit" class="button button-primary frm-button-primary">Start Import</button></p>
					</form>
					<div id="frm-importer-process" class="frm-importer-process frm_hidden">

						<p class="process-count">
							<span class="frm-wait" aria-hidden="true"></span>
							Importing <span class="form-current">1</span> of <span class="form-total">0</span> forms
							from <?php echo esc_html( $this->name ); ?>.
						</p>

						<p class="process-completed" class="frm_hidden">
							The import process has finished! We have successfully imported
							<span class="forms-completed"></span> forms. You can review the results below.
						</p>

						<div class="status"></div>

					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Import all forms using ajax
	 */
	public function import_forms() {

		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		$forms = FrmAppHelper::get_simple_request(
			array(
				'param'    => 'form_id',
				'type'     => 'post',
				'sanitize' => 'absint',
			)
		);

		if ( is_array( $forms ) ) {
			$imported = array();
			foreach ( $forms as $form_id ) {
				$imported[] = $this->import_form( $form_id );
			}
		} else {
			$imported = $this->import_form( $forms );
		}

		wp_send_json_success( $imported );
	}

	/**
	 * Import a single form
	 */
	protected function import_form( $source_id ) {

		$source_form      = $this->get_form( $source_id );
		$source_form_name = $this->get_form_name( $source_form );
		$source_fields    = $this->get_form_fields( $source_form );
		$this->maybe_add_end_fields( $source_fields );

		$this->current_source_form = $source_form;

		// If form does not contain fields, bail.
		if ( empty( $source_fields ) ) {
			wp_send_json_success(
				array(
					'error' => true,
					'name'  => esc_html( $source_form_name ),
					'msg'   => __( 'No form fields found.', 'formidable' ),
				)
			);
		}

		$form = $this->prepare_new_form( $source_id, $source_form_name );

		$this->prepare_fields( $source_fields, $form );

		$this->prepare_form( $source_form, $form );

		$response = $this->add_form( $form );

		// reset
		$this->current_source_form = null;

		return $response;
	}

	protected function prepare_new_form( $source_id, $source_form_name ) {
		return array(
			'import_form_id' => $source_id,
			'fields'         => array(),
			'name'           => $source_form_name,
			'description'    => '',
			'options'        => array(),
			'actions'        => array(),
		);
	}

	protected function prepare_form( $form, &$new_form ) {
		// customize this function
	}

	protected function prepare_fields( $fields, &$form ) {
		$field_order = 1;

		foreach ( $fields as $field ) {
			$field = (array) $field;

			$label = $this->get_field_label( $field );
			$type  = $this->get_field_type( $field );

			// check if field is unsupported. If unsupported make note and continue
			if ( $this->is_unsupported_field( $type ) ) {
				$this->response['unsupported'][] = $label;
				continue;
			}

			if ( $this->should_skip_field( $type ) ) {
				$this->response['upgrade_omit'][] = $label;
				continue;
			}

			$new_type = $this->convert_field_type( $type, $field );

			$new_field                = FrmFieldsHelper::setup_new_vars( $new_type );
			$new_field['name']        = $label;
			$new_field['field_order'] = $field_order;
			$new_field['original']    = $type;

			$this->prepare_field( $field, $new_field );

			$in_section = ! empty( $this->current_section ) && ! in_array( $new_type, $this->fields_with_end() ) && $new_type !== 'break';
			if ( $in_section ) {
				$new_field['field_options']['in_section'] = $this->current_section['id'];
			}

			$form['fields'][] = $new_field;

			if ( in_array( $new_type, $this->fields_with_end() ) ) {
				$this->current_section = $field;
			} elseif ( $new_type === 'break' || $new_type === 'end_divider' ) {
				$this->current_section = array();
			}

			// This may occassionally skip one level/order e.g. after adding a
			// list field, as field_order would already be prepared to be used.
			++$field_order;

			if ( ! empty( $new_field['fields'] ) && is_array( $new_field['fields'] ) ) {
				// we have (inner) fields to merge

				$form['fields'] = array_merge( $form['fields'], $new_field['fields'] );
				// set the new field_order as it would have changed
				$field_order = $new_field['current_order'];
			}
		}//end foreach
	}

	protected function prepare_field( $field, &$new_field ) {
		// customize this function
	}

	/**
	 * Add any field types that will need an end section field.
	 *
	 * @since 4.04.03
	 */
	protected function fields_with_end() {
		return array( 'divider' );
	}

	/**
	 * @since 4.04.03
	 */
	protected function maybe_add_end_fields( &$fields ) {
		$with_end = $this->fields_with_end();
		if ( empty( $with_end ) ) {
			return;
		}

		$open = array();

		$order = 0;
		foreach ( $fields as $field ) {
			++$order;
			$type     = $this->get_field_type( $field );
			$new_type = $this->convert_field_type( $type, $field );
			if ( ! in_array( $new_type, $with_end ) && $new_type !== 'break' ) {
				continue;
			}

			if ( ! empty( $open ) ) {
				$this->insert_end_section( $fields, $order );
				$open = array();
			}

			if ( in_array( $new_type, $with_end ) ) {
				$open = $field;
			}
		}

		if ( ! empty( $open ) ) {
			$this->insert_end_section( $fields, $order );
		}
	}

	/**
	 * @since 4.04.03
	 */
	protected function insert_end_section( &$fields, &$order ) {
		$sub         = FrmFieldsHelper::setup_new_vars( 'end_divider' );
		$sub['name'] = __( 'Section Buttons', 'formidable' );
		$subs        = array( $sub );
		$this->insert_fields_in_array( $subs, $order, 0, $fields );
		++$order;
	}

	/**
	 * Replace the original combo field with a group.
	 * This switches the name field to individual fields.
	 *
	 * @since 4.04.03
	 */
	protected function insert_fields_in_array( $subs, $start, $remove, &$fields ) {
		array_splice( $fields, $start, $remove, $subs );
	}

	/**
	 * @param string $type
	 * @param array  $field
	 * @param string $use   Which field type to prefer to consider $field as.
	 *                      This also eases the recursive use of the method,
	 *                      particularly the overrides in child classes, as
	 *                      there will be no need to rebuild the converter
	 *                      array at usage locations.
	 */
	protected function convert_field_type( $type, $field = array(), $use = '' ) {
		if ( empty( $field ) ) {
			// For reverse compatability.
			return $type;
		}

		return $use ? $use : $field['type'];
	}

	/**
	 * Add the new form to the database and return AJAX data.å
	 *
	 * @param array $form Form to import.
	 * @param array $upgrade_omit No field alternative.
	 */
	protected function add_form( $form, $upgrade_omit = array() ) {

		// Create empty form so we have an ID to work with.
		$form_id = $this->create_form( $form );

		if ( empty( $form_id ) ) {
			return $this->form_creation_error_response( $form );
		}

		$this->create_fields( $form_id, $form );

		$this->create_emails( $form, $form_id );

		$this->track_import( $form['import_form_id'], $form_id );

		// Build and send final AJAX response!
		return array(
			'name'         => $form['name'],
			'id'           => $form_id,
			'link'         => esc_url_raw( FrmForm::get_edit_link( $form_id ) ),
			'upgrade_omit' => $this->response['upgrade_omit'],
		);
	}

	/**
	 * @since 4.04.03
	 *
	 * @param array $form parameters for the new form to be created. Only
	 *              the name key is a must. The keys are the column
	 *              names of the forms table in the DB.
	 *
	 * @return bool|int The ID of the newly created form or false on failure.
	 */
	protected function create_form( $form ) {
		$form['form_key'] = $form['name'];
		$form['status']   = 'published';

		return FrmForm::create( $form );
	}

	/**
	 * @since 4.04.03
	 */
	protected function form_creation_error_response( $form ) {
		return array(
			'error' => true,
			'name'  => sanitize_text_field( $form['name'] ),
			'msg'   => esc_html__( 'There was an error while creating a new form.', 'formidable' ),
		);
	}

	/**
	 * @since 4.04.03
	 *
	 * @param int   $form_id
	 * @param array $form
	 */
	protected function create_fields( $form_id, &$form ) {
		foreach ( $form['fields'] as $key => $new_field ) {
			$new_field['form_id']         = $form_id;
			$form['fields'][ $key ]['id'] = FrmField::create( $new_field );
		}
	}

	/**
	 * @since 4.04.03
	 *
	 * @param array $form
	 */
	protected function create_emails( $form, $form_id ) {
		foreach ( $form['actions'] as $action ) {
			$this->save_action( $action, $form, $form_id );
		}
	}

	/**
	 * @since 4.04.03
	 *
	 * @param array $action
	 * @param array $form
	 * @param int   $form_id
	 */
	protected function save_action( $action, $form, $form_id ) {
		$action_control = FrmFormActionsController::get_form_actions( $action['type'] );
		unset( $action['type'] );
		$new_action = $action_control->prepare_new( $form_id );
		foreach ( $action as $key => $value ) {
			if ( $key === 'post_title' ) {
				$new_action->post_title = $value;
			} elseif ( $key === 'ID' ) {
				$new_action->ID = $value;
			} elseif ( $key === 'the_post_title' ) {
				$new_action->post_content['post_title'] = $value;
			} elseif ( is_string( $value ) ) {
				$new_action->post_content[ $key ] = $this->replace_smart_tags( $value, $form['fields'] );
			} else {
				$new_action->post_content[ $key ] = $value;
			}
		}

		return $action_control->save_settings( $new_action );
	}

	/**
	 * After a form has been successfully imported we track it, so that in the
	 * future we can alert users if they try to import a form that has already
	 * been imported.
	 *
	 * @param int $source_id Imported plugin form ID.
	 * @param int $new_form_id Formidable form ID.
	 */
	protected function track_import( $source_id, $new_form_id ) {

		$imported = $this->get_tracked_import();

		$imported[ $this->slug ][ $new_form_id ] = $source_id;

		update_option( $this->tracking, $imported, false );
	}

	/**
	 * @return array
	 */
	private function get_tracked_import() {
		return get_option( $this->tracking, array() );
	}

	/**
	 * @param int $source_id Imported plugin form ID.
	 *
	 * @return int the ID of the created form or 0
	 */
	private function is_imported( $source_id ) {
		$imported    = $this->get_tracked_import();
		$new_form_id = 0;
		if ( ! isset( $imported[ $this->slug ] ) || ! in_array( $source_id, $imported[ $this->slug ] ) ) {
			return $new_form_id;
		}

		$new_form_id = array_search( $source_id, array_reverse( $imported[ $this->slug ], true ) );
		if ( ! empty( $new_form_id ) && empty( FrmForm::get_key_by_id( $new_form_id ) ) ) {
			// Allow reimport if the form was deleted.
			$new_form_id = 0;
		}

		return $new_form_id;
	}

	/** Start functions here that should be overridden **/

	/**
	 * @return array
	 */
	protected function unsupported_field_types() {
		return array();
	}

	private function is_unsupported_field( $type ) {
		$fields = $this->unsupported_field_types();

		return in_array( $type, $fields, true );
	}

	/**
	 * Strict PRO fields with no Lite alternatives.
	 *
	 * @return array
	 */
	protected function skip_pro_fields() {
		return array();
	}

	protected function should_skip_field( $type ) {
		$skip_pro_fields = $this->skip_pro_fields();

		return ( ! FrmAppHelper::pro_is_installed() && in_array( $type, $skip_pro_fields, true ) );
	}

	/**
	 * Replace 3rd-party form provider tags/shortcodes with our own Tags.
	 *
	 * @param string $string String to process the smart tag in.
	 * @param array  $fields List of fields for the form.
	 *
	 * @return string
	 */
	protected function replace_smart_tags( $string, $fields ) {
		return $string;
	}

	/**
	 * Get ALL THE FORMS.
	 *
	 * @return array
	 */
	public function get_forms() {
		return array();
	}

	public function get_form( $id ) {
		return array();
	}

	/**
	 * @param array|object $source_form
	 *
	 * @return string
	 */
	protected function get_form_name( $source_form ) {
		return __( 'Default Form', 'formidable' );
	}

	/**
	 * @param array|object $source_form
	 *
	 * @return array
	 */
	protected function get_form_fields( $source_form ) {
		return array();
	}

	/**
	 * @param array $field
	 *
	 * @return string
	 */
	protected function get_field_type( $field ) {
		return $field['type'];
	}

	/**
	 * @param array $field
	 *
	 * @return string
	 */
	protected function get_field_label( $field ) {
		$label = isset( $field['label'] ) ? $field['label'] : '';
		if ( ! empty( $label ) ) {
			return $label;
		}

		$type  = $this->get_field_type( $field );
		$label = sprintf(
			/* translators: %1$s - field type */
			esc_html__( '%1$s Field', 'formidable' ),
			ucfirst( $type )
		);

		return trim( $label );
	}
}
