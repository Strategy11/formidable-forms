<div class="wrap">
	<div id="icon-edit-pages" class="icon32"><br/></div>
	<h2><?php esc_html_e( 'Edit Payment', 'formidable' ); ?>
		<a href="?page=formidable-payments&amp;action=new" class="add-new-h2"><?php esc_html_e( 'Add New', 'formidable' ); ?></a>
	</h2>

	<div class="form-wrap">
		<?php include FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php'; ?>

		<form method="post">
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				<div class="inner-sidebar">
					<div id="submitdiv" class="postbox ">
						<h3 class="hndle"><span><?php esc_html_e( 'Publish', 'formidable' ); ?></span></h3>
						<div class="inside">
							<div class="submitbox">
								<div id="minor-publishing" style="border:none;">
									<div class="misc-pub-section">
										<a href="?page=formidable-payments&amp;action=show&amp;frm_action=show&amp;id=<?php echo absint( $payment->id ); ?>" class="button-secondary alignright">
											<?php esc_html_e( 'View', 'formidable' ); ?>
										</a>
										<?php FrmTransLitePaymentsController::load_sidebar_actions( $payment ); ?>
										<div class="clear"></div>
									</div>
								</div>

								<div id="major-publishing-actions">
									<div id="delete-action">
										<a class="submitdelete deletion" href="<?php echo esc_url( add_query_arg( 'frm_action', 'destroy' ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete that payment?', 'formidable' ); ?>');" title="<?php esc_attr_e( 'Delete', 'formidable' ); ?>">
											<?php esc_html_e( 'Delete', 'formidable' ); ?>
										</a>
									</div>
									<div id="publishing-action">
										<input type="submit" name="Submit" value="<?php esc_attr_e( 'Update', 'formidable' ); ?>" class="button-primary" />
									</div>
									<div class="clear"></div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div id="post-body">
					<div id="post-body-content">
						<input type="hidden" name="id" value="<?php echo esc_attr( $payment->id ); ?>" />
						<?php
						$form_action = 'update';
						wp_nonce_field( 'update-options' );
						require FrmTransLiteAppHelper::plugin_path() . '/views/payments/form.php';
						?>

						<p>
							<input class="button-primary" type="submit" name="Submit" value="<?php esc_attr_e( 'Update', 'formidable' ); ?>" />
						</p>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
