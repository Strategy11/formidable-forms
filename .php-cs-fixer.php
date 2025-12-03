<?php

$finder = ( new PhpCsFixer\Finder() )->in( __DIR__ );
$rules  = array(
	// Keep these rules for sure.
	'phpdoc_order'                         => array(
		'order' => array( 'since', 'param', 'throws', 'return' ),
	),
	'phpdoc_scalar'                        => true,
	'phpdoc_trim'                          => true,
	'phpdoc_var_without_name'              => true,
	'phpdoc_separation'                    => true,
	'phpdoc_indent'                        => true,
	'align_multiline_comment'              => true,
	'short_scalar_cast'                    => true,
	'standardize_not_equals'               => true,
	'echo_tag_syntax'                      => true,
	'semicolon_after_instruction'          => true,
	'no_useless_else'                      => true,
	'no_superfluous_elseif'                => true,
	'elseif'                               => true,
	'phpdoc_add_missing_param_annotation'  => true,
	'no_extra_blank_lines'                 => true,
	'blank_line_before_statement'          => array(
		'statements' => array(
			'try',
			'for',
			'if',
			'foreach',
			'while',
		),
	),
	'phpdoc_types_order'                   => array(
		'null_adjustment' => 'always_last',
	),
);

$config = new PhpCsFixer\Config();
$config->setRules( $rules );

return $config->setFinder( $finder );

// Maybe include these.
// 'phpdoc_summary' => true,
// 'phpdoc_align' => true,
// 'visibility_required' => true,
// 'multiline_comment_opening_closing' => true,
// 'phpdoc_types_order' => true,
// 'return_assignment' => true,
// 'static_lambda' => true,
