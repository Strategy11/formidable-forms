<?php

_deprecated_file( esc_html( basename( __FILE__ ) ), '3.0', null, 'FrmFieldType::show_on_form_builder' );

$field_obj = FrmFieldFactory::get_field_object( $field['id'] );
$field_obj->show_on_form_builder();
