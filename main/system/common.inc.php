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

if (version_compare(PHP_VERSION, '5.6', '>=')) {
	echo 'DCRM not compatible with PHP 5.6+ now, please check your PHP version.';
	exit();
}
error_reporting(E_ALL ^ E_NOTICE);
define("DCRM",true);
define('IN_DCRM', true);
define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');
define('TIMESTAMP', time());
define('VERSION', '1.7.15.7.12');
define('UI_VERSION', '1.0');

define('DEBUG_ENABLED', isset($_GET['debug']));
error_reporting(DEBUG_ENABLED ? E_ALL & !E_NOTICE & !E_STRICT : E_ERROR | E_PARSE);
@ini_set('display_errors', DEBUG_ENABLED);

require_once SYSTEM_ROOT.'./class/error.php';
set_exception_handler(array('error', 'exception_error'));

function class_loader($class_name){
	$file_path = "system/class/{$class_name}.php";
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

/* Language Switch */
if(file_exists(CONF_PATH.'config.inc.php')){
	require_once(CONF_PATH.'config.inc.php');
}
require_once(SYSTEM_ROOT.'languages/l10n.php');
$link_language = localization_load();
require_once(SYSTEM_ROOT.'class/locale.php');

$version_file = SYSTEM_ROOT.'version.inc.php';
if(!file_exists($version_file)){
	@touch($version_file);
	if(!is_writable($version_file)) throw new Exception('Version file is not writable!');
	$content = '<?php'.PHP_EOL.'/* Auto-generated version file */'.PHP_EOL.'$_version = \'1.5\';'.PHP_EOL.'?>';
	file_put_contents($version_file, $content);
}
require_once($version_file);

class_loader('core');
class_loader('db');
class_loader('Updater');

/*if (function_exists('spl_autoload_register')){
	spl_autoload_register('class_loader');
}else{
	function __autoload($class_name){
		class_loader($class_name);
	}
}*/

// Move to base_url() function.

require_once SYSTEM_ROOT.'./function/core.php';

$system = new core();
$system->init();
