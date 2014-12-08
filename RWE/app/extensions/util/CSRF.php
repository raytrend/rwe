<?php

namespace app\extensions\util;

use lithium\security\validation\RequestToken;

class CSRF {
	
	private static $_token = '';
	private static $_session_key = 'security.token';
	private static $_white = array (
			"weixin/api",
			"upload",
			"rest/"
	);

	/**
	 * 初始化CSRF并检查token是否存在, 不存在则生成token 
	 */
	public static function init() {
		$value = \lithium\storage\Session::read(self::$_session_key);
		
		if (empty($value)) {
			RequestToken::get();
		}
	}

	/**
	 * 生成token
	 * @param array $config
	 * @return string
	 */
	public static function generate(array $config = array()) {
		self::$_token = RequestToken::key(array (
				'sessionKey' => self::$_session_key,
				'salt' => null 
		) + $config);
		
		return self::$_token;
	}

	/**
	 * 获取token
	 * NOTICE: 获取token一定在检查之后
	 * @return string
	 */
	public static function token() {
		if (empty(self::$_token))
			self::generate();
		
		return self::$_token;
	}

	/**
	 * 验证token
	 * @param string $token
	 * @return boolean
	 */
	public static function check($token) {
		$check = RequestToken::check($token);
		
		self::generate(array (
				'regenerate' => true 
		));
		
		return $check;
	}

	/**
	 * 检查referer防CSRF
	 * @param unknown_type $url
	 * @return boolean
	 */
	public static function check_referer($url) {
		$url = strtolower($url);
		foreach ( self::$_white as $v ) {
			if (substr($url, 0, strlen($v)) == $v) {
				return TRUE;
			}
		}
		$referer = $_SERVER['HTTP_REFERER'];
		if (empty($referer)) {
			return FALSE;
		}
		
		$referer = parse_url($referer);
		if ($referer == NULL || $referer['host'] != $_SERVER['HTTP_HOST']) {
			return FALSE;
		}
		
		return TRUE;
	}
}

