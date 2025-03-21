<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>
<script>
	// Example Square URL
	// https://connect.squareup.com/oauth2/authorize?client_id={YOUR_APP_ID}&scope=CUSTOMERS_WRITE+CUSTOMERS_READ&session=false&state=82201dd8d83d23cc8a48caf52b

	const appId = 'sandbox-sq0idb-MXl8ilzmhAgsHWKV9c6ycQ';
//	const appId = 'sq0idp-eR4XI1xgNduJAXcBvjemTg';
	const state = '<?php echo uniqid(); ?>';
	// Production uses connect.squareup.com
	// Sandbox uses connect.squareupsandbox.com
	const baseUrl = 'https://connect.squareupsandbox.com';
//	const baseUrl = 'https://connect.squareup.com';
	const url = baseUrl + '/oauth2/authorize?client_id=' + appId + '&scope=CUSTOMERS_WRITE+CUSTOMERS_READ&session=false&state=' + state;	

	alert( url );
</script>

