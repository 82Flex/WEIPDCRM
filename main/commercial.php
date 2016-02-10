<?php
/**
 * DCRM Commercial Package API
 * Copyright (c) 2015 Hintay <hintay@me.com>
 *
 * This file is part of WEIPDCRM.
 * 
 * WEIPDCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * WEIPDCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with WEIPDCRM.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Designed by Hintay in China
 */

if (!isset($package_info)) {
	if(!isset($_GET['Package']))
		exit();

	if(!defined('SYSTEM_STARTED')){
		require_once('system/common.inc.php');
		base_url();
	}

	if(!isset($detect)){
		class_loader('Mobile_Detect');
		$detect = new Mobile_Detect;
	}
	$package_info = DB::fetch_first(DB::prepare("SELECT `Package`, `Name`, `Tag`, `Level`, `Price`, `Purchase_Link` FROM `".DCRM_CON_PREFIX."Packages` WHERE `Package` = '%s' AND `Stat` = '1'", $_GET['Package']));
	if(!$package_info)
		exit();
?>
<!DOCTYPE html SYSTEM "about:legacy-compat">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Activation · Cydia</title>
<link rel="stylesheet" type="text/css" href="./css/menes.min.css">
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="format-detection" content="telephone=no">
<script type="text/javascript" src="./js/fastclick.js"></script>
<script type="text/javascript" src="./js/cytyle.js"></script>
<script type="text/javascript" src="./js/menes.js"></script>
</head>
<body class="pinstripe">
<panel>
<?php
}
if(check_commercial_tag($package_info['Tag'])) {
	$nowip = _ip2long(getIp());

	if(isset($_GET['udid'])){
		$udid_status = DB::fetch_first(DB::prepare("SELECT `Packages`, `Level`, `IP` FROM `".DCRM_CON_PREFIX."UDID` WHERE `UDID` = '%s' LIMIT 1", $_GET['udid']));
	} else {
		$udid_status = DB::fetch_first("SELECT `Packages`, `Level`, `IP` FROM `".DCRM_CON_PREFIX."UDID` WHERE `IP` = '".$nowip."' LIMIT 1");
	}
	$candownloaded = false;
	if(!empty($udid_status)){
		if(!empty($udid_status['Packages'])) {
			$udid_packages = TrimArray(explode(',', $udid_status['Packages']));
			if(in_array($package_info['Package'], $udid_packages, true))
				$candownloaded = true;
		} else {
			$udid_level = (int)$udid_status['Level'];
			$package_level = (int)$package_info['Level'];
			if($udid_level > $package_level)
				$candownloaded = true;
		}
	}

	if($candownloaded) {
		//$not_installed_button = 'null, null, null';
		$purchase_status = 'candownload';
		$fieldset_color = '#eefff0';
		$fieldset_icons = 'https://cache.saurik.com/crystal/256x256/actions/agt_action_success.png';
	} else {
		$fieldset_color = '#ffc040';
		//$not_installed_button = '"'.__('Recheck').'", "Highlighted", function(){reload_();}';
		$purchase_status = 'protection';
		$fieldset_icons = 'https://cache.saurik.com/crystal/64x64/apps/alert.png';
	}
?>
<fieldset style="background-color:<?php echo($fieldset_color); ?>">
	<a target="_popup" <?php if('notpurchase' == $purchase_status) echo('href="'.htmlspecialchars(SITE_PATH.$package_info['Purchase_Link']).'"'); ?>>
		<img class="icon" src="<?php echo($fieldset_icons); ?>">
		<div>
			<div>
<?php 
	switch($purchase_status){
		case 'protection':
?>
				<label>
					<p><?php _e('Package Protected'); ?></p>
				</label>
<?php
			break;
		case 'candownload':
?>
				<label>
					<p><?php _e('Package Available'); ?></p>
				</label>
<?php
			break;
	}
?>
			</div>
		</div>
	</a>
</fieldset>
<?php
}
?>