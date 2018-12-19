/**
 * Updates an attribute with the specified new value
 *
 * @param attribute_name
 * @param attribute_value
 * @param setAttributes
 */
export function updateAttribute( attribute_name, attribute_value, setAttributes ) {
    setAttributes( {
        [ attribute_name ]: attribute_value,
    } );
}

/**
 * Filters a list of forms to remove Repeaters, drafts/trash, templates, and forms without names
 *
 * @param forms
 * @returns {{}}
 */
export function filterForms( forms ) {
    if ( ! forms ) {
        return {};
    }
    return Object.keys( forms ).reduce( ( list, key ) => {
        if (
            ( ! forms[ key ].hasOwnProperty( 'parent_form_id' ) || forms[ key ].parent_form_id === '0' ) &&
            ( forms[ key ].hasOwnProperty( 'status' ) && forms[ key ].status === 'published' ) &&
            ( ! forms[ key ].hasOwnProperty( 'is_template' ) || forms[ key ].is_template === '0' ) &&
            ( forms[ key ].hasOwnProperty( 'name' ) && forms[ key ].name )

        ) {
            return {
                ...list,
                [ key ]: forms[ key ],
            };
        }
        return list;
    }, {} );
}

/**
 * Gets a form object from a list of forms objects
 *
 * @param formsObject
 * @param formId
 * @returns {*}
 */
export function getFormObject( formsObject, formId ) {
    if ( ! forms_object ) {
        return '';
    }

    let forms = Object.values( formsObject );

    for ( let form of forms ) {
        if ( form.hasOwnProperty( 'id' ) && form.id == formId ) {
            return form;
        }
    }

    return false;
}

/**
 * Sets text attribute for a shortcode or API call from a key value pair
 *
 * @param value
 * @param attribute_name
 * @returns {string}
 */
export function setTextAttribute( value, attribute_name ) {
    if ( value ) {
        return ` ${ attribute_name }="${ value }"`;
    }
    return '';
}

/**
 * Gets subdirectory of current site, if the site isn't on the top level of the domain
 *
 * @returns {string}
 */
export function getSubDir() {
    let page = window.location.pathname;
    let index = page.indexOf( 'wp-admin' );

    let sub_dir = '/';

    if ( index > - 1 ) {

        sub_dir = page.substr( 0, index );
    }

    return sub_dir;
}
