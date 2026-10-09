<?php
/**
 * The editable style class, shown in both the quick settings and the advanced settings.
 *
 * Only the quick settings copy has a name, so the class is submitted once. The other copy
 * mirrors it while typing (see initStyleClassRename() in js/src/admin/styles.js).
 *
 * @package Formidable
 * @since x.x
 *
 * @var WP_Post $style      The style being edited.
 * @var string  $input_id   The id of the class input.
 * @var string  $input_name The name of the class input. Empty when the input is a mirror.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$description_id = $input_id . '_description';
?>
<div class="frm-style-class-editor">
	<span class="frm-style-class-prefix" aria-hidden="true">.frm_style_</span>
	<input
		type="text"
		id="<?php echo esc_attr( $input_id ); ?>"
		class="frm-style-class-input"
		<?php if ( $input_name ) { ?>
		name="<?php echo esc_attr( $input_name ); ?>"
		<?php } ?>
		value="<?php
			// skipcq: PHP-E1002
			echo esc_attr( $style->post_name );
		?>"
		autocomplete="off"
		spellcheck="false"
		aria-describedby="<?php echo esc_attr( $description_id ); ?>" />
	<button
		type="button"
		class="frm-style-class-copy"
		aria-label="<?php esc_attr_e( 'Copy style class', 'formidable' ); ?>"
		title="<?php esc_attr_e( 'Copy class', 'formidable' ); ?>"
		data-frm-copied-tip="<?php esc_attr_e( 'Class copied', 'formidable' ); ?>">
		<?php FrmAppHelper::icon_by_class( 'frmfont frm-copy-icon' ); ?>
	</button>
</div>
<p id="<?php echo esc_attr( $description_id ); ?>" class="frm-style-class-description frm_hidden"><?php
	esc_html_e( 'Renaming changes the class on your forms. Update any custom CSS that targets the old class.', 'formidable' );
?></p>
