<?php
/**
 * DCRM Debian Download Router
 * Copyright (c) 2015 Hintay <hintay@me.com>
 * Copyright (c) 2015 i_82 <i.82@me.com>
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
 */

if (!file_exists('./system/config/connect.inc.php')) {
	header("HTTP/1.1 500 Internal Server Error");
	header("Status: 500 Internal Server Error");
	exit();
}
$_customct = true;
require_once('system/common.inc.php');
set_time_limit(0);
@ini_set("max_execution_time", 1800);
base_url();

if (!empty($_GET['request']) && (!empty($_SERVER['HTTP_X_UNIQUE_ID']) || DCRM_DIRECT_DOWN == 1)) {
	$r_path = $_GET['request'];

	$php_forward = get_option('php_forward');
	$webserver = explode('/', $_SERVER['SERVER_SOFTWARE']);
	// Apache URL重写模式下无法使用X-sendfile，因此在此处跳转
	if($webserver[0] == 'Apache' && $php_forward == 1 && strstr($_SERVER["REQUEST_URI"], '/debs/') != false){
		header('Location: '.SITE_URL.'downloads.php?request='.$r_path);
		exit();
	}

	if (pathinfo($r_path, PATHINFO_EXTENSION) != "deb") {
		httpinfo(400);
	} else {
		$text_id = substr($r_path, 0, strlen($r_path) - 4);
		if (ctype_digit($text_id) && intval($text_id) <= 10000) {
			$request_id = intval($text_id);
		} else {
			httpinfo(405);
			exit();
		}
	}
	$m_row = DB::fetch_first("SELECT `Package`, `Version`, `Architecture`, `Filename`, `Tag`, `DownloadTimes`, `Level` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . (string)$request_id . "'");
	
	if (!$m_row) {
		httpinfo(404);
	} else {
		$download_path = substr($m_row['Filename'], 1);
	}
	if (!empty($download_path)) {
		if (file_exists($download_path)) {
			$fake_name = $m_row['Package'] . "_" . $m_row['Version'] . "_" . $m_row['Architecture'] . ".deb";
			if(check_commercial_tag($m_row['Tag'])){
				if(isset($_SERVER['HTTP_X_UNIQUE_ID'])) {
					$udid_status = DB::fetch_first("SELECT `Packages`, `Level`, `Downloads` FROM `".DCRM_CON_PREFIX."UDID` WHERE `UDID` = '".$_SERVER['HTTP_X_UNIQUE_ID']."' LIMIT 1");
					if(!empty($udid_status)){
						if(!empty($udid_status['Packages'])) {
							$udid_packages = TrimArray(explode(',', $udid_status['Packages']));
							if(!in_array($m_row['Package'], $udid_packages, true)) {
								httpinfo(4033);
							}
						} else {
							$udid_level = (int)$udid_status['Level'];
							$package_level = (int)$m_row['Level'];
							if($udid_level <= $package_level) {
								httpinfo(4033);
							}
						}
						DB::update(DCRM_CON_PREFIX.'UDID', array('Downloads' => ((int)$udid_status['Downloads'] + 1)), array('UDID' => $_SERVER['HTTP_X_UNIQUE_ID']));
					} else {
						httpinfo(4033);
					}
				} else {
					httpinfo(4030);
				}
			}
			DB::update(DCRM_CON_PREFIX.'Packages', array('DownloadTimes' => ((int)$m_row['DownloadTimes'] + 1)), array('ID' => (string)$request_id));
			if($php_forward == 2 || $php_forward == ""){
				downFile($download_path, $fake_name);
			} else {
				function xsendfile_header($self_header, $relative = true){
					global $download_path, $fake_name;
					header('Accept-Ranges: bytes');
					header('Content-type: application/octet-stream');
					header('Content-Disposition: attachment; filename="' . rawurlencode($fake_name). '"');
					header($self_header.': '.($relative ? SITE_PATH.substr($download_path, 2) : $download_path));
					exit();
				}
				$module_enabled = get_option('module_enabled');
				switch($webserver[0]){
					case 'nginx':
						xsendfile_header('X-Accel-Redirect');
						break;
					case 'Apache':
						if($module_enabled == 2) {
							xsendfile_header('X-sendfile', false);
						} elseif (function_exists('apache_get_modules')) {
							$Mods = apache_get_modules();
							if(in_array('mod_xsendfile', $Mods)){
								xsendfile_header('X-sendfile', false);
							}
						}
						if(file_exists(ROOT.'downloads/.htaccess'))
							unlink(ROOT.'downloads/.htaccess');
						break;
					case 'Lighttpd':
						if($module_enabled == 2) {
							xsendfile_header('X-LIGHTTPD-send-file');
						}
						break;
					// IIS? Hehe
					//case 'IIS':
				}
				header('Location: '.SITE_URL.$download_path);
				exit();
			}
		} else {
			httpinfo(404);
		}
		exit();
	} else {
		httpinfo(500);
	}
} else {
	httpinfo(400);
}
?>
