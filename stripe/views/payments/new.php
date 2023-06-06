<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="wrap">
	<div id="icon-edit-pages" class="icon32"><br/></div>
	<h2><?php esc_html_e( 'New Payment', 'formidable' ); ?></h2>

	<div class="form-wrap">
		<?php include FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php'; ?>

		<form method="post">
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				<div class="inner-sidebar">
					<div id="submitdiv" class="postbox ">
						<h3 class="hndle"><span><?php esc_html_e( 'Publish', 'formidable' ); ?></span></h3>
						<div class="inside">
							<div id="major-publishing-actions">
								<div id="publishing-action">
									<input type="submit" name="Submit" value="<?php esc_attr_e( 'Submit', 'formidable' ); ?>" class="button-primary" />
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
				</div>
		
				<div id="post-body">
					<div id="post-body-content">
						<?php
						$form_action = 'create';
						wp_nonce_field( 'create-options' );
						require FrmTransLiteAppHelper::plugin_path() . '/views/payments/form.php';
						?>

						<p>
							<input class="button-primary" type="submit" name="Submit" value="<?php esc_attr_e( 'Submit', 'formidable' ); ?>" />
						</p>
					</div>
				</div>

			</form>
		</div>
	</div>
</div>
	
</div>
