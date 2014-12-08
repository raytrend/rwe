<?php
use app\extensions\core\Constant;
use app\extensions\core\Connections;
use lithium\core\Environment;
use lithium\core\Libraries;

ini_set('display_errors', 1);
error_reporting(E_ALL & ~ E_DEPRECATED & ~ E_NOTICE);
const DB_NAME = 'djtplatform';
const DB_NAME_V2 = 'djtv2';
const TPAI_DB = 'tpai';
const TPAI_BBS_DB = 'tpai_bbs';
const CXO_TID = 29144;
const CXO_FID = 62;
const CXO_ACTIVITY_ID = 1;
const CXO_DEFAULT_PLOGO = 'default_plogo.gif';
const CXO_STOP_SYNC_DATE = '2012-9-21';
const CXO_ARTICLE_UPDATE_INTERVAL = 3600;
const CXO_RANK_LIMIT = 50;
const BG_MODIFIED_BY = 'bg_sync';
const DATE_LONG_FORMAT = "Y-n-j H:i:s";
const TPAI_FEED = 'tpai_feeds';
const DOWNLOAD_OUT_TIME = 16384;
const TPAI_FEED_DAY_ACTIVITY_CLICK_TIMES = 3;
const TPAI_FEED_WEEK_ACTIVITY_DAYS = 3;
const TPAI_FEED_MONTH_ACTIVITY_DAYS = 10;

define('PROXY_SERVER', '172.23.33.138');
define('PROXY_PORT', '8080');

define('DB_REPLICATION', 'djt_replication');

define('SLEEP_TIME', 3);
define('SLEEP_TIME_SHORT', 1);

$config = Connections::get_config('default');
date_default_timezone_set('Asia/Chongqing');
define('MYSQL_HOST', $config['host']);
define('MYSQL_PORT', 3306);
define('MYSQL_USER', $config['login']);
define('MYSQL_PASS', $config['password']);
define('DB_CHARSET', $config['encoding']);

$GLOBALS['DB_MONITOR'] = array (
		array (
				'host' => '10.153.150.82',
				'user' => MYSQL_USER,
				'pass' => MYSQL_PASS 
		),
		array (
				'host' => '10.169.129.177',
				'user' => MYSQL_USER,
				'pass' => MYSQL_PASS 
		) 
);

// 配置 监控的jobs
$monitor_jobs = array ();



