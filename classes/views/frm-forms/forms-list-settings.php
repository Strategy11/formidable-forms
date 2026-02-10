<?php
/**
 * Template for the forms list settings
 *
 * @since x.x
 *
 * @package Formidable
 *
 * @var int       $per_page
 * @var array     $columns
 * @var array     $hidden
 * @var array     $skip_cols
 * @var WP_Screen $screen
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-forms-list-settings" class="frm_hidden">
	<div class="frm-collapsible-box">
		<a href="#" class="frm-collapsible-box__btn">
			<?php
			esc_html_e( 'Columns', 'formidable' );
			FrmAppHelper::icon_by_class( 'frmfont frm_arrowup6_icon' );
			?>
		</a>

		<div class="frm-collapsible-box__content">
			<div class="frm-forms-list-column-checkboxes">
				<?php
				foreach ( $columns as $key => $label ) {
					if ( in_array( $key, $skip_cols, true ) ) {
						continue;
					}

					$is_hidden = in_array( $key, $hidden, true );
					?>
					<label>
						<input
							type="checkbox"
							value="1"
							data-screen-option-id="<?php echo esc_attr( $key ); ?>-hide"
							<?php checked( ! $is_hidden ); ?>
						/>
						<?php echo esc_html( $label ); ?>
					</label>
					<?php
				}//end foreach
				?>
			</div>
		</div>
	</div>

	<div>
		<div class="frm-flex frm-items-center frm-justify-between frm-mb-sm">
			<label for="frm-forms-list-show-desc"><?php esc_html_e( 'Form description', 'formidable' ); ?></label>
			<?php
			FrmHtmlHelper::toggle(
				'frm-forms-list-show-desc',
				'show_desc',
				array(
					'echo'       => true,
					'checked'    => intval( get_user_option( 'frm_forms_show_desc' ) ) === 1,
					'input_html' => array(
						'data-screen-option-id' => 'frm-forms-show-desc',
					),
				)
			);
			?>
		</div>

		<div class="frm-flex frm-items-center frm-justify-between">
			<label for="frm-forms-list-per-page"><?php esc_html_e( 'Items per page', 'formidable' ); ?></label>
			<input
				type="number"
				id="frm-forms-list-per-page"
				value="<?php echo intval( $per_page ); ?>"
				min="1"
				data-screen-option-id="formidable_page_formidable_per_page"
			/>
		</div>
	</div>

	<div style="text-align: right;">
		<button type="button" class="frm-button-primary button-primary" id="frm-save-forms-list-settings-btn"><?php esc_html_e( 'Apply', 'formidable' ); ?></button>
	</div>
</div>
