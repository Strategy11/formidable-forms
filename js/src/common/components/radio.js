/**
 * WordPress dependencies
 */
//import { withInstanceId } from wp.compose;

const {
	BaseControl,
} = wp.components;

function RadioControl( { label, className, selected, help, instanceId, onChange, options = [] } ) {
	const id = `inspector-radio-control-${ instanceId }`;
	const onChangeValue = event => onChange( event.target.value );
	className = className + ' components-radio-control';

	// eslint-disable-next-line @wordpress/no-base-control-with-label-without-id
	return <BaseControl label={ label } help={ help } className={ className }>
		{ options.map( ( option, index ) =>
			<div
				key={ `${ id }-${ index }` }
				className="components-radio-control__option"
			>
				<input
					id={ `${ id }-${ index }` }
					className="components-radio-control__input"
					type="radio"
					name={ id }
					value={ option.value }
					onChange={ onChangeValue }
					checked={ option.value === selected }
					aria-describedby={ !! help ? `${ id }__help` : undefined }
				/>
				<label htmlFor={ `${ id }-${ index }` }>
					{ option.label }
				</label>
				{ option.help &&
				<p>{ option.help }</p>
				}
			</div>
		) }
	</BaseControl>;
}

export default RadioControl;
