/**
 * Give the In Theme preview a minimum gutter on each side of the viewport.
 *
 * @since x.x
 */
document.querySelectorAll( '.js-frm-in-theme-preview' ).forEach( preview => {
	const MIN_GUTTER = 16;

	const updatePadding = () => {
		const { left, right } = preview.getBoundingClientRect();
		const viewportWidth = document.documentElement.clientWidth;
		const leftPadding = Math.max( 0, MIN_GUTTER - left );
		const rightPadding = Math.max( 0, MIN_GUTTER - ( viewportWidth - right ) );

		preview.style.paddingLeft = leftPadding ? `${ leftPadding }px` : '';
		preview.style.paddingRight = rightPadding ? `${ rightPadding }px` : '';
	};

	updatePadding();
	window.addEventListener( 'resize', updatePadding );
} );
