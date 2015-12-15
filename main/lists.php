<?php
/**
 * DCRM Packages Provider
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

$_customct = true;
require_once('system/common.inc.php');

if (!empty($_GET['request']) AND (!empty($_SERVER['HTTP_X_UNIQUE_ID']) OR DCRM_DIRECT_DOWN == 1)) {
	$lastip = DB::fetch_first("SELECT `IP` FROM `".DCRM_CON_PREFIX."UDID` WHERE `UDID` = '".$_SERVER['HTTP_X_UNIQUE_ID']."'");
	$nowip = _ip2long(getIp());
	if(!empty($lastip) && $lastip['IP'] != $nowip)
		DB::update(DCRM_CON_PREFIX.'UDID', array('IP' => $nowip), array('UDID' => $_SERVER['HTTP_X_UNIQUE_ID']));
	$r_path = $_GET['request'];
	$list_text = array('Release', 'Release.gpg', 'Packages', 'Packages.gz', 'Packages.bz2');
	if (in_array($r_path, $list_text)) {
		if (file_exists($r_path)) {
			downFile($r_path, $r_path);
		} else {
			httpinfo(404);
		}
	} else {
		httpinfo(405);
	}
} else {
	httpinfo(400);
}
?>
