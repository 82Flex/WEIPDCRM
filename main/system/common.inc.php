<?php
/**
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

error_reporting(E_ALL ^ E_NOTICE);
define("DCRM",true);
define('IN_DCRM', true);
define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');
define('TIMESTAMP', time());
define('VERSION', '1.7.16.7.31');
define('UI_VERSION', '1.0');

define('DEBUG_ENABLED', isset($_GET['debug']));
error_reporting(DEBUG_ENABLED ? E_ALL & !E_NOTICE & !E_STRICT : E_ERROR | E_PARSE);
@ini_set('display_errors', DEBUG_ENABLED);

require_once SYSTEM_ROOT.'./class/error.php';
set_exception_handler(array('Crash', 'exception_error'));

function class_loader($class_name, $extension = 'php'){
	$file_path = "system/class/{$class_name}.{$extension}";
	$real_path = ROOT.strtolower($file_path);
	if (!file_exists($real_path)) {
		throw new Exception('Ooops, system file is losing: '.strtolower($file_path));
	} else {
		require_once $real_path;
	}
}

/* Base Configuration File Check */
if(file_exists(ROOT.'manage/include/connect.inc.php')){
	define('CONF_PATH', ROOT.'manage/include/');
} elseif(file_exists(SYSTEM_ROOT.'config/connect.inc.php')) {
	define('CONF_PATH', SYSTEM_ROOT.'config/');
} else {
	if (strstr($_SERVER['PHP_SELF'], '/manage/'))
		$root = dirname(dirname($_SERVER['PHP_SELF'])).'/';
	else
		$root = dirname($_SERVER['PHP_SELF']).'/';
	header('Location: '.$root.'install');
	exit();
}
require_once(CONF_PATH.'connect.inc.php');

/* Load Localization System */
if(file_exists(CONF_PATH.'config.inc.php')){
	require_once(CONF_PATH.'config.inc.php');
}
require_once(SYSTEM_ROOT.'languages/l10n.php');
$link_language = localization_load();
require_once(SYSTEM_ROOT.'class/locale.php');

class_loader('core');
class_loader('db');
class_loader('Updater');

require_once SYSTEM_ROOT.'./function/core.php';
$system = new core();
if(isset($_customct) && $_customct === true) $system->CCT = true;
$system->init();
