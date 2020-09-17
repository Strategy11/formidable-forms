<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! empty( $template['custom'] ) ) {
	$preview_base = admin_url( 'admin-ajax.php?action=frm_forms_preview&form=' );
	$preview_end  = '';
} else {
	$preview_base = 'https://sandbox.formidableforms.com/demos/wp-json/frm/v2/forms/';
	$preview_end  = '?return=html';
}
?><li class="frm-selectable <?php echo ! empty( $searchable ) ? 'frm-searchable-template' : ''; ?> <?php echo $plan_required ? 'frm-locked-template' : ''; ?>" <?php echo ( ! empty( $template['custom'] ) ? "data-href='" . esc_url( $template['url'] ) . "'" : "data-rel='" . esc_url( $link['url'] ) . "'" ); ?> data-preview="<?php echo esc_url( $preview_base . $template['key'] . $preview_end ); ?>">
	<div class="frm-featured-form">
		<?php
		if ( $render_icon ) {
			?><div>
				<?php FrmFormsHelper::template_icon( $template['categories'] ); ?>
			</div><?php
		}
		?><div>
			<h3>
				<?php if ( $plan_required ) { ?>
					<svg class="frmsvg">
						<use xlink:href="#frm_lock_simple"></use>
					</svg>
				<?php } ?>
				<?php echo esc_html( preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] ) ); ?>
				<?php if ( $plan_required ) { ?>
					<span class="frm-plan-required-tag">
						<?php
						echo esc_html( $plan_required );

						if ( $plan_required !== 'Elite' ) {
							echo esc_html( ' +' );
						}
						?>
					</span>
				<?php } ?>
			</h3>
			<p><?php echo esc_html( $template['description'] ); ?></p>
		</div>
	</div>
</li><?php
