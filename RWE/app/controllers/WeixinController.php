<?php

namespace app\controllers;

use app\services\OauthWeixinUserService;

use app\models\OauthWeixinUsers;

use app\extensions\util\CacheUtil;

use app\extensions\util\EncryptUtil;

use app\extensions\util\UrlUtil;

use app\extensions\util\HttpUserAgentUtil;

use app\extensions\util\DateUtil;

use app\models\WeixinUsers;

use app\extensions\util\WeixinUtil;

use app\services\weixin\operators\event\UnsubscribeOperator;

use app\services\weixin\operators\event\SubscribeOperator;

use lithium\action\DispatchException;

use app\models\WeixinMedias;

/**
 * 与微信服务器进行交互的控制器.
 * 
 * @author brishenzhou
 */
class WeixinController extends \lithium\action\Controller {
	
	/**
	 * 微信服务器企业号请求的api接口, e.g. ~/weixin/api/{token}?msg_signature={s}&timestap={t}&nonce={n}&echostr={e}
	 * 
	 * @args msg_signature 微信加密签名，msg_signature结合了企业填写的token、请求中的timestamp、nonce参数、加密的消息体
	 * @args timestamp 时间戳
	 * @args nonce 随机数
	 * @args echostr 加密的随机字符串，以msg_encrypt格式提供。需要解密并返回echostr明文，解密后有random、msg_len、msg、corpid四个字段，其中msg即为echostr明文
	 */
	public function qyapi() {
		$this->_render['layout'] = false;
		
		$encoding_aeskey = 'jWmYm7qr5nMoAUwZRjGtBxmz3KA1tkAj3ykkR6q2B2C';
		$corpid = 'wxafe9ead6759b61f7';
// 		$token = empty($this->request->params['args'][0]) ? '' : $this->request->params['args'][0];
		$token = 'qdg6ek';
		
		$msg_signature = empty($this->request->query['msg_signature']) ? '' : urldecode($this->request->query['msg_signature']);
		$timestamp = empty($this->request->query['timestamp']) ? '' : urldecode($this->request->query['timestamp']);
		$nonce = empty($this->request->query['nonce']) ? '' : urldecode($this->request->query['nonce']);
		$echostr = empty($this->request->query['echostr']) ? '' : urldecode($this->request->query['echostr']);
		
		if (empty($token) || empty($msg_signature) || empty($timestamp) || empty($nonce)) {
			exit();
		}
		
		if (isset($echostr)) {
			// 微信服务器首次校验
			$array = array($echostr, $token, $timestamp, $nonce);
			sort($array, SORT_STRING);
			$str = implode($array);
			$signature = sha1($str);
			// TODO 这里$signature==$msg_signature
// 			if ($signature == $msg_signature) {
// 			}
			$response = EncryptUtil::weixin_qy_decrypt($echostr, $corpid, $encoding_aeskey);
			echo htmlspecialchars($response);
			
		} elseif (! empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
			// TODO
		}
		exit();
	}
	
	public function _check_qy_msg_signature($token, $timestamp, $nonce, $echostr) {
		$array = array($token, $timestamp, $nonce, $encrypt_msg);
		sort($array, SORT_STRING);
		$str = implode($array);
		$signature = sha1($str);
		// FIXME
		return true;
	}
	
	/**
	 * 微信服务器请求的 api 接口, e.g. ~/weixin/api/{token}?signature={s}&timestap={t}&nonce={n}&echostr={e}
	 */
	public function api() {
		$this->_render['layout'] = false;
		
		$token = empty($this->request->params['args'][0]) ? '' : $this->request->params['args'][0];
		$signature = empty($this->request->query['signature']) ? '' : $this->request->query['signature'];
		$timestamp = empty($this->request->query['timestamp']) ? '' : $this->request->query['timestamp'];
		$nonce = empty($this->request->query['nonce']) ? '' : $this->request->query['nonce'];
		$echostr = empty($this->request->query['echostr']) ? '' : $this->request->query['echostr'];
		
		if (empty($token) || empty($signature) || empty($timestamp) || empty($nonce)) {
			exit();
		}
		
		if ($this->_check_signature($token, $signature, $timestamp, $nonce)) {
			if (isset($this->request->query['echostr'])) {
				// 微信服务器检测是否为开发者
				echo htmlspecialchars($this->request->query['echostr']);
			} elseif (! empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
				// 微信服务器发送交互内容过来
				$request = simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA'], 'SimpleXMLElement', LIBXML_NOCDATA);
				$weixin_media = WeixinMedias::get_cache_weixin_media_by_media_id(strval($request->ToUserName));
				if (empty($weixin_media)) {
					exit();
				}
				$msg_type = $request->MsgType;
				$method = '';
				switch ($msg_type) {
					case 'text':
						// 接收处理普通消息
						$method = 'text';
						break;
						
					case 'event':
						// 接收处理事件
						$method = 'event_' . strtolower($request->Event);
						break;
						
					case 'voice':
						// TODO 接收处理语音
						break;
						
					default:
						$method = 'unknown';
						break;
				}
				if (! method_exists($this, $method)) {
					// TODO 这里处理微信没有定义的事件
					throw new DispatchException("Weixin API event `{$method}` not found.");
				}
				$this->set(array('to_user_name' => $request->FromUserName, 'from_user_name' => $request->ToUserName));
				return $this->invokeMethod($method, array($weixin_media, $request));
			}
		}
		exit();
	}
	
	/**
	 * 检查是否来自微信侧的请求.
	 */
	private function _check_signature($token, $signature, $timestamp, $nonce) {
		if (ISDEV) {
			return true;
		}
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		return ($tmpStr == $signature);
	}
	
	/**
	 * oauth2 绑定用户, 这步主要是想通过加密的token获得用户uid.
	 */
	public function bind() {
		$token = rawurldecode($this->request->query['token']);
		$weixin_user_id = EncryptUtil::decrypt($token, WeixinUsers::KEY_ENCRYPT);
		if (! is_numeric($weixin_user_id)) {
			// 解密失败
			throw new DispatchException('Weixin token descrypt failed.');
		}
// 		$key = WeixinUsers::CACHE_KEY_TOKEN_PREFIX . $weixin_user_id;
// 		$cache = CacheUtil::cache_get($key);
// 		if (empty($cache)) {
// 			// 该链接已过期
// 			throw new DispatchException('Weixin token expired.');
// 		}
// 		CacheUtil::cache_delete($key);
		
		$ref_url = UrlUtil::get_safe_redirect_url($this->request->query['ref_url']);
		$ref_url = empty($ref_url) ? urlencode(SYS_PATH . '/mobile') : $ref_url;
		$url = HttpUserAgentUtil::get_weixin_oauth2_url($weixin_user_id, $ref_url);
		return $this->redirect($url, array('exit' => true));
	}
	
	/**
	 * oauth2 回调接口, 在这里获取到用户的授权 code 等来获取该用户信息. (不需要关注)
	 */
	public function register() {
		if (empty($this->request->query['code'])) {
			// TODO 用户不授权
			exit();
		}
		$response = WeixinUtil::get_oauth2_response($this->request->query['code']);
		if (empty($response)) {
			// TODO 获取用户信息出错
			exit();
		}
		$user_info = WeixinUtil::get_oauth2_user_info($response->access_token, $response->openid);
		if (empty($user_info)) {
			// TODO 获取用户信息出错
			exit();
		}
		// 获得该用户的信息, 更新到数据库
		$weixin_user_id = intval($this->request->query['uid']);
		$weixin_user =  WeixinUsers::find('first', array('conditions' => array('id' => $weixin_user_id)));
		if (! empty($weixin_user)) {
			if (empty($user_info->headimgurl)) {
				// 微信如果没有上传过头像则拉取不到, 采用默认头像
				$user_info->headimgurl = 'http://weixiao.qq.com/upload/avatar/default-avatar.jpg';
			}
			$weixin_user->nickname = $user_info->nickname;
			$weixin_user->avatar_image = $user_info->headimgurl;
			$weixin_user->save();
			
			// 注意这里的 oauth_weixin_user 有可能是某些场景下登录时写入的, 这个时候该记录有可能是没有对应到的weixin_user_id的
			// 这个时候也算是将其weixin_user_id绑定起来(即使有weixin_user_id这里也将其更新下, 有可能是解绑后残留下的)
			$oauth_weixin_user = OauthWeixinUsers::find('first', array('conditions' => array(
					'weixin_media_id' => $weixin_user->weixin_media_id, 'openid' => strval($response->openid))));
			if (empty($oauth_weixin_user)) {
				$oauth_weixin_user = OauthWeixinUsers::create();
				$oauth_weixin_user->weixin_media_id = $weixin_user->weixin_media_id;
				$oauth_weixin_user->openid = $user_info->openid;
			}
			$oauth_weixin_user->nickname = $user_info->nickname;
			$oauth_weixin_user->avatar_image = $user_info->headimgurl;
			$oauth_weixin_user->weixin_user_id = $weixin_user->id;
			$oauth_weixin_user->modified_at = DateUtil::now();
			$oauth_weixin_user->save();
			
			// 该用户注册信息成功后, 默认把该用户的登录态带上让其进入登录
			OauthWeixinUserService::after_login($oauth_weixin_user->to('array'));
		}
		
		$ref_url = UrlUtil::get_safe_redirect_url($this->request->query['ref_url']);
		return $this->redirect($ref_url, array('exit' => true));
	}
	
	/**
	 * 微信的登录页面.
	 */
	public function login() {
		if (empty($this->request->query['code'])) {
			// TODO 用户不授权
			exit();
		}
		$response = WeixinUtil::get_oauth2_response($this->request->query['code']);
		if (empty($response)) {
			// TODO
			exit();
		}
		$weixin_media_id = intval($this->request->query['media']);
		$oauth_weixin_user = OauthWeixinUsers::find('first', array('conditions' => array(
				'weixin_media_id' => $weixin_media_id, 'openid' => strval($response->openid))));
		if (empty($oauth_weixin_user)) {
			// 用户没有经过register步骤而是直接login的, 这个时候没有绑定到weixin_user
			$user_info = WeixinUtil::get_oauth2_user_info($response->access_token, $response->openid);
			if (empty($user_info)) {
				// TODO 获取用户信息出错
				exit();
			}
			if (empty($user_info->headimgurl)) {
				$user_info->headimgurl = 'http://weixiao.qq.com/upload/avatar/default-avatar.jpg';
			}
			$oauth_weixin_user = OauthWeixinUsers::create();
			$oauth_weixin_user->weixin_media_id = $weixin_media_id;
			$oauth_weixin_user->openid = $user_info->openid;
			$oauth_weixin_user->nickname = $user_info->nickname;
			$oauth_weixin_user->avatar_image = $user_info->headimgurl;
			$oauth_weixin_user->weixin_user_id = 0;
			$oauth_weixin_user->modified_at = DateUtil::now();
			$oauth_weixin_user->save();
		}
		// 让该用户登录
		OauthWeixinUserService::after_login($oauth_weixin_user->to('array'));
		
		$ref_url = UrlUtil::get_safe_redirect_url($this->request->query['ref_url']);
		return $this->redirect($ref_url, array('exit' => true));
	}
	
	/**
	 * 退出微信的登录.
	 */
	public function logout() {
		OauthWeixinUserService::after_logout();
		return $this->redirect('/index', array('exit' => true));
	}
	
	/**
	 * 处理普通文本请求.
	 * 
	 * @param array $weixin_media
	 * @param object $request
	 */
	public function text($weixin_media, $request) {
		// FIXME 这里先认为该用户全部选择了所有功能
		$text_operators = array(
				'app\services\weixin\operators\text\CertificateOperator',
				'app\services\weixin\operators\text\wall\WallEndOperator',
				'app\services\weixin\operators\text\wall\WallIngOperator',
				'app\services\weixin\operators\text\wall\WallStartOperator',
				'app\services\weixin\operators\text\RuleAnswerOperator',
				'app\services\weixin\operators\text\AutoAnswerOperator'
		);
		$template = '';
		$answer_array = array();
		foreach ($text_operators as $text_operator) {
			$operator = new $text_operator(array('media' => $weixin_media, 'request' => $request));
			if ($operator->match()) {
				$answer_array = json_decode($operator->handle(), true);
				$template = $operator->template();
				break;
			}
		}
		$this->_render['template'] = $template;
		switch ($template) {
			case 'text':
				$this->set(array('content' => $answer_array['content']));
				break;
				
			case 'image':
				$this->set(array('content' => $answer_array['media_id']));
				break;
				
			case 'news':
				$this->set(array('articles' => $answer_array));
				break;
				
			default:
				break;
		}
	}
	
	/**
	 * 处理订阅请求.
	 * 
	 * @param array $weixin_media
	 * @param object $request
	 */
	public function event_subscribe($weixin_media, $request) {
		$operator = new SubscribeOperator(array('media' => $weixin_media, 'request' => $request));
		$answer = $operator->handle();
		$answer_array = json_decode($answer, true);
		$template = $operator->template();
		$this->_render['template'] = $template;
		switch ($template) {
			case 'image':
				$this->set(array('media_id' => $answer_array['media_id']));
				break;
				
			case 'text':
				$this->set(array('content' => $answer_array['content']));
				break;
				
			case 'news':
				$this->set(array('articles' => $answer_array));
				break;
				
			default:
				break;
		}
	}
	
	/**
	 * 处理取消订阅请求.
	 * 
	 * @param array $weixin_media
	 * @param object $request
	 */
	public function event_unsubscribe($weixin_media, $request) {
		$operator = new UnsubscribeOperator(array('media' => $weixin_media, 'request' => $request));
		$this->_render['template'] = $operator->template();
		$this->set(json_decode($operator->handle(), true));
	}
	
	public function event_click($weixin_media, $request) {
	
	}
	
	public function event_scan($weixin_media, $request) {
	
	}
	
	public function event_location($weixin_media, $request) {
		
	}
}
