/**
 * External dependencies
 */
import { CURRENT_CLASS } from 'core/constants';
import { frmAnimate, setQueryParam, hide, show } from 'core/utils';

/**
 * Internal dependencies
 */
import { setupUsageData } from '../dataUtils';
import { getElements } from '../elements';
import { getState, PREFIX, setSingleState } from '../shared';
import { updateRootline } from '../ui';

/**
 * Navigates to the given step in the onboarding sequence.
 * Optionally updates the browser's history state to include the current step.
 *
 * @param {string} stepName                   The name of the step to navigate to.
 * @param {string} [updateMethod='pushState'] Specifies the method to update the browser's history and URL. Accepts 'pushState' or 'replaceState'. If omitted, defaults to 'pushState'.
 * @return {void}
 */
export const navigateToStep = ( stepName, updateMethod = 'pushState' ) => {
	// Find the target step element
	const targetStep = document.querySelector( `.${ PREFIX }-step[data-step-name="${ stepName }"]` );
	if ( ! targetStep ) {
		return;
	}

	// Find and hide the current step element
	const currentStep = document.querySelector( `.${ PREFIX }-step.${ CURRENT_CLASS }` );
	if ( currentStep ) {
		currentStep.classList.remove( CURRENT_CLASS );
		hide( currentStep );
	}

	// Display the target step element
	targetStep.classList.add( CURRENT_CLASS );
	show( targetStep );
	new frmAnimate( targetStep ).fadeIn();

	// Update the onboarding wizard's current step attribute
	const { onboardingWizardPage } = getElements();
	onboardingWizardPage.setAttribute( 'data-current-step', stepName );

	// Update the URL query parameter, with control over history update method
	setQueryParam( 'step', stepName, updateMethod );

	updateRootline( stepName );
};

/**
 * Navigates to the next step in the sequence.
 *
 * The function assumes steps are sequentially ordered in the DOM.
 *
 * @return {void}
 */
export const navigateToNextStep = () => {
	const currentStep = document.querySelector( `.${ PREFIX }-step.${ CURRENT_CLASS }` );
	const nextStep = currentStep?.nextElementSibling;

	if ( ! nextStep ) {
		return;
	}

	const processedStep = currentStep.dataset.stepName;
	const nextStepName = nextStep.dataset.stepName;

	// Save processed steps
	const { processedSteps } = getState();
	if ( ! processedSteps.includes( processedStep ) ) {
		processedSteps.push( processedStep );
		setSingleState( 'processedSteps', processedSteps );
	}
	setupUsageData( processedStep, nextStepName );

	navigateToStep( nextStepName );
};

/**
 * Navigates to the previous step in the sequence.
 *
 * The function assumes steps are sequentially ordered in the DOM.
 *
 * @return {void}
 */
export const navigateToPrevStep = () => {
	const currentStep = document.querySelector( `.${ PREFIX }-step.${ CURRENT_CLASS }` );
	const prevStep = currentStep?.previousElementSibling;

	if ( ! prevStep ) {
		return;
	}

	navigateToStep( prevStep.dataset.stepName );
};
