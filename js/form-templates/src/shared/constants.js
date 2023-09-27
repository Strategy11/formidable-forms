/**
 * Copyright (C) 2023 Formidable Forms
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

export const { url: PLUGIN_URL, nonce } = window.frmGlobal;
export const { FEATURED_TEMPLATES_KEYS } = window.frmFormTemplatesVars;

export const PREFIX = 'frm-form-templates';
export const HIDDEN_CLASS = 'frm_hidden';
export const CURRENT_CLASS = 'frm-current';

export const VIEW_SLUGS = {
	ALL_TEMPLATES: 'all-templates',
	AVAILABLE_TEMPLATES: 'available-templates',
	FREE_TEMPLATES: 'free-templates',
	FAVORITES: 'favorites',
	CUSTOM: 'custom',
	SEARCH: 'search'
};

export const PLANS = {
	BASIC: 'basic',
	PLUS: 'plus',
	BUSINESS: 'business',
	ELITE: 'elite',
	RENEW: 'renew',
	FREE: 'free'
};