<ul class="frm-category-tabs frm-form-setting-tabs">
	<?php foreach ( $sections as $section ) { ?>
		<li class="<?php echo esc_attr( $current === $section['anchor'] ? 'tabs active starttab' : '' ); ?>">
			<a href="#<?php echo esc_attr( $section['anchor'] ); ?>" class="<?php echo esc_attr( $section['html_class'] ); ?>"
				<?php
				if ( isset( $section['data'] ) ) {
					foreach ( $section['data'] as $data_key => $data_value ) {
						?>
						data-<?php echo esc_attr( $data_key ); ?>="<?php echo esc_attr( $data_value ); ?>"
						<?php
					}
				}
				?>
				>
				<span class="<?php echo esc_attr( $section['icon'] ); ?>" aria-hidden="true"></span>
				<?php echo esc_html( $section['name'] ); ?>
			</a>
		</li>
	<?php } ?>
</ul>
