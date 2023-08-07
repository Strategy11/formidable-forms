<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Remove "Form Template" string from `$template['name']` and assign to a variable.
$template_name = preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] );

// [comment-here].
$plan_required = FrmFormsHelper::get_plan_required( $template );
$is_free       = $plan_required === 'free';

// Set Attributes.
$attributes                  = array();
$attributes['data-template'] = sanitize_title( $template_name );
$attributes['class']         = 'frm-form-templates-item frm4';
$attributes['aria-label']    = $template_name;

// [comment-here].
if ( $is_free ) {
	$attributes['data-key'] = $template['key'];
} elseif ( ! $plan_required ) {
	$link = FrmFormsHelper::get_template_install_link( $template, array( 'plan_required' => $plan_required ) );
	$attributes['data-rel'] = esc_url( $link['url'] );
}

// [comment-here].
if ( ! empty( $template['custom'] ) ) {
	$attributes['data-formid'] = absint( $template['id'] );
	$attributes['data-custom'] = '1';
	$attributes['data-href'] = esc_url( $template['url'] );
}
?>

<li <?php FrmAppHelper::array_to_html_params( $attributes, true ); ?>>
	<?php if ( $render_icon ) : ?>
		<div class="frm-form-templates-item-icon">
			<?php
			FrmFormsHelper::template_icon(
				$template['categories'],
				array(
					'html' => 'div',
					'bg' => true,
				)
			);
			?>
		</div><!-- .frm-form-templates-item-icon -->
	<?php endif; ?>

	<div class="frm-form-templates-item-details">
		<h3 class="frm-form-templates-item-title">
			<?php
			if ( ! $is_free ) {
				FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon', array( 'aria-label' => _x( 'Create', 'form templates: create a blank form', 'formidable' ) ) );
			}
			?>

			<span><?php echo esc_html( $template_name ); ?></span>

			<?php
			if ( empty( $template['custom'] ) && ! empty( $template['is_new'] ) ) {
				FrmAppHelper::show_pill_text();
			}
			?>
		</h3><!-- .frm-form-templates-item-title -->

		<p class="frm-form-templates-item-description"><?php echo $template['description'] ? esc_html( $template['description'] ) : '<i>' . esc_html__( 'No description', 'formidable' ) . '</i>'; ?></p>
	</div><!-- .frm-form-templates-item-details -->
</li><!-- .frm-form-templates-item -->
