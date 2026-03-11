/**
 * Initializes banner offset adjustment to prevent overlap with fixed page containers.
 *
 * @since x.x
 *
 * @return {void}
 */
export function initBannerAdjustment() {
	const banner = document.querySelector( '#wpbody-content > .frm_previous_install' );
	if ( ! banner ) {
		return;
	}

	const containers = document.querySelectorAll( '.frm_page_container' );
	if ( ! containers.length ) {
		return;
	}

	/**
	 * Applies banner offset to page containers.
	 */
	const applyBannerOffset = () => {
		containers.forEach( container => {
			container.style.setProperty( '--min-version-banner-height', `${ Math.ceil( banner.offsetHeight ) }px` );
		} );
	};

	applyBannerOffset();
	window.addEventListener( 'resize', () => applyBannerOffset() );
}
