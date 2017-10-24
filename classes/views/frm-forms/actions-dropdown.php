<div id="frm_actions_dropdown" class="dropdown">
	<a href="#" id="frm-navbarDrop" class="frm-dropdown-toggle" data-toggle="dropdown"><span class="dashicons dashicons-arrow-down-alt2"></span></a>
	<ul class="frm-dropdown-menu frm-on-top" role="menu" aria-labelledby="frm-navbarDrop">
		<?php foreach ( $links as $link ) { ?>
		<li>
			<a href="<?php echo esc_url( $link['url'] ); ?>" tabindex="-1" <?php
				if ( isset( $link['data'] ) ) {
					foreach ( $link['data'] as $data => $value ) {
						echo 'data-' . esc_attr( $data ) . '="' . esc_attr( $value ) . '" ';
					}
				}
				?> >
				<span class="<?php echo esc_attr( $link['icon'] ) ?>"></span>
				<span class="frm_link_label"><?php echo esc_html( $link['label'] ) ?></span>
			</a>
		</li>
		<?php } ?>
	</ul>
</div>