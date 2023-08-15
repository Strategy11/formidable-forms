<?php
/**
 * Template for stats email
 *
 * @since x.x
 * @package Formidable
 *
 * @var array $args Content args.
 */
?>

<div>
	<!-- Header section -->
	<h1 style="font-size: 2.5em; line-height: 1.2em; margin-bottom: 32px; padding-top: 70px;"><?php echo esc_html( $args['subject'] ); ?></h1>

	<div style="line-height: 1.5; color: #475467; margin-bottom: 48px;">
		<?php echo esc_html( $args['from_date'] ); ?> - <?php echo esc_html( $args['to_date'] ); ?> &middot; <?php echo esc_url( $args['site_url'] ); ?>
	</div>

	<?php echo $args['line']; ?>

	<!-- Overall section -->
	<div>
		<h2 style="font-size: 1.125em; line-height: 1.33em;">
			<img style="vertical-align: bottom;" src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/chart.png' ); ?>" alt="chart" />
			<?php esc_html_e( 'Statistics', 'formidable' ); ?>
		</h2>

		<table width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<?php
				foreach ( $args['stats'] as $key => $stat ) {
					?>
					<td>
						<?php echo esc_html( $stat['label'] ); ?><br />
						<strong><?php echo intval( $stat['count'] ); ?></strong>
					</td>
					<?php
				}
				?>
			</tr>
		</table>
	</div>

	<?php echo $args['line']; ?>

	<!-- Top forms section -->
	<div>
		<h2 style="font-size: 1.125em; line-height: 1.33;">
			<img style="vertical-align: bottom;" src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/trophy.png' ); ?>" alt="trophy" />
			<?php echo esc_html( $args['top_forms_label'] ); ?>
		</h2>

		<table width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<th align="left"><?php esc_html_e( 'Form name', 'formidable' ); ?></th>
				<th align="right"><?php esc_html_e( 'Submissions', 'formidable' ); ?></th>
			</tr>

			<?php
			foreach ( $args['top_forms'] as $index => $top_form ) {
				?>
				<tr>
					<td><?php echo intval( $index + 1 ); ?>. Form #<?php echo intval( $top_form->form_id ); // TODO ?></td>
					<td align="right"><?php echo intval( $top_form->items_count ); ?></td>
				</tr>
				<?php
			}
			?>
		</table>
	</div>

	<?php echo $args['line']; ?>

	<!-- Inbox notice section -->
	<div></div>
</div>
