<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$plan_required          = FrmFormsHelper::get_plan_required( $template );
$args['plan_required']  = $plan_required;
$stripped_template_name = preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] );

if ( ! empty( $template['custom'] ) ) {
	$preview_base = admin_url( 'admin-ajax.php?action=frm_forms_preview&form=' );
	$preview_end  = '';
} else {
	$preview_base = 'https://sandbox.formidableforms.com/demos/wp-json/frm/v2/forms/';
	$preview_end  = '?return=html';
}
?><li
	class="frm-selectable <?php echo ! empty( $searchable ) ? 'frm-searchable-template' : ''; ?> <?php echo $plan_required ? 'frm-locked-template frm-' . esc_attr( $plan_required ) . '-template' : ''; ?>"
	aria-label="<?php echo esc_attr( $stripped_template_name ); ?>"
	<?php
	if ( 'free' === $plan_required ) {
		echo 'data-key="' . esc_attr( $template['key'] ) . '" ';
	} elseif ( ! empty( $template['custom'] ) ) {
		echo 'data-formid="' . absint( $template['id'] ) . '" ';
		echo 'data-custom="1" ';
		echo 'data-href="' . esc_url( admin_url( '?page=formidable&frm_action=duplicate&id=' . $template['id'] ) ) . '" ';
	} elseif ( ! $plan_required ) {
		$link = FrmFormsHelper::get_template_install_link( $template, $args );
		echo 'data-rel="' . esc_url( $link['url'] ) . '" ';
	}
	?>
	data-preview="<?php echo esc_url( $preview_base . $template['key'] . $preview_end ); ?>"
>
	<div class="frm-featured-form">
		<?php
		if ( $render_icon ) {
			?><div class="frm-category-icon" role="button">
				<?php FrmFormsHelper::template_icon( $template['categories'] ); ?>
			</div><?php
		}
		?><div>
			<h3 role="button">
				<?php if ( $plan_required ) { ?>
					<svg class="frmsvg">
						<use xlink:href="#frm_lock_simple"></use>
					</svg>
				<?php } ?>
				<?php echo esc_html( $stripped_template_name ); ?>
				<?php if ( $plan_required ) { ?>
					<span class="frm-plan-required-tag">
						<?php
						echo esc_html( $plan_required );
						if ( ! in_array( $plan_required, array( 'free', 'Elite' ), true ) ) {
							echo esc_html( ' +' );
						}
						?>
					</span>
				<?php } ?>
			</h3>
			<p role="button"><?php echo $template['description'] ? esc_html( $template['description'] ) : '<i>' . esc_html__( 'No description', 'formidable' ) . '</i>'; ?></p>
			<?php
			$template_is_new = strtotime( $template['released'] ) > strtotime( '-10 days' );
			if ( $template_is_new && empty( $template['custom'] ) ) {
				?><div class="frm_ribbon">
					<span>New</span>
				</div><?php
			}
			?>
		</div>
	</div>
</li><?php
