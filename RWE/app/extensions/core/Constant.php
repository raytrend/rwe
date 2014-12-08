<?php

namespace app\extensions\core;

use lithium\core\Environment;

class Constant {

	public static function set($key, $productionValue, $testValue = null, $developmentValue = null) {
		$testValue = $testValue ?  : $productionValue;
		$developmentValue = $developmentValue ?  : $testValue;
		Environment::set('development', array($key => $developmentValue));
		Environment::set('test', array($key => $testValue));
		Environment::set('production', array($key => $productionValue));
	}

	public static function get($key) {
		return Environment::get($key);
	}

	public static function set_define($name, $value, $throw_exception = false) {
		if (! defined($name)) {
			define($name, $value);
			return true;
		} else if ($throw_exception) {
			throw new \Exception('定义常量失败');
		}
		
		return false;
	}
}