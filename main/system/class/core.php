<?php
if (!defined('IN_DCRM')) exit();
class core {
	function init() {
		global $_version;
		require_once(CONF_PATH.'autofill.inc.php');
		$this->init_header();
		Updater::init();
		$this->init_final();
	}
	function init_header() {
		global $_customct;
		ob_start();
		if(!isset($_customct) || empty($_customct))
			header("Content-Type: text/html; charset=UTF-8");
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		header('Powered: WEIPDCRM');
		header('License: AGPL');
	}
	function init_final() {
		define('SYSTEM_STARTED', true);
		@ignore_user_abort(true);
	}
}
