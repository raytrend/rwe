<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2013, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * This file contains a series of method filters that allow you to intercept different parts of
 * Lithium's dispatch cycle. The filters below are used for on-demand loading of routing
 * configuration, and automatically configuring the correct environment in which the application
 * runs.
 *
 * For more information on in the filters system, see `lithium\util\collection\Filters`.
 *
 * @see lithium\util\collection\Filters
 */

use lithium\core\Libraries;
use lithium\core\Environment;
use lithium\action\Dispatcher;

use app\extensions\util\CSRF;

/**
 * This filter intercepts the `run()` method of the `Dispatcher`, and first passes the `'request'`
 * parameter (an instance of the `Request` object) to the `Environment` class to detect which
 * environment the application is running in. Then, loads all application routes in all plugins,
 * loading the default application routes last.
 *
 * Change this code if plugin routes must be loaded in a specific order (i.e. not the same order as
 * the plugins are added in your bootstrap configuration), or if application routes must be loaded
 * first (in which case the default catch-all routes should be removed).
 *
 * If `Dispatcher::run()` is called multiple times in the course of a single request, change the
 * `include`s to `include_once`.
 *
 * @see lithium\action\Request
 * @see lithium\core\Environment
 * @see lithium\net\http\Router
 */

function load_environment() {
	$environmentsFile = Libraries::get('app', 'path') . '/config/environments.php';
	file_exists($environmentsFile) ? include $environmentsFile : null;
	foreach ( Libraries::get() as $name => $config ) {
		if ($name === 'lithium') {
			continue;
		}
		$file = "{$config['path']}/config/routes.php";
		file_exists($file) ? include $file : null;
	}
}

Dispatcher::applyFilter('run', function($self, $params, $chain) {
	if (defined('ENVIRONMENT')) {
		// 以上常量在 bootstrap.php 文件中设置, 但一般不这样做, 而是选择让它根据域名而加载不同的配置
		Environment::is(function ($request) {
			$environment = environment_config();
			return ENVIRONMENT;
		});
	} else {
		Environment::is(function ($request) {
			$environment = environment_config();
			return $environment['environment'];
		});
	}
	
	Environment::set($params['request']);
	
	load_environment();
	
	// CSRF
// 	CSRF::init();
// 	if ($params['request']->is('post')) {
// 		if (! CSRF::check_referer($params['request']->url)) {
// 			throw new \Exception("CSRF验证失败");
// 		}
// 		// POST method: check token
// 	}
	
	return $chain->next($self, $params, $chain);
});

?>