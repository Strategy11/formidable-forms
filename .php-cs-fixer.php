<?php

$finder = ( new PhpCsFixer\Finder() )->in( __DIR__ );
$rules  = array(
	'phpdoc_order' => true,
);

$config = new PhpCsFixer\Config();
$config->setRules( $rules );

return $config->setFinder( $finder );
