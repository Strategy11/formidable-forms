<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Keep alternate layouts inert until selected, using the same renderer as the visible preview.
foreach ( array( 'international', 'us', 'europe', 'generic' ) as $address_type ) {
	$preview_field                                = (object) $field;
	$preview_field->address_type                  = $address_type;
	$preview_field->field_options['address_type'] = $address_type;
	$preview                                      = new FrmFieldAddress( $preview_field );
	?>
	<template data-frm-address-type="<?php echo esc_attr( $address_type ); ?>">
		<?php $preview->show_on_form_builder(); ?>
	</template>
	<?php
}
