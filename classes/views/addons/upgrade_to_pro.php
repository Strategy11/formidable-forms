<div class="wrap">
	<h2><?php _e( 'Upgrade to Pro', 'formidable' ) ?></h2>

	<table class="wp-list-table widefat fixed striped frm_pricing">
		<thead>
			<tr>
				<th></th>
			<?php foreach ( $pro['pricing'] as $name => $price ) {
				$price_id++;
				if ( $name == 'smallbusiness' ) {
					$name = 'Small Business';
				} ?>
				<th>
					<h3><?php echo esc_attr( ucfirst( $name ) ) ?></h3>
					<h4>$<?php echo esc_attr( $price ) ?></h4>
					<a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( 'https://formidablepro.com/checkout?edd_action=add_to_cart&download_id=93790' ) ) ?>&amp;edd_options[price_id]=<?php echo absint( $price_id ) ?>" class="button-primary" target="_blank"><?php _e( 'Get Started', 'formidable' ) ?></a>
				</th>
			<?php } ?>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th>Knowledge Base Support For # Sites</th>
				<td>1 Site</td>
				<td>1 Site + 1 Staging Site</td>
				<td>15 Sites</td>
				<td>200 Sites</td>
			</tr>
			<tr>
				<th>Product Updates Forever</th>
				<td>Manual</td>
				<td>Manual</td>
				<td>Manual</td>
				<td>Automatic</td>
			</tr>
			<tr>
				<th>Automatic Updates</th>
				<td>1 Year</td>
				<td>1 Year</td>
				<td>2 Years</td>
				<td>Lifetime</td>
			</tr>
			<tr>
				<th>1 Year of Ticket Support</th>
				<td>None</td>
				<td>Standard Support</td>
				<td>Priority Support</td>
				<td>Elite Support</td>
			</tr>
			<tr>
				<th>Included AddOns</th>
				<td>None</td>
				<td><a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url('https://formidablepro.com/pricing/#addon-lists') ) ?>" target="_blank">Basic Addons</a></td>
				<td><a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url('https://formidablepro.com/pricing/#addon-lists') ) ?>" target="_blank">Advanced Addons</a></td>
				<td><a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url('https://formidablepro.com/pricing/#addon-lists') ) ?>" target="_blank">Enterprise Addons</a></td>
			</tr>
		</tbody>
	</table>
	<br/>

	<h2>Features</h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th></th>
				<th><h3>Lite</h3></th>
				<th><h3>Pro</h3></th>
			</tr>
		</thead>
		<tbody>
			<tr><th colspan="3" class="frm_table_break">Form Building</th></tr>
			<tr>
				<th>Drag & Drop Form building</th>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Create forms from Templates</th>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Import and export forms with XML</th>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Use Placeholders in your fields that clear when typing starts</th>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Include text, email, url, paragraph text, radio, checkbox, and dropdown fields in your form</th>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Include Section headings, page breaks, rich text, number, phone number, date, time, scale, dynamic fields populated from other forms, hidden fields, user ID fields, password, HTML, and tags fields</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Save a calculated value into a field</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Allow File Uploads</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Repeat sections of fields</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Hide and show fields conditionally based on other fields or the user's role</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Confirmation fields</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Multi-paged forms</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr><th colspan="3" class="frm_table_break">Form Actions</th></tr>
			<tr>
				<th>Send multiple emails and autoresponders</th>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Conditionally send your email notifications based on values in your form</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Create and edit WordPress posts or custom posts from the front-end</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr><th colspan="3" class="frm_table_break">Form Appearance</th></tr>
			<tr>
				<th>Customizable layout with CSS classes</th>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Customize the HTML for your forms</th>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Style your form with the Visual Styler</th>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Create Multiple styles for different forms</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr><th colspan="3" class="frm_table_break">Entry Management</th></tr>
			<tr>
				<th>View form submissions from the back-end</th>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Export your entries to a CSV</th>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Import entries from a CSV</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Logged-in users can save drafts and return later</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Flexibly and powerfully view, edit, and delete entries from anywhere on your site</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr><th colspan="3" class="frm_table_break">Display Entries</th></tr>
			<tr>
				<th>Generate graphs and stats based on your submitted data</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
			<tr>
				<th>Virtually limitless views</th>
				<td><i class="frm_icon_font frm_cancel1_icon"></i></td>
				<td><i class="frm_icon_font frm_check_icon"></i></td>
			</tr>
		</tbody>
	</table>
</div>
