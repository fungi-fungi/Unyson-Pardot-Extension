<?php if (!defined('FW')) die('Forbidden');

$manifest = array();

$manifest['name'] = __('Pardot Forms', 'fw');
$manifest['version'] = '1.0.0';
$manifest['standalone'] = true;
$manifest['display'] = false;
$manifest['requirements']  = array(
	'extensions' => array(
		'mailer' => array(),
	),
);