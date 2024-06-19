<?php
/**
 * Form Templates - Template.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! is_array( $template ) ) {
	return;
}

if ( ! empty( $template['message'] ) ) {
	?>
	<li class="frm_error_style inline">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo FrmAppHelper::kses( $template['message'], array( 'a', 'b', 'strong', 'br' ) );
		?>
	</li>
	<?php
	return;
}

FrmFormTemplatesHelper::prepare_template_details( $template, $pricing, $license_type );
?>
<li <?php FrmFormTemplatesHelper::add_template_attributes( $template, $expired ); ?>>
	<?php if ( $template['is_featured'] ) : ?>
		<div class="frm-form-templates-item-icon">
			<?php FrmFormsHelper::template_icon( $template['categories'] ); ?>
		</div>
	<?php endif; ?>

	<div class="frm-form-templates-item-body">
		<h3 class="frm-form-templates-item-title frm-font-medium">
			<span class="frm-form-templates-item-title-text">
				<?php if ( $template['plan_required'] ) { ?>
					<span class="frm-form-templates-item-lock-icon">
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon', array( 'aria-label' => __( 'Lock icon', 'formidable' ) ) ); ?>
					</span>
				<?php } ?>

				<span class="frm-form-template-name">
					<?php echo esc_html( $template['name'] ); ?>
				</span>
			</span>

			<span class="frm-flex-box frm-gap-xs frm-items-center frm-ml-auto">
				<?php
				if ( $template['is_custom'] ) {
					$trash_links = FrmFormsHelper::delete_trash_links( $template['id'] );
					?>
					<a href="<?php echo esc_url( $trash_links['trash']['url'] ); ?>" class="frm-form-templates-custom-item-trash-button frm-flex-center frm-fadein" data-frmverify="<?php esc_attr_e( 'Do you want to move this form template to the trash?', 'formidable' ); ?>" data-frmverify-btn="frm-button-red" role="button" aria-label="<?php esc_attr_e( 'Move to the trash button', 'formidable' ); ?>">
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_delete_icon', array( 'aria-label' => __( 'Move to Trash', 'formidable' ) ) ); ?>
					</a>
					<span class="frm-vertical-line frm-fadein"></span>
				<?php } ?>

				<a href="#" class="frm-form-templates-item-favorite-button frm-fadein" role="button" aria-label="<?php esc_attr_e( 'Add to favorites', 'formidable' ); ?>">
					<?php
					$favorite_button_icon = $template['is_favorite'] ? 'frm_heart_solid_icon' : 'frm_heart_icon';
					FrmAppHelper::icon_by_class( 'frmfont ' . $favorite_button_icon );
					?>
				</a>
			</span>
		</h3>

		<div class="frm-form-templates-item-content">
			<div class="frm-form-templates-item-buttons frm-fadein-down-short">
				<a <?php FrmFormTemplatesHelper::add_template_link_attributes( $template ); ?>>
					<?php echo $template['is_custom'] ? esc_html__( 'Edit', 'formidable' ) : esc_html__( 'View Demo', 'formidable' ); ?>
				</a>
				<a href="<?php echo esc_url( $template['use_template'] ); ?>" class="button button-primary frm-button-primary frm-small frm-form-templates-use-template-button" role="button">
					<?php esc_html_e( 'Use Template', 'formidable' ); ?>
				</a>
			</div>

			<p class="frm-form-templates-item-description">
				<?php
				if ( $template['description'] ) {
					echo FrmAppHelper::kses( $template['description'], array( 'a', 'i', 'span', 'use', 'svg' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} elseif ( $template['is_custom'] ) {
					echo '<i>';
					printf(
						/* translators: %s: date */
						esc_html__( 'Created %s', 'formidable' ),
						esc_html( date_i18n( get_option( 'date_format' ), strtotime( $template['created_at'] ) ) )
					);
					echo '</i>';
				} else {
					echo '<i>' . esc_html__( 'No description', 'formidable' ) . '</i>';
				}
				?>
			</p>
		</div>
	</div>
</li>
