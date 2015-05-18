<?php wp_nonce_field('frm_save_form_nonce', 'frm_save_form'); ?>
<input type="hidden" name="status" value="<?php echo esc_attr( $values['status'] ); ?>" />
<input type="hidden" name="new_status" value="" />

<div id="frm_form_editor_container">
<div id="titlediv">
    <input type="text" name="name" value="<?php echo esc_attr($form->name); ?>" id="title" placeholder="<?php esc_attr_e( 'Enter title here') ?>" />
    <div id="edit-slug-box" class="hide-if-no-js">
        <div id="frm_form_key_box" class="alignright">
        <strong><?php _e( 'Form Key:', 'formidable' ) ?></strong>
		<div id="editable-post-name" class="frm_ipe_form_key" title="<?php esc_attr_e( 'Click to edit.', 'formidable' ) ?>"><?php echo esc_html( $form->form_key ); ?></div>
        </div>
		<div id="frm_form_desc" class="frm_ipe_form_desc alignleft"><?php echo ( $form->description == '' ) ? esc_html__( '(Click to add description)', 'formidable' ) : wp_kses_post( force_balance_tags( $form->description ) ); ?></div>
        <div class="frm_clear"></div>
    </div>
</div>

<div <?php echo version_compare( $GLOBALS['wp_version'], '3.7.2', '>') ? 'class="postbox"' : ''; ?>>
    <div class="frm_no_fields <?php echo ( isset($values['fields']) && ! empty($values['fields']) ) ? 'frm_hidden' : ''; ?>">
	    <div class="alignleft sketch1">
	        <img src="<?php echo FrmAppHelper::plugin_url() .'/images/sketch_arrow1.png'; ?>" alt="" />
	    </div>
	    <div class="alignleft sketch1_text">
	        <?php _e( '1. Name your form', 'formidable' ) ?>
	    </div>

	    <div class="alignright sketch2">
	        <?php _e( '2. Click or drag a field to<br/>add it to your form', 'formidable' ) ?>
	        <div class="clear"></div>
	        <img src="<?php echo FrmAppHelper::plugin_url() .'/images/sketch_arrow2.png'; ?>" alt="" />
	    </div>
	    <div class="clear"></div>

    	<div class="frm_drag_inst"><?php _e( 'Add Fields Here', 'formidable' ) ?></div>
		<p id="frm_create_template_form">
			<?php _e( 'Or load fields from a template', 'formidable' ); ?>
			<select id="frm_create_template_dropdown">
				<?php foreach ( $all_templates as $temp ) { ?>
				<option value="<?php echo esc_attr( $temp->id ) ?>"><?php echo FrmAppHelper::truncate( $temp->name, 40 ) ?></option>
				<?php } ?>
			</select>
			<input type="button" id="frm_create_template_button" class="button-secondary" value="<?php esc_attr_e( 'Load Template', 'formidable' ) ?>" />
		</p>

    	<div class="alignleft sketch3">
	        <div class="alignright"><?php _e( '3. Save your form', 'formidable' ) ?></div>
	        <img src="<?php echo FrmAppHelper::plugin_url() .'/images/sketch_arrow3.png'; ?>" alt="" />
	    </div>
    	<div class="clear"></div>
    </div>
<ul id="new_fields" class="frm_sorting <?php echo version_compare( $GLOBALS['wp_version'], '3.7.2', '>') ? 'inside' : ''; ?>">
<?php
if ( isset($values['fields']) && ! empty($values['fields']) ) {
    $count = 0;
    foreach ( $values['fields'] as $field ) {
        $count++;
        $field_name = 'item_meta['. $field['id'] .']';
        $html_id = FrmFieldsHelper::get_html_id($field);
        require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/add_field.php');
        unset($field, $field_name);
    }
    unset($count);
} ?>
</ul>
</div>

</div>
