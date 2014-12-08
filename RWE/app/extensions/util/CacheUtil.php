<?php

namespace app\extensions\util;

use lithium\core\Libraries;

use lithium\storage\Cache;

/**
 * 缓存工具类.
 * 
 * @author brishenzhou
 */
class CacheUtil extends \lithium\core\StaticObject {
	
	public static function cache_get($key, $cache_name = 'default') {
	
		if (CACHE) {
			$key = self::_get_cache_name($key);
			return Cache::read($cache_name, $key);
		}
		return null;
	}
	
	public static function cache_set($key, $value, $expiry = '+5 mins', $cache_name = 'default') {
		if (CACHE) {
			$key = self::_get_cache_name($key);
			$path = Libraries::get(true, 'resources') . '/tmp/cache';
			$config = Cache::config($cache_name);
			if (strtolower($config["adapter"]) == 'file') {
				if (! file_exists($path . '/' . $key)) {
					DirUtil::create_dir(dirname($path . '/' . $key));
				}
			}
	
			return Cache::write($cache_name, $key, $value, $expiry);
		}
		return null;
	}
	
	public static function cache_delete($key, $cache_name = 'default') {
		$key = self::_get_cache_name($key);
		return Cache::delete($cache_name, $key);
	}
	
	public static function _get_cache_name($key, $cache_name = 'default') {
		$cache_key = $key;
		$key_md5 = md5($key);
	
		$config = Cache::config($cache_name);
		if (strtolower($config["adapter"]) == 'file') {
			$cache_key = 'cache/' . substr($key_md5, 0, 1) . '/' . substr($key_md5, 1, 1) . '/' . substr($key_md5, 2, 1) . '/' . $key_md5;
		} else {
			$cache_key .= '_' . substr($key_md5, 0, 2);
		}
	
		return $cache_key;
	}
}