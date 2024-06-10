<?php
/**
 * Add-Ons addon view.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! is_array( $addon ) ) {
	return;
}
?>
<div class="frm-card-item plugin-card-<?php echo esc_attr( $slug ); ?> frm-no-thumb frm-addon-<?php echo esc_attr( $addon['status']['type'] ); ?>">
	<div class="plugin-card-top">
		<h2>
			<?php
			echo esc_html( ! empty( $addon['display_name'] ) ? $addon['display_name'] : $addon['title'] );

			if ( ! empty( $addon['is_new'] ) ) {
				FrmAppHelper::show_pill_text();
			}
			?>
		</h2>
		<p>
			<?php
			echo FrmAppHelper::kses( $addon['excerpt'], array( 'a' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			$show_docs = ! empty( $addon['docs'] ) && $addon['installed'];
			?>
			<?php if ( $show_docs ) { ?>
				<br/><a href="<?php echo esc_url( $addon['docs'] ); ?>" target="_blank" aria-label="<?php esc_attr_e( 'View Docs', 'formidable' ); ?>">
					<?php esc_html_e( 'View Docs', 'formidable' ); ?>
				</a>
			<?php } ?>
		</p>
		<?php
		$plan_required = FrmFormsHelper::get_plan_required( $addon );
		if ( ! $show_docs ) {
			FrmFormsHelper::show_plan_required( $plan_required, $pricing . '&utm_content=' . $addon['slug'] );
		}
		?>
	</div>
	<div class="plugin-card-bottom">
		<span class="addon-status">
			<?php
			printf(
				/* translators: %s: Status name */
				esc_html__( 'Status: %s', 'formidable' ),
				'<span class="addon-status-label">' . esc_html( $addon['status']['label'] ) . '</span>'
			);
			?>
		</span>
		<?php
		$passing = array(
			'addon'         => $addon,
			'license_type'  => ! empty( $license_type ) ? $license_type : false,
			'plan_required' => 'plan_required',
			'upgrade_link'  => $pricing,
		);
		FrmAddonsController::show_conditional_action_button( $passing );
		?>
	</div>
</div>
