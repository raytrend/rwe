<?php

namespace app\extensions\util;

class JSON {

	public static function encode(array $encode_array) {
		return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($encode_array));
	}
}