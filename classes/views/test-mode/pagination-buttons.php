<?php
/**
 * Test Mode Pagination Buttons Placeholder.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$number_of_pages_to_show = 4; // This is hard coded. In Lite, there is only ever a single form.
?>
<div id="frm_test_mode_pagination">
	<?php for ( $i = 1; $i <= $number_of_pages_to_show; $i++ ) : ?>
		<input type="button" class="frm_noallow <?php echo $i === 1 ? 'frm_test_mode_active_page' : ''; ?>" value="<?php echo esc_attr( $i ); ?>"/>
	<?php endfor; ?>
</div>
