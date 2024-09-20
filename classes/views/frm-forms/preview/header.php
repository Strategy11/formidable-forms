<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * This view is created to avoid deprecation warnings when previewing a form and the
 * code in this view is mostly copied from "theme-compat/header.php"
 *
 * @since 6.x
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<link rel="profile" href="https://gmpg.org/xfn/11" />
<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />

<title><?php echo wp_get_document_title(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></title>

<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_url' ); ?>" type="text/css" media="screen" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

<?php
wp_head();
?>
</head>
<body <?php body_class(); ?>>
<div id="page">
	<div id="header" role="banner">
		<div id="headerimg">
			<h1><a href="<?php echo esc_url( home_url() ); ?>/"><?php bloginfo( 'name' ); ?></a></h1>
			<div class="description"><?php bloginfo( 'description' ); ?></div>
		</div>
	</div>
	<hr />
