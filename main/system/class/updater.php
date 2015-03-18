<?php
if(!defined('IN_DCRM')) exit('Access Denied');
class Updater{
	public static function init(){
		global $_version;
		$current_version = $_version;
		if ($current_version == VERSION) return;
		$version = $current_version;
		while($version){
			$filepath = SYSTEM_ROOT."./function/updater/{$version}.php";
			if(file_exists($filepath)){
				include $filepath;
				exit();
			} else{
				$version = substr($version, 0, strrpos($version, '.'));
			}
		}
		//include SYSTEM_ROOT.'./function/updater/fallback.php';
		exit();
	}
}
?>