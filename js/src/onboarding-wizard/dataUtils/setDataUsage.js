/**
 * Set steps completed data.
 *
 * @return {void}
 */
function setDataUsage() {
	const { doJsonPost } = frmDom.ajax;
	console.log( 'setDataUsage' );
	doJsonPost( 'onboarding_setup_usage_data', new FormData() );
}

export default setDataUsage;
