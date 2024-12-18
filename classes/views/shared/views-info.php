<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_wrap frm-views-info-page">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label' => __( 'Views', 'formidable' ),
			'form'  => $form,
			'close' => $form ? admin_url( 'admin.php?page=formidable&frm_action=views&form=' . $form->id ) : '',
		)
	);
	?>
	<div class="frmcenter frm-m-12" style="max-width:706px;margin:auto;padding-top:50px;">
		<h2 style="margin-bottom:15px;"><?php esc_html_e( 'Show and Edit Entries with Views', 'formidable' ); ?></h2>
		<p style="max-width:400px;margin:0 auto 32px;">
			<?php esc_html_e( 'Bring entries to the front-end of your site for full-featured applications or just to show the content.', 'formidable' ); ?>
		</p>
		<?php
		$upgrade_link_args = array(
			'medium' => 'views-info',
			'plan'   => 'view',
			'class'  => 'frm-mb-md frm-button-primary frm-gradient',
			'text'   => __( 'Get Formidable Views', 'formidable' ),
		);
		FrmAddonsController::conditional_action_button( 'views', $upgrade_link_args );
		?>

		<a href="https://formidableforms.com/demos/" class="frm-mb-md frm-ml-xs frm-button-secondary"><?php esc_html_e( 'View Demos', 'formidable' ); ?></a>

		<div class="frm-views-features frm_grid_container">
			<div class="frm4">
				<div class="frm-views-feature">
					<div class="frm-views-feature__icon" style="--gcolor-start: #7E5CF6; --gcolor-end: #4E24F2;">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19.25h5.25a2 2 0 0 0 2-2V12M12 19.25H6.75a2 2 0 0 1-2-2V12M12 19.25V4.75m0 0H6.75a2 2 0 0 0-2 2V12M12 4.75h5.25a2 2 0 0 1 2 2V12m-14.5 0h14.5"/></svg>
					</div>
					<div class="frm-views-feature__title"><?php esc_html_e( 'Grid', 'formidable' ); ?></div>
					<div class="frm-views-feature__desc">
						<?php esc_html_e( 'Create a view and write less code', 'formidable' ); ?>
					</div>
				</div>
			</div>
			<div class="frm4">
				<div class="frm-views-feature">
					<div class="frm-views-feature__icon" style="--gcolor-start: #4098FD; --gcolor-end: #056FFC;">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.75 8.75a2 2 0 0 1 2-2h10.5a2 2 0 0 1 2 2v8.5a2 2 0 0 1-2 2H6.75a2 2 0 0 1-2-2v-8.5ZM8 4.75v3.5M16 4.75v3.5M7.75 10.75h8.5"/></svg>
					</div>
					<div class="frm-views-feature__title"><?php esc_html_e( 'Calendar', 'formidable' ); ?></div>
					<div class="frm-views-feature__desc">
						<?php esc_html_e( 'Insert entries into a calendar', 'formidable' ); ?>
					</div>
				</div>
			</div>
			<div class="frm4">
				<div class="frm-views-feature">
					<div class="frm-views-feature__icon" style="--gcolor-start: #67DADC; --gcolor-end: #30C9CC;">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.75 19.25h12.5a1 1 0 0 0 1-1V5.75a1 1 0 0 0-1-1H5.75a1 1 0 0 0-1 1v12.5a1 1 0 0 0 1 1ZM19.25 9.25h-14M19.25 14.75h-14"/></svg>
					</div>
					<div class="frm-views-feature__title"><?php esc_html_e( 'Table', 'formidable' ); ?></div>
					<div class="frm-views-feature__desc">
						<?php esc_html_e( 'Insert and display entries into a table', 'formidable' ); ?>
					</div>
				</div>
			</div>
			<div class="frm4">
				<div class="frm-views-feature">
					<div class="frm-views-feature__icon" style="--gcolor-start: #5EA93D; --gcolor-end: #258602;">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.3 11c0 4-6.3 8.3-6.3 8.3S5.7 15 5.7 11c0-3.5 3-6.3 6.3-6.3s6.3 2.8 6.3 6.3Z"/><path stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.3 11a2.3 2.3 0 1 1-4.6 0 2.3 2.3 0 0 1 4.6 0Z"/></svg>
					</div>
					<div class="frm-views-feature__title"><?php esc_html_e( 'Map', 'formidable' ); ?></div>
					<div class="frm-views-feature__desc">
						<?php esc_html_e( 'Show your entries on a map', 'formidable' ); ?>
					</div>
				</div>
			</div>
			<div class="frm4">
				<div class="frm-views-feature">
					<div class="frm-views-feature__icon" style="--gcolor-start: #F4AC3C; --gcolor-end: #EF8A01;">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m15.75 8.75 3.5 3.25-3.5 3.25m-7.5-6.5L4.75 12l3.5 3.25m5-9.5-2.5 12.5"/></svg>
					</div>
					<div class="frm-views-feature__title"><?php esc_html_e( 'Classic', 'formidable' ); ?></div>
					<div class="frm-views-feature__desc">
						<?php esc_html_e( 'Create a new view from scratch', 'formidable' ); ?>
					</div>
				</div>
			</div>
			<div class="frm4">
				<div class="frm-views-feature">
					<div class="frm-views-feature__icon" style="--gcolor-start: #FF6853; --gcolor-end: #FF3119;">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.75 10.75v6.5c0 1.1.9 2 2 2h7.5a2 2 0 0 0 2-2v-4.5a2 2 0 0 0-2-2h-9.5Zm0 0v-1c0-1.1.9-2 2-2h3.06a2 2 0 0 1 1.76 1.04l1.07 1.96m-4.89-3v-1c0-1.1.9-2 2-2h3.06a2 2 0 0 1 1.76 1.04l1.07 1.96h1.61a2 2 0 0 1 2 2v4.5a2 2 0 0 1-2 2h-1"/></svg>
					</div>
					<div class="frm-views-feature__title"><?php esc_html_e( 'Ready made solution', 'formidable' ); ?></div>
					<div class="frm-views-feature__desc">
						<?php esc_html_e( 'Start from an application template', 'formidable' ); ?>
					</div>
				</div>
			</div>
		</div><!--- End .frm-views-features -->

		<div class="frm_grid_container">
			<div class="frm6">
				<div class="frm-views-learn-more">
					<h3><?php esc_html_e( 'Learn more', 'formidable' ); ?></h3>
					<p style="margin-bottom: var(--gap-md);"><?php esc_html_e( 'Bring entries to the front-end of your site for full-featured applications or just to show the content.', 'formidable' ); ?></p>
					<a href="https://formidableforms.com/features/display-form-data-views/" class="frm-button-secondary"><?php esc_html_e( 'Learn more', 'formidable' ); ?></a>
				</div>
			</div>
			<div class="frm6">
				<div class="frm-video-wrapper">
					<iframe width="843" height="200" src="https://www.youtube.com/embed/gdUt8vJ33LE" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
				</div>
			</div>
		</div>

		<hr style="margin-top:35px;border-bottom:0;border-top:1px solid var(--grey-300);" />

		<div class="frm_grid_container frm-views-reviews">
			<div class="frm4">
				<div class="frm-views-review">
					<svg xmlns="http://www.w3.org/2000/svg" width="78" height="19" fill="none"><g clip-path="url(#a)"><path fill="#00749A" d="M45 7.5h-3v.3c1 0 1 .2 1 1.4v2.2c0 1.2 0 1.4-1 1.4-.8-.1-1.3-.5-2-1.2l-.7-.9c1-.2 1.6-.8 1.6-1.6 0-1-.8-1.6-2.3-1.6h-3v.3c1 0 1.1.2 1.1 1.4v2.2c0 1.2-.1 1.4-1 1.4v.3H39v-.3c-1 0-1-.2-1-1.4v-.6h.2L40 13H45c2.4 0 3.4-1.3 3.4-2.8 0-1.6-1-2.8-3.4-2.8Zm-7 2.7V8h.6c.8 0 1.1.5 1.1 1.1 0 .6-.3 1.1-1.1 1.1h-.7Zm7 2.4c-.7 0-.8-.2-.8-1V8h.8c1.8 0 2.1 1.3 2.1 2.3 0 1-.3 2.3-2 2.3ZM26.2 10.9l1.2-3.5c.3-1 .2-1.3-.9-1.3v-.3h3.2V6c-1 0-1.3.3-1.8 1.5L26 13.4h-.2L24 8l-1.8 5.3h-.2l-1.9-5.8c-.4-1.2-.7-1.5-1.6-1.5v-.3h3.7V6c-1 0-1.3.3-.9 1.3l1.1 3.5 1.7-5.1h.4l1.6 5ZM32 13.3c-1.9 0-3.4-1.3-3.4-3s1.5-3 3.4-3c1.8 0 3.4 1.3 3.4 3s-1.6 3-3.4 3Zm0-5.5c-1.6 0-2.1 1.4-2.1 2.5s.5 2.5 2 2.5c1.6 0 2.2-1.4 2.2-2.5s-.6-2.5-2.1-2.5Z"/><path fill="#464342" d="M52.6 12.8v.3h-3.9v-.3c1.2 0 1.4-.3 1.4-2V8c0-1.7-.2-2-1.4-2v-.3h3.5c1.8 0 2.7.9 2.7 2 0 1.3-1 2.2-2.7 2.2h-1v.8c0 1.7.3 2 1.4 2Zm-.4-6.4h-1v3h1c1 0 1.4-.7 1.4-1.5s-.4-1.5-1.4-1.5ZM66.6 11.5l-.1.3c-.2.6-.4.8-1.6.8h-.2c-.9 0-1-.2-1-1.4v-.8c1.3 0 1.4.1 1.4 1h.3V8.9h-.3c0 .9-.1 1-1.4 1v-2h.9c1.2 0 1.4.3 1.5.8l.1.4h.3l-.1-1.6h-5v.3c1 0 1.1.2 1.1 1.4v2.2c0 1-.1 1.3-.9 1.4-.7-.1-1.2-.5-1.8-1.2l-.8-.9c1-.2 1.6-.8 1.6-1.6 0-1-.8-1.6-2.3-1.6h-3v.3c1 0 1.1.2 1.1 1.4v2.2c0 1.2-.1 1.4-1 1.4v.3h3.3v-.3c-1 0-1.1-.2-1.1-1.4v-.6h.3l1.9 2.3h7v-1.6h-.2Zm-9-1.3V8h.7c.8 0 1 .5 1 1.1 0 .6-.2 1.1-1 1.1h-.7ZM70 13.3c-.7 0-1.3-.3-1.5-.5-.1 0-.3.3-.3.5h-.3V11h.3c.1 1.1 1 1.8 2 1.8.5 0 .9-.3.9-.8s-.4-.8-1-1.1l-1-.5c-.7-.3-1.2-.9-1.2-1.6 0-.8.7-1.5 1.8-1.5.5 0 1 .2 1.3.4l.2-.4h.3v2h-.3c-.1-.8-.6-1.5-1.5-1.5-.4 0-.9.3-.9.7 0 .4.4.7 1.2 1l1 .5c.7.4 1 1 1 1.5 0 1-.9 1.8-2 1.8ZM75.2 13.3c-.7 0-1.3-.3-1.5-.5-.1 0-.3.3-.3.5h-.3V11h.3c.1 1.1 1 1.8 2 1.8.5 0 .9-.3.9-.8s-.4-.8-1-1.1l-1-.5c-.7-.3-1.2-.9-1.2-1.6 0-.8.7-1.5 1.8-1.5.5 0 1 .2 1.3.4l.2-.4h.3v2h-.3c-.1-.8-.6-1.5-1.5-1.5-.4 0-.9.3-.9.7 0 .4.4.7 1.2 1l1 .5c.7.4 1 1 1 1.5 0 1-.9 1.8-2 1.8ZM1.7 9.3c0 3 1.7 5.6 4.2 6.8L2.3 6.3c-.4 1-.6 2-.6 3ZM14.2 9a4 4 0 0 0-.6-2.1c-.4-.6-.8-1.1-.8-1.8 0-.6.6-1.3 1.3-1.3A7.4 7.4 0 0 0 3 5.2h2.5c.4 0 .4.5 0 .6h-.8L7.3 14 8.9 9 7.8 5.8H7c-.4 0-.4-.7 0-.7l2 .1h2c.4 0 .4.5 0 .6h-.8l2.7 8 .7-2.4c.3-1 .6-1.8.6-2.4Z"/><path fill="#464342" d="M9.3 10 7 16.5a7.5 7.5 0 0 0 4.6-.1v-.1L9.3 10ZM15.7 5.7v.8a7 7 0 0 1-.5 2.7l-2.3 6.6a7.5 7.5 0 0 0 2.8-10Z"/><path fill="#464342" d="M9.1.6a8.7 8.7 0 1 0 0 17.5A8.7 8.7 0 0 0 9.2.6Zm0 17A8.3 8.3 0 1 1 9.1 1a8.3 8.3 0 0 1 0 16.6Z"/></g><defs><clipPath id="a"><path fill="#fff" d="M.4.6h76.8V18H.4z"/></clipPath></defs></svg>
					<div class="frm-views-review__rating">
						<?php FrmAddonsHelper::show_five_star_rating( '#00749A' ); ?>
						<span><strong>4.8</strong> / 5</span>
					</div>

					<div class="frm-views-review__desc">
						<?php echo esc_html( FrmAddonsHelper::get_reviews_text( '1.200+', 'WordPress.org' ) ); ?>
					</div>
				</div><!-- End .frm-views-review -->
			</div>

			<div class="frm4">
				<div class="frm-views-review">
					<svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" fill="none"><g fill="#EC573C" clip-path="url(#a)"><path d="M15.7 5.8h-2.6c0-.4.3-.7.8-1l.5-.2c.9-.4 1.3-1 1.3-1.7 0-.5-.2-1-.6-1.2-.4-.3-.8-.5-1.4-.5-.4 0-.8.2-1.2.4a2 2 0 0 0-.7.8l.7.8c.3-.6.7-.9 1.3-.9.5 0 .8.3.8.6s-.2.5-.7.8l-.3.1c-.7.4-1.2.8-1.4 1.2-.3.4-.4 1-.4 1.7v.1h3.9v-1ZM15.4 8H11L9 11.8h4.3l2.2 3.8 2.1-3.8-2.1-3.7Z"/><path d="M9.3 14.3a5 5 0 0 1 0-10L11 .9A8.8 8.8 0 0 0 .5 9.3a8.7 8.7 0 0 0 13.9 7l-1.9-3.2a5 5 0 0 1-3.2 1.2Z"/></g><defs><clipPath id="a"><path fill="#fff" d="M.5.6H18V18H.5z"/></clipPath></defs></svg>

					<div class="frm-views-review__rating">
						<?php FrmAddonsHelper::show_five_star_rating( '#EC573C' ); ?>
						<span><strong>4.8</strong> / 5</span>
					</div>

					<div class="frm-views-review__desc">
						<?php echo esc_html( FrmAddonsHelper::get_reviews_text( 87, 'G2' ) ); ?>
					</div>
				</div><!-- End .frm-views-review -->
			</div>

			<div class="frm4">
				<div class="frm-views-review">
					<svg xmlns="http://www.w3.org/2000/svg" width="85" height="19" fill="none"><g clip-path="url(#a)"><path fill="#FF9D28" d="M1.1 7h11.5V2.7L1.2 7Z"/><path fill="#68C5ED" d="M12.7 2.7V18L18 .6l-5.4 2Z"/><path fill="#044D80" d="M12.7 7H8.3l4.3 11V7Z"/><path fill="#E54747" d="M1.1 7 9.5 10 8.3 7H1Z"/><path fill="#044D80" d="m27.6 14-.3.2-.6.3-.8.3c-1.2.2-2.4 0-3.5-.3-.6-.3-1-.7-1.5-1.2-.4-.5-.7-1-.9-1.7-.4-1.5-.4-3 0-4.5a6 6 0 0 1 1-1.7c.4-.6.9-1 1.5-1.2.6-.3 1.4-.4 2.2-.4.7 0 1.3 0 2 .3l.6.3.4.3.2.3.1.4c0 .2 0 .4-.2.6l-.6.6-.3-.4a3.2 3.2 0 0 0-3.5-.4l-.9 1c-.2.2-.3.6-.4 1.1-.2 1-.2 1.9 0 2.8 0 .4.3.8.5 1.2a2.6 2.6 0 0 0 2.3 1.1h.8l.7-.3.5-.4.3-.2.3.2.2.4.2.3v.7l-.3.3Zm8.2-1.2c0 .8 0 1.4.4 1.8l-.6.2h-.5c-.4 0-.7 0-.9-.2a1 1 0 0 1-.4-.6l-.9.6-1.4.2h-.8a2.1 2.1 0 0 1-1.4-1.2c-.2-.3-.2-.7-.2-1.1 0-.6 0-1 .4-1.3.2-.4.5-.6 1-.8.3-.2.8-.4 1.2-.4l1.3-.2h.7v-.3c0-.4-.1-.7-.4-.9l-1-.2c-.4 0-.9 0-1.2.2l-1 .5-.4-.6-.1-.5.2-.4.7-.4c.7-.3 1.4-.4 2.2-.4.5 0 1 0 1.4.2l1 .5.5 1 .2 1.2v3Zm-2-1.9h-.5l-.7.1-.7.2-.5.5a1 1 0 0 0-.2.6c0 .3 0 .6.2.8.2.2.5.3.9.3s.8-.1 1-.3l.4-.3V11Zm6-3.1c.1-.2.3-.4.7-.6a3.3 3.3 0 0 1 2.5-.1c.4 0 .7.3 1 .5l.6 1.2c.2.6.3 1.1.3 1.9a5.4 5.4 0 0 1-1 3.3l-1 .7c-.7.2-1.4.2-2 0-.3 0-.7-.2-1-.5V18h-2.3V7.1h.8l.9.1c.2.1.4.3.5.6Zm2.8 3v-1l-.3-.7a1 1 0 0 0-.6-.5l-.7-.1c-.4 0-.8 0-1 .2l-.2.6v3.4l.5.3.8.1c.5 0 .9-.2 1.1-.6.3-.4.4-1 .4-1.7Zm4 2.7-.2-.7V4.7H47.6l.4.2c.2 0 .3.2.4.3l.1.7V7h2.2v1.7h-2.2V12c0 .7.4 1 1 1l.7-.1.3-.2.2-.1.3.5.1.5-.1.4-.5.4-.7.3h-1c-.5.1-1 0-1.5-.3-.4-.3-.6-.6-.8-1Zm9.7-.2c.2 0 .5 0 .7-.2l.5-.1.4-.3.3-.3.4.6.2.6c0 .3 0 .5-.4.7a3 3 0 0 1-.9.4 6 6 0 0 1-1.5.2c-.4 0-1 0-1.4-.2-.5-.1-1-.4-1.3-.6a4.4 4.4 0 0 1-1.3-3.3l.3-1.8c.3-.5.5-.9.9-1.2a3 3 0 0 1 1.2-.7c.9-.3 1.9-.3 2.8 0 .4.2.8.5 1 .8l.5 1.1c.2.4.2.8.2 1.3V11.2h-4.7c0 .5.2 1 .5 1.5.3.3.9.5 1.6.5ZM57 10a2 2 0 0 0-.4-1.2c-.1-.3-.5-.5-1-.5-.4 0-.8.2-1 .4-.3.4-.4.8-.4 1.3H57Zm5.7-2.2.3-.3.3-.3.6-.3h1.4l.2.4.2.3-.6 1.6-.5-.4h-.7c-.3 0-.5 0-.8.2a1 1 0 0 0-.3.7V15h-2.2V7h.7c.4 0 .7 0 1 .2.2.1.3.3.4.6Zm6.1 0 .3-.3.4-.3.5-.3h1.4l.3.4.1.3c0 .5-.3 1-.5 1.6l-.5-.4H70c-.3 0-.5 0-.8.2a1 1 0 0 0-.3.7V15h-2.2V7h.7c.4 0 .6 0 1 .2.1.1.3.3.3.6Zm10.4 5c0 .8.1 1.4.4 1.8l-.6.2h-.5c-.4 0-.7 0-.9-.2a1 1 0 0 1-.4-.6l-.9.6-1.4.2h-.8a2.1 2.1 0 0 1-1.4-1.2c-.2-.3-.2-.7-.2-1.1 0-.6.1-1 .4-1.3.2-.4.5-.6 1-.8.3-.2.8-.4 1.2-.4l1.3-.2h.7v-.3c0-.4 0-.7-.3-.9a2 2 0 0 0-1-.2c-.5 0-1 0-1.3.2l-1 .5-.4-.6L73 8l.2-.4.7-.4c.7-.3 1.4-.4 2.2-.4.5 0 1 0 1.4.2l1 .5.5 1 .2 1.2v3Zm-2-1.9h-.5l-.8.1-.7.2-.5.5a1 1 0 0 0-.2.6c0 .3.1.6.3.8.1.2.4.3.9.3a2 2 0 0 0 1-.3l.3-.3.1-.3V11Z"/></g><defs><clipPath id="a"><path fill="#fff" d="M.7.6h83.5V18H.7z"/></clipPath></defs></svg>
					<div class="frm-views-review__rating">
						<?php FrmAddonsHelper::show_five_star_rating( '#FF9E28' ); ?>
						<span><strong>4.9</strong> / 5</span>
					</div>

					<div class="frm-views-review__desc">
						<?php echo esc_html( FrmAddonsHelper::get_reviews_text( 99, 'Capterra' ) ); ?>
					</div>
				</div><!-- End .frm-views-review -->
			</div>
		</div>

		<div class="frm-views-guarantee">
			<?php FrmAddonsHelper::guarantee_icon(); ?>
			<h4 style="font-weight: 600;"><?php esc_html_e( '100% No-Risk, Money Back Guarantee!', 'formidable' ); ?></h4>
			<p><?php esc_html_e( 'We\'re excited to have you experience the power of Formidable Forms. Over the next 14 days, if Formidable Forms isn’t the best fit for your project, simply reach out! We’ll happily refund 100% of your money. No questions asked.', 'formidable' ); ?></p>
		</div>
	</div>
</div>

<style>
	.frm-video-wrapper iframe {
		border-radius: 16px;
	}
	.frm-views-info-page .frm_grid_container {
		grid-gap: 16px;
	}
	.frm-views-features {
		margin-top: 18px;
		margin-bottom: 40px;
	}
	.frm-views-feature {
		box-shadow: 0 1px 3px 0 #1018281A;
		padding: 16px 24px;
		text-align: left;
		border-radius: 8px;
		background-color: #fff;
	}
	.frm-views-feature__icon {
		height: 40px;
		width: 40px;
		border-radius: 9px;
		padding: 8px;
		box-sizing: border-box;
		background: linear-gradient(var(--gcolor-start), var(--gcolor-end));
		color: #fff;
		margin-bottom: 8px;
	}
	.frm-views-feature__icon > svg {
		width: 24px;
	}
	.frm-views-feature__title {
		font-weight: 600;
		color: var(--grey-900);
		margin-bottom: 2px;
	}
	.frm-views-feature__desc {
		color: #667085;
	}
	.frm-views-learn-more {
		text-align: left;
	}
	.frm-views-learn-more h3 {
		font-size: 18px;
		font-weight: 600;
		margin-bottom: 16px;
		margin-top: 23px;
	}
	.frm-views-reviews {
		margin-top: 33px;
	}
	.frm-views-review {
		box-shadow: 0 1px 4px 1px #10182814;
		text-align: left;
		padding: 10px 15px;
		border-radius: 8px;
		background-color: #fff;
	}
	.frm-views-review__rating {
		line-height: 1;
		margin-bottom: 3px;
	}
	.frm-views-review__rating svg {
		margin-right: 3px;
	}
	.frm-views-review__rating span {
		font-size: 9px;
		vertical-align: middle;
	}
	.frm-views-review__desc {
		font-size: 9px;
	}
	.frm-views-guarantee {
		text-align: left;
		margin-top: 32px;
	}
	.frm-views-guarantee:after {
		content: '';
		display: block;
		height: 0;
		visibility: hidden;
		clear: both;
	}
	.frm-views-guarantee > svg,
	.frm-views-guarantee > img {
		float: left;
		margin-top: 9px;
		margin-right: 20px;
	}
	.frm-views-guarantee h4 {
		margin-bottom: 5px;
	}
	.frm-views-guarantee p {
		margin-top: 0;
		font-size: 12px;
	}
</style>
