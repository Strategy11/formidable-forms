<?php
/**
 * Form Templates - Template.
 *
 * @package   Strategy11/FormidableForms
 * @copyright 2010 Formidable Forms
 * @license   GNU General Public License, version 2
 * @link      https://formidableforms.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$is_featured_template = ! empty( $template['is_featured'] );
$is_favorite_template = ! empty( $template['is_favorite'] );
$is_custom_template   = ! empty( $template['is_custom'] );

$plan_required = FrmFormsHelper::get_plan_required( $template );

// Remove "Form Template" string from `$template['name']` and assign to a variable.
$template_name = preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] );

$class_names      = array( 'frm-form-templates-item' );
$use_template_url = '#';

/**
 * Set Attributes.
 */
$attributes                    = array();
$attributes['data-id']         = $template['id'];
$attributes['frm-search-text'] = strtolower( $template_name );

// Set 'data-categories' attribute.
if ( ! empty( $template['category_slugs'] ) ) {
	$attributes['data-categories'] = implode( ',', $template['category_slugs'] );
}

if ( $is_featured_template ) {
	$class_names[] = 'frm-form-templates-featured-item';
}

if ( $is_favorite_template ) {
	$class_names[] = 'frm-form-templates-favorite-item';
}

if ( $is_custom_template ) {
	$class_names[]     = 'frm-form-templates-custom-item';
	$use_template_url = esc_url( $template['url'] );
}

if ( $plan_required ) {
	$required_plan_slug = sanitize_title( $plan_required );
	$class_names[]      = 'frm-form-templates-locked-item frm-' . esc_attr( $required_plan_slug ) . '-template';
	// Set 'data-required-plan' attribute.
	$attributes['data-required-plan'] = $expired && 'free' !== $required_plan_slug ? 'renew' : $required_plan_slug;
	if ( 'free' === $required_plan_slug ) {
		// Set 'data-key' attribute for free templates.
		$attributes['data-key'] = $template['key'];
	}
} else {
	$link             = FrmFormsHelper::get_template_install_link( $template, compact( 'pricing', 'license_type' ) );
	$use_template_url = esc_url( $link['url'] );
}

// Set 'class' attribute.
$attributes['class'] = implode( ' ', $class_names );
?>
<li <?php FrmAppHelper::array_to_html_params( $attributes, true ); ?>>
	<?php if ( $is_featured_template ) : ?>
		<div class="frm-form-templates-item-icon">
			<?php FrmFormsHelper::template_icon( $template['categories'] ); ?>
		</div>
	<?php endif; ?>

	<div class="frm-form-templates-item-body">
		<h3 class="frm-form-templates-item-title">
			<div class="frm-form-templates-item-title-text">
				<?php if ( $plan_required ) { ?>
					<span class="frm-form-templates-item-lock-icon">
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon', array( 'aria-label' => __( 'Lock icon', 'formidable' ) ) ); ?>
					</span>
				<?php } ?>

				<span class="frm-form-template-name"><?php echo esc_html( $template_name ); ?></span>
			</div>

			<a href="#" class="frm-form-templates-item-favorite-button" role="button" aria-label="<?php esc_attr_e( 'Add to favorite button', 'formidable' ); ?>">
				<?php
				$favorite_button_icon = $is_favorite_template ? 'frm_heart_solid_icon' : 'frm_heart_icon';
				FrmAppHelper::icon_by_class( 'frmfont ' . $favorite_button_icon );
				?>
			</a>
		</h3>

		<div class="frm-form-templates-item-content">
			<div class="frm-form-templates-item-buttons">
				<a href="<?php echo esc_url( $template['link'] ); ?>" class="button button-secondary frm-button-secondary" target="_blank" role="button">
					<?php esc_html_e( 'View Demo', 'formidable' ); ?>
				</a>
				<a href="<?php echo esc_url( $use_template_url ); ?>" class="button button-primary frm-button-primary frm-form-templates-use-template-button" role="button">
					<?php esc_html_e( 'Use Template', 'formidable' ); ?>
				</a>
			</div>

			<p class="frm-form-templates-item-description">
				<?php
				if ( $template['description'] ) {
					echo FrmAppHelper::kses( $template['description'], 'all' ); // phpcs: ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					echo '<i>' . esc_html__( 'No description', 'formidable' ) . '</i>';
				}
				?>
			</p>
		</div>
	</div>
</li>
