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
	$a_query = DB::query("UPDATE `".DCRM_CON_PREFIX."Packages` SET `DownloadTimes` = `DownloadTimes` + 1 WHERE `ID` = '" . (string)$request_id . "'");
	if (!$a_query) {
		httpinfo(500);
	} else {
		$m_query = DB::query("SELECT `Package`, `Version`, `Architecture`, `Filename` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . (string)$request_id . "'");
	}
	if (!$m_query) {
		httpinfo(500);
	} else {
		$m_row = mysql_fetch_assoc($m_query);
	}
	if (!$m_row) {
		httpinfo(404);
	} else {
		$download_path = substr($m_row['Filename'], 1);
	}
	if (!empty($download_path)) {
		if (file_exists($download_path)) {
			$fake_name = $m_row['Package'] . "_" . $m_row['Version'] . "_" . $m_row['Architecture'] . ".deb";
			downFile($download_path,$fake_name);
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