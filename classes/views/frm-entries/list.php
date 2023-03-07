<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$pro_is_installed = FrmAppHelper::pro_is_installed();
?>
<div id="form_entries_page" class="frm_wrap frm_list_entry_page">
		<?php
		// Adding new entries from an admin page is a Pro feature.
		FrmAppHelper::get_admin_header(
			array(
				'label'       => __( 'Form Entries', 'formidable' ),
				'form'        => $form,
				'close'       => $form ? admin_url( 'admin.php?page=formidable-entries&form=' . $form->id ) : '',
				'import_link' => $pro_is_installed,
				'publish'     => ! $form || ! $pro_is_installed ? true : array(
					'FrmAppHelper::add_new_item_link',
					array(
						'new_link' => admin_url( 'admin.php?page=formidable-entries&frm_action=new&form=' . $form->id ),
					),
				),
			)
		);
		?>

		<div class="wrap">
			<?php if ( $form ) { ?>
				<h2>
					<?php esc_html_e( 'Form Entries', 'formidable' ); ?>
				</h2>
				<?php if ( ! $pro_is_installed ) { ?>
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
				<?php require FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php'; ?>
				<?php $wp_list_table->display(); ?>
			</form>
			<?php do_action( 'frm_page_footer', array( 'table' => $wp_list_table ) ); ?>
		</div>
</div>
