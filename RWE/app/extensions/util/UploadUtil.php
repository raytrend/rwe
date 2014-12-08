<?php

namespace app\extensions\util;

use app\extensions\util\MimeType;
use app\extensions\util\QueueUtil;

class UploadUtil {
	const EVENT = 1;
	private static $PREFIX_LEN = 6;

	public static function get_code() {
		list($usec, $sec) = explode(' ', microtime());
		
		return intval($usec * 100000);
	}

	public static function encry_data($upload_config) {
		srand((double) microtime() * 1000000);
		return substr(base64_encode(rand(1000, 9999) * 100), - self::$PREFIX_LEN) . base64_encode(Crypt::encrypt(serialize($upload_config)));
	}

	public static function decrypt_data($data) {
		return unserialize(Crypt::decrypt(base64_decode(substr($data, self::$PREFIX_LEN))));
	}

	public static function encry_code($code_config) {
		return md5($code_config);
	}

	public static function get_exts_by_code($code, $append = '.') {
		$code = array_reverse(str_split(decbin($code)));
		$ext = array ();
		foreach ( $code as $key => $value ) {
			if (intval($value) === 1) {
				$ext[] = $append . MimeType::get_ext_by_code($value << $key);
			}
		}
		
		return $ext;
	}

	public static function get_mimes_by_code($code) {
		$code = array_reverse(str_split(decbin($code)));
		$mime = array ();
		foreach ( $code as $key => $value ) {
			if (intval($value) === 1) {
				$code = $value << $key;
				$ext = MimeType::get_ext_by_code($code);
				if ($ext != NULL)
					$mime[$ext] = MimeType::get_mime_by_code($code);
			}
		}
		
		return $mime;
	}

	public static function compress_image($image_name) {
		return true;
		//if (file_exists($image_name)) {
		$ext_list = explode('|', IMG_COMPRESS_EXT);
		
		$ext = strrchr($image_name, '.');
		
		if (array_search($ext, $ext_list) !== FALSE) {
			$queue = new QueueUtil(array (
					'queue' => IMG_COMPRESS_QUEUE 
			));
			
			$try_times = 0;
			do {
				if ($queue->send($image_name)) {
					$try_times = 3;
					return true;
				} else {
					$try_times ++;
				}
			} while ( $try_times < 3 );
		}
		return false;
	}
	
	public static function rotate_and_thumb_image($image_name) {
		if (!empty($image_name)) {
			$queue = new QueueUtil(array (
					'queue' => IMG_ROTATE_THUMB_QUEUE
			));
				
			$try_times = 0;
			do {
				if ($queue->send($image_name)) {
					$try_times = 3;
					return true;
				} else {
					$try_times ++;
				}
			} while ( $try_times < 3 );
		}
		return false;
	}
	
}
