<?php
/**
 * Entries and Spam tabs above the entries list.
 *
 * @since x.x
 *
 * @package Formidable
 *
 * @var int|string $form_id    The form being filtered, or 0 for all forms.
 * @var string     $active_tab Either "entries" or "spam".
 * @var int        $spam_count The number of spam entries.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$tabs = array(
	'entries' => array(
		'url'   => FrmSpamEntriesController::get_tab_url( false, $form_id ),
		'label' => __( 'Entries', 'formidable' ),
	),
	'spam'    => array(
		'url'   => FrmSpamEntriesController::get_tab_url( true, $form_id ),
		/* translators: %s: The number of spam entries. */
		'label' => sprintf( __( 'Spam (%s)', 'formidable' ), number_format_i18n( $spam_count ) ),
	),
);
?>
<div class="frm-payments-tabs frm-entries-tabs">
	<div class="frm-payments-tab-filler"></div>
	<?php foreach ( $tabs as $tab_key => $details ) : ?>
		<?php
		$is_active = $tab_key === $active_tab;
		$classes   = 'frm-payments-tab';

		if ( $is_active ) {
			$classes .= ' frm-active';
		}
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( $is_active ) : ?>
				<span aria-current="page"><?php echo esc_html( $details['label'] ); ?></span>
			<?php else : ?>
				<a href="<?php echo esc_url( $details['url'] ); ?>">
					<?php echo esc_html( $details['label'] ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
		$filler_params = array(
			'class' => 'frm-payments-tab-filler',
		);

		if ( 'spam' === $tab_key ) {
			$filler_params['style'] = 'flex: 1;';
		}
		?>
		<div <?php FrmAppHelper::array_to_html_params( $filler_params, true ); ?>></div>
	<?php endforeach; ?>
	<?php if ( current_user_can( 'frm_change_settings' ) ) : ?>
		<div class="frm-payments-settings-button">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-settings&t=captcha_settings' ) ); ?>" class="button button-secondary frm-button">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_small_settings_icon' ); ?>
				<?php esc_html_e( 'Spam settings', 'formidable' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
