<?php
	/*
		This file is part of WEIPDCRM.
	
	    WEIPDCRM is free software: you can redistribute it and/or modify
	    it under the terms of the GNU General Public License as published by
	    the Free Software Foundation, either version 3 of the License, or
	    (at your option) any later version.
	
	    WEIPDCRM is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	    GNU General Public License for more details.
	
	    You should have received a copy of the GNU General Public License
	    along with WEIPDCRM.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	/* DCRM Debian Download */
	
	error_reporting(0);
	ob_start();
	define("DCRM",true);
	require_once("manage/include/config.inc.php");
	require_once('manage/include/connect.inc.php');
	require_once("manage/include/func.php");
	
	if (!empty($_GET['request']) AND (!empty($_SERVER['HTTP_X_UNIQUE_ID']) OR DCRM_DIRECT_DOWN == 1)) {
		$r_path = $_GET['request'];
		if (pathinfo($r_path, PATHINFO_EXTENSION) != "deb") {
			httpinfo(400);
		}
		else {
			$text_id = substr($r_path, 0, strlen($r_path) - 4);
			$request_id = (int)$text_id;
		}
		$con = mysql_connect(DCRM_CON_SERVER, DCRM_CON_USERNAME, DCRM_CON_PASSWORD);
		if (!$con) {
			httpinfo(500);
		}
		else {
			mysql_query("SET NAMES utf8");
			mysql_select_db(DCRM_CON_DATABASE);
		}
		$a_query = mysql_query("UPDATE `".DCRM_CON_PREFIX."Packages` SET `DownloadTimes` = `DownloadTimes` + 1 WHERE `ID` = '" . (string)$request_id . "'",$con);
		if (!$a_query) {
			httpinfo(500);
		}
		else {
			$m_query = mysql_query("SELECT `Package`, `Version`, `Filename` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . (string)$request_id . "'");
		}
		if (!$m_query) {
			httpinfo(500);
		}
		else {
			$m_row = mysql_fetch_assoc($m_query);
		}
		if (!$m_row) {
			httpinfo(4030);
		}
		else {
			$download_path = substr($m_row['Filename'], 1);
		}
		if (!empty($download_path)) {
			mysql_close($con);
			if (file_exists($download_path)) {
				$fake_name = $m_row['Package'] . "_" . $m_row['Version'] . "_iphoneos-arm.deb";
				downFile($download_path,$fake_name);
			}
			else {
				httpinfo(404);
			}
			exit();
		}
		else {
			httpinfo(4030);
		}
	}
	else {
		httpinfo(400);
	}
?>