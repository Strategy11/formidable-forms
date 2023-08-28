export class FrmOverlayEvent extends EventTarget {

	constructor() {
		super();
		this.overlayItems = [];
	}

	open( overlayItem ) {
		this.overlayItems.push( overlayItem );
		this.dispatchEvent( new CustomEvent( 'openOverlay', { detail: overlayItem } ) );
	}

}