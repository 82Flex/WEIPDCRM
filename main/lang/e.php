<?php
	$vaild_lang = array(
		'zh-cn',
		'en',
		'en-us',
	);
	if (!defined("DCRM_LANG")) {
		$lang_arr = explode(",", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$lang = trim($lang_arr[0]);
	} else {
		$lang = DCRM_LANG;
	}
	if (!in_array($lang, $vaild_lang, true) || !file_exists("./lang/".$lang.".php")) {
		$lang = "zh-cn";
	}
	include_once("./lang/".$lang.".php");
	function _e() {
		global $_e;
		$numargs = func_num_args();
		$args = func_get_args();
		if ($numargs < 1) {
			return false;
		} elseif ($numargs == 1) {
			return($_e[$args[0]]);
		} else {
			$string = $_e[$args[0]];
			for ($i = 1; $i < count($args); $i++) {
				$string = preg_replace("/%@/i", $args[$i], $string, 1);
			}
			return($string);
		}
	}
?>