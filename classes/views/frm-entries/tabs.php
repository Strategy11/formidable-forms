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
<nav class="frm-entries-tabs" aria-label="<?php esc_attr_e( 'Entry status', 'formidable' ); ?>">
	<ul class="frm-entries-tab-list">
		<?php foreach ( $tabs as $tab_key => $details ) : ?>
			<?php
			$link_attrs = array( 'class' => 'frm-entries-tab' );

			if ( $tab_key === $active_tab ) {
				$link_attrs['aria-current'] = 'page';
			}
			?>
			<li>
				<a href="<?php echo esc_url( $details['url'] ); ?>"<?php FrmAppHelper::array_to_html_params( $link_attrs, true ); ?>>
					<?php echo esc_html( $details['label'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php if ( current_user_can( 'frm_change_settings' ) ) : ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-settings&t=captcha_settings' ) ); ?>" class="frm-entries-settings frm-with-icon">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_small_settings_icon' ); ?>
			<?php esc_html_e( 'Spam settings', 'formidable' ); ?>
		</a>
	<?php endif; ?>
</nav>
