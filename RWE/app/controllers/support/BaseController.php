<?php

namespace app\controllers\support;

use app\extensions\util\HttpUserAgentUtil;

abstract class BaseController extends \lithium\action\Controller {
	
	protected $_mobile = false;

	protected function _init() {
		parent::_init();
		
		// 判断其是否从 mobile 端请求过来
		if (! empty($_SERVER['HTTP_USER_AGENT']) && HttpUserAgentUtil::is_mobile($_SERVER['HTTP_USER_AGENT'])) {
			$this->_mobile = true;
		}
	}
}

