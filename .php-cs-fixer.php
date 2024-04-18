<?php

$finder = ( new PhpCsFixer\Finder() )->in( __DIR__ );
$rules  = array(
	// Keep these rules for sure.
	// 'phpdoc_order'  => true,
	// 'phpdoc_scalar' => true,
	// 'phpdoc_trim'   => true,
);

$config = new PhpCsFixer\Config();
$config->setRules( $rules );

return $config->setFinder( $finder );

// Probably include these (but not right away).
// 'phpdoc_types_order' => true,

// Maybe include these.
// 'phpdoc_separation' => true,
// 'phpdoc_summary' => true,
