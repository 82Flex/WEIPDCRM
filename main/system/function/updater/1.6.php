<?php
if(!defined('IN_DCRM')) exit('Access Denied');
if($current_version == '1.6.15.3.12'){
	// This is a example.
	//DB::query('CREATE TABLE IF NOT EXISTS `'.DCRM_CON_PREFIX.'cache` ( `k` varchar(32) NOT NULL, `v` TEXT NOT NULL, PRIMARY KEY (`k`)) ENGINE=InnoDB DEFAULT CHARSET=utf8');
	update_final('1.6.15.3.18');
}