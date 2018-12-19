import Link from "./link";
import PropTypes from 'prop-types';

const { __ } = wp.i18n;
const {
    Component
} = wp.element;

/**
 * Renders a link to the specified form in the WordPress admin
 *
 */
export default class FormLink extends Component {
    constructor( props ) {
        super( ...arguments );
    }

    render() {
        const {
            form_id,
        } = this.props;

        return (
            <Link href={ `wp-admin\/admin.php?page=formidable&frm_action=edit&id=${ form_id }` }
                  link_text={ __( 'Go to form' ) }
                  add_sub_dir={ true }
            />
        );
    }
}

Link.propTypes = {
    form_id: PropTypes.string,//id of form
};


