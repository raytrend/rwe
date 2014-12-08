<?php

use lithium\core\Libraries;
use lithium\core\Environment;
use app\extensions\core\Constant;

Constant::set_define('TMP_PATH', Libraries::get('app', 'resources') . DIRECTORY_SEPARATOR . 'tmp');
Constant::set_define('QUEUE_PORT', 22201);
Constant::set_define('QUEUE_PREFIX', 'WEIXIAO_');
Constant::set_define('QUEUE_CLI_JOB_STATUS', QUEUE_PREFIX . 'cli_job_status_queue');

Constant::set_define('DEFAULT_PAGE_NUMBER', 20);

Constant::set_define('UPLOAD_LEFT_TIME', 600);

Constant::set_define('WEBROOT', Libraries::get('app', 'path') . '/webroot'); // Media::webroot()


Constant::set_define('UPLOAD_PATH', WEBROOT . '/upload');
Constant::set_define('STATIC_PATH', WEBROOT . '/static');

Constant::set_define('SESSION_LEFT_TIME', 18000);

Constant::set_define('RELATIVE_UPLOAD_PATH', '/upload');

Constant::set_define('IMG_COMPRESS_EXT', '.png|.jpg');

Constant::set_define('COMMENT_ON', true);

// 允许跳转的域名
Constant::set_define('SAFE_DOMAIN', 'qq.com');

Constant::set_define('APP_ROOT', dirname(LITHIUM_APP_PATH));

Constant::set_define('COFFEESDK_VERSION', file_exists(APP_ROOT . '/version.log') ? trim(file_get_contents(APP_ROOT . '/version.log')) : 'V1.0.0');


