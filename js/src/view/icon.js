/**
 * Formidable View icon
 */
const { __ } = wp.i18n;
const { Component } = wp.element;

export default class FormidableIcon extends Component {
    constructor( props ) {
        super( ...arguments );
    }

    render() {
        return (
            <svg id='Layer_1' data-name='Layer 1' xmlns='http://www.w3.org/2000/svg'
                 viewBox='0 0 599.68 601.37'>
                <defs/>
                <path className='cls-1'
                      d='M300.27,601.37A300.28,300.28,0,0,1,.43,300.68,299.84,299.84,0,1,1,512.29,513.3,297.46,297.46,0,0,1,300.27,601.37Zm0-563A262,262,0,0,0,38.69,300.68,261.58,261.58,0,1,0,485.24,115.2,259.5,259.5,0,0,0,300.27,38.37Z'
                      transform='translate(-.43)'/>
                <path className='cls-1 orange' d='M407.4,314.83l56-137.3H398.91q-30.45,0-40.19,25.37l-36.21,111Z'
                      transform='translate(-.43)'/>
                <polygon className='cls-1'
                         points='310.61 349.03 301.06 378.33 234.08 177.53 142.75 177.53 258.44 463.94 346.11 463.94 392.64 349.94 310.61 349.03'
                />
            </svg>
        )
    }
}