<div id="frm-html-tags" class="tabs-panel">
	<ul class="frm_code_list frm-full-hover">
		<?php
		$entry_shortcodes = FrmFormsHelper::html_shortcodes();

		foreach ( $entry_shortcodes as $skey => $code ) {
			FrmFormsHelper::insert_code_html(
				array(
					'code'  => $skey,
					'label' => $code['label'],
					'class' => $code['class'],
					'title' => isset( $code['title'] ) ? $code['title'] : '',
				)
			);
		}
		?>
	</ul>
</div>
