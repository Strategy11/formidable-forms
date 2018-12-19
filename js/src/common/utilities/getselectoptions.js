/**
 * Creates a list of key-value pairs of options to send to ItemSelect, with optional alphabetical sorting
 *
 * @param items
 * @param rest_item_name
 * @param rest_item_id
 * @param sort
 * @returns {*}
 */
export default function createOptions( items, rest_item_name = 'name', rest_item_id = 'id', sort = true ) {
    if ( ! items || Object.keys( items ).length === 0 ) {
        return [];
    }

    let options = Object.keys( items ).map( key => {
        return {
            label: items[ key ][ rest_item_name ],
            value: items[ key ][ rest_item_id ],
        }
    } );

    if ( sort ) {
        sortOptions( options );
    }

    return options;
}

/**
 * Alphabetically sorts a list of options by their labels.
 *
 * @param options
 */
function sortOptions( options ) {
    options.sort( ( first, second ) => {

        var firstLabel = first.label.toUpperCase();
        var secondLabel = second.label.toUpperCase();
        if ( firstLabel < secondLabel ) {
            return - 1;
        }
        if ( firstLabel > secondLabel ) {
            return 1;
        }

        return 0;
    } );

}