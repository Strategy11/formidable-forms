/**
 *  Creates a string of parameters for the form shortcode or a form API call
 */
const { __ } = wp.i18n;
import {
    setTextAttribute,
} from "../common/utilities/values";

export default function createParamsText( atts ) {

    const {
        form_id,
        title,
        description,
        minimize,
    } = atts;

    let params_text = '';

    params_text += setTextAttribute( form_id, 'id' );

    params_text += setTextAttribute( title, 'title' );
    params_text += setTextAttribute( description, 'description' );
    params_text += setTextAttribute( minimize, 'minimize' );

    return params_text;
}