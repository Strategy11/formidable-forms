<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$is_featured_template = ! empty( $template['is_featured'] );
$is_favorite_template = ! empty( $template['is_favorite'] );
$is_custom_template   = ! empty( $template['is_custom'] );

$plan_required = FrmFormsHelper::get_plan_required( $template );

// Remove "Form Template" string from `$template['name']` and assign to a variable.
$template_name = preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] );

/**
 * Set Attributes.
 */
$attributes               = array();
$attributes['class']      = 'frm-form-templates-item frm4';
$attributes['data-id']    = $template['id'];
$attributes['aria-label'] = $template_name;

// Set data categories attribute.
if ( ! empty( $template['category_slugs'] ) ) {
	$attributes['data-categories'] = implode( ',', $template['category_slugs'] );
}

// Set class attribute.
if ( $is_featured_template ) {
	$attributes['class'] .= ' frm-form-templates-featured-item';
}
if ( $is_favorite_template ) {
	$attributes['class'] .= ' frm-form-templates-favorite-item';
}
if ( $is_custom_template ) {
	$attributes['class']       .= ' frm-form-templates-custom-item';
	$attributes['data-formid'] = absint( $template['id'] );
	$attributes['data-custom'] = '1';
	$attributes['data-href']   = esc_url( $template['url'] );
}
if ( $plan_required ) {
	$attributes['class'] .= ' frm-form-templates-locked-item frm-' . esc_attr( $plan_required ) . '-template';
}
?>

<li <?php FrmAppHelper::array_to_html_params( $attributes, true ); ?>>
	<!-- Featured Template Icon -->
	<?php if ( $is_featured_template ) : ?>
		<div class="frm-form-templates-item-icon">
			<?php FrmFormTemplatesHelper::template_icon( $template['categories'] ); ?>
		</div><!-- .frm-form-templates-item-icon -->
	<?php endif; ?>

	<div class="frm-form-templates-item-body">
		<!-- Template Title -->
		<h3 class="frm-form-templates-item-title">
			<div class="frm-form-templates-item-title-text">
				<!-- Lock Icon -->
				<?php if ( $plan_required && 'free' !== $plan_required ) { ?>
					<span class="frm-form-templates-item-lock-icon">
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon', array( 'aria-label' => __( 'Lock icon', 'formidable' ) ) ); ?>
					</span>
				<?php } ?>

				<!-- Template Title Text -->
				<span><?php echo esc_html( $template_name ); ?></span>
			</div><!-- .frm-form-templates-item-title-text -->

			<!-- Add to Favorite Button -->
			<a href="#" class="frm-form-templates-item-favorite-button" role="button" aria-label="<?php esc_attr_e( 'Add to favorite button', 'formidable' ); ?>">
				<?php
				$favorite_button_icon = $is_favorite_template ? 'frm_heart_solid_icon' : 'frm_heart_icon';
				FrmAppHelper::icon_by_class( 'frmfont ' . $favorite_button_icon );
				?>
			</a><!-- .frm-form-templates-item-favorite-button -->
		</h3><!-- .frm-form-templates-item-title -->

		<div class="frm-form-templates-item-content">
			<!-- Action Buttons -->
			<div class="frm-form-templates-item-buttons">
				<a href="<?php echo esc_url( $template['link'] ); ?>" class="button button-secondary frm-button-secondary" target="_blank" role="button">
					<?php esc_html_e( 'View Demo', 'formidable' ); ?>
				</a>
				<a href="<?php echo esc_url( $template['url'] ); ?>" class="button button-primary frm-button-primary" role="button">
					<?php esc_html_e( 'Use Template', 'formidable' ); ?>
				</a>
			</div><!-- .frm-form-templates-item-buttons -->

			<!-- Template Description -->
			<p class="frm-form-templates-item-description"><?php echo $template['description'] ? esc_html( $template['description'] ) : '<i>' . esc_html__( 'No description', 'formidable' ) . '</i>'; ?></p>
		</div><!-- .frm-form-templates-item-content -->
	</div><!-- .frm-form-templates-item-body -->
</li><!-- .frm-form-templates-item -->
