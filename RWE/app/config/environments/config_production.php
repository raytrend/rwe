<?php

use lithium\storage\Cache;

use app\extensions\core\Constant;
use app\extensions\core\Connections;

ini_set("display_errors", 0);
Constant::set_define('QUEUE_HOST', '10.153.147.144');
Constant::set_define('ISDEV', false);
Constant::set_define('DEMO_LOGIN', false);
Constant::set_define('CACHE', true);
Constant::set_define('SYS_PATH', 'http://www.raytrend.cn');
Constant::set_define('WEIXIN_APP_ID', '');
Constant::set_define('WEIXIN_APP_SECRET', '');

Connections::add('default', array (
		'type' => 'database',
		'adapter' => 'MySql',
		'host' => '',
		'login' => '',
		'password' => 'DJT@',
		'database' => '',
		'encoding' => 'UTF-8'
));

Connections::add('default_read', array (
		'type' => 'database',
		'adapter' => 'MySql',
		'host' => '',
		'login' => '',
		'password' => '',
		'database' => '',
		'encoding' => 'UTF-8'
));

Connections::add('feeds', array (
		'type' => 'database',
		'adapter' => 'MySql',
		'host' => '',
		'login' => '',
		'password' => 'DJT@',
		'database' => '',
		'encoding' => 'UTF-8',
		'persistent' => false
));


Cache::config(array (
		'default' => array (
				'adapter' => 'Memcache',
				'host' => '127.0.0.1:11221')
));