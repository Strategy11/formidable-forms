<?php
/**
 * Template for stats email
 *
 * @since x.x
 * @package Formidable
 *
 * @var array $args Content args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<div style="padding-top: 1.375em;">
	<!-- Header section -->
	<div style="<?php echo esc_attr( FrmSummaryEmailsHelper::get_section_style() ); ?>">
		<h1 style="font-size: 2.5em; line-height: 1.2em; margin: 0 0 32px;"><?php echo esc_html( $args['subject'] ); ?></h1>

		<div style="line-height: 1.5; color: #475467;">
			<?php echo esc_html( FrmAppHelper::get_formatted_time( $args['from_date'] ) ); ?> - <?php echo esc_html( FrmAppHelper::get_formatted_time( $args['to_date'] ) ); ?> &middot; <a style="color: #475467;" href="<?php echo esc_url( $args['site_url'] ); ?>" title=""><?php echo esc_url( $args['site_url_display'] ); ?></a>
		</div>
	</div>

	<!-- Overall section -->
	<div style="<?php echo esc_attr( FrmSummaryEmailsHelper::get_section_style() ); ?>">
		<h2 style="<?php echo esc_attr( FrmSummaryEmailsHelper::get_heading2_style() ); ?>">
			<img style="vertical-align: bottom; margin-right: 4px;" src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/chart.png' ); ?>" alt="chart" />
			<?php esc_html_e( 'Statistics', 'formidable' ); ?>
		</h2>

		<table width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<?php
				foreach ( $args['stats'] as $key => $stat ) {
					?>
					<td>
						<div style="line-height: 1.5; margin-bottom: 0.375em;"><?php echo esc_html( $stat['label'] ); ?></div>
						<div>
							<strong style="font-size: 1.5em; line-height: 1; vertical-align: text-top;">
								<?php echo isset( $stat['display'] ) ? esc_html( $stat['display'] ) : intval( $stat['count'] ); ?>
							</strong>
							<?php FrmSummaryEmailsHelper::show_comparison( $stat['compare'] ); ?>
						</div>
					</td>
					<?php
				}
				?>
			</tr>
		</table>

		<?php if ( ! empty( $args['dashboard_url'] ) ) : ?>
			<a
				href="<?php echo esc_url( $args['dashboard_url'] ); ?>"
				style="display: block; font-size: 0.875em; line-height: 2.4; border-radius: 1.2em; border: 1px solid #d0d5dd; font-weight: 600; text-align: center; margin-top: 2.6em; color: #1d2939; text-decoration: none;"
			><?php esc_html_e( 'Open Dashboard', 'formidable' ); ?></a>
		<?php endif; ?>
	</div>

	<!-- Top forms section -->
	<div style="<?php echo esc_attr( FrmSummaryEmailsHelper::get_section_style() ); ?>">
		<h2 style="<?php echo esc_attr( FrmSummaryEmailsHelper::get_heading2_style() ); ?>">
			<img style="vertical-align: bottom;" src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/trophy.png' ); ?>" alt="trophy" />
			<?php echo esc_html( $args['top_forms_label'] ); ?>
		</h2>

		<table width="100%" cellspacing="0" cellpadding="0">
			<tr style="font-size: 0.75em; font-weight: 500; line-height: 2; text-transform: uppercase;">
				<th align="left" style=""><?php esc_html_e( 'Form name', 'formidable' ); ?></th>
				<th align="right" style=""><?php esc_html_e( 'Submissions', 'formidable' ); ?></th>
			</tr>

			<?php
			foreach ( $args['top_forms'] as $index => $top_form ) {
				?>
				<tr>
					<td align="left" style="padding: 1em 0.33em 0.28em"><?php echo intval( $index + 1 ); ?>. <?php echo esc_html( $top_form->form_name ); ?></td>
					<td align="right" style="padding: 1em 0 0.28em;"><?php echo intval( $top_form->items_count ); ?></td>
				</tr>
				<?php
			}
			?>
		</table>
	</div>

	<?php if ( ! empty( $args['inbox_msg'] ) ) : ?>
		<!-- Inbox notice section -->
		<div style="<?php echo esc_attr( FrmSummaryEmailsHelper::get_section_style() ); ?>">
			<h2 style="<?php echo esc_attr( FrmSummaryEmailsHelper::get_heading2_style() ); ?>">
				<img style="vertical-align: bottom;" src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/speaker.png' ); ?>" alt="speaker" />
				<?php echo esc_html( $args['inbox_msg']['subject'] ); ?>
			</h2>

			<div style="line-height: 1.5;">
				<?php echo wp_kses_post( wpautop( $args['inbox_msg']['message'] ) ); ?>
			</div>

			<?php if ( ! empty( $args['inbox_msg']['cta'] ) ) : ?>
				<p>
					<?php echo wp_kses_post( FrmSummaryEmailsHelper::process_inbox_cta_button( $args['inbox_msg']['cta'] ) ); ?>
				</p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $args['out_of_date_plugins'] ) ) : ?>
		<div style="<?php echo esc_attr( FrmSummaryEmailsHelper::get_section_style() ); ?>">
			<h2 style="<?php echo esc_attr( FrmSummaryEmailsHelper::get_heading2_style() ); ?>">
				<img style="vertical-align: bottom;" src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/notice.png' ); ?>" alt="speaker" />
				<?php esc_html_e( 'Out of date plugins', 'formidable' ); ?>
			</h2>

			<p style="line-height: 1.5;">
				<?php
				printf(
					// translators: the list of out-of-date plugins.
					esc_html__( 'Following plugins are out of date: %s', 'formidable' ),
					'<span style="font-style: italic;">' . esc_html( implode( ', ', $args['out_of_date_plugins'] ) ) . '</span>'
				)
				?>
			</p>

			<a
				href="<?php echo esc_url( $args['plugins_url'] ); ?>"
				style="display: block; font-size: 0.875em; line-height: 2.4; border-radius: 1.2em; border: 1px solid #d0d5dd; font-weight: 600; text-align: center; margin-top: 2.6em; color: #1d2939; text-decoration: none;"
			><?php esc_html_e( 'Update', 'formidable' ); ?></a>
		</div>
	<?php endif; ?>
</div>
