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
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once("include/connect.inc.php");
	header("Content-Type: text/html; charset=UTF-8");
	
	if (isset($_SESSION['connected'])) {
		$con = mysql_connect($server,$username,$password);
		if (!$con) {
			die("MYSQL Error!");
		}
		mysql_query("SET NAMES utf8",$con);
		$select  = mysql_select_db($database,$con);
		if (!$select) {
			die("MYSQL Error!");
		}
		if (isset($_POST['action']) && $_POST['action'] == "adv_info") {
			$item_id = (int)$_POST['item'];
			$item_col = mysql_real_escape_string($_POST['col']);
			if (empty($item_col) || $item_id == 0) {
				die("NULL");
			}
			$item_query = mysql_query("SELECT * FROM `Packages` WHERE `ID` = '" . $item_id . "'",$con);
			if (!$item_query) {
				die("MYSQL Error.");
			}
			$item = mysql_fetch_assoc($item_query);
			if (empty($item[$item_col])) {
				die("NULL");
			}
			else {
				die($item[$item_col]);
			}
		}
		else {
			die("Invaild Operation.");
		}
	}
	else {
		die("Permission Denied.");
	}
	
?>