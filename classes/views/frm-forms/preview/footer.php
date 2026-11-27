<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * This view is created to avoid deprecation warnings when previewing a form and the
 * code in this view is mostly copied from "theme-compat/footer.php"
 *
 * @since 6.x
 */
wp_footer();
?>
	<hr />
	<div id="footer" role="contentinfo">
		<p>
			<?php
			printf(
				/* translators: 1: Site name, 2: WordPress */
				esc_html__( '%1$s is proudly powered by %2$s', 'formidable' ),
				esc_html( get_bloginfo( 'name' ) ),
				'<a href="https://wordpress.org/">WordPress</a>'
			);
			?>
		</p>
	</div>
</div>
<?php wp_footer(); ?>
</body>
</html>
