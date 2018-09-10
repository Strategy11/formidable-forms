<div id="form_entries_page" class="frm_wrap frm_list_entry_page">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label' => __( 'Entries', 'formidable' ),
			'link_hook' => array(
				'hook'  => 'frm_entry_inside_h2',
				'param' => $form,
			),
			'form' => $form,
		)
	);
	?>
	<div class="wrap">
		<form id="posts-filter" method="get">
			<input type="hidden" name="page" value="formidable-entries" />
			<input type="hidden" name="form" value="<?php echo esc_attr( $form ? $form->id : '' ); ?>" />
			<input type="hidden" name="frm_action" value="list" />
			<?php $wp_list_table->search_box( __( 'Search', 'formidable' ), 'entry' ); ?>

			<?php include( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>
			<?php FrmTipsHelper::pro_tip( 'get_entries_tip' ); ?>

			<?php $wp_list_table->display(); ?>
		</form>

	</div>
</div>
