<div class="wrap upgrade_to_pro">
	<h1 class="frm_pro_heading">
		<img src="<?php echo esc_url( FrmAppHelper::plugin_url() ) ?>/images/logo.png" alt="Upgrade to Pro" />
		<span class="alignright">Take on bigger projects, earn more clients and grow your business.<br/>
			<a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( FrmAppHelper::admin_upgrade_link( $link_parts ) ) ); ?>" target="_blank">Upgrade to Pro</a>.</span>
		<span class="clear"></span>
	</h1>

	<div class="clear"></div>

	<p>Are you outgrowing your basic forms? You can add a ton more field types and features, create advanced forms, and even build form-based solutions in no time at all.</p>
	<p>Are you currently collecting data offline? Streamline your business by using your forms to get online. Whether you need surveys, polls, client contracts, mortgage calculators, or directories, we've got you covered. Save time by allowing clients to return and make changes to their own submissions, or let them contribute content to your site. Generate more leads by adding headings and page breaks, only showing the fields you need, and letting your clients repeat a section of fields as many times as they need.</p>
	<p>Projects that once seemed impossible are within your reach with Pro. That project you’ve been dreaming of pursuing? Chances are <strong>Formidable Pro can handle it</strong>.</p><br/>

	<table class="wp-list-table widefat fixed striped frm_pricing">
		<thead>
			<tr>
				<th></th>
			<?php foreach ( $pro_pricing as $price_info ) { ?>
				<th>
					<h3><?php echo esc_attr( ucfirst( $price_info['name'] ) ) ?></h3>
					<a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( FrmAppHelper::admin_upgrade_link( $link_parts ) ) ); ?>" class="button-primary" target="_blank">
						<?php esc_html_e( 'Get Started', 'formidable' ) ?>
					</a>
				</th>
			<?php } ?>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th>Number of Sites</th>
				<td>1 Site</td>
				<td>3 Sites</td>
				<td>15 Sites</td>
				<td>Unlimited Sites</td>
			</tr>
			<tr>
				<th>Support Priority</th>
				<td>Standard Support</td>
				<td>Standard Support</td>
				<td>Priority Support</td>
				<td>Elite Support</td>
			</tr>
			<tr>
				<th>Included Add-Ons</th>
				<td>None</td>
				<td><a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( FrmAppHelper::admin_upgrade_link( $link_parts ) ) ); ?>" target="_blank">Premium Add-Ons</a></td>
				<td><a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( FrmAppHelper::admin_upgrade_link( $link_parts ) ) ); ?>" target="_blank">Advanced Add-Ons</a></td>
				<td><a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( FrmAppHelper::admin_upgrade_link( $link_parts ) ) ); ?>" target="_blank">Elite Add-Ons</a></td>
			</tr>
		</tbody>
	</table>
	<br/>

	<?php do_action( 'frm_upgrade_page' ); ?>

	<h2>Features</h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th style="width:60%"></th>
				<th><h3>Lite</h3></th>
				<th><h3>Pro</h3></th>
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
						<td><i class="frm_icon_font <?php echo esc_attr( $feature['lite'] ? 'frm_check_icon' : 'frm_cancel1_icon' ); ?>"></i></td>
						<td><i class="frm_icon_font frm_check_icon"></i></td>
					</tr>
				<?php } ?>
			<?php } ?>
		</tbody>
	</table>
</div>
