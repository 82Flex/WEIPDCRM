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
	
	if (!file_exists('./include/connect.inc.php')) {
		$root .= ($directory = trim(dirname($_SERVER["SCRIPT_NAME"]), "/\,")) ? "/$directory/" : "/";

		header('Location: '.$root.'../init');
		exit;
	}
	
	/* DCRM Management Jump Page */
	
	ob_start();
	session_start();
	if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
		header("Location: center.php");
	}
	else {
		header("Location: login.php");
	}
	exit();
?>