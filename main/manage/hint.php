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

/* DCRM Debian Information Ajax API */

session_start();
define("DCRM",true);
define('MANAGE_ROOT', dirname(__FILE__).'/');
define('ABSPATH', dirname(MANAGE_ROOT).'/');
require_once ABSPATH.'system/common.inc.php';

if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
	$con = mysql_connect(DCRM_CON_SERVER, DCRM_CON_USERNAME, DCRM_CON_PASSWORD);
	if (!$con) {
		die("MYSQL Error!");
	}
	mysql_query("SET NAMES utf8");
	$select  = mysql_select_db(DCRM_CON_DATABASE);
	if (!$select) {
		die("MYSQL Error!");
	}
	if (isset($_POST['action']) && $_POST['action'] == "adv_info") {
		$item_id = (int)$_POST['item'];
		$item_col = mysql_real_escape_string($_POST['col']);
		if (empty($item_col) || $item_id == 0) {
			die("NULL");
		}
		$item_query = mysql_query("SELECT * FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $item_id . "'");
		if (!$item_query) {
			die("MYSQL Error.");
		}
		$item = mysql_fetch_assoc($item_query);
		if (empty($item[$item_col])) {
			die("NULL");
		} else {
			die($item[$item_col]);
		}
	} else {
		die("Invaild Operation.");
	}
} else {
	die("Permission Denied.");
}
?>