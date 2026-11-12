/* global frmAdminBuild, frm_admin_js */

/**
 * Lite's original confirmation field settings code, for Pro versions that do not register their own.
 * The code was moved here from admin.js.
 *
 * @since x.x
 */

const frmAdminJs = frm_admin_js;

//Fade confirmation field and validation option in or out
function addConf() {
	/*jshint validthis:true */
	const fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
	const val = jQuery( this ).val();
	const $thisField = jQuery( document.getElementById( `frm_field_id_${ fieldId }` ) );

	frmAdminBuild.toggleValidationBox( val !== '', `.frm_conf_details${ fieldId }` );

	if ( val !== '' ) {
		//Add default validation message if empty
		const valMsg = jQuery( `.frm_validation_box .frm_conf_details${ fieldId } input` );
		if ( valMsg.val() === '' ) {
			valMsg.val( frmAdminJs.default_conf );
		}

		setConfirmationFieldDescriptions( fieldId );

		//Add or remove class for confirmation field styling
		if ( val === 'inline' ) {
			$thisField.removeClass( 'frm_conf_below' ).addClass( 'frm_conf_inline' );
		} else if ( val === 'below' ) {
			$thisField.removeClass( 'frm_conf_inline' ).addClass( 'frm_conf_below' );
		}
		jQuery( `.frm-conf-box-${ fieldId }` ).removeClass( 'frm_hidden' );
	} else {
		jQuery( `.frm-conf-box-${ fieldId }` ).addClass( 'frm_hidden' );
		setTimeout( function() {
			$thisField.removeClass( 'frm_conf_inline frm_conf_below' );
		}, 200 );
	}
}

function setConfirmationFieldDescriptions( fieldId ) {
	const fieldType = document.getElementsByName( `field_options[type_${ fieldId }]` )[ 0 ].value;

	const fieldDescription = document.getElementById( `field_description_${ fieldId }` );
	const hiddenDescName = `field_options[description_${ fieldId }]`;
	const newValue = frmAdminJs[ `enter_${ fieldType }` ];
	maybeSetNewDescription( fieldDescription, hiddenDescName, newValue );

	const confFieldDescription = document.getElementById( `conf_field_description_${ fieldId }` );
	const hiddenConfName = `field_options[conf_desc_${ fieldId }]`;
	const newConfValue = frmAdminJs[ `confirm_${ fieldType }` ];
	maybeSetNewDescription( confFieldDescription, hiddenConfName, newConfValue );
}

function maybeSetNewDescription( descriptionDiv, hiddenName, newValue ) {
	if ( descriptionDiv.innerHTML === frmAdminJs.desc ) {
		// Set the visible description value and the hidden description value
		descriptionDiv.innerHTML = newValue;
		document.getElementsByName( hiddenName )[ 0 ].value = newValue;
	}
}

/**
 * @since x.x
 * @return {void}
 */
export function registerConfirmationField() {
	const registered = frmAdminBuild.registerBuilderFeature( 'confirmation_field', {
		addConf,
		setConfirmationFieldDescriptions,
		maybeSetNewDescription,
	} );
	if ( ! registered ) {
		return;
	}

	jQuery( document ).on( 'change', '#new_fields select.conf_field', addConf );
}
