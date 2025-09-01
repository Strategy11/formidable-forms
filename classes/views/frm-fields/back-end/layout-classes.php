<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-layout-classes">
	<ul class="frm_code_list frm_grid_container">
		<li class="frm_half frm_form_field">
			<a href="javascript:void(0);" data-code="frm_half" class="frm_insert_code show_frm_classes" tabindex="0">
				1/2
			</a>
		</li>
		<?php
		foreach ( FrmFormsHelper::grid_classes() as $c => $d ) {
			?>
			<li class="<?php echo esc_attr( $c ); ?> frm_form_field">
				<a href="javascript:void(0);" data-code="<?php echo esc_attr( $c ); ?>" class="frm_insert_code show_frm_classes" tabindex="0">
					<?php echo esc_html( FrmFormsHelper::style_class_label( $d, $c ) ); ?>
				</a>
			</li>
			<?php
			unset( $c, $d );
		}
		?>
	</ul>

	<h4 class="frm-collapsible frm-collapsed" role="button" aria-expanded="false" tabindex="0" aria-label="<?php esc_attr_e( 'Other Style Classes', 'formidable' ); ?>" aria-controls="collapsible-section">
		<?php esc_html_e( 'Other Style Classes', 'formidable' ); ?>
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown8_icon frm_svg13', array( 'aria-hidden' => 'true' ) ); ?>
	</h4>

	<div class="frm-collapse-me" role="group">
		<ul class="frm_code_list frm-full-hover">
			<?php
			foreach ( FrmFormsHelper::css_classes() as $c => $d ) {
				$title = ! empty( $d ) && is_array( $d ) && isset( $d['title'] ) ? $d['title'] : '';
				?>
				<li>
					<a href="javascript:void(0);" data-code="<?php echo esc_attr( $c ); ?>" class="frm_insert_code show_frm_classes<?php echo esc_attr( ! empty( $title ) ? ' frm_help' : '' ); ?>" tabindex="0" <?php echo ( ! empty( $title ) ? ' title="' . esc_attr( $title ) . '"' : '' ); ?>>
						<span><?php echo esc_html( FrmFormsHelper::style_class_label( $d, $c ) ); ?></span>
						<span class="frm-text-grey-500"><?php echo esc_html( $c ); ?></span>
					</a>
				</li>
				<?php
				unset( $c, $d );
			}
			?>
		</ul>
	</div>
</div>
