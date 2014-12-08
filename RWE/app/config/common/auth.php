<?php

use app\services\OauthWeixinUserService;

use app\extensions\util\HttpUserAgentUtil;

use lithium\action\Response;

use app\services\UserService;

use lithium\net\http\Router;

use lithium\action\Dispatcher;

/**
 * 拦截登录过滤器, 检测页面访问权限.
 */
Dispatcher::applyFilter('run', function ($self, $params, $chain) {
	$router = Router::parse($params['request']);
	if (empty($router->params)) {
		return $chain->next($self, $params, $chain);
	} else {
		$router = $router->params;
	}
	
	if (UserService::check_auth($router)) {
		// 如果是微信里的页面, 则需要注入当前的微信用户信息
		if (HttpUserAgentUtil::is_weixin()) {
			OauthWeixinUserService::set_current_oauth_user();
		}
		return $chain->next($self, $params, $chain);
	} else {
		$login_url = SYS_PATH . '/user/login?ref_url=' . urldecode(current_url());
		return new Response(array('location' => $login_url));
	}
});
