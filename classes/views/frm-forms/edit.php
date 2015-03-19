<div class="wrap">
    <div class="frmicon icon32"><br/></div>
    <h2><?php echo ( $form->is_template ? __( 'Templates', 'formidable' ) : __( 'Build', 'formidable' )); ?>
        <a href="?page=formidable&amp;frm_action=new" class="add-new-h2"><?php _e( 'Add New', 'formidable' ); ?></a>
    </h2>

	<?php
	// Add form messages
	require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' );
	?>

    <div id="poststuff">

    <div id="post-body" class="metabox-holder columns-2">
    <div id="post-body-content">
    <?php

	// Add form nav
	if ( ! $values['is_template'] ) {
		FrmAppController::get_form_nav( $id, true, 'hide' );
	}

    ?>
    <div class="frm_form_builder<?php echo FrmFormsHelper::get_form_style_class($form); ?>">

        <p class="frm_hidden frm-no-margin">
			<input type="button" value="<?php esc_attr_e( 'Update', 'formidable' ) ?>" class="frm_submit_<?php echo ( isset( $values['ajax_load'] ) && $values['ajax_load'] ) ? '' : 'no_'; ?>ajax button-primary" />
            <span class="frm-loading-img"></span>
        </p>

    <form method="post" id="frm_build_form">
        <input type="hidden" name="frm_action" value="update" />
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="id" id="form_id" value="<?php echo (int) $id; ?>" />

        <?php require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/form.php'); ?>

        <p>
			<input type="button" value="<?php esc_attr_e( 'Update', 'formidable' ) ?>" class="frm_submit_<?php echo ( isset( $values['ajax_load'] ) && $values['ajax_load'] ) ? '' : 'no_'; ?>ajax button-primary" />
            <span class="frm-loading-img"></span>
        </p>
    </form>
    </div>
    </div>
    <?php require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/add_field_links.php'); ?>
    </div>
    </div>
</div>
