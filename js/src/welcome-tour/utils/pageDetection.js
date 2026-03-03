/**
 * Internal dependencies
 */
import { getQueryParam } from 'core/utils/url';

/**
 * Checks if the current page is the editor page.
 *
 * @return {boolean} True if the current page is the editor page, false otherwise.
 */
export const onEditorPage = () => document.getElementById( 'frm_form_editor_container' ) !== null;

/**
 * Checks if the current page is the styler page.
 *
 * @return {boolean} True if the current page is the styler page, false otherwise.
 */
export const onStylerPage = () => document.getElementById( 'frm_active_style_form' ) !== null;

/**
 * Checks if the current page is the form templates page.
 *
 * @return {boolean} True if the current page is the form templates page, false otherwise.
 */
export const onFormTemplatesPage = () => getQueryParam( 'page' ) === 'formidable-form-templates';

/**
 * Checks if the current page is the dashboard page.
 *
 * @return {boolean} True if the current page is the dashboard page, false otherwise.
 */
export const onDashboardPage = () => getQueryParam( 'page' ) === 'formidable-dashboard';
