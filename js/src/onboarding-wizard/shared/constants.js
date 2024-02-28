export const { nonce } = window.frmGlobal;
export const { INITIAL_STEP, proIsIncluded } =  window.frmOnboardingWizardVars;

export const PREFIX = 'frm-onboarding';
export const HIDDEN_CLASS = 'frm_hidden';
export const HIDE_JS_CLASS = 'frm-hide-js';
export const CURRENT_CLASS = 'frm-current';
export const WELCOME_STEP_ID = `${PREFIX}-welcome-step`;

export const STEPS = {
	INITIAL: INITIAL_STEP,
	DEFAULT_EMAIL_ADDRESS: 'default-email-address',
	INSTALL_FORMIDABLE_PRO: 'install-formidable-pro',
	LICENSE_MANAGEMENT: 'license-management'
};
