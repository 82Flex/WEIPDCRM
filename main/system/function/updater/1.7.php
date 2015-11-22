<?php
if(!defined('IN_DCRM')) exit('Access Denied');
if($current_version == '1.7.15.7.12' || $current_version == '1.7.15.11.17'){
	DB::query("ALTER TABLE `".DCRM_CON_PREFIX."Packages` ADD `Video_Preview` varchar(512) NOT NULL AFTER `Changelog_Older_Shows`,
				ADD `System_Support` longtext NOT NULL AFTER `Video_Preview`");

	if($current_version == '1.7.15.11.17'){
		$packages = DB::fetch_all("SELECT `ID`, `Minimum_System_Support`, `Maxmum_System_Support` FROM `".DCRM_CON_PREFIX."Packages`");
		foreach($packages as $package){
			if(!empty($package['Minimum_System_Support']) && !empty($package['Maxmum_System_Support']))
				DB::update(DCRM_CON_PREFIX.'Packages', array('System_Support' => serialize(array('Minimum' => $package['Minimum_System_Support'], 'Maxmum' => $package['Maxmum_System_Support']))), array('ID' => $package['ID']));
		}

		DB::query("ALTER TABLE `".DCRM_CON_PREFIX."Packages` DROP `Maxmum_System_Support`, DROP `Minimum_System_Support");
	}

	update_final('1.7.15.11.21');
}
?>