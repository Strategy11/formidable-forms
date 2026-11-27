<?php
/**
 * Add-Ons addon view.
 *
 * @package Formidable
 *
 * @var array  $addon        Single add-on data (slug, title, display_name, excerpt, status, docs, docs_label, is_new, etc.).
 * @var string $license_type Current license type or empty string.
 * @var string $pricing      Upgrade URL used for CTAs.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! is_array( $addon ) || $addon['slug'] === 'views' ) {
	return;
}

$is_boost = FrmAddonsHelper::is_boost( $addon );
?>
<li <?php FrmAddonsHelper::add_addon_attributes( $addon ); ?>>
	<div class="frm-flex frm-gap-xs frm-items-center frm-mb-2xs">
		<span class="frm-border-icon">
			<?php
			if ( $is_boost && ! empty( $addon['icon'] ) && is_string( $addon['icon'] ) && str_starts_with( $addon['icon'], 'frm_' ) ) {
				FrmAppHelper::icon_by_class( 'frmfont ' . $addon['icon'], array( 'aria-hidden' => 'true' ) );
			} else {
				FrmAddonsHelper::get_addon_icon( $addon['slug'] );
			}
			?>
		</span>

		<h3>
			<span class="frm-font-medium frm-truncate"><?php echo esc_html( ! empty( $addon['display_name'] ) ? $addon['display_name'] : $addon['title'] ); ?></span>
			<?php
			if ( ! empty( $addon['is_new'] ) ) {
				FrmAppHelper::show_pill_text();
			}

			if ( $is_boost ) {
				?>
				<span class="frm-meta-tag frm-boost-pill" title="<?php esc_attr_e( 'Boosts are sold separately and are not included in any plan.', 'formidable' ); ?>"><?php esc_html_e( 'Boost', 'formidable' ); ?></span>
				<?php
			}
			?>
		</h3>

		<?php
		if ( ! $is_boost && FrmAddonsHelper::is_locked() ) {
			?>
			<span class="frm-card-lock-icon frm-ml-auto">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon', array( 'aria-label' => __( 'Lock icon', 'formidable' ) ) ); ?>
			</span>
			<?php
		} elseif ( ! $is_boost || ! FrmAddonsHelper::is_locked() ) {
			$toggle_classes = 'with_frm_style frm_toggle frm-ml-auto';

			if ( FrmAddonsHelper::is_built_in() ) {
				// The wrapper carries the layout class and explains the always-on toggle.
				$toggle_classes = 'with_frm_style frm_toggle';
				?>
				<span class="frm-ml-auto frm_help" title="<?php esc_attr_e( 'This payment gateway is built into Formidable and is always on.', 'formidable' ); ?>">
				<?php
			} else {
				FrmAddonsController::show_conditional_action_button(
					array(
						'addon'         => $addon,
						'license_type'  => ! empty( $license_type ) ? $license_type : false,
						'plan_required' => 'plan_required',
						'upgrade_link'  => $pricing,
					)
				);
			}

			FrmHtmlHelper::toggle(
				'frm-' . $addon['slug'],
				'frm-' . $addon['slug'],
				array(
					'div_class'       => $toggle_classes,
					'checked'         => $addon['status']['type'] === 'active',
					'disabled'        => $addon['slug'] === 'formidable-pro' || FrmAddonsHelper::is_built_in(),
					'echo'            => true,
					'aria-label-attr' => $addon['title'],
				)
			);

			if ( FrmAddonsHelper::is_built_in() ) {
				echo '</span>';
			}
		}//end if
		?>
	</div>

	<?php if ( ! empty( $addon['excerpt'] ) ) { ?>
		<p class="frm-line-clamp-2">
			<?php FrmAppHelper::kses_echo( $addon['excerpt'], array( 'a' ) ); ?>
		</p>
	<?php } ?>

	<span class="frm-page-skeleton-divider frm-mt-auto"></span>

	<div class="frm-flex frm-items-center frm-justify-between">
		<?php
		if ( $is_boost ) {
			if ( ! empty( $addon['link'] ) ) {
				?>
			<a class="frm-link-with-external-icon" href="<?php echo esc_url( $addon['link'] ); ?>" target="_blank" rel="noopener">
				<?php esc_html_e( 'Learn More', 'formidable' ); ?>
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowup8_icon', array( 'aria-hidden' => 'true' ) ); ?>
			</a>
			<?php
			}

			if ( empty( $addon['installed'] ) && empty( $addon['url'] ) ) {
				FrmAddonsController::addon_upgrade_link( $addon, $pricing );
			}
		} elseif ( FrmAddonsHelper::is_built_in() ) {
			if ( ! empty( $addon['docs'] ) ) {
				$docs_label = ! empty( $addon['docs_label'] ) ? $addon['docs_label'] : __( 'View Docs', 'formidable' );
				?>
				<a class="frm-link-with-external-icon" href="<?php echo esc_url( $addon['docs'] ); ?>" target="_blank" aria-label="<?php echo esc_attr( $docs_label ); ?>">
					<?php echo esc_html( $docs_label ); ?>
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowup8_icon' ); ?>
				</a>
				<?php
			}
		} elseif ( ! empty( $addon['docs'] ) && ! FrmAddonsHelper::get_plan() ) {
			$docs_label = ! empty( $addon['docs_label'] ) ? $addon['docs_label'] : __( 'View Docs', 'formidable' );
			?>
			<a class="frm-link-with-external-icon" href="<?php echo esc_url( $addon['docs'] ); ?>" target="_blank" aria-label="<?php echo esc_attr( $docs_label ); ?>">
				<?php echo esc_html( $docs_label ); ?>
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowup8_icon' ); ?>
			</a>
			<?php
		} else {
			FrmFormsHelper::show_plan_required( FrmAddonsHelper::get_plan(), $pricing . '&utm_content=' . $addon['slug'] );
			?>
			<div>
				<?php FrmAddonsController::addon_upgrade_link( $addon, $pricing ); ?>
			</div>
			<?php
		}//end if
		?>
	</div>
</li>
