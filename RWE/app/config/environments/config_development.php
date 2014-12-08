<?php

use lithium\storage\Cache;
use app\extensions\core\Connections;
use app\extensions\core\Constant;

Constant::set_define('ISDEV', true);
Constant::set_define('DEMO_LOGIN', true);
Constant::set_define('CACHE', false);
Constant::set_define('SYS_PATH', 'http://dev.raytrend.oa.com');
Constant::set_define('DEV_SERVER_HOSET', 'localhost');
Constant::set_define('WEIXIN_APP_ID', '');
Constant::set_define('WEIXIN_APP_SECRET', '');

Connections::add('default', array (
		'type' => 'database',
		'adapter' => 'MySql',
		'host' => 'localhost',
		'login' => 'root',
		'password' => 'sa',
		'database' => 'rwe',
		'encoding' => 'UTF-8',
		'persistent' => false
));

Connections::add('feeds', array (
		'type' => 'database',
		'adapter' => 'MySql',
		'host' => 'localhost',
		'login' => 'root',
		'password' => 'sa',
		'database' => 'rwe_feeds',
		'encoding' => 'UTF-8' 
));

Cache::config(array (
		'default' => array (
				'adapter' => 'File',
				'strategies' => array('Serializer') 
		)
));