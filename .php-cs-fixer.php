<?php

$finder = ( new PhpCsFixer\Finder() )->in( __DIR__ );
$rules  = array(
	// Keep these rules for sure.
	'phpdoc_order'                => true,
	'phpdoc_scalar'               => true,
	'phpdoc_trim'                 => true,
	'phpdoc_var_without_name'     => true,
	'phpdoc_indent'               => true,
	'align_multiline_comment'     => true,
	'short_scalar_cast'           => true,
	'standardize_not_equals'      => true,
	'echo_tag_syntax'             => true,
	'semicolon_after_instruction' => true,
	'no_useless_else'             => true,
	'no_superfluous_elseif'       => true,
	'phpdoc_types_order'          => true,
);

$config = new PhpCsFixer\Config();
$config->setRules( $rules );

return $config->setFinder( $finder );

// Maybe include these.
// 'phpdoc_separation' => true,
// 'phpdoc_summary' => true,
// 'phpdoc_align' => true,
// 'visibility_required' => true,
// 'multiline_comment_opening_closing' => true,
// 'phpdoc_types_order' => true,
// 'return_assignment' => true,
// 'static_lambda' => true,
