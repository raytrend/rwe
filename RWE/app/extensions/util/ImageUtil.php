<?php

namespace app\extensions\util;

use lithium\core\StaticObject;

class ImageUtil extends StaticObject {
	
	const DEFAULT_IMG_THUMB_QUALITY = 85;
	
	public static function image_crop_by_size($dst_file, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
		$dst_image = imagecreatetruecolor ( $dst_w, $dst_h );
		imagecopyresampled ( $dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h );
		imagejpeg ( $dst_image, $dst_file, 100 );
		chmod ( $dst_file, 0777 );
	}
	public static function image_resize_by_width($file, $width, $toFile = "") {
		list ( $img_width, $img_height ) = getimagesize ( $file );
		
		$ratio = $img_height / $img_width;
		
		$toW = $width;
		$toH = $width * $ratio;
		
		ImageUtil::image_resize_by_fix_size ( $file, $toW, $toH, $toFile );
	}
	public static function image_resize_by_height($file, $height, $toFile = "") {
		list ( $img_width, $img_height ) = getimagesize ( $file );
		
		$ratio = $img_width / $img_height;
		
		$toW = $height * $ratio;
		$toH = $height;
		
		ImageUtil::image_resize_by_fix_size ( $file, $toW, $toH, $toFile );
	}
	public static function image_resize_by_max_length($file, $max_length, $toFile = "") {
		list ( $img_width, $img_height ) = getimagesize ( $file );
		
		if ($img_width > $img_height) {
			$toW = $max_length;
			$toH = $max_length * ($img_height / $img_width);
		} else {
			$toH = $max_length;
			$toW = $max_length * ($img_width / $img_height);
		}
		
		ImageUtil::image_resize_by_fix_size ( $file, $toW, $toH, $toFile );
	}
	
	/**
	 * image resize function with GD lib.
	 * ported from http://prato.bokele.com/?CH=749&ViewID=19533
	 *
	 * @return void
	 * @author mic
	 *        
	 */
	public static function image_resize($file, $toW, $toH, $toFile = "") {
		list ( $img_width, $img_height ) = getimagesize ( $file );
		
		$toWH = $toW / $toH;
		$srcWH = $img_width / $img_height;
		if ($toWH <= $srcWH) {
			$toH = $toW * ($img_height / $img_width);
		} else {
			$toW = $toH * ($img_width / $img_height);
		}
		
		ImageUtil::image_resize_by_fix_size ( $file, $toW, $toH, $toFile );
	}
	public static function image_resize_by_fix_size($srcFile, $toW, $toH, $toFile = "") {
		// echo 'handling '. $srcFile.'\n';
		if ($toFile == "") {
			$toFile = $srcFile;
		}
		
		$im = ImageUtil::imagecreatefromfile ( $srcFile, true );
		if (! $im) {
			echo 'Error creating image';
			return false;
		}
		
		$srcW = imagesx ( $im );
		$srcH = imagesy ( $im );
		
		if ($srcW > $toW || $srcH > $toH) {
			if (function_exists ( "imagecreatetruecolor" )) {
				@$ni = imagecreatetruecolor ( $toW, $toH );
				if ($ni) {
					imagecopyresampled ( $ni, $im, 0, 0, 0, 0, $toW, $toH, $srcW, $srcH );
				} else {
					$ni = imagecreate ( $toW, $toH );
					imagecopyresized ( $ni, $im, 0, 0, 0, 0, $toW, $toH, $srcW, $srcH );
				}
			} else {
				$ni = imagecreate ( $toW, $toH );
				imagecopyresized ( $ni, $im, 0, 0, 0, 0, $toW, $toH, $srcW, $srcH );
			}
			if (function_exists ( 'imagejpeg' )) {
				imagejpeg ( $ni, $toFile );
			} else {
				imagepng ( $ni, $toFile );
			}
			imagedestroy ( $ni );
		}
		imagedestroy ( $im );
	}
	public static function imagecreatefromfile($file, $user_functions = false) {
		$info = @getimagesize ( $file );
		if (! $info) {
			return false;
		}
		$type = $info [2];
		
		$functions = array (
				IMAGETYPE_GIF => 'imagecreatefromgif',
				IMAGETYPE_JPEG => 'imagecreatefromjpeg',
				IMAGETYPE_PNG => 'imagecreatefrompng',
				IMAGETYPE_WBMP => 'imagecreatefromwbmp',
				IMAGETYPE_XBM => 'imagecreatefromwxbm' 
		);
		
		if ($user_functions) {
			$functions [IMAGETYPE_BMP] = 'imagecreatefrombmp';
		}
		
		if (! $functions [$type] || ! function_exists ( $functions [$type] )) {
			return false;
		}
		return @$functions [$type] ( $file );
	}
	
	/**
	 *
	 *
	 *
	 * @ceate a BMP image
	 *
	 * @param string $filename        	
	 * @return bin string on success
	 * @return bool false on failure
	 */
	public static function imagecreatefrombmp($filename) {
		/**
		 * * create a temp file **
		 */
		$tmp_name = tempnam ( "/tmp", "GD" );
		/**
		 * * convert to gd **
		 */
		if (bmp2gd ( $filename, $tmp_name )) {
			/**
			 * * create new image **
			 */
			$img = imagecreatefromgd ( $tmp_name );
			/**
			 * * remove temp file **
			 */
			unlink ( $tmp_name );
			/**
			 * * return the image **
			 */
			return $img;
		}
		return false;
	}
	
	/**
	 *
	 *
	 *
	 * @convert BMP to GD
	 *
	 * @param string $src        	
	 * @param string|bool $dest        	
	 * @return bool
	 */
	public static function bmp2gd($src, $dest = false) {
		/**
		 * * try to open the file for reading **
		 */
		if (! ($src_f = fopen ( $src, "rb" ))) {
			return false;
		}
		
		/**
		 * * try to open the destination file for writing **
		 */
		if (! ($dest_f = fopen ( $dest, "wb" ))) {
			return false;
		}
		
		/**
		 * * grab the header **
		 */
		$header = unpack ( "vtype/Vsize/v2reserved/Voffset", fread ( $src_f, 14 ) );
		
		/**
		 * * grab the rest of the image **
		 */
		$info = unpack ( "Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant", fread ( $src_f, 40 ) );
		
		/**
		 * * extract the header and info into varibles **
		 */
		extract ( $info );
		extract ( $header );
		
		/**
		 * * check for BMP signature **
		 */
		if ($type != 0x4D42) {
			return false;
		}
		
		/**
		 * * set the pallete **
		 */
		$palette_size = $offset - 54;
		$ncolor = $palette_size / 4;
		$gd_header = "";
		
		/**
		 * * true-color vs.
		 * palette **
		 */
		$gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";
		$gd_header .= pack ( "n2", $width, $height );
		$gd_header .= ($palette_size == 0) ? "\x01" : "\x00";
		if ($palette_size) {
			$gd_header .= pack ( "n", $ncolor );
		}
		/**
		 * * we do not allow transparency **
		 */
		$gd_header .= "\xFF\xFF\xFF\xFF";
		
		/**
		 * * write the destination headers **
		 */
		fwrite ( $dest_f, $gd_header );
		
		/**
		 * * if we have a valid palette **
		 */
		if ($palette_size) {
			/**
			 * * read the palette **
			 */
			$palette = fread ( $src_f, $palette_size );
			/**
			 * * begin the gd palette **
			 */
			$gd_palette = "";
			$j = 0;
			/**
			 * * loop of the palette **
			 */
			while ( $j < $palette_size ) {
				$b = $palette {$j ++};
				$g = $palette {$j ++};
				$r = $palette {$j ++};
				$a = $palette {$j ++};
				/**
				 * * assemble the gd palette **
				 */
				$gd_palette .= "$r$g$b$a";
			}
			/**
			 * * finish the palette **
			 */
			$gd_palette .= str_repeat ( "\x00\x00\x00\x00", 256 - $ncolor );
			/**
			 * * write the gd palette **
			 */
			fwrite ( $dest_f, $gd_palette );
		}
		
		/**
		 * * scan line size and alignment **
		 */
		$scan_line_size = (($bits * $width) + 7) >> 3;
		$scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size & 0x03) : 0;
		
		/**
		 * * this is where the work is done **
		 */
		for($i = 0, $l = $height - 1; $i < $height; $i ++, $l --) {
			/**
			 * * create scan lines starting from bottom **
			 */
			fseek ( $src_f, $offset + (($scan_line_size + $scan_line_align) * $l) );
			$scan_line = fread ( $src_f, $scan_line_size );
			if ($bits == 24) {
				$gd_scan_line = "";
				$j = 0;
				while ( $j < $scan_line_size ) {
					$b = $scan_line {$j ++};
					$g = $scan_line {$j ++};
					$r = $scan_line {$j ++};
					$gd_scan_line .= "\x00$r$g$b";
				}
			} elseif ($bits == 8) {
				$gd_scan_line = $scan_line;
			} elseif ($bits == 4) {
				$gd_scan_line = "";
				$j = 0;
				while ( $j < $scan_line_size ) {
					$byte = ord ( $scan_line {$j ++} );
					$p1 = chr ( $byte >> 4 );
					$p2 = chr ( $byte & 0x0F );
					$gd_scan_line .= "$p1$p2";
				}
				$gd_scan_line = substr ( $gd_scan_line, 0, $width );
			} elseif ($bits == 1) {
				$gd_scan_line = "";
				$j = 0;
				while ( $j < $scan_line_size ) {
					$byte = ord ( $scan_line {$j ++} );
					$p1 = chr ( ( int ) (($byte & 0x80) != 0) );
					$p2 = chr ( ( int ) (($byte & 0x40) != 0) );
					$p3 = chr ( ( int ) (($byte & 0x20) != 0) );
					$p4 = chr ( ( int ) (($byte & 0x10) != 0) );
					$p5 = chr ( ( int ) (($byte & 0x08) != 0) );
					$p6 = chr ( ( int ) (($byte & 0x04) != 0) );
					$p7 = chr ( ( int ) (($byte & 0x02) != 0) );
					$p8 = chr ( ( int ) (($byte & 0x01) != 0) );
					$gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
				}
				/**
				 * * put the gd scan lines together **
				 */
				$gd_scan_line = substr ( $gd_scan_line, 0, $width );
			}
			/**
			 * * write the gd scan lines **
			 */
			fwrite ( $dest_f, $gd_scan_line );
		}
		/**
		 * * close the source file **
		 */
		fclose ( $src_f );
		/**
		 * * close the destination file **
		 */
		fclose ( $dest_f );
		
		return true;
	}
	
	//保存并裁剪外部图片
	public static function save_images_bysize($url, $file_name, $width, $height) {
		$imgmessage = getimagesize ( $url );
		if ($imgmessage [0] > 300 && $imgmessage [1] > 100 && $imgmessage [0] / $imgmessage [1] > 1.2 && $imgmessage [0] / $imgmessage [1] < 2) {
			// 物理地址,保存路径
			$cover_image_filename = UPLOAD_PATH . $file_name . self::get_image_type ( $imgmessage ['mime'] );
			if (ImageUtil::image_resize_by_fix_size ( $url, $width, $height, $cover_image_filename ) === false) {
				return null;
			} else {
				//域地址，用于src
				$cover_image = SYS_PATH . RELATIVE_UPLOAD_PATH . $file_name . self::get_image_type ( $imgmessage ['mime'] );
				return $cover_image;
			}	
		}
		
	}
	
	//保存外部图片
	public static function save_images($url, $file_name) {
		$upload_path = UPLOAD_PATH . $file_name;
		$timeout = 10;
		$ch = curl_init ( $url );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_BINARYTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout );
		
		$raw = curl_exec ( $ch );
		curl_close ( $ch );
		
		if (! file_exists ( $upload_path )) {
			$fp = @fopen ( $upload_path, 'x+' );
			fwrite ( $fp, $raw );
			fclose ( $fp );
		}
		if (file_exists ( $upload_path )) {
			$mime = mime_content_type ( $upload_path );
			$file_type = self::get_image_type ( $mime );
			
			if ($file_type) {
				$result = rename ( $upload_path, $upload_path . $file_type );
				if ($result) {
					return $file_name . $file_type;
				}
			}
		}
		return false;
	}
	
	
	public static function get_image_type($mime) {
		$type = false;
		switch (strtolower ( $mime )) {
			case "image/gif" :
				$type = '.gif';
				break;
			case "image/jpg" :
				$type = '.jpg';
				break;
			case "image/jpeg" :
				$type = '.jpeg';
				break;
			case "image/png" :
				$type = '.png';
				break;
		}
		return $type;
	}
	
	public static function rotate_image($img_path) {
		chmod($img_path, FILE_MOD);
		$func_exif = '\tencent\best_image\get_exif';
		if (! function_exists ( $func_exif )) {
			\lithium\analysis\Logger::write('debug',' rotate_image, function get_exif is undefined. img_path: '. $img_path . '   ' . PHP_EOL);
			return;
		}
		$orientation = @$func_exif ( $img_path, 'Exif.Image.Orientation' );
		if (empty ( $orientation )) {
			\lithium\analysis\Logger::write('debug', ' rotate_image, orientation is empty. img_path: '. $img_path . '   '. PHP_EOL);
			return;
		}
		$func_rotate = '\tencent\best_image\rotate';
		if (! function_exists ( $func_rotate )) {
			\lithium\analysis\Logger::write('debug',' rotate_image, function rotate is undefined. img_path: '. $img_path . '   '. PHP_EOL);
			return;
		}
	
		switch ($orientation) {
			case 3 :
				@$func_rotate ( $img_path, $img_path, 180 );
				break;
			case 6 :
				@$func_rotate ( $img_path, $img_path, 90 );
				break;
			case 8 :
				@$func_rotate ( $img_path, $img_path, 270 );
				break;
			default :
				break;
		}
		chmod($img_path, FILE_MOD);
	}
	
	public static function create_thumb($img_path, $thumb_width = IMG_THUMB_WIDTH, $quality = self::DEFAULT_IMG_THUMB_QUALITY) {
		$func_resize = '\tencent\best_image\smart_resize';
		if (!function_exists($func_resize)) {
			\lithium\analysis\Logger::write('debug',' create_thumb, function smart_resize is undefined. img_path: '. $img_path . '   '. PHP_EOL);
			return;
		}
	
		$thumb_path = self::get_thumb_name_by_width($img_path, $thumb_width);
	
		list ( $width, $height, $type, $attr ) = getimagesize ( $img_path );
	
		$new_size = self::resize_width ( $width, $height, $thumb_width );
		
		if ($new_size) {
			$func_resize ( $img_path, $thumb_path, $new_size ['width'], $new_size ['height'], $quality);
			if (is_file($thumb_path)) {
				chmod($thumb_path, FILE_MOD);
				return $thumb_path;
			}
		}
		return false;
	}
	
	/**
	 * @param $img_path 原图路径
	 * @param $width 输出宽度
	 * @param $height 输出高度
	 * 
	 * @return 输出图路径。如果转换失败则返回空字符串
	 */
	public static function create_thumb_by_width_and_height($img_path, $width, $height, $quality = self::DEFAULT_IMG_THUMB_QUALITY){
		$func_resize = '\tencent\best_image\smart_resize';
		if (!function_exists($func_resize)) {
			\lithium\analysis\Logger::write('debug',' create_thumb, function smart_resize is undefined. img_path: '. $img_path . '   '. PHP_EOL);
			return "";
		}
		$thumb_path = self::get_thumb_name($img_path, $width, $height);

		$func_resize ( $img_path, $thumb_path, $width, $height, $quality);
		if (is_file($thumb_path)) {
			chmod($thumb_path, FILE_MOD);
			return $thumb_path;
		}else{
			return "";
		}
	}
	
	public static function get_thumb_name($image_name, $thumb_width = IMG_THUMB_WIDTH, $thumb_height) {
		if(!empty($image_name)){
			if(empty($thumb_height)){
				return preg_replace ( '/^(.*)(\.[^.]+)$/', '$1_'. $thumb_width .'$2', $image_name );
			}else{
				return preg_replace ( '/^(.*)(\.[^.]+)$/', '$1_'. $thumb_width . '_' . $thumb_height .'$2', $image_name );
			}
		}else{
			return "";
		}
	}
	
	public static function get_thumb_name_by_width($image_name, $thumb_width = IMG_THUMB_WIDTH) {
		if(!empty($image_name)){
			return preg_replace ( '/^(.*)(\.[^.]+)$/', '$1_'. $thumb_width .'$2', $image_name );
		}else{
			return "";
		}
	}
	
	public static function resize_rate($width, $height) {
		$new_width = $new_height = 400;
	
		if ($width > $new_width || $height > $new_height) {
			$rate = $width / $height;
			$new_rate = $new_width / $new_height;
	
			if ($rate <= $new_rate) {
				$new_width = round($width * $new_width / $height);
			} else {
				$new_height = round($height * $new_width / $width);
			}
			return array('width' => $new_width, 'height' => $new_height);
		}
		return false;
	}
	
	public static function resize($width, $height, $new_width = 320, $new_height = 0) {
		if(!empty($new_height) && !empty($new_width)){
			//指定宽高，有可能非等比缩放
			return array('width' => $new_width, 'height' => $new_height);
		}
		if ($width > $new_width) {
			$new_height = round($height * $new_width / $width);
	
			return array('width' => $new_width, 'height' => $new_height);
		}
		return array('width' => $width, 'height' => $height);
	}
}
