<?php
/**
 * Resumable native form, field, View and draft page creation for add-ons.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmSolutionSetupHelper {
	/**
	 * Create or repair a starter form without overwriting existing fields.
	 *
	 * @since x.x
	 *
	 * @param string $key Stable form key.
	 * @param string $name Form label.
	 * @param array  $spec Fields keyed by semantic role, with type and label.
	 * @param array  $options Form options.
	 *
	 * @return array|WP_Error
	 */
	public static function form( $key, $name, $spec, $options = array() ) {
		$form = FrmForm::getOne( $key );
		$id   = $form ? (int) $form->id : (int) FrmForm::create(
			array(
				'form_key'    => $key,
				'name'        => $name,
				'description' => '',
				'options'     => $options,
			)
		);

		if ( ! $id ) {
			return new WP_Error( 'frm_setup_form', __( 'The starter form could not be created.', 'formidable' ) );
		}

		$fields          = array();
		$existing_fields = FrmField::get_all_for_form( $id );
		$order           = 0;

		foreach ( $existing_fields as $existing_field ) {
			$order = max( $order, (int) $existing_field->field_order );
		}

		foreach ( $spec as $role => $field ) {
			$field_key = $key . '-' . $role;
			$existing  = FrmField::getOne( $field_key );

			if ( $existing && (int) $existing->form_id === $id ) {
				$fields[ $role ] = (int) $existing->id;
				continue;
			}

			$values                = FrmFieldsHelper::setup_new_vars( $field[0], $id );
			$values['name']        = $field[1];
			$values['field_key']   = $field_key;
			$values['required']    = $field[2] ?? 0;
			$values['field_order'] = ++$order;

			if ( isset( $field[3]['default_value'] ) ) {
				$values['default_value'] = $field[3]['default_value'];
			}

			$values['field_options'] = array_merge( (array) ( $values['field_options'] ?? array() ), $field[3] ?? array() );

			if ( isset( $field[4] ) ) {
				$values['options'] = $field[4];
			}

			$fields[ $role ] = (int) FrmField::create( $values );

			if ( ! $fields[ $role ] ) {
				return new WP_Error( 'frm_setup_field', __( 'A field could not be created. Run setup again to repair it.', 'formidable' ) );
			}
		}//end foreach

		foreach ( $existing_fields as $existing_field ) {
			if ( 'submit' === $existing_field->type && (int) $existing_field->field_order <= $order ) {
				FrmField::update( $existing_field->id, array( 'field_order' => ++$order ) );
			}
		}

		return array(
			'form_id' => $id,
			'fields'  => $fields,
		);
	}
	/**
	 * Create a managed View and a draft page, preserving surviving resources.
	 *
	 * @since x.x
	 *
	 * @param array  $resource Existing View/page IDs.
	 * @param string $type Registered View type.
	 * @param int    $form_id Source form.
	 * @param string $title Resource title.
	 * @param array  $settings Initial View settings.
	 *
	 * @return array|WP_Error
	 */
	public static function view_page( $resource, $type, $form_id, $title, $settings ) {
		if ( ! class_exists( 'FrmViewsDisplay' ) || ! class_exists( 'FrmViewsTypeSettingsHelper' ) ) {
			return new WP_Error( 'frm_setup_views', __( 'Install a compatible version of Formidable Views first.', 'formidable' ) );
		}

		$view_id          = absint( $resource['view'] ?? 0 );
		$previous_view_id = $view_id;
		$view             = $view_id ? get_post( $view_id ) : null;

		if ( ! $view || 'frm_display' !== $view->post_type || 'trash' === $view->post_status ) {
			$view_id = FrmViewsDisplay::create( $form_id, $type, $title );

			if ( ! $view_id ) {
				return new WP_Error( 'frm_setup_view', __( 'A View could not be created.', 'formidable' ) );
			}

			$options                  = get_post_meta( $view_id, 'frm_options', true );
			$options                  = is_array( $options ) ? $options : array();
			$options['type_settings'] = FrmViewsTypeSettingsHelper::sanitize( get_post( $view_id ), $settings );
			$options['page_size']     = 12;
			update_post_meta( $view_id, 'frm_options', $options );
		}

		$page_id = absint( $resource['page'] ?? 0 );
		$page    = $page_id ? get_post( $page_id ) : null;

		if ( ! $page || 'page' !== $page->post_type || 'trash' === $page->post_status ) {
			$page_id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'draft',
					'post_title'   => $title,
					'post_content' => is_callable( 'FrmViewsSetupHelper::view_block' ) ? FrmViewsSetupHelper::view_block( $view_id ) : '[display-frm-data id="' . $view_id . '"]',
				),
				true
			);

			if ( is_wp_error( $page_id ) ) {
				return $page_id;
			}
		}

		if ( $page && $previous_view_id && $previous_view_id !== $view_id ) {
			$old_shortcode = '[display-frm-data id="' . $previous_view_id . '"]';
			$new_shortcode = '[display-frm-data id="' . $view_id . '"]';

			if ( str_contains( $page->post_content, $old_shortcode ) || has_block( 'formidable/simple-view', $page ) ) {
				$content = is_callable( 'FrmViewsSetupHelper::replace_view_embed' )
				? FrmViewsSetupHelper::replace_view_embed( $page->post_content, $previous_view_id, $view_id )
				: str_replace( $old_shortcode, $new_shortcode, $page->post_content );
				wp_update_post(
					array(
						'ID'           => $page_id,
						'post_content' => $content,
					)
				);
			}
		}

		return array(
			'view' => $view_id,
			'page' => $page_id,
		);
	}
}
