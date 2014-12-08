<?php

/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2013, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use lithium\core\ErrorHandler;
use lithium\action\Response;
use lithium\net\http\Media;

use app\extensions\util\HttpUserAgentUtil;

ErrorHandler::apply('lithium\action\Dispatcher::run', array(), function($info, $params) {
	$response = new Response(array(
		'request' => $params['request'],
		'status' => $info['exception']->getCode()
	));
	
	// 根据抛出来的错误类型而进行处理, 这里分开发和生产环境, 方便开发环境进行异常处理
	$exception = $info['exception'];
	$template = 'production';
	$layout = 'default';
	$mobile = false;
	
	if (! empty($_SERVER['HTTP_USER_AGENT']) && HttpUserAgentUtil::is_mobile($_SERVER['HTTP_USER_AGENT'])) {
		$mobile = true;
	}
	
	$environment = environment_config();
	if ($environment['environment'] == 'production') {
		if ($mobile) {
			$layout = 'mobile';
			$template = 'mobile';
		}
	} else {
		$layout = 'error';
		$template = 'development';
	}

	if ($exception instanceof \app\extensions\core\exception\WeixinLoginException) {
		$template = ($environment['environment'] == 'production') ? 'business/weixin_login_failed' : 'development';
	}

	Media::render($response, compact('info', 'params'), array(
		'library' => true,
		'controller' => '_errors',
		'template' => $template,
		'layout' => $layout,
		'request' => $params['request']
	));
	return $response;
});

?>