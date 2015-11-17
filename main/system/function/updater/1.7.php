<?php
if(!defined('IN_DCRM')) exit('Access Denied');
if($current_version == '1.7.15.7.12'){
	$exists = DB::fetch_first("Describe `".DCRM_CON_PREFIX."Packages` `Minimum_System_Support`");
	if(empty($exists))
		DB::query("ALTER TABLE `".DCRM_CON_PREFIX."Packages` ADD `Minimum_System_Support` CHAR ( 8 ) NOT NULL AFTER `CreateStamp`,
					ADD `Maxmum_System_Support` CHAR ( 8 ) NOT NULL AFTER `Minimum_System_Support`");
	update_final('1.7.15.11.17');
}
?>