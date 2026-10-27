<?php
/**
 * Native solution setup links.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="wrap frm_wrap">
	<h1><?php echo esc_html( $definition['label'] ); ?></h1>
	<p><?php esc_html_e( 'Create starter forms and draft pages, then customize them with the Formidable builder, actions and Views.', 'formidable' ); ?></p>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<?php wp_nonce_field( 'frm_solution_setup' ); ?>
		<input type="hidden" name="action" value="frm_solution_setup" />
		<input type="hidden" name="solution" value="<?php echo esc_attr( $slug ); ?>" />
		<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Create missing forms and draft pages', 'formidable' ); ?></button></p>
	</form>
	<h2><?php esc_html_e( 'Forms', 'formidable' ); ?></h2>
	<ul>
	<?php
	foreach ( $definition['forms'] as $source => $configuration ) {
		$form_id = FrmSolutionFormsHelper::form( $slug, $source );

		if ( ! $form_id ) {
			continue;
		}
		?>
		<li>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable&frm_action=edit&id=' . $form_id ) ); ?>"><?php echo esc_html( $configuration['label'] ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-entries&form=' . $form_id ) ); ?>"><?php esc_html_e( 'Manage entries', 'formidable' ); ?></a>
		</li>
	<?php } ?>
	</ul>
	<h2><?php esc_html_e( 'Draft pages', 'formidable' ); ?></h2>
	<ul>
	<?php foreach ( array_merge( $state['form_pages'] ?? array(), $state['pages'] ?? array() ) as $id ) { ?>
		<li><a href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a></li>
	<?php } ?>
	</ul>
</div>
