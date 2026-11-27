<?php
/**
 * The Outline tab in the builder's side panel: search and jump to pages, sections and fields.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-builder-outline" class="tabs-panel frm-builder-outline-panel">
	<p class="frm-search">
		<label class="screen-reader-text" for="frm-builder-outline-search-input">
			<?php esc_html_e( 'Search Pages and Fields', 'formidable' ); ?>
		</label>
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_search_icon frm_svg20' ); ?>
		<input type="search" id="frm-builder-outline-search-input" class="frm-search-input" autocomplete="off" placeholder="<?php esc_attr_e( 'Search Pages and Fields', 'formidable' ); ?>" />
	</p>
	<div class="frm-builder-outline-loading frm_hidden">
		<span class="frm-wait frm_visible_spinner"></span>
		<span><?php esc_html_e( 'Loading fields…', 'formidable' ); ?></span>
	</div>
	<nav aria-label="<?php esc_attr_e( 'Form Outline', 'formidable' ); ?>">
		<ul class="frm-builder-outline-list"></ul>
	</nav>
	<div class="frm-builder-outline-empty frm_hidden">
		<strong class="frm-builder-outline-empty-title"></strong>
		<span class="frm-builder-outline-empty-text"></span>
	</div>
	<p class="screen-reader-text frm-builder-outline-status" aria-live="polite"></p>
</div>
