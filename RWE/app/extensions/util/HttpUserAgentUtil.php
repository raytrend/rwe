<?php

namespace app\extensions\util;

require_once (__DIR__ . '/../../libraries/mobiledetect/MobileDetect.php');

use MobileDetect;

/**
 * 用来判断用户浏览器来源.
 * 
 * @author brishenzhou
 */
class HttpUserAgentUtil extends \lithium\core\StaticObject {
	
	/**
	 * 判断访问来源是否是 mobile 侧.
	 * 
	 * @param string $user_agent
	 * @return boolean true/false 是/不是
	 */
	public static function is_mobile($user_agent = null) {
		$user_agent = empty($user_agent) ?  $_SERVER['HTTP_USER_AGENT'] : $user_agent;
		$detect = new MobileDetect();
		return $detect->isMobile($user_agent);
	}
	
	/**
	 * 判断访问来源是否是微信侧.
	 * 
	 * @param string $user_agent
	 */
	public static function is_weixin($user_agent = null) {
		if (ISDEV) {
			return true;
		}
		$user_agent = empty($user_agent) ?  $_SERVER['HTTP_USER_AGENT'] : $user_agent;
		if (strpos(strtolower($user_agent), 'micromessenge') !== false) {
			return true;
		}
		return false;
	}
	
	/**
	 * 获取微校的 oauth2 接口地址.
	 * 
	 * @param string $ref_url 注意这里的 ref_url 需要 urlencde() 后再传入
	 */
	public static function get_weixin_oauth2_url($weixin_user_id, $ref_url) {
		$url = 'http://open.weixin.qq.com/connect/oauth2/authorize?appid=' . WEIXIN_APP_ID;
		$url .= '&redirect_uri=' . urlencode(SYS_PATH . "/weixin/register?uid={$weixin_user_id}&ref_url={$ref_url}");
		$url .= '&response_type=code&scope=snsapi_userinfo#wechat_redirect';
		
		return $url;
	}
	
	/**
	 * 获取微信端的登录页面, 这里可以先oauth2获取到用户openid, 然后openid从oauth_weixin_users表获得其所对应的weixin_user值.
	 * 
	 * @param string $ref_url
	 */
	public static function get_weixin_login_url($weixin_media_id, $ref_url) {
		$url = 'http://open.weixin.qq.com/connect/oauth2/authorize?appid=' . WEIXIN_APP_ID;
		$url .= '&redirect_uri=' . urlencode(SYS_PATH . "/weixin/login?media={$weixin_media_id}&ref_url={$ref_url}");
		$url .= '&response_type=code&scope=snsapi_userinfo#wechat_redirect';
		
		return $url;
	}
}
