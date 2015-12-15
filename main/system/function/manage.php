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
	if(!file_exists($dir)) return;
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
function _TrimArray($input){
	if(!is_array($input))
		return trim($input);
	return array_map('TrimArray', $input);
}
/**
 * 字符串处理
 *
 * 用于商业软件包处理，可在标签中添加或删除cydia::commercial等
 * 
 * @param string $input 输入字符串
 * @param bool $switch 在字符串中添加(true)或删除(false)$switch_string
 * @param string $switch_string 要添加或删除的字符串
 * @param string $separator 分隔符
 * @return string 处理完成的字符串
 */
function _string_handle($input, $switch, $switch_string = 'cydia::commercial', $separator = ','){
	if(is_array($input)) $input = $input[0];
	$input_array = TrimArray(explode($separator, $input));

	if($switch){
		if(!in_array($switch_string, $input_array, true))
			$input_array[] = $switch_string;
	} else {
		if(in_array($switch_string, $input_array, true))
			$input_array = array_diff($input_array, array($switch_string));
	}
	if(!strpos(' ', $separator)) $separator = $separator.' ';
	return trim(implode($separator, array_filter($input_array)));
}
function _check_commercial_tag($tag){
	if(!empty($tag))
		return false !== strpos($tag, 'cydia::commercial');
	return false;
}
?>