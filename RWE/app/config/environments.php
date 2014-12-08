<?php

use lithium\core\Environment;
use lithium\core\Libraries;
use app\extensions\core\Constant;

$environment = environment_config();

$filename = '/config/environments/config_development.php';

if ($environment['environment'] == 'production' || (defined('ENVIRONMENT') && ENVIRONMENT == 'production')) {
	$filename = '/config/environments/config_production.php';
} else if ($environment['environment'] == 'sae' || (defined('ENVIRONMENT') && ENVIRONMENT == 'sae')) {
	$filename = '/config/environments/config_sae.php';
}

foreach ( Libraries::get() as $name => $config ) {
	if ($name === 'lithium') {
		continue;
	}
	$file = $config['path'] . $filename;
	
	file_exists($file) ? include $file : null;
	
	$constants = $config['path'] . '/config/constants.php';
	
	file_exists($constants) ? include $constants : null;
	
	if (PHP_SAPI == 'cli') {
		$file = $config['path'] . '/config/environments/config_cli.php';
		file_exists($file) && include $file;
	}
}
