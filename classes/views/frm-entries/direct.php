<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<title><?php bloginfo( 'name' ); ?> | <?php echo esc_html( $form->name ) ?></title>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<?php wp_head(); ?>
</head>
<body class="frm_preview_page">
	<?php echo FrmFormsController::show_form( $form->id, '', true, true ); // WPCS: XSS ok. ?>
	<?php wp_footer(); ?>
</body>
</html>
