<div id="form_entries_page" class="frm_wrap frm_list_entry_page">
	<?php if ( $form ) { ?>
	<div class="frm_page_container">
	<?php } ?>

		<?php
		FrmAppHelper::get_admin_header(
			array(
				'label'       => __( 'Form Entries', 'formidable' ),
				'form'        => $form,
				'close'       => $form ? '?page=formidable-entries&form=' . $form->id : '',
			)
		);
		?>

		<div class="wrap">
			<?php if ( $form ) { ?>
				<h2>
					<?php esc_html_e( 'Form Entries', 'formidable' ); ?>
				</h2>
			<?php } ?>

			<?php require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>

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
	<?php if ( $form ) { ?>
	</div>
	<?php } ?>
</div>
