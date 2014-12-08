<?php

namespace app\extensions\core\exception;

/**
 * 微信登录出错.
 * 
 * @author brishenzhou
 */
class WeixinLoginException extends \RuntimeException {
	
	protected $code = 450;
}