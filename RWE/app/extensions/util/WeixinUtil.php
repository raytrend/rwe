<?php

namespace app\extensions\util;

class WeixinUtil extends \lithium\core\StaticObject {
	
	/**
	 * 根据当前的微信的原始id来生成token.
	 */
	public static function generate_weixin_token($media_id) {
		return substr(md5($media_id), 0, 8);
	}
	
	/**
	 * 根据传入的 key 生成一个 43 位的key.
	 *
	 * @param string $token
	 */
	public static function generate_weixin_aeskey($token) {
		$current_length = mb_strlen($token, 'UTF-8');
		$aeskey = '';
		if ($current_length <= 43) {
			$aeskey = EncryptUtil::rand_code(43 - $current_length);
			$aeskey .= $token;
		} else {
			$aeskey = mb_substr($token, 0, 43, 'utf-8');
		}
	
		return $aeskey;
	}
	
	/**
	 * 将 appid 和 appsecret 组成查询字符串.
	 * 
	 * @return string
	 */
	private static function _get_appid_and_secret_query() {
		$appid = defined('WEIXIN_APP_ID') ? WEIXIN_APP_ID : '';
		$secret = defined('WEIXIN_APP_SECRET') ? WEIXIN_APP_SECRET : '';
		return "appid={$appid}&secret={$secret}";
	}
	
	/**
	 * 处理异常情况, 返回是否处理成功.
	 * 
	 * @param unknown_type $response
	 * @return boolean
	 */
	private static function _handle_invalid_oauth2_token($response) {
		if ($response->errcode == 40029) {
			// code 无效
			return false;
		} else if ($response->errcode == 40003) {
			// openid 无效
			return false;
		}
		return true;
	}
	
	/**
	 * 返回 oauth2 的数据.
	 * 
	 * @param string $code
	 * @return {access_token|expires_in|refresh_token|openid|scope}
	 */
	public static function get_oauth2_response($code) {
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?code=' . $code . '&grant_type=authorization_code&';
		$url .= self::_get_appid_and_secret_query();
		$response = self::_get($url);
		$success = self::_handle_invalid_oauth2_token($response);
		if ($success) {
			return $response;
		}
		return null;
	}
	
	/**
	 * 通过 oauth2 接口获取未关注的用户的信息.
	 * 
	 * @param string $access_token
	 * @param string $openid
	 * @return {openid|nickname|sex|province|city|country|headimgurl|privilege[P1|P2]}
	 */
	public static function get_oauth2_user_info($access_token, $openid) {
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
		$response = self::_get($url);
		$success = self::_handle_invalid_oauth2_token($response);
		if ($success) {
			return $response;
		}
		return null;
	}
	
	private static function _get($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_TIMEOUT, 3);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($curl);
		curl_close($curl);
		return json_decode($response);
	}
	
	private static function _post($url, $json_post_data) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json_post_data);
		$response = curl_exec($curl);
		curl_close($curl);
		return json_decode($response);
	}
}
