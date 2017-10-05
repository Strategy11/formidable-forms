<?php
_deprecated_file( basename( __FILE__ ), '3.0', null, 'FrmFieldType::field_input' );

$field_obj = FrmFieldFactory::get_field_type( $field['type'], $field );
return $field_obj->front_field_input( compact( 'errors', 'form' ), $atts );
