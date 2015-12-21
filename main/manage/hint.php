<?php
/**
 * DCRM Debian Information Ajax API
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

session_start();
define('MANAGE_ROOT', dirname(__FILE__).'/');
define('ABSPATH', dirname(MANAGE_ROOT).'/');
require_once ABSPATH.'system/common.inc.php';

if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
	if (isset($_POST['action']) && $_POST['action'] == "adv_info") {
		$item_id = (int)$_POST['item'];
		$item_col = DB::real_escape_string($_POST['col']);
		if (empty($item_col) || $item_id == 0) {
			die("NULL");
		}
		$item = DB::fetch_first("SELECT * FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $item_id . "'");
		if (empty($item[$item_col]) && $item[$item_col] !== '0') {
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