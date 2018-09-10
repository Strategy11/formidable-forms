<div id="frm_builder_page" class="frm_wrap">
    <div id="poststuff" class="frm_page_container">

    <div id="post-body" class="metabox-holder columns-2">
    <div id="post-body-content">

	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label'       => ( $form->is_template ? __( 'Templates', 'formidable' ) : __( 'Build Form', 'formidable' ) ),
			'is_template' => $values['is_template'],
			'form'        => $form,
			'new_link'    => '?page=formidable&frm_action=new',
			'hide_title'  => true,
		)
	);

	require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' );

	?>

        <div class="frm_form_builder with_frm_style">

        <form method="post" id="frm_build_form">
            <input type="hidden" name="frm_action" value="create" />
            <input type="hidden" name="action" value="create" />
            <input type="hidden" name="id" id="form_id" value="<?php echo (int) $id; ?>" />

			<?php require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/form.php' ); ?>

        </form>

        </div>
    </div>
	<?php require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/add_field_links.php' ); ?>
    </div>
    </div>
</div>
