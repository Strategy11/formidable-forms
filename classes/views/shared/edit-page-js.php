<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<script>
( function() {
	const handleDomReady = () => {
		if ( 'undefined' === typeof wp || 'undefined' === typeof wp.data || 'function' !== typeof wp.data.subscribe ) {
			return;
		}

		const closeListener = wp.data.subscribe(
			() => {
				const editor = wp.data.select( 'core/editor' );

				if ( 'function' !== typeof editor.__unstableIsEditorReady ) {
					closeListener();
					return;
				}

				const isReady = editor.__unstableIsEditorReady();
				if ( isReady ) {
					closeListener();
					requestAnimationFrame( () => injectFormidableBlock() );
				}
			}
		);
	}

	document.addEventListener( 'DOMContentLoaded', handleDomReady );

	const injectFormidableBlock = () => {
		insertedBlock = wp.blocks.createBlock(
			'<?php echo esc_js( $block_name ); ?>',
			{ <?php echo esc_js( $object_key ); ?>: '<?php echo absint( $object_id ); ?>' }
		);

		const getBlocks = () => wp.data.select( 'core/editor' ).getBlocks();
		const blockList = getBlocks();

		const closeListener = wp.data.subscribe(
			() => {
				const currentBlocks = getBlocks();
				if ( currentBlocks === blockList ) {
					return;
				}

				closeListener();
				const block = currentBlocks[ currentBlocks.length - 1 ];
				setTimeout(
					() => document.getElementById( 'block-' + block.clientId ).scrollIntoView({ behavior: 'smooth' }),
					1
				);
			}
		);

		wp.data.dispatch( 'core/block-editor' ).insertBlocks( insertedBlock );
	};
}() );
</script>
