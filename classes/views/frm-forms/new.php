<div class="wrap">
    <h2><?php _e( 'Build New Form', 'formidable' ) ?>
		<a href="?page=formidable-new" class="add-new-h2 frm_invisible"><?php _e( 'Add New', 'formidable' ); ?></a>
	</h2>

    <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
    <div id="post-body-content">
        <?php
        if ( ! $values['is_template'] ) {
            FrmAppController::get_form_nav($id, true, 'hide');
        }
		require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' );

        ?>

        <div class="frm_form_builder with_frm_style">

        <form method="post" id="frm_build_form">
            <input type="hidden" name="frm_action" value="create" />
            <input type="hidden" name="action" value="create" />
            <input type="hidden" name="id" id="form_id" value="<?php echo (int) $id; ?>" />

			<?php require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/form.php' ); ?>

            <p>
				<input type="button" value="<?php esc_attr_e( 'Create', 'formidable' ) ?>" class="frm_submit_<?php echo ( isset( $values['ajax_load'] ) && $values['ajax_load'] ) ? '' : 'no_'; ?>ajax button-primary" />
                <span class="frm-loading-img"></span>
            </p>
        </form>

        </div>
    </div>
	<?php require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/add_field_links.php' ); ?>
    </div>
    </div>
</div>
