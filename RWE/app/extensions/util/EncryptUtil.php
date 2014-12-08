<?php

namespace app\extensions\util;

/**
 * 加解密工具类.
 * 
 * @author brishenzhou
 */
class EncryptUtil extends \lithium\core\StaticObject {
	
	const BLOCK_SIZE_PKSC7 = 32;
	
	/**
	 * 对用户名加密来作为密码.
	 * 
	 * @param string $username
	 * @return string
	 */
	public static function encrypt_username($username) {
		return substr(md5(base64_encode(strrev($username))), 8, 16);
	}
	
	public static function encrypt($data, $key) {
		$prep_code = serialize($data);
		$block = mcrypt_get_block_size('des', 'ecb');
		if (($pad = $block - (strlen($prep_code) % $block)) < $block) {
			$prep_code .= str_repeat(chr($pad), $pad);
		}
		$encrypt = mcrypt_encrypt(MCRYPT_DES, $key, $prep_code, MCRYPT_MODE_ECB);
		return base64_encode($encrypt);
	}
	
	public static function decrypt($str, $key) {
		$str = base64_decode($str);
		$str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
		$block = mcrypt_get_block_size('des', 'ecb');
		$pad = ord($str[($len = strlen($str)) - 1]);
		if ($pad && $pad < $block && preg_match('/' . chr($pad) . '{' . $pad . '}$/', $str)) {
			$str = substr($str, 0, strlen($str) - $pad);
		}
		return unserialize($str);
	}
	
	/**
	 * 随机一个指定长度的字符串.
	 * 
	 * @param int $length
	 * @return string
	 */
	public static function rand_code($length) {
		$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		$res = "";
		for ($i = 0; $i < $length; $i++) {
			$res .= $chars[mt_rand(0, strlen($chars)-1)];
		}
		return $res;
	}
	
	/**
	 * 基于pkcs#7算法的加密, 对需要加密的明文进行填充补位.
	 * 
	 * @param string $data 需要进行填充补位操作的明文
	 */
	public static function pkcs7_encrypt($data) {
		$length = strlen($data);
// 		$length = mb_strlen($data, 'UTF-8');
		// 计算需要填充的位数
		$amount_to_pad = self::BLOCK_SIZE_PKSC7 - ($length % self::BLOCK_SIZE_PKSC7);
		if ($amount_to_pad == 0) {
			$amount_to_pad = self::BLOCK_SIZE_PKSC7;
		}
		// 获得补位所用的字符
		$pad_chr = chr($amount_to_pad);
		$tmp = '';
		for ($i = 0; $i < $amount_to_pad; $i++) {
			$tmp .= $pad_chr;
		}
		return $data . $tmp;
	}
	
	/**
	 * 对解密后的明文进行补位删除.
	 * 
	 * @param string $data 解密后的明文
	 */
	public static function pkcs7_decrypt($data) {
		$pad = ord(substr($data, -1));
		if ($pad < 1 || $pad > self::BLOCK_SIZE_PKSC7) {
			$pad = 0;
		}
		return substr($data, 0, strlen($data) - $pad);
	}
	
	/**
	 * 微信企业号对明文进行加密.
	 * 
	 * @param string $data
	 * @param string $corpid
	 */
	public static function weixin_qy_encrypt($data, $corpid, $key) {
		$key = base64_decode($key, '=');
		try {
			// 获得16位随机字符串, 填充到明文之前
			$random = self::rand_code(16);
			$data = $random . pack('N', strlen($data)) . $data . $corpid;
			
			// 网络字节序
			$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
			$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
			$iv = substr($key, 0, 16);
			
			// 使用pkc#7的填充方式对明文进行补位填充
			$data = self::pkcs7_encrypt($data);
			mcrypt_generic_init($module, $key, $iv);
			
			// 加密
			$encrypt = mcrypt_generic($module, $data);
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
			
			// 使用BASE64对加密后的字符串进行编码
			return base64_encode($encrypt);
			
		} catch (\Exception $e) {
			return null;
		}
	}
	
	public static function weixin_qy_decrypt($data, $corpid, $key) {
		$key = base64_decode($key, '=');
		try {
			// 使用BASE64对需要解密的字符串进行解码
			$ciphertext_dec = base64_decode($data);
			$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
			$iv = substr($key, 0, 16);
			mcrypt_generic_init($module, $key, $iv);
			
			// 解密
			$decrypt = mdecrypt_generic($module, $ciphertext_dec);
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
			
			// 去除补位字符
			$result = self::pkcs7_decrypt($decrypt);
			
			// 去除16位随机字符串, 网络字节序和AppId
			if (strlen($result) < 16) {
				return null;
			}
			$content = substr($result, 16, strlen($result));
			$len_list = unpack("N", substr($content, 0, 4));
			$xml_len = $len_list[1];
			$xml_content = substr($content, 4, $xml_len);
			$from_corpid = substr($content, $xml_len + 4);
			if ($from_corpid != $corpid) {
				return null;
			}
			return $xml_content;
		} catch (\Exception $e) {
			return null;
		}
	}
}
