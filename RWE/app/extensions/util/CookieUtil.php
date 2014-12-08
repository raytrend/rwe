<?php

namespace app\extensions\util;

/**
 * Cookie 工具类.
 * 
 * @author brishenzhou
 */
class CookieUtil extends \lithium\core\StaticObject {
	
	//加解密算法常量
	const UC_KEY = 'mddeZ4z226D1b1mar6K1KfH3W040ydrfp29bn2q2XdQ6i1183212Y8UeF4r493Hd';
	
	// cookie 中用于加密密钥种子
	const COOKIE_SECURITY_AUTHKEY = 'c6b333jp';
	
	//cookie构成参数
	const COOKIE_COOKIEPRE = 'hwJR_';
	const COOKIE_DOMAIN = '';
	const COOKIE_PATH = '/';
	
	/**
	 * 获取 cookie 的前缀, 比如得到 'hwJR_2132_' 等.
	 * 
	 * @return string
	 */
	public static function get_cookie_prefix() {
		return self::COOKIE_COOKIEPRE . substr(md5(self::COOKIE_PATH . '|' . self::COOKIE_DOMAIN), 0, 4) . '_';
	}

	/**
	 * 保存 cookie.
	 * 
	 * @$var：cookie名
	 * @$value：cookie值
	 * @$life：有效期
	 * @httponly：是否仅为http
	 * @return
	 */
	public static function set_prefix_cookie($var, $value = '', $life = 0, $httponly = false) {
		$timestamp = strtotime('now');
		$cookie_prefix = self::get_cookie_prefix();
		
		//cookie[xxx_auth] = value
		$var = $cookie_prefix . $var;
		$_COOKIE[$var] = $value;
		
		if ($value == '' || $life < 0) {
			$value = '';
			$life = - 1;
		}
		
		$life = $life > 0 ? $timestamp + $life : ($life < 0 ? $timestamp - 31536000 : 0);
		$path = self::COOKIE_PATH;
		
		$secure = 0;
		
		self::set_cookie($var, $value, $life, $path, self::COOKIE_DOMAIN, $secure, $httponly);
	}

	/**
	 * 读取 cookie.
	 * 
	 * @param key-cookie名
	 * @return cookie值
	 */
	public static function get_prefix_cookie($key) {
		$cookie_prefix = self::get_cookie_prefix();
		return (empty($_COOKIE[$cookie_prefix . $key])) ? '' : $_COOKIE[$cookie_prefix . $key];
	}

	/**
	 * 删除 cookie.
	 * 
	 * @key：cookie名
	 * @return void
	 */
	public static function del_cookie($key) {
		$cookie_prefix = self::get_cookie_prefix();
		self::set_cookie($cookie_prefix . $key, '', time() - 1, '/');
	}

	/**
	 * 加解密算法.
	 * 
	 * @$string：加解密原字串
	 * @$operation：操作，decode为解密；encode为加密
	 * @$key：加密密钥
	 * @return：返回加解密后字串
	 */
	public static function uc_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		if (! function_exists('uc_authcode')) {
			require_once LITHIUM_APP_PATH . '/libraries/uc_client/client.php';
		}
		return uc_authcode($string, $operation, $key, $expiry);
	}

	/**
	 * 把当前站点的所有 cookie 都获取出来, 得到如: 'pgv_pvid=418019633; ptui_loginuin=506483382@qq.com; pt2gguin=o2813575517; o_cookie=2813575517'.
	 */
	public static function get_current_cookies() {
		$cookie = '';
		foreach ($_COOKIE as $k => $v) {
			$cookie = $cookie . $k . '=' . $v . '; ';
		}
		return substr($cookie, 0, strlen($cookie) - 2);
	}

	public static function set_cookie($name, $value = NULL, $expire = 0, $path = NULL, $domain = NULL, $secure = false, $httponly = false) {
		if ($domain == NULL) {
			$domain = self::COOKIE_DOMAIN;
		}
		setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}
}

