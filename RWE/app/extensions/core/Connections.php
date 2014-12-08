<?php

namespace app\extensions\core;

class Connections extends \lithium\data\Connections {

	/**
	 * 添加数据库链接
	 * @param string $name 名称
	 * @param array $config 配置
	 * @param bool $overwrite 是否覆盖已有配置,默认否
	 * @return array
	 */
	public static function add($name, array $config = array(), $overwrite = false) {
		if ($overwrite) {
			parent::add($name, $config);
		} else if (! isset(static::$_configurations[$name])) {
			parent::add($name, $config);
		}
		
		return static::$_configurations[$name];
	}

	public static function get_config($name) {
		return isset(static::$_configurations[$name]) ? static::$_configurations[$name] : null;
	}
}
