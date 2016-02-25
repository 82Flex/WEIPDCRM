<?php
if(!defined('IN_DCRM')) exit('Access Denied');
switch($this->current_version){
	case '1.6.15.3.12':
	case '1.6.15.3.18':
	case '1.6.15.3.25':
		DB::query('CREATE TABLE IF NOT EXISTS `'.DCRM_CON_PREFIX.'UDID` (
					`ID` int(8) NOT NULL AUTO_INCREMENT,
					`UDID` varchar(128) NOT NULL,
					`Level` int(8) NOT NULL DEFAULT \'0\',
					`Packages` text NOT NULL,
					`Comment` varchar(512) NOT NULL,
					`TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					`Downloads` int(8) NOT NULL,
					`IP` bigint NOT NULL,
					`CreateStamp` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\',
					PRIMARY KEY (`ID`)
					) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8');
		$exists = DB::fetch_first("Describe `".DCRM_CON_PREFIX."Packages` `Level`");
		if(empty($exists))
			DB::query("ALTER TABLE `".DCRM_CON_PREFIX."Packages` ADD `Level` INT NOT NULL DEFAULT '0' AFTER `UUID` ,
					ADD `Price` CHAR( 8 ) NOT NULL AFTER `Level` ,
					ADD `Purchase_Link` VARCHAR( 512 ) NOT NULL AFTER `Price`");
		DB::query('CREATE TABLE IF NOT EXISTS `'.DCRM_CON_PREFIX.'Options` (
					`option_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					`option_name` varchar(64) NOT NULL,
					`option_value` longtext NOT NULL,
					`autoload` varchar(20) NOT NULL DEFAULT \'yes\',
					PRIMARY KEY (`option_id`)
					) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8');
		if(!get_option('udid_level'))
			update_option('udid_level', array( __('Guest'), ''));
		$purchase_link_stat_exists = DB::fetch_first("Describe `".DCRM_CON_PREFIX."Packages` `Purchase_Link_Stat`");
		// 1.6.15.3.26新增
		if(empty($purchase_link_stat_exists))
			DB::query("ALTER TABLE `".DCRM_CON_PREFIX."Packages` ADD `Purchase_Link_Stat` INT NOT NULL DEFAULT '0' AFTER `Purchase_Link`");
		update_option('autofill_depiction', '2');
		$this->update_version('1.6.15.3.26');
	case '1.6.15.3.26':
		if(file_exists(ROOT.'manage/js/'))
			deldir(ROOT.'manage/js/');
		$this->update_version('1.6.15.3.29', true);
	case '1.6.15.3.29':
		$exists = DB::fetch_first("Describe `".DCRM_CON_PREFIX."Packages` `Changelog`");
		if(empty($exists))
			DB::query("ALTER TABLE `".DCRM_CON_PREFIX."Packages` ADD `Changelog` varchar( 512 ) NOT NULL AFTER `Purchase_Link_Stat`,
						ADD `Changelog_Older_Shows` INT NOT NULL DEFAULT '0' AFTER `Changelog`");
		$this->update_version('1.6.15.4.2');
	case '1.6.15.4.2':
	case '1.6.15.5.16':
		$exist = DB::fetch_first("Describe `".DCRM_CON_PREFIX."Packages` `Level`");
		if(empty($exist))
			DB::query("ALTER TABLE `".DCRM_CON_PREFIX."Packages` ADD `Level` INT NOT NULL DEFAULT '0' AFTER `UUID`");
		$this->update_version('1.6.15.6.18');
	case '1.6.15.6.18':
		/* Rewrite Mod Check */
		if(strstr($_SERVER['PHP_SELF'], '/manage/'))
			base_url(true);
		else
			base_url();
		if(url_code(url_scheme().SITE_URL.'misc') === 200)
			update_option('rewrite_mod', 3);
		elseif(url_code(url_scheme().SITE_URL.'rewritetest') === 200)
			update_option('rewrite_mod', 2);
		else
			update_option('rewrite_mod', 1);

		update_option('php_forward', 2);
		update_option('module_enabled', 1);

		if(!file_exists(CONF_PATH.'installed.lock'))
			file_put_contents(CONF_PATH.'installed.lock', time());
		$this->update_version('1.7.15.7.12');
		break;
	default:
		$this->fallback();
		break;
}
?>