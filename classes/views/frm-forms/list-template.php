<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$plan_required         = FrmFormsHelper::get_plan_required( $template );
$args['plan_required'] = $plan_required;

// TODO remove this
// start temporary workaround since API has not been updated
if ( isset( $template['url'] ) && in_array( 'free', $template['categories'], true ) && ! FrmAppHelper::pro_is_installed() && ! ( new FrmFormTemplateApi() )->get_free_license() ) {
	$plan_required = 'free';
}
// end temporary workaround

if ( ! empty( $template['custom'] ) ) {
	$preview_base = admin_url( 'admin-ajax.php?action=frm_forms_preview&form=' );
	$preview_end  = '';
} else {
	$preview_base = 'https://sandbox.formidableforms.com/demos/wp-json/frm/v2/forms/';
	$preview_end  = '?return=html';
}
?><li
	class="frm-selectable <?php echo ! empty( $searchable ) ? 'frm-searchable-template' : ''; ?> <?php echo ! empty( $template['custom'] ) ? 'frm-build-template' : ''; ?> <?php echo $plan_required ? 'frm-locked-template frm-' . esc_attr( $plan_required ) . '-template' : ''; ?>"
	<?php
	if ( 'free' === $plan_required ) {
		echo "data-key='" . esc_attr( $template['key'] ) . "' ";
	} elseif ( ! empty( $template['custom'] ) ) {
		echo 'data-custom="1" ';
		echo 'data-fullname="' . esc_attr( preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] ) ) . '" ';
		echo 'data-formid="' . absint( $template['id'] ) . '" ';
	} elseif ( ! $plan_required ) {
		$link = FrmFormsHelper::get_template_install_link( $template, $args );
		echo "data-rel='" . esc_url( $link['url'] ) . "' ";
	}
	?>
	data-preview="<?php echo esc_url( $preview_base . $template['key'] . $preview_end ); ?>"
>
	<div class="frm-featured-form">
		<?php
		if ( $render_icon ) {
			?><div class="frm-category-icon">
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
			<p><?php echo $template['description'] ? esc_html( $template['description'] ) : '<i>' . esc_html__( 'No description', 'formidable' ) . '</i>'; ?></p>
		</div>
	</div>
</li><?php
