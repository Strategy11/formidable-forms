<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?><li>
	<label for="frm-template-drop">
		<?php esc_html_e( 'Create a template from an existing form', 'formidable' ); ?>
	</label>
	<div class="dropdown frm-fields">
		<button type="button" class="frm-dropdown-toggle dropdown-toggle btn btn-default" id="frm-template-drop" data-toggle="dropdown" style="width:auto">
			<?php esc_html_e( 'Select form for new template', 'formidable' ); ?>
			<b class="caret"></b>
		</button>
		<ul class="frm-dropdown-menu" role="menu" aria-labelledby="frm-template-drop">
			<?php
			if ( empty( $forms ) ) {
				?>
				<li class="frm_dropdown_li">
					<?php esc_html_e( 'You have not created any forms yet.', 'formidable' ); ?>
				</li>
				<?php
			} else {
				foreach ( $forms as $form ) {
					?>
					<li>
						<a href="#" data-formid="<?php echo esc_attr( $form->id ); ?>" class="frm-build-template" data-fullname="<?php echo esc_attr( $form->name ); ?>" tabindex="-1">
							<?php echo esc_html( empty( $form->name ) ? __( '(no title)', 'formidable' ) : FrmAppHelper::truncate( $form->name, 33 ) ); ?>
						</a>
					</li>
					<?php
					unset( $form );
				}
			}
			?>
		</ul>
	</div>
</li><?php
