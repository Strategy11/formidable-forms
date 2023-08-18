<?php
/**
 * Template for stats email
 *
 * @since x.x
 * @package Formidable
 *
 * @var array $args Content args.
 */

$border_color = '#eaecf0';
$section_style = 'padding: 3em 4.375em; border-bottom: 1px solid ' . $border_color;
$heading2_style = 'font-size: 1.125em; line-height: 1.33em; margin: 0 0 1.33em;';
?>

<div style="padding-top: 1.375em;">
	<!-- Header section -->
	<div style="<?php echo esc_attr( $section_style ); ?>">
		<h1 style="font-size: 2.5em; line-height: 1.2em; margin: 0 0 32px;"><?php echo esc_html( $args['subject'] ); ?></h1>

		<div style="line-height: 1.5; color: #475467;">
			<?php echo esc_html( $args['from_date'] ); ?> - <?php echo esc_html( $args['to_date'] ); ?> &middot; <?php echo esc_url( $args['site_url'] ); ?>
		</div>
	</div>

	<!-- Overall section -->
	<div style="<?php echo esc_attr( $section_style ); ?>">
		<h2 style="<?php echo esc_attr( $heading2_style ); ?>">
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
						<strong style="font-size: 1.5em; line-height: 1;"><?php echo intval( $stat['count'] ); ?></strong>
					</td>
					<?php
				}
				?>
				<?php
				foreach ( $args['stats'] as $key => $stat ) {
					?>
					<td>
						<div style="line-height: 1.5; margin-bottom: 0.375em;"><?php echo esc_html( $stat['label'] ); ?></div>
						<strong style="font-size: 1.5em; line-height: 1;"><?php echo intval( $stat['count'] ); ?></strong>
					</td>
					<?php
				}
				?>
				<?php
				foreach ( $args['stats'] as $key => $stat ) {
					?>
					<td>
						<div style="line-height: 1.5; margin-bottom: 0.375em;"><?php echo esc_html( $stat['label'] ); ?></div>
						<strong style="font-size: 1.5em; line-height: 1;"><?php echo intval( $stat['count'] ); ?></strong>
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
			><?php esc_html_e( 'Open Dashboard' ); ?></a>
		<?php endif; ?>
	</div>

	<!-- Top forms section -->
	<div style="<?php echo esc_attr( $section_style ); ?>">
		<h2 style="<?php echo esc_attr( $heading2_style ); ?>">
			<img style="vertical-align: bottom;" src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/trophy.png' ); ?>" alt="trophy" />
			<?php echo esc_html( $args['top_forms_label'] ); ?>
		</h2>

		<table width="100%" cellspacing="0" cellpadding="0">
			<tr style="font-size: 0.75em; font-weight: 500; line-height: 2; text-transform: uppercase;">
				<th align="left" style="padding: 0.67em 0;"><?php esc_html_e( 'Form name', 'formidable' ); ?></th>
				<th align="right" style="padding: 0.67em 0;"><?php esc_html_e( 'Submissions', 'formidable' ); ?></th>
			</tr>

			<?php
			foreach ( $args['top_forms'] as $index => $top_form ) {
				?>
				<tr>
					<td align="left" style="padding: 0.5em 0;"><?php echo intval( $index + 1 ); ?>. Form #<?php echo intval( $top_form->form_id ); // TODO ?></td>
					<td align="right" style="padding: 0.5em 0;"><?php echo intval( $top_form->items_count ); ?></td>
				</tr>
				<?php
			}
			?>
		</table>
	</div>

	<!-- Inbox notice section -->
	<div></div>
</div>
