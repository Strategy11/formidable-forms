<div class="intro">
	<div class="block">
		<strong><?php esc_html_e( 'Thanks for choosing Formidable Forms — the most powerful and versatile form builder for WordPress.', 'formidable' ); ?></strong>
	</div>

	<div class="block">

		<strong><?php esc_html_e( 'Formidable Forms makes building the most complex forms simple and enjoyable. You can read our tutorial to get started.', 'formidable' ); ?></strong>

		<div class="button-wrap">
			<div class="alignleft">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable' ) ); ?>" class="button-primary frm-button-primary">
					<?php esc_html_e( 'Create a New Form', 'formidable' ); ?>
				</a>
			</div>
			<div class="alignright">
				<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'welcome', 'knowledgebase/create-a-form/' ) ); ?>"
					class="button-secondary frm-button-secondary" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Read the Tutorial', 'formidable' ); ?>
				</a>
			</div>
			<div class="frm_clear"></div>
		</div>

	</div>

</div><!-- /.intro -->

<?php do_action( 'frm_welcome_intro_after' ); ?>

<div class="upgrade-cta upgrade">

	<div class="block">

		<div class="alignleft">
			<h2><?php esc_html_e( 'Upgrade to PRO', 'formidable' ); ?></h2>
			<ul>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Conditional Logic', 'formidable' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Multi-page Forms', 'formidable' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Calculations', 'formidable' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Mailchimp Integration', 'formidable' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'User Registration', 'formidable' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Repeater Fields', 'formidable' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Advanced Templates', 'formidable' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Review Before Submit', 'formidable' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Formidable Views', 'formidable' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Post Submissions', 'formidable' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'File Uploads', 'formidable' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Front-end Editing', 'formidable' ); ?></li>
			</ul>
		</div>

		<div class="alignright">
			<h2><span><?php esc_html_e( 'Starting At', 'formidable' ); ?></span></h2>
			<div class="price">
				<span class="amount">149</span><br/>
				<span class="term"><?php esc_html_e( 'per year', 'formidable' ); ?></span>
			</div>
			<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'welcome' ) ); ?>" rel="noopener noreferrer" target="_blank"
				class="button-primary frm-button-primary">
				<?php esc_html_e( 'Upgrade Now', 'formidable' ); ?>
			</a>
		</div>
		<div class="frm_clear"></div>
	</div>

</div>

<div class="testimonials upgrade">

	<div class="block">

		<h1><?php esc_html_e( 'Testimonials', 'formidable' ); ?></h1>

		<div class="testimonial-block">
			<p><?php esc_html_e( 'Formidable does indeed help me transform ideas from conceptual riffs to concrete experiences. In so doing, your efforts help my efforts positively impact the quality of my family\'s life, and the qualities of life for those I serve. I\'ve just upgraded to the Elite plan, in advance of one of the most ambitious projects I\'ve ever embarked on...', 'formidable' ); ?></p>
			<p><strong>Mickael Clark</strong></p>
		</div>
	</div>

</div><!-- /.testimonials -->

<div class="footer">

	<div class="block">

		<div class="button-wrap">
			<div class="alignleft">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable' ) ); ?>" class="button-primary frm-button-primary">
					<?php esc_html_e( 'Create a New Form', 'formidable' ); ?>
				</a>
			</div>
			<div class="alignright">
				<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'welcome' ) ); ?>" target="_blank" rel="noopener noreferrer"
					class="button-secondary frm-button-secondary">
					<span class="underline">
						<?php esc_html_e( 'Upgrade to Formidable Forms Pro', 'formidable' ); ?> <span class="dashicons dashicons-arrow-right"></span>
					</span>
				</a>
			</div>
			<div class="frm_clear"></div>
		</div>

	</div>

</div><!-- /.footer -->
