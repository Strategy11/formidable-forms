<div id="misc-publishing-actions">
	<div class="misc-pub-section">
		<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?> wp-media-buttons-icon"></span>
		<?php echo esc_html( FrmTransLiteAppHelper::formatted_amount( $payment ) ); ?>
		<a href="?page=formidable-payments&amp;action=show&amp;id=<?php echo absint( $payment->id ); ?>">
			<?php echo esc_html( $created_at ); ?>
		</a>
	</div>
</div>
