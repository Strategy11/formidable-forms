<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$form_name   = ! empty( $form->name ) ? $form->name : __( '(no title)', 'formidable' );
$dropdown_id = 'frm_style_dropdown_' . absint( $form->id );
?>
<tr>
	<td>
		<label for="<?php echo esc_html( $dropdown_id ); ?>"><?php echo esc_html( $form_name ); ?></label>
	</td>
	<td>
		<input type="hidden" name="prev_style[<?php echo absint( $form->id ); ?>]" value="<?php echo esc_attr( $active_style_id ); ?>" />

		<select id="<?php echo esc_html( $dropdown_id ); ?>" name="style[<?php echo absint( $form->id ); ?>]">
			<?php foreach ( $styles as $style ) { ?>
				<option value="<?php echo esc_attr( $style->ID ); ?>" <?php selected( $style->ID, $active_style_id ); ?>>
					<?php echo esc_html( $style->post_title . ( empty( $style->menu_order ) ? '' : ' (' . __( 'default', 'formidable' ) . ')' ) ); ?>
				</option>
			<?php } ?>
			<option value="" <?php selected( 0, $active_style_id ); ?>>
				<?php esc_html_e( 'Styling disabled', 'formidable' ); ?>
			</option>
		</select>
	</td>
</tr>
