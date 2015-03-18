<?php
if(!defined('IN_DCRM')) exit();
function _randstr($len = 40) {
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	mt_srand((double)microtime() * 1000000 * getmypid());
	$ranseed = '';
	while (strlen($ranseed) < $len) {
		$ranseed .= substr($chars,(mt_rand() % strlen($chars)),1);
	}
	return $ranseed;
}
function _deldir($dir) {
	$dh = opendir($dir);
	while ($file = readdir($dh)) {
		if ($file != "." && $file != "..") {
			$fullpath = $dir . "/" . $file;
			if (!is_dir($fullpath)) {
				unlink($fullpath);
			} else {
				deldir($fullpath);
			}
		}
	}
	closedir($dh);
	if (rmdir($dir)) {
		return true;
	} else {
		return false;
	}
}
function _dirsize($dirName) {
	$dirsize = 0;
	$dir = opendir($dirName);
	while ($fileName = readdir($dir)) {
		$file = $dirName . "/" . $fileName;
		if ($fileName != "." && $fileName != "..") {
			if (is_dir($file)) {
				$dirsize += dirsize($file);
			} else {
				$dirsize += filesize($file);
			}
		}
	}
	closedir($dir);
	return $dirsize;
}
function _execInBackground($cmd) {
	if (substr(php_uname(),0,7) == "Windows") {
		pclose(popen("start /B " . $cmd,"r"));
	} else {
		exec($cmd . " > /dev/null &");
	}
}
/** 
 * utf-8 转unicode 
 * 
 * @param string $name 
 * @return string 
 */  
function _utf8_unicode($name){  
	$name = iconv('UTF-8', 'UCS-2', $name);  
	$len  = strlen($name);  
	$str  = '';  
	for ($i = 0; $i < $len - 1; $i = $i + 2){  
		$c  = $name[$i];  
		$c2 = $name[$i + 1];  
		if (ord($c) > 0){   //两个字节的文字  
			$str .= '\u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);  
		} else {  
			$str .= '\u'.str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);  
		}  
	}  
	return $str;  
}
?>