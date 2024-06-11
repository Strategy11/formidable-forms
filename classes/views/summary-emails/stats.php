<?php
/**
 * Template for stats email
 *
 * @since 6.7
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
	<div style="<?php echo esc_attr( FrmEmailSummaryHelper::get_section_style( '' ) ); ?>">
		<h1 style="font-size: 2.5em; line-height: 1.2em; margin: 0 0 32px;"><?php echo esc_html( $args['subject'] ); ?></h1>

		<div style="line-height: 1.5; color: #475467;">
			<?php echo esc_html( FrmAppHelper::get_formatted_time( $args['from_date'] ) ); ?> - <?php echo esc_html( FrmAppHelper::get_formatted_time( $args['to_date'] ) ); ?> &middot; <a style="color: #475467;" href="<?php echo esc_url( $args['site_url'] ); ?>" title=""><?php echo esc_url( $args['site_url_display'] ); ?></a>
		</div>
	</div>

	<!-- Overall section -->
	<div style="<?php echo esc_attr( FrmEmailSummaryHelper::get_section_style() ); ?>">
		<?php FrmEmailSummaryHelper::section_heading_with_icon( 'chart', __( 'Statistics', 'formidable' ) ); ?>

		<table width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<?php
				foreach ( $args['stats'] as $stat ) {
					?>
					<td>
						<div style="line-height: 1.5; margin-bottom: 0.375em;"><?php echo esc_html( $stat['label'] ); ?></div>
						<div>
							<strong style="font-size: 1.5em; line-height: 1; vertical-align: text-top;">
								<?php echo isset( $stat['display'] ) ? esc_html( $stat['display'] ) : intval( $stat['count'] ); ?>
							</strong>
							<?php FrmEmailSummaryHelper::show_comparison( $stat['compare'] ); ?>
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
				style="<?php echo esc_attr( FrmEmailSummaryHelper::get_button_style( true ) ); ?>"
			><?php esc_html_e( 'Open Dashboard', 'formidable' ); ?></a>
		<?php endif; ?>
	</div>

	<?php if ( $args['top_forms'] ) : ?>
		<!-- Top forms section -->
		<div style="<?php echo esc_attr( FrmEmailSummaryHelper::get_section_style() ); ?>">
			<?php FrmEmailSummaryHelper::section_heading_with_icon( 'trophy', $args['top_forms_label'] ); ?>

			<table width="100%" cellspacing="0" cellpadding="0">
				<tr style="font-size: 0.75em; font-weight: 500; line-height: 2; text-transform: uppercase;">
					<th align="left" style=""><?php esc_html_e( 'Form name', 'formidable' ); ?></th>
					<th align="right" style=""><?php esc_html_e( 'Submissions', 'formidable' ); ?></th>
				</tr>

				<?php
				foreach ( $args['top_forms'] as $index => $top_form ) {
					?>
					<tr>
						<td align="left" style="padding: 1em 0.33em 0.28em 0"><?php echo intval( $index + 1 ); ?>. <?php echo esc_html( $top_form->form_name ); ?></td>
						<td align="right" style="padding: 1em 0 0.28em;"><?php echo intval( $top_form->items_count ); ?></td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $args['inbox_msg'] ) ) : ?>
		<!-- Inbox notice section -->
		<div style="<?php echo esc_attr( FrmEmailSummaryHelper::get_section_style() ); ?>">
			<?php FrmEmailSummaryHelper::section_heading_with_icon( 'speaker', $args['inbox_msg']['subject'] ); ?>

			<div style="line-height: 1.5;">
				<?php echo wp_kses_post( wpautop( $args['inbox_msg']['message'] ) ); ?>
			</div>

			<?php if ( ! empty( $args['inbox_msg']['cta'] ) ) : ?>
				<p>
					<?php echo wp_kses_post( FrmEmailSummaryHelper::process_inbox_cta_button( $args['inbox_msg']['cta'] ) ); ?>
				</p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $args['out_of_date_plugins'] ) ) : ?>
		<div style="<?php echo esc_attr( FrmEmailSummaryHelper::get_section_style() ); ?>">
			<?php FrmEmailSummaryHelper::section_heading_with_icon( 'notice', __( 'Out of date plugins', 'formidable' ) ); ?>

			<p style="line-height: 1.5;">
				<?php
				printf(
					// translators: the list of out-of-date plugins.
					esc_html__( 'The following plugins are out of date: %s', 'formidable' ),
					'<span style="font-style: italic;">' . esc_html( implode( ', ', $args['out_of_date_plugins'] ) ) . '</span>'
				);
				?>
			</p>

			<a
				href="<?php echo esc_url( $args['plugins_url'] ); ?>"
				style="<?php echo esc_attr( FrmEmailSummaryHelper::get_button_style( true ) ); ?>"
			><?php esc_html_e( 'Update', 'formidable' ); ?></a>
		</div>
	<?php endif; ?>
</div>
