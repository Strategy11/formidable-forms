<div id="form_entries_page" class="frm_wrap frm_list_entry_page">
	<?php if ( $form ) { ?>
	<div class="frm_page_container">
	<?php } ?>

		<?php
		FrmAppHelper::get_admin_header(
			array(
				'label'       => __( 'Form Entries', 'formidable' ),
				'form'        => $form,
				'close'       => $form ? admin_url( 'admin.php?page=formidable-entries&form=' . $form->id ) : '',
			)
		);
		?>

		<div class="wrap">
			<?php if ( $form ) { ?>
				<h2>
					<?php esc_html_e( 'Form Entries', 'formidable' ); ?>
					<?php
					FrmAppHelper::add_new_item_link(
						array(
							'new_link' => FrmAppHelper::maybe_full_screen_link( admin_url( 'admin.php?page=formidable-entries&frm_action=new&form=' . $form->id ) ),
						)
					);
					?>
				</h2>
				<?php if ( ! FrmAppHelper::pro_is_installed() ) { ?>
				<div class="clear"></div>
				<?php } ?>
			<?php } ?>

			<form id="posts-filter" method="get">
				<input type="hidden" name="page" value="formidable-entries" />
				<input type="hidden" name="form" value="<?php echo esc_attr( $form ? $form->id : '' ); ?>" />
				<input type="hidden" name="frm_action" value="list" />
				<?php do_action( 'frm_entry_inside_h2', $form ); ?>
				<?php $wp_list_table->search_box( __( 'Search', 'formidable' ), 'entry' ); ?>

				<?php FrmTipsHelper::pro_tip( 'get_entries_tip', 'p' ); ?>

				<div class="clear"></div>
				<?php require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>
				<?php $wp_list_table->display(); ?>
			</form>
		</div>
	<?php if ( $form ) { ?>
	</div>
	<?php } ?>
</div>
