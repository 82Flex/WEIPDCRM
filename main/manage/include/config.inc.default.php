<?php
	if (!defined("DCRM")) {
		exit;
	}
	//define("DCRM_LANG", "zh-cn");
	include_once("./lang/e.php");
	define("DCRM_MAXLOGINFAIL",5);
	define("DCRM_SHOWLIST",1);
	define("DCRM_SHOW_NUM",5);
	define("DCRM_SPEED_LIMIT",0);
	define("DCRM_DIRECT_DOWN",0);
	define("DCRM_MOBILE",1);
	define("DCRM_SCREENSHOTS",1);
	define("DCRM_REPORTING",1);
	define("DCRM_UPDATELOGS",1);
	define("DCRM_MOREINFO",1);
	define("DCRM_MULTIINFO",1);
	define("DCRM_LISTS_METHOD",3);
	define("DCRM_CHECK_METHOD",1);
	define("DCRM_REPOURL","");
	define("DCRM_LOGINFAILRESETTIME",600);
?>