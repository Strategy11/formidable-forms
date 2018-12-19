const { __ } = wp.i18n;
const {
    Component
} = wp.element;
const {
    ExternalLink
} = wp.components;

import PropTypes from 'prop-types';
import { getSubDir } from "../utilities/values";

/**
 * Displays a link to the specified URL
 *
 */
export default class Link extends Component {
    constructor( props ) {
        super( ...arguments );
    }

    render() {
        const {
            href,
            link_text,
            add_sub_dir,
        } = this.props;

        let adjusted_href = ( add_sub_dir ? getSubDir() : '' ) + href;

        return (
            <ExternalLink href={ adjusted_href }>
                { link_text }
            </ExternalLink>
        );
    }
}

Link.propTypes = {
    href: PropTypes.string,//href attribute value of the link
    link_text: PropTypes.string,//label of the link,
    add_sub_dir: PropTypes.bool,//whether a subdirectory should be added to the beginning of the link, for cases where WordPress isn't installed on the top level and a relative link is being used
};

