<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div>
	<p class="howto">
		<?php esc_html_e( 'Easily change which style your forms are using by making changes below.', 'formidable' ); ?>
	</p>

	<?php require FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php'; ?>

	<table class="widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="column-locations">
					<?php esc_html_e( 'Form Title', 'formidable' ); ?>
				</th>
				<th scope="col">
					<?php esc_html_e( 'Assigned Style Templates', 'formidable' ); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if ( $forms ) {
				$row_view_file_path = FrmAppHelper::plugin_path() . '/classes/views/styles/_manage-styles-row.php';
				array_walk(
					$forms,
					/**
					 * @param stdClass       $form
					 * @param array<WP_Post> $styles
					 * @param WP_Post        $default_style
					 * @param string         $row_view_file_path
					 * @return void
					 */
					function ( $form ) use ( $styles, $default_style, $row_view_file_path ) {
						$active_style_id = isset( $form->options['custom_style'] ) ? (int) $form->options['custom_style'] : 1;
						if ( 1 === $active_style_id ) {
							// use the default style
							$active_style_id = $default_style->ID;
						}

						include $row_view_file_path;
					}
				);
			} else {
				?>
				<tr>
					<td><?php esc_html_e( 'No Forms Found', 'formidable' ); ?></td>
				</tr>
				<?php
			}//end if
			?>
		</tbody>
	</table>
</div>
