<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_wrap">
	<?php
	if ( method_exists( 'FrmAppHelper', 'maybe_autocomplete_options' ) ) {
		$posts = get_posts(
			array(
				'posts_per_page' => -1,
				'post_type'      => 'any',
				'order'          => 'DESC',
			)
		);

		$source = array();
		foreach ( $posts as $post ) {
			$source[ $post->ID ] = $post->post_title;
		}
		FrmAppHelper::maybe_autocomplete_options(
			array(
				'source'         => $source,
				'selected'       => 1,
				'dropdown_limit' => 30,
			)
		);
	}//end if

	FrmAppHelper::get_admin_header(
		array(
			'label' => __( 'Views', 'formidable' ),
			'form'  => $form,
			'close' => $form ? admin_url( 'admin.php?page=formidable&frm_action=views&form=' . $form->id ) : '',
		)
	);
	?>
	<div class="frmcenter frm-m-12">
		<h2><?php esc_html_e( 'Show and Edit Entries with Views', 'formidable' ); ?></h2>
		<p style="max-width:400px;margin:20px auto">
			<?php esc_html_e( 'Bring entries to the front-end of your site for full-featured applications or just to show the content.', 'formidable' ); ?>
		</p>
		<?php
		$upgrade_link_args = array(
			'medium' => 'views-info',
			'plan'   => 'view',
			'class'  => 'frm-mb-md frm-button-primary',
		);
		FrmAddonsController::conditional_action_button( 'views', $upgrade_link_args );
		?>
		<div class="frm-video-wrapper">
			<iframe width="843" height="474" src="https://www.youtube.com/embed/pmYbQ79wonQ" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		</div>
	</div>
</div>
