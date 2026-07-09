<?php
/**
 * Gated Content shortcode reference section
 *
 * @package Formidable
 *
 * @since x.x
 *
 * @var int $frm_gc_action_id Gated content action post ID.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$frm_gc_shortcodes = FrmGatedContentAction::get_shortcodes( $frm_gc_action_id );
?>
<div class="frm_form_field frm_gc_shortcodes_section" style="margin-top: 20px;">
	<h3 class="frm-mb-xs"><?php esc_html_e( 'Access Link Shortcodes', 'formidable' ); ?></h3>
	<p class="frm_description">
		<?php esc_html_e( 'Add these shortcodes to a Confirmation or Send Email action to include the access link.', 'formidable' ); ?>
	</p>
	<table class="frm_gc_shortcode_table widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Shortcode', 'formidable' ); ?></th>
				<th><?php esc_html_e( 'Output', 'formidable' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $frm_gc_shortcodes as $frm_gc_shortcode ) : ?>
				<tr>
					<td>
						<code><?php echo esc_html( $frm_gc_shortcode['code'] ); ?></code>
						<button
							type="button"
							class="frm_gc_copy_shortcode button-link"
							data-frm-copy="<?php echo esc_attr( $frm_gc_shortcode['code'] ); ?>"
							data-copied-label="<?php esc_attr_e( 'Copied!', 'formidable' ); ?>"
							aria-label="<?php esc_attr_e( 'Copy shortcode', 'formidable' ); ?>"
						>
							<svg class="frmsvg frm_svg14 frm-gc-icon frm-gc-icon--copy" aria-hidden="true" focusable="false">
								<use href="#frm_clone_icon"></use>
							</svg>
							<svg class="frmsvg frm_svg14 frm-gc-icon frm-gc-icon--check" aria-hidden="true" focusable="false">
								<use href="#frm_checkmark_icon"></use>
							</svg>
						</button>
					</td>
					<td><?php echo esc_html( $frm_gc_shortcode['output'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div><!-- .frm_gc_shortcodes_section -->
<?php unset( $frm_gc_shortcodes, $frm_gc_shortcode ); ?>
