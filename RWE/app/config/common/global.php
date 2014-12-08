<?php
/**
 * 提供一些经常使用的PHP全局方法.
 */

/**
 * 获取当前页面的 url 的方法.
 */
function current_url() {
	$current_url = 'http';
	if (isset($_SERVER['HTTPS'])) {
		$current_url .= 's';
	}
	$current_url .= '://';
	if ($_SERVER['SERVER_PORT'] != '80') {
		$current_url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
	} else {
		$current_url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	}
	return $current_url;
}