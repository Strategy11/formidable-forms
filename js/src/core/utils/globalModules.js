const { div, span, tag, a, img, svg } = frmDom;
const { maybeCreateModal, footerButton } = frmDom.modal;
const { onClickPreventDefault, documentOn } = frmDom.util;
const { doJsonPost } = frmDom.ajax;
const p = args => tag( 'p', args );
const bold = args => tag( 'strong', args );
const button = args => tag( 'button', args );

export { div, span, tag, a, img, svg, p, bold, button, maybeCreateModal, footerButton, onClickPreventDefault, documentOn, doJsonPost };

