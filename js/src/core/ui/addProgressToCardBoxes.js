/**
 * Internal Dependencies
 */
const { span } = window.frmDom;

/**
 * Adds a progress bar to each card box element to visually indicate its position in the sequence.
 *
 * @param {Element[]} cardBoxes Collection of card box elements to enhance with progress bars.
 * @return {void}
 */
function addProgressToCardBoxes( cardBoxes ) {
	if ( ! Array.isArray( cardBoxes ) || ! cardBoxes.length ) {
		console.warn( 'addProgressToCardBoxes: Expected a non-empty array of cardBoxes.' );
		return;
	}

	cardBoxes.forEach( ( element, index ) => {
		// Exclude cards that either don't require a progress bar or already include one
		if ( ! element.classList.contains( 'frm-has-progress-bar' ) || element.querySelector( '.frm-card-box-progress-bar' ) ) {
			return;
		}

		const progressBar = span();
		const widthPercentage = ( ( index + 1 ) / cardBoxes.length ) * 100;
		progressBar.style.width = `${widthPercentage}%`;

		const progressBarContainer = span({
			className: 'frm-card-box-progress-bar',
			child: progressBar
		});
		element.insertAdjacentElement( 'afterbegin', progressBarContainer );
	});
}

export default addProgressToCardBoxes;
