export default function returnCss( css ) {

	const { color, backgroundColor, borderColor, borderWidth, borderStyle } = css;

	return `
	#react_tester .frm_form_field input[type=text]{
	color:${ color ? color : '#444444' };
	background-color: ${ backgroundColor ? backgroundColor : '#ffffff' };
	border-color:${ borderColor ? borderColor : '#B94A48' };
	border-width: ${ borderWidth ? borderWidth : '1px' };
	border-style: ${ borderStyle ? borderStyle : 'solid' };
}
`;
}
