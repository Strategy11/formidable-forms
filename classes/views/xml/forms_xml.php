<?php

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
		<id><?php echo $form->id ?></id>
		<form_key><?php echo FrmXMLHelper::cdata($form->form_key) ?></form_key>
		<name><?php echo FrmXMLHelper::cdata($form->name) ?></name>
		<description><?php echo FrmXMLHelper::cdata($form->description) ?></description>
		<created_at><?php echo $form->created_at ?></created_at>
		<logged_in><?php echo $form->logged_in ?></logged_in>
		<is_template><?php echo $form->is_template ?></is_template>
		<default_template><?php echo $form->default_template ?></default_template>
		<editable><?php echo $form->editable ?></editable>
		<options><?php echo FrmXMLHelper::prepare_form_options_for_export($form->options) ?></options>
		<status><?php echo FrmXMLHelper::cdata($form->status) ?></status>
        <parent_form_id><?php echo $form->parent_form_id ?></parent_form_id>
<?php

		$fields = FrmDb::get_results( $wpdb->prefix . 'frm_fields', array( 'form_id' => $form->id ), '*', array( 'order_by' => 'field_order' ) );

		foreach ( $fields as $field ) { ?>
		<field>
		    <id><?php echo $field->id ?></id>
            <field_key><?php echo FrmXMLHelper::cdata($field->field_key) ?></field_key>
            <name><?php echo FrmXMLHelper::cdata($field->name) ?></name>
            <description><?php echo FrmXMLHelper::cdata($field->description) ?></description>
            <type><?php echo FrmXMLHelper::cdata($field->type) ?></type>
            <default_value><?php echo FrmXMLHelper::cdata($field->default_value) ?></default_value>
            <field_order><?php echo $field->field_order ?></field_order>
            <form_id><?php echo $field->form_id ?></form_id>
            <required><?php echo (bool) $field->required ?></required>
            <options><?php echo FrmXMLHelper::cdata($field->options) ?></options>
            <field_options><?php echo FrmXMLHelper::cdata($field->field_options) ?></field_options>
		</field>
<?php	} ?>
	</form>
<?php
    	unset( $fields );
	}
}
