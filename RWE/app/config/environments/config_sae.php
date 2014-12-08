<?php

use lithium\storage\Cache;
use app\extensions\core\Constant;
use app\extensions\core\Connections;

Constant::set_define('ISDEV', false);
Constant::set_define('DEMO_LOGIN', false);
Constant::set_define('CACHE', true);
Constant::set_define('SYS_PATH', 'http://coffeesdk.sinaapp.com');
Constant::set_define('WEIXIN_APP_ID', '');
Constant::set_define('WEIXIN_APP_SECRET', '');

// ini_set('session.save_handler', 'memcache');
// ini_set('session.save_path', 'tcp://' . DB_SERVER . ':11221');

Connections::add('default', array (
		'type' => 'database',
		'adapter' => 'MySql',
		'host' => SAE_MYSQL_HOST_M,
		'login' => SAE_MYSQL_USER,
		'password' => SAE_MYSQL_PASS,
		'database' => SAE_MYSQL_DB,
		'port' => SAE_MYSQL_PORT,
		'encoding' => 'UTF-8'
));

Connections::add('default_read', array (
		'type' => 'database',
		'adapter' => 'MySql',
		'host' => SAE_MYSQL_HOST_S,
		'login' => SAE_MYSQL_USER,
		'password' => SAE_MYSQL_PASS,
		'database' => SAE_MYSQL_DB,
		'port' => SAE_MYSQL_PORT,
		'encoding' => 'UTF-8'
));

Connections::add('feeds', array (
		'type' => 'database',
		'adapter' => 'MySql',
		'host' => SAE_MYSQL_HOST_M,
		'login' => SAE_MYSQL_USER,
		'password' => SAE_MYSQL_PASS,
		'database' => SAE_MYSQL_DB,
		'port' => SAE_MYSQL_PORT,
		'encoding' => 'UTF-8'
));

Cache::config(array (
		'default' => array (
				'adapter' => 'Memcache',
				'host' => '127.0.0.1:11221' 
		)
));