<?php
if(!defined('IN_DCRM')) exit('Access Denied');
switch($this->current_version){
	case '1.7.15.7.12':
	case '1.7.15.11.17':
		$exist = DB::fetch_first("Describe `".DCRM_CON_PREFIX."Packages` `Video_Preview`");
		if(empty($exist))
			DB::query("ALTER TABLE `".DCRM_CON_PREFIX."Packages` ADD `Video_Preview` varchar(512) NOT NULL AFTER `Changelog_Older_Shows`");
		$System_Support = DB::fetch_first("Describe `".DCRM_CON_PREFIX."Packages` `System_Support`");
		if(empty($System_Support))
			DB::query("ALTER TABLE `".DCRM_CON_PREFIX."Packages` ADD `System_Support` longtext NOT NULL AFTER `Video_Preview`");

		if($this->current_version == '1.7.15.11.17' && empty($System_Support)){
			$packages = DB::fetch_all("SELECT `ID`, `Minimum_System_Support`, `Maxmum_System_Support` FROM `".DCRM_CON_PREFIX."Packages`");
			foreach($packages as $package){
				if(!empty($package['Minimum_System_Support']) && !empty($package['Maxmum_System_Support']))
					DB::update(DCRM_CON_PREFIX.'Packages', array('System_Support' => serialize(array('Minimum' => $package['Minimum_System_Support'], 'Maxmum' => $package['Maxmum_System_Support']))), array('ID' => $package['ID']));
			}
			DB::query("ALTER TABLE `".DCRM_CON_PREFIX."Packages` DROP `Maxmum_System_Support`, DROP `Minimum_System_Support");
		}

		$this->update_version('1.7.15.11.21');
	case '1.7.15.11.21':
		$exist = DB::fetch_first("Describe `".DCRM_CON_PREFIX."Packages` `ScreenShots`");
		if(empty($exist)){
			DB::query("ALTER TABLE `".DCRM_CON_PREFIX."Packages` ADD `ScreenShots` longtext NOT NULL AFTER `System_Support`");
			$screenshots = DB::fetch_all("SELECT * FROM `".DCRM_CON_PREFIX."ScreenShots`");
			if(!empty($screenshots)){
				foreach($screenshots as $screenshot){
					$screenshots_cleaned[$screenshot['PID']][] = &$screenshots[$screenshot['Image']];
				}
				foreach($screenshots_cleaned as $package_id => $screenshots_pid){
					DB::update(DCRM_CON_PREFIX.'Packages', array('ScreenShots' => serialize($screenshots_pid)), array('ID' => $package_id));
				}
			}
			DB::query("DROP TABLE IF EXISTS `".DCRM_CON_PREFIX."ScreenShots`");
		}
		$this->update_version('1.7.15.12.9');
	case '1.7.15.12.9':
	case '1.7.15.12.12':
		moveDir(ROOT.'manage/image', ROOT.'image', false, false);

		function manage_replace($input){
			return str_replace('/manage/', '/', $input);
		}

		$packages = DB::fetch_all("SELECT `ScreenShots`, `ID` FROM `".DCRM_CON_PREFIX."Packages`");
		foreach ($packages as $package) {
			if(strpos($package['ScreenShots'], '/manage/') === false)
				continue;
			$screenshots = unserialize($package['ScreenShots']);
			DB::update(DCRM_CON_PREFIX.'Packages', array('ScreenShots' => serialize(array_map('manage_replace', $screenshots))), array('ID' => $package['ID']));
		}
		$this->update_version('1.7.15.12.30', true);
	case '1.7.15.12.30':
		moveDir(ROOT.'icons', ROOT.'icon', false, false);
		$langfile_suffixs = array('install-', 'manage-', 'system-', '');
		foreach ($langfile_suffixs as $suffix) {
			unlink(LANG_DIR.'/'.$suffix.'zh_CN.po');
			unlink(LANG_DIR.'/'.$suffix.'zh_CN.mo');
		}
		$this->update_version('1.7.16.2.4', true);
	case '1.7.16.2.4':
		DB::query("ALTER TABLE `".DCRM_CON_PREFIX."Packages` MODIFY `Package` varchar(512) NULL,
			MODIFY `Source` varchar(512) NULL,
			MODIFY `Version` varchar(512) NULL,
			MODIFY `Priority` varchar(512) NULL,
			MODIFY `Section` varchar(512) NULL,
			MODIFY `Essential` varchar(512) NULL,
			MODIFY `Maintainer` varchar(512) NULL,
			MODIFY `Pre-Depends` varchar(512) NULL,
			MODIFY `Depends` varchar(512) NULL,
			MODIFY `Recommends` varchar(512) NULL,
			MODIFY `Suggests` varchar(512) NULL,
			MODIFY `Conflicts` varchar(512) NULL,
			MODIFY `Provides` varchar(512) NULL,
			MODIFY `Replaces` varchar(512) NULL,
			MODIFY `Enhances` varchar(512) NULL,
			MODIFY `Filename` varchar(512) NULL,
			MODIFY `Size` int(11) NULL,
			MODIFY `Installed-Size` varchar(512) NULL,
			MODIFY `Description` varchar(512) NULL,
			MODIFY `Multi` varchar(2048) NULL,
			MODIFY `Origin` varchar(512) NULL,
			MODIFY `Bugs` varchar(512) NULL,
			MODIFY `Name` varchar(512) NULL,
			MODIFY `Author` varchar(512) NULL,
			MODIFY `Sponsor` varchar(512) NULL,
			MODIFY `Homepage` varchar(512) NULL,
			MODIFY `Website` varchar(512) NULL,
			MODIFY `Depiction` varchar(512) NULL,
			MODIFY `Icon` varchar(512) NULL,
			MODIFY `MD5sum` varchar(512) NULL,
			MODIFY `SHA1` varchar(512) NULL,
			MODIFY `SHA256` varchar(512) NULL,
			MODIFY `Stat` int(1) NULL,
			MODIFY `Tag` varchar(512) NULL,
			MODIFY `UUID` varchar(512) NOT NULL,
			MODIFY `Level` CHAR( 8 ) NULL,
			MODIFY `Price` CHAR( 8 ) NULL,
			MODIFY `Purchase_Link` VARCHAR( 512 ) NULL,
			MODIFY `Purchase_Link_Stat` int( 1 ) NOT NULL DEFAULT '0',
			MODIFY `Changelog` varchar( 512 ) NULL,
			MODIFY `Changelog_Older_Shows` INT NOT NULL DEFAULT '0',
			MODIFY `Video_Preview` varchar(512) NULL,
			MODIFY `System_Support` longtext NULL,
			MODIFY `ScreenShots` longtext NULL,
			MODIFY `DownloadTimes` int(8) NOT NULL DEFAULT '0'");
		DB::query("ALTER TABLE `".DCRM_CON_PREFIX."UDID` MODIFY `Packages` varchar(512) NULL,
			MODIFY `Comment` varchar(512) NULL,
			MODIFY `Downloads` int(8) NOT NULL DEFAULT '0',
			MODIFY `IP` bigint(20) NULL");
		$this->update_version('1.7.16.7.31');
		break;
	default:
		$this->fallback();
		break;
}
?>