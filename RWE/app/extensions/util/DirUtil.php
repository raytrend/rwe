<?php

namespace app\extensions\util;

/**
 * 目录工具类.
 * 
 * @author brishenzhou
 */
class DirUtil extends \lithium\core\StaticObject {

	public static function create_dir($dir, $mod = 0705) {
		if (! is_dir($dir)) {
			self::create_dir(dirname($dir));
			mkdir($dir, $mod);
		}
	}

	public static function rm_dir($directory, $empty = false) {
		if (substr($directory, - 1) == "/") {
			$directory = substr($directory, 0, - 1);
		}
		
		if (! file_exists($directory) || ! is_dir($directory)) {
			return false;
		} elseif (! is_readable($directory)) {
			return false;
		} else {
			$directory_handle = opendir($directory);
			
			while ( $contents = readdir($directory_handle) ) {
				if ($contents != '.' && $contents != '..') {
					$path = $directory . "/" . $contents;
					
					if (is_dir($path)) {
						self::rm_dir($path);
					} else {
						unlink($path);
					}
				}
			}
			
			closedir($directory_handle);
			
			if ($empty == false) {
				if (! rmdir($directory)) {
					return false;
				}
			}
			
			return true;
		}
	}
}

