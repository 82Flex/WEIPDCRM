<?php
/**
 * This file is part of WEIPDCRM.
 * 
 * WEIPDCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * WEIPDCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with WEIPDCRM.  If not, see <http://www.gnu.org/licenses/>.
 */

/* DCRM Debian Download */

if (!file_exists('./system/config/connect.inc.php')) {
	header("HTTP/1.1 500 Internal Server Error");
	header("Status: 500 Internal Server Error");
	exit();
}
require_once('system/common.inc.php');

if (!empty($_GET['request']) AND (!empty($_SERVER['HTTP_X_UNIQUE_ID']) OR DCRM_DIRECT_DOWN == 1)) {
	$r_path = $_GET['request'];
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
	$m_row = DB::fetch_first("SELECT `Package`, `Version`, `Architecture`, `Filename`, `Tag`, `DownloadTimes`, `Level`, `Price` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . (string)$request_id . "'");
	
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
								if(empty($m_row['Price']))
									httpinfo(4033);
								else
									httpinfo(4032);
							}
						} else {
							$udid_level = (int)$udid_status['Level'];
							$package_level = (int)$m_row['Level'];
							if($udid_level <= $package_level) {
								if(empty($m_row['Price']))
									httpinfo(4033);
								else
									httpinfo(4032);
							}
						}
						DB::update(DCRM_CON_PREFIX.'UDID', array('Downloads' => ((int)$udid_status['Downloads'] + 1)), array('UDID' => $_SERVER['HTTP_X_UNIQUE_ID']));
					} else {
						if(empty($m_row['Price']))
							httpinfo(4033);
						else
							httpinfo(4032);
					}
				} else {
					httpinfo(4030);
				}
			}
			DB::update(DCRM_CON_PREFIX.'Packages', array('DownloadTimes' => ((int)$m_row['Downloads'] + 1)), array('ID' => (string)$request_id));
			downFile($download_path, $fake_name);
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