<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?><li class="frm-selectable <?php echo ! empty( $searchable ) ? 'frm-searchable-template' : ''; ?> <?php echo $plan_required ? 'frm-locked-template' : ''; ?>" data-rel="<?php echo esc_url( $link['url'] ); ?>" data-preview="<?php echo esc_url( 'https://sandbox.formidableforms.com/demos/wp-json/frm/v2/forms/' . $template['key'] . '?return=html' ); ?>">
	<div class="frm-featured-form">
		<?php
		if ( $render_icon ) {
			?><div>
				<?php FrmFormsHelper::template_icon( isset( $template['categories'] ) ? $template['categories'] : array() ); ?>
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
			</h3>
			<p><?php echo esc_html( $template['description'] ); ?></p>
		</div>
	</div>
</li><?php
