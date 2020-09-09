<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-layout-classes">
	<p class="howto">
		<?php esc_html_e( 'Click on any box below to set the width for your selected field.', 'formidable' ); ?>
	</p>
	<ul class="frm_code_list frm_grid_container">
		<li class="frm_half frm_form_field">
			<a href="javascript:void(0);" data-code="frm_half" class="frm_insert_code show_frm_classes">
				1/2
			</a>
		</li>
		<?php
		foreach ( FrmFormsHelper::grid_classes() as $c => $d ) {
			?>
			<li class="<?php echo esc_attr( $c ); ?> frm_form_field">
				<a href="javascript:void(0);" data-code="<?php echo esc_attr( $c ); ?>" class="frm_insert_code show_frm_classes">
					<?php echo esc_html( FrmFormsHelper::style_class_label( $d, $c ) ); ?>
				</a>
			</li>
			<?php
			unset( $c, $d );
		}
		?>
	</ul>

	<h4 class="frm-with-line">
		<span><?php esc_html_e( 'Other Style Classes', 'formidable' ); ?></span>
	</h4>
	<ul class="frm_code_list frm-full-hover">
		<?php
		foreach ( FrmFormsHelper::css_classes() as $c => $d ) {
			$title = ( ! empty( $d ) && is_array( $d ) && isset( $d['title'] ) ) ? $d['title'] : '';
			?>
			<li>
				<a href="javascript:void(0);" data-code="<?php echo esc_attr( $c ); ?>" class="frm_insert_code show_frm_classes<?php echo esc_attr( ! empty( $title ) ? ' frm_help' : '' ); ?>" <?php echo ( ! empty( $title ) ? ' title="' . esc_attr( $title ) . '"' : '' ); ?>>
					<span><?php echo esc_html( $c ); ?></span>
					<?php echo esc_html( FrmFormsHelper::style_class_label( $d, $c ) ); ?>
				</a>
			</li>
			<?php
			unset( $c, $d );
		}
		?>
	</ul>
</div>
