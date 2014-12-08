<?php

namespace app\extensions\util;

use app\extensions\helper\Upload;

class MimeType {
	private static $ext = array (
			Upload::IMG_PNG => 'png',
			Upload::IMG_JPG => 'jpg',
			Upload::IMG_JPE => 'jpe',
			Upload::IMG_JPEG => 'jpeg',
			Upload::IMG_GIF => 'gif',
			Upload::IMG_BMP => 'bmp',
			Upload::IMG_ICO => 'ico',
			Upload::IMG_TIFF => 'tiff',
			Upload::IMG_TIF => 'tif',
			Upload::IMG_SVG => 'svg',
			Upload::IMG_SVGZ => 'svgz',
			
			Upload::ARC_ZIP => 'zip',
			Upload::ARC_RAR => 'rar',
			Upload::ARC_EXE => 'exe',
			Upload::ARC_MSI => 'msi',
			Upload::ARC_CAB => 'cab',
			
			Upload::PDF => 'pdf',
			
			Upload::DOC_DOC => 'doc',
			Upload::DOC_RTF => 'rtf',
			Upload::DOC_XLS => 'xls',
			Upload::DOC_PPT => 'ppt',
			Upload::DOC_PPTX => 'pptx',
			Upload::DOC_CSV => 'csv'
			/*			
			'psd',
			'ai',
			'eps',
			'ps',
			
			// audio/video
			'mp3',
			'qt',
			'mov',
			
			// open office
			'odt',
			'ods',
			
			// js
			'css',
			'js',
			'json',
			
			// 					'xml' => 'application/xml',
			'swf',
			'flv' 
			*/
	);
	private static $mime = array (
			// 					'txt' => 'text/plain',
			// 					'htm' => 'text/html',
			// 					'html' => 'text/html',
			// 					'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			// 					'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',
			
			// images
			'png' => 'image/png|image/jpeg',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg|image/png',
			'jpg' => 'image/jpeg|image/png',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',
			
			// archives
			'zip' => 'application/zip|application/octet-stream',
			'rar' => 'application/x-rar-compressed|application/x-rar',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',
			
			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',
			
			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',
			
			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => '*', //'application/vnd.ms-powerpoint|application/msword',
			'pptx' => '*', //'application/vnd.ms-powerpoint',
			'csv' => '*',
			
			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet' 
	);
	private static $_mime = array ();

	public static function get_mime_type() {
		if (empty(self::$_mime)) {
			foreach ( self::$ext as $v ) {
				self::$_mime[$v] = self::$mime[$v];
			}
		}
		return self::$_mime;
	}

	public static function get_ext_by_code($code) {
		return isset(self::$ext[$code]) ? self::$ext[$code] : NULL;
	}

	public static function get_mime_by_code($code) {
		return isset(self::$ext[$code]) ? self::$mime[self::$ext[$code]] : NULL;
	}
}
