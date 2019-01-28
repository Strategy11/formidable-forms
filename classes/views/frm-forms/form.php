<?php wp_nonce_field( 'frm_save_form_nonce', 'frm_save_form' ); ?>
<input type="hidden" name="status" value="<?php echo esc_attr( $values['status'] ); ?>" />
<input type="hidden" name="new_status" value="" />

<div id="frm_form_editor_container">

<div class="postbox">
	<div id="titlediv" class="inside">
		<input type="text" name="name" value="<?php echo esc_attr( $form->name ); ?>" id="title" placeholder="<?php esc_attr_e( 'Enter title here', 'formidable' ); ?>" />
	</div>

	<div class="frm_no_fields <?php echo ( isset( $values['fields'] ) && ! empty( $values['fields'] ) ) ? 'frm_hidden' : ''; ?>">
	    <div class="alignleft sketch1">
			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/sketch_arrow1.png' ); ?>" alt="" />
	    </div>
	    <div class="alignleft sketch1_text">
			<?php esc_html_e( '1. Name your form', 'formidable' ) ?>
	    </div>

	    <div class="alignright sketch2">
			<?php esc_html_e( '2. Click or drag a field to add it to your form', 'formidable' ); ?>
	        <div class="clear"></div>
			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/sketch_arrow2.png' ); ?>" alt="" />
	    </div>
	    <div class="clear"></div>

		<div class="frm_drag_inst"><?php esc_html_e( 'Add Fields Here', 'formidable' ) ?></div>

    	<div class="alignleft sketch3">
			<div class="alignright"><?php esc_html_e( '3. Save your form', 'formidable' ) ?></div>
			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/sketch_arrow3.png' ); ?>" alt="" />
	    </div>
    	<div class="clear"></div>
    </div>
<ul id="new_fields" class="frm_sorting inside">
<?php
if ( isset( $values['fields'] ) && ! empty( $values['fields'] ) ) {
	$values['count'] = 0;
	foreach ( $values['fields'] as $field ) {
		$values['count']++;
		FrmFieldsController::load_single_field( $field, $values );
		unset( $field );
	}
}
?>
</ul>

</div>

</div>
