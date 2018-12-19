/**
 *  Creates params text for View shortcode or API call
 */
const { __ } = wp.i18n;
import {
    setTextAttribute,
} from "../common/utilities/values";

export default function createParamsText( atts ) {

    const {
        view_id,
        filter,
    } = atts;

    let params_text = '';

    params_text += setTextAttribute( view_id, 'id' );

    params_text += setTextAttribute( filter, 'filter' );

    return params_text;
}