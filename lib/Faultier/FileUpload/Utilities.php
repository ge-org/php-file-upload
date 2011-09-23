<?php

	namespace Faultier\FileUpload;
	
	class Utilities {
	
	private function __construct() {}
	
	/**
		* Returns a textual representation of any amount of bytes
		*
		* @author		wesman20 (php.net)
		* @author		Jonas John
		* @version	0.3
		* @link			http://www.jonasjohn.de/snippets/php/readable-filesize.htm
		*
		* @param $size	int	size in bytes
		*
		* @return string A readable representation
		*/
	public static function makeHumanReadableSize($size) {
	
		$mod		= 1024;
		$units	= explode(' ','B KB MB GB TB PB');
		for ($i = 0; $size > $mod; $i++) {
			$size /= $mod;
		}
		
		return round($size, 2) . ' ' . $units[$i];
	}
	
	}

?>