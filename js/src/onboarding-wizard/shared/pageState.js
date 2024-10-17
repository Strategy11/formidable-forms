/**
 * External dependencies
 */
import { createPageState } from 'core/factory';

export const { getState, getSingleState, setState, setSingleState } = createPageState({
	processedSteps: [],
	installedAddons: [],
});
