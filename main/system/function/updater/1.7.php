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
		moveDir(ROOT.'manage/image', ROOT.'image');

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
		break;
	default:
		$this->fallback();
		break;
}
?>