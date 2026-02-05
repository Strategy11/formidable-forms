<?php
/**
 * Solutions header view
 *
 * @since 4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @var array $size Logo dimensions (height and width)
 */
?>
<section class="top">
	<div class="frm-smtp-logos">
		<?php FrmAppHelper::show_logo( $size ); ?>
		<?php
		FrmAppHelper::icon_by_class(
			'frmfont frm_arrow_right_icon',
			array(
				'aria-label' => 'Install',
				'style'      => 'width:30px;height:30px;margin:0 35px;',
			)
		);
		FrmAppHelper::icon_by_class(
			'frmfont frm_wordpress_icon',
			array(
				'aria-label' => 'WordPress',
				'style'      => 'width:90px;height:90px;',
			)
		);
		?>
	</div>
	<h1><?php echo esc_html( $this->page_title() ); ?></h1>
	<p><?php echo esc_html( $this->page_description() ); ?></p>
</section>
