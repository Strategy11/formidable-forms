<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Generate the XML for export for forms and fields.
 *
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
 */

if ( ! $item_ids ) {
	return;
}

// fetch 20 posts at a time rather than loading the entire table into memory
while ( $next_set = array_splice( $item_ids, 0, 20 ) ) {
	$forms = FrmDb::get_results( $wpdb->prefix . 'frm_forms', array( 'id' => $next_set ) );

	// Begin Loop
	foreach ( $forms as $form ) {
		?>
	<form>
		<id><?php echo absint( $form->id ); ?></id>
		<form_key><?php echo FrmXMLHelper::cdata( $form->form_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></form_key>
		<name><?php echo FrmXMLHelper::cdata( $form->name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></name>
		<description><?php echo FrmXMLHelper::cdata( $form->description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></description>
		<created_at><?php echo esc_html( $form->created_at ); ?></created_at>
		<logged_in><?php echo esc_html( $form->logged_in ); ?></logged_in>
		<is_template><?php echo esc_html( $form->is_template ); ?></is_template>
		<editable><?php echo esc_html( $form->editable ); ?></editable>
		<options><?php echo FrmXMLHelper::prepare_form_options_for_export( $form->options ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></options>
		<status><?php echo FrmXMLHelper::cdata( $form->status ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></status>
		<parent_form_id><?php echo esc_html( $form->parent_form_id ); ?></parent_form_id>
<?php

		$fields = FrmDb::get_results( $wpdb->prefix . 'frm_fields', array( 'form_id' => $form->id ), '*', array( 'order_by' => 'field_order' ) );

		foreach ( $fields as $field ) {
			FrmXMLHelper::prepare_field_for_export( $field );
			?>
		<field>
			<id><?php echo absint( $field->id ); ?></id>
			<field_key><?php echo FrmXMLHelper::cdata( $field->field_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></field_key>
			<name><?php echo FrmXMLHelper::cdata( $field->name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></name>
			<description><?php echo FrmXMLHelper::cdata( $field->description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></description>
			<type><?php echo FrmXMLHelper::cdata( $field->type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></type>
			<default_value><?php echo FrmXMLHelper::cdata( $field->default_value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></default_value>
			<field_order><?php echo absint( $field->field_order ); ?></field_order>
			<form_id><?php echo absint( $field->form_id ); ?></form_id>
			<required><?php echo absint( $field->required ); ?></required>
			<options><?php echo FrmXMLHelper::cdata( $field->options ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></options>
			<field_options><?php echo FrmXMLHelper::cdata( $field->field_options ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></field_options>
		</field>
<?php	} ?>
	</form>
<?php
		unset( $fields );
	}
}
