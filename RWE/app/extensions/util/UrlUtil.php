<?php

namespace app\extensions\util;

class UrlUtil extends \lithium\core\StaticObject {

	/**
	 * 从URL中获取域名
	 *
	 * @author bookswang
	 *
	 * @return eg: dev.djt.qq.com  =>  qq.com
	 */
	public static function get_domain_from_url($url) {
		return parse_url($url, PHP_URL_HOST);
	}

	/**
	 * 是否为安全域名
	 * @author victor
	 * @param string $domain 请传人域名, 如djt.qq.com
	 * @return bool 
	 */
	public static function is_safe_domain($domain) {
		$ret = false;
		$qqmail_domain = 'mail.qq.com';
		if (strlen($domain) >= strlen($qqmail_domain) && $qqmail_domain == substr($domain, - strlen($qqmail_domain))) {
			$ret = false;
		} else if (SAFE_DOMAIN == $domain || '.' . SAFE_DOMAIN == substr($domain, - (strlen(SAFE_DOMAIN) + 1))) {
			$ret = true;
		}
		
		return $ret;
	}

	public static function get_safe_redirect_url($url) {
		$url = strtolower(ltrim($url, '/'));
		
		if (empty($url)) {
			$url = SYS_PATH . '/';
		}
		
		$domain = self::get_domain_from_url($url);
		$qqmail_domain = 'mail.qq.com';
		
		if (strlen($url) >= strlen($qqmail_domain) && $qqmail_domain == substr($domain, - strlen($qqmail_domain))) {
			return SYS_PATH . '/';
		} else if (SAFE_DOMAIN == $domain || '.' . SAFE_DOMAIN == substr($domain, - (strlen(SAFE_DOMAIN) + 1))) {
			return $url;
		} else if ($domain != NULL) {
			return SYS_PATH . '/';
		} else {
			return SYS_PATH . '/' . $url;
		}
		
		return SYS_PATH . '/';
	}

	public static function generate_short_url($url) {
		$request = "http://openapi.omg.tencent-cloud.com:8080/innerapi/short_url/shorten?appid=800100940&app_password=q5cyp7Iy3V&format=json&long_url=";
		$request .= urlencode($url);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $request);
		curl_setopt($curl, CURLOPT_TIMEOUT, 3);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
		curl_close($curl);
		$result = json_decode($result, true);
		if (empty($result['data']['short_url'])) {
			error_log('generate short url failed. ' . print_r($result, true));
			return '';
		} else {
			return 'http://url.cn/' . $result['data']['short_url'];
		}
	}
	
	public static function get_img_path_from_url($url){
		return WEBROOT . '/' . substr($url, strlen(SYS_PATH), strlen($url));
	}
	
	public static function generate_url_from_img_path($path){
		return SYS_PATH . substr($path, strlen(WEBROOT) + 1, strlen($path));
	}
}