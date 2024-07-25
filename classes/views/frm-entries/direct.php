<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

add_filter(
	'document_title',
	function ( $title ) use ( $form ) {
		$form_name = '' === $form->name ? __( '(no title)', 'formidable' ) : $form->name;
		return get_bloginfo( 'name', 'display' ) . ' | ' . wp_strip_all_tags( $form_name );
	}
);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<?php
	if ( ! current_theme_supports( 'title-tag' ) ) {
		?>
		<title><?php bloginfo( 'name' ); ?> | <?php echo esc_html( $form->name ); ?></title>
		<?php
	}

	wp_head();
	FrmFormsController::maybe_load_css( $form, 1, false );
	?>
</head>
<body class="frm_preview_page">
	<?php
	if ( is_callable( 'twentynineteen_setup' ) ) {
		?>
	<div class="site-branding frm_hidden"></div>
		<?php
	}
	echo FrmFormsController::show_form( $form->id, '', 'auto', 'auto' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
	<?php wp_footer(); ?>
</body>
</html>
