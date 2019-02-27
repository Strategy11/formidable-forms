<div id="form_entries_page" class="frm_wrap frm_list_entry_page">
	<div class="frm_page_container">

		<?php
		FrmAppHelper::get_admin_header(
			array(
				'label'       => __( 'Form Entries', 'formidable' ),
				'form'        => $form,
				'hide_title'  => true,
				'close'       => '?page=formidable-entries&form=' . $form->id,
			)
		);
		?>

		<div id="frm-bar-two">
			<?php FrmFormsHelper::form_switcher( $form->name ); ?>
			<h2><?php esc_html_e( 'Form Entries', 'formidable' ); ?></h2>

			<?php require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>
		</div>

		<div class="wrap">
			<form id="posts-filter" method="get">
				<input type="hidden" name="page" value="formidable-entries" />
				<input type="hidden" name="form" value="<?php echo esc_attr( $form ? $form->id : '' ); ?>" />
				<input type="hidden" name="frm_action" value="list" />
				<?php do_action( 'frm_entry_inside_h2', $form ); ?>
				<?php $wp_list_table->search_box( __( 'Search', 'formidable' ), 'entry' ); ?>

				<?php include( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>
				<?php FrmTipsHelper::pro_tip( 'get_entries_tip' ); ?>

				<?php $wp_list_table->display(); ?>
			</form>
		</div>
	</div>
</div>
