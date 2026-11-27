/**
 * Buttons that leave the current page without sending the entry: Previous, the rootline
 * page links and Save Draft. The server does not check the captcha for these submits.
 *
 * @type {string}
 */
const NAVIGATION_SELECTOR = '.frm_prev_page, .frm_page_back, .frm_save_draft, [name="frm_prev_page"], [name="frm_save_draft"]';

/**
 * Whether a submit only moves back a page or saves a draft, so the widget does not need to verify first.
 *
 * Formidable Pro sets the hidden frm_saving_draft input when the visitor clicks Save Draft, before
 * the submit event, which also covers a draft submit that does not report its button.
 *
 * @since x.x
 *
 * @param {HTMLFormElement}        form      The form being submitted.
 * @param {Element|null|undefined} submitter The button that submitted the form, if known.
 * @return {boolean} True when the captcha can be skipped for this submit.
 */
export const skipsVerification = ( form, submitter ) => {
	if ( submitter && typeof submitter.matches === 'function' && submitter.matches( NAVIGATION_SELECTOR ) ) {
		return true;
	}

	return form.querySelector( 'input.frm_saving_draft' )?.value === '1';
};
