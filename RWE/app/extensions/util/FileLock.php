<?php

namespace app\extensions\util;

/*
 * 文件锁工具类
 * @author victor
 */
class FileLock {
	private $filename = '';
	private $fp = NULL;
	
	/*
	 * 构造函数,文件名为空时
	 * @author victor
	 * @param  string $filename 为空时随机生成, 多程序锁时最好使用非空值
	 * @return NULL
	 */
	function __construct($filename = '') {
		if (empty($filename)) {
			$filename = tempnam("/", "FileLock");
		}
		
		$this->set_filename($filename);
	}
	
	/*
	 * 设置文件名
	 * @author victor
	 * @param  string $filename 为空时随机生成, 多程序锁时最好使用非空值
	 * @return NULL
	 */
	public function set_filename($filename) {
// 		Constant::set_define('TMP_PATH', Libraries::get('app', 'resources') . DIRECTORY_SEPARATOR . 'tmp');
		$this->filename = TMP_PATH . '/' . md5($filename);
	}
	
	/*
	 * 获取文件名
	* @author victor
	* @param  string $filename 为空时随机生成, 多程序锁时最好使用非空值
	* @return NULL
	*/
	public function get_filename() {
		return $this->filename;
	}
	
	/*
	 * 获得文件锁
	 * @author victor
	 * @param  boolean $wait_if_locked 是否使用阻塞锁
	 * @return boolean
	 */
	public function lock($wait_if_locked = false) {
		clearstatcache();
		
		if ($this->fp === NULL) {
			
			file_put_contents($this->filename, '');
			$this->fp = fopen($this->filename, "r+");
			$operation = LOCK_EX;
			if (! $wait_if_locked)
				$operation |= LOCK_NB;
			if (flock($this->fp, $operation)) {
				// var_dump ( $this->filename, __LINE__ );
				// ftruncate ( $this->fp, 0 );
				// fwrite ( $fp, "Write something here\n" );
				// fflush ( $fp );
				return true;
			}
		}
		
		if (PHP_SAPI == 'cli') {
			echo 'Lock key: ' . $this->filename . PHP_EOL;
		}
		return false;
	}
	
	/*
	 * 释放文件锁
	 * @author victor
	 * @return NULL
	 */
	public function free() {
		if ($this->fp === NULL) {
			flock($this->fp, LOCK_UN);
			fclose($this->fp);
			$this->fp = NULL;
			@unlink($this->filename);
		}
	}
	
	/*
	 * 释放文件锁
	 * @author victor
	 * @return NULL
	 */
	public function unlock() {
		$this->free();
	}
	
	/*
	 * 获得文件锁状态
	 * @author victor
	 * @param  boolean $wait_if_locked 是否使用阻塞锁
	 * @return boolean
	 */
	public function is_lock($wait_if_locked = false) {
		return $this->lock($wait_if_locked);
	}
}

