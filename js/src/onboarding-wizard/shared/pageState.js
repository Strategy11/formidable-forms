/**
 * External dependencies
 */
import { createPageState } from 'core/factory';

export const {
	initializePageState,
	getState,
	getSingleState,
	setState,
	setSingleState,
} = createPageState({
	processedSteps: [],
	installedAddons: [],
	emailStepData: {},
});
