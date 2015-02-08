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

	/* DCRM Packages Provider */
	
	error_reporting(0);
	ob_start();
	define('DCRM',true);
	require_once('manage/include/func.php');
	if (!file_exists('./manage/include/connect.inc.php')) {
		httpinfo(500);
		exit();
	}
	require_once('./manage/include/config.inc.php');
	
	if (!empty($_GET['request']) AND (!empty($_SERVER['HTTP_X_UNIQUE_ID']) OR DCRM_DIRECT_DOWN == 1)) {
		$r_path = $_GET['request'];
		$list_text = array('Release', 'Packages', 'Packages.gz', 'Packages.bz2');
		if (in_array($r_path, $list_text)) {
			if (file_exists($r_path)) {
				downFile($r_path, $r_path);
			} else {
				httpinfo(404);
			}
		}
		else {
			httpinfo(405);
		}
	}
	else {
		httpinfo(400);
	}
?>
