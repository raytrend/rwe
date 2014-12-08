<?php

namespace app\extensions\util;

class DateUtil extends \lithium\core\StaticObject {

	/**
	 * 返回时间戳的mysql时间字符串
	 *
	 * @param int $time 时间戳
	 * @return string
	 */
	public static function mysql_time($time) {
    	return date('Y-m-d H:i:s', $time);
	}
	
	/**
	 * 返回当前时间.
	 * 
	 * @return string
	 */
	public static function now() {
		return date('Y-m-d H:i:s');
	}
	
	/**
	 * 将数据库的时间格式转成社会化时间格式.
	 * 
	 * @param string $timestamp 格式一般为 "date('Y-m-d H:i:s')" 的形式
	 */
	public static function socialize($timestamp) {
		if (empty($timestamp)) {
			return '';
		}
		$unixtime = strtotime($timestamp);
		$now = time();
		$diff = $now - $unixtime;
		if ($diff < 60) {
			// 一分钟之内显示为 "刚刚"
			return '刚刚';
		}
		if ($diff < (60 * 60)) {
			// 一小时之内显示为 "x分钟前"
			$minute = floor($diff / 60);
			return "{$minute}分钟前";
		}
		if ($diff < (60 * 60 * 24)) {
			// 一天之内显示为 "x小时前"
			$hour = floor($diff / (60 * 60));
			return "{$hour}小时前";
		}
		if ($diff < (60 * 60 * 24 * 2)) {
			// 两天之内显示为 "1天前"
			return "1天前";
		}
		if ($diff < (60 * 60 * 24 * 30)) {
			// 超过两天显示为 n 天前
			$day = floor($diff / (60 * 60 * 24));
			return "{$day}天前";
		}
		if ($diff < (60 * 60 * 24 * 30 * 12)) {
			$month = floor($diff / (60 * 60 * 24 * 30));
			return "{$month}个月前";
		}
		$year = floor($diff / (60 * 60 * 24 * 30 * 12));
		return "{$year}年前";
	}
}
