<div class="frm_wrap wrap upgrade_to_pro frm-fields">
	<h1 class="frm_pro_heading">
		<img src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/logo.png" alt="Upgrade to Pro" />
		<span class="alignright">Take on bigger projects, earn more clients and grow your business.<br/>
			<a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( FrmAppHelper::admin_upgrade_link( $link_parts ) ) ); ?>" target="_blank">Upgrade to Pro</a>.</span>
		<span class="clear"></span>
	</h1>

	<div class="clear"></div>

	<p>Are you outgrowing your basic forms? You can add a ton more field types and features, create advanced forms, and even build form-based solutions in no time at all.</p>
	<p>Are you currently collecting data offline? Streamline your business by using your forms to get online. Whether you need surveys, polls, client contracts, mortgage calculators, or directories, we've got you covered. Save time by allowing clients to return and make changes to their own submissions, or let them contribute content to your site. Generate more leads by adding headings and page breaks, only showing the fields you need, and letting your clients repeat a section of fields as many times as they need.</p>
	<p>Projects that once seemed impossible are within your reach with Pro. That project you’ve been dreaming of pursuing? Chances are <strong>Formidable Pro can handle it</strong>.</p><br/>


	<p class="frmcenter">
		<a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( FrmAppHelper::admin_upgrade_link( $link_parts ) ) ); ?>" class="button-primary frm-button-primary frm_large" target="_blank">
			<?php esc_html_e( 'Get Started Now', 'formidable' ); ?>
		</a>
	</p>
	<br/>

	<?php do_action( 'frm_upgrade_page' ); ?>

	<table class="widefat">
		<thead>
			<tr>
				<th style="width:60%;border:none"></th>
				<th class="frmcenter"><h3>Lite</h3></th>
				<th class="frmcenter"><h3>Pro</h3></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $features as $name => $group ) { ?>
				<tr><th colspan="3" class="frm_table_break"><?php echo esc_html( $name ); ?></th></tr>
				<?php foreach ( $group as $feature ) { ?>
					<tr>
						<th>
							<?php
							if ( isset( $feature['link'] ) ) {
								$feature['link']['medium'] = 'upgrade';
								?>
								<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( $feature['link'] ) ); ?>" target="_blank">
									<?php echo esc_html( $feature['label'] ); ?>
								</a>
								<?php
							} else {
								echo esc_html( $feature['label'] );
							}
							?>
						</th>
						<td class="<?php echo esc_attr( $feature['lite'] ? 'frm-checked' : '' ); ?> frmcenter">
							<?php if ( $feature['lite'] ) { ?>
							<span class="frm-yes">
								<?php FrmAppHelper::icon_by_class( 'frmfont frm_checkmark_icon' ); ?>
							</span>
							<?php } else { ?>
							<span class="frm-nope">&#10008;</span>
							<?php } ?>
						</td>
						<td class="<?php echo esc_attr( 'frm-checked' ); ?> frmcenter">
							<span class="frm-yes">
								<?php FrmAppHelper::icon_by_class( 'frmfont frm_checkmark_icon' ); ?>
							</span>
						</td>
					</tr>
				<?php } ?>
			<?php } ?>
		</tbody>
	</table>

	<br/>
	<p class="frmcenter">
		<a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( FrmAppHelper::admin_upgrade_link( $link_parts ) ) ); ?>" class="button-primary frm-button-primary frm_large" target="_blank">
			<?php esc_html_e( 'Get Started Now', 'formidable' ); ?>
		</a>
	</p>
</div>
