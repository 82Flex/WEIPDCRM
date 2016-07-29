<?php
/**
 * DCRM Database Configure
 * Copyright (c) 2015 Hintay <hintay@me.com>
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
 
header('Content-Type: text/html; charset=utf-8');
define("DCRM", true);

error_reporting(E_ALL ^ E_WARNING);
require_once('function.php');
$header_title = __('Configuration Wizard');

$notice = check_notice( $install = false );

$step = isset($_GET['step']) ? $_GET['step'] : 0;

$disabled = true;
$env_vars = check_env($disabled);
$dir_file_vars = check_dir($disabled);
$func_vars = check_func($disabled);

if( $step != 0 && $step != 1 && $disabled == false ){
	header('location: setup-config.php?step=1&'.$step_language);
	exit();
}

switch($step) {
	case 0:
		display_header($notice);
		if (empty($notice) && available(BASE_URL.'rewritetest') !== 200):
?>
		<div class="alert alert-warning"><strong><?php _e('Notice: '); ?></strong><?php _e('The server does not support URL Rewrite or cannot detect automatically. DCRM will disable this function and you can change this option later in Settings.'); ?></div>
<?php 	elseif(available(BASE_URL.'misc') !== 200): ?>
		<div class="alert alert-warning"><strong><?php _e('Notice: '); ?></strong><?php _e('You should update your Rewrite config to enable Elegant Mod.'); ?></div>
<?php 	endif; ?>
		<p><strong><?php _e('Welcome to DCRM!'); ?></strong></p>
		<p><a href="https://github.com/Lessica/WEIPDCRM">DCRM</a> <?php _e('is a Darwin Cydia Repository Manager write by PHP.'); ?> <?php _e('This project is a original designs by <a href="http://weibo.cn/u/3246210680">@i_82</a>, and written collaboratively by <a href="http://weibo.com/hintay">@Hintay</a>.'); ?></p>
		<p><?php _e('If you pay close attention to <a href="http://weibo.cn/u/3246210680">i_82</a> and some of his <a href="http://82flex.com/projects">projects</a>. Also wanted to help him, welcome to donate him.'); ?></p>
	<blockquote class="thanks">
		<font color="#4169e1" size="3"><strong><?php _e('Credits'); ?></strong></font>
		<br><?php _e('Touch Sprite Team'); ?> Z<?php _ex(', ' , 'Punctuation'); ?>F<?php _ex(', ' , 'Punctuation'); ?>K<?php _ex(', ' , 'Punctuation'); ?><?php _e('WeiPhone'); ?> <a target="_blank" href="http://weibo.cn/375584554">北国飘雪</a> <?php _ex('support' , 'Credits'); ?>
		<br><?php _e('E-pal'); ?> <a target="_blank" href="http://weibo.com/u/1766730601">zsm1703</a><?php _ex(', ' , 'Punctuation'); ?><a target="_blank" href="http://weibo.com/u/2175594103">Naville</a><?php _ex(', ' , 'Punctuation'); ?><a target="_blank" href="http://weibo.com/u/1931192555">Q某某某某</a><?php _ex(', ' , 'Punctuation'); ?><a target="_blank" href="http://weibo.com/u/3254325910">摇滚米饭_</a><?php _ex(', ' , 'Punctuation'); ?>
		<br><?php _e('WeiPhone Test Group'); ?> <a target="_blank" href="http://weibo.com/u/1675423275">Sunbelife</a><?php _ex(', ' , 'Punctuation'); ?><?php _e('WeiPhone Technology Group'); ?> <a target="_blank" href="http://weibo.cn/nivalxer">NivalXer</a><?php _ex(', ' , 'Punctuation'); ?><a target="_blank" href="http://weibo.com/u/1417725530">ioshack</a> <?php _e('provide viewpoints'); ?>
		<br><?php _e('WeiPhone Technology Group'); ?> <a target="_blank" href="http://weibo.com/u/2004244347">autopear</a><?php _e('\'s articles: '); ?><a target="_blank" href="http://bbs.feng.com/read-htm-tid-669283.html">从零开始搭建 Cydia™ 软件源，制作 Debian 安装包</a>
		<br><?php _e('Also, the father of the Cydia™'); ?> <a target="_blank" href="http://www.saurik.com/">Saurik</a><?php _e('\'s articles: '); ?><a target="_blank" href="http://www.saurik.com/id/7">How to host a Cydia™ Repository</a>
	</blockquote>
	<br/>
	<p><?php _e('Before getting started, you will need to know the following items before proceeding.'); ?></p>
	<ol>
		<li><?php _e('Database name'); ?></li>
		<li><?php _e('Database username'); ?></li>
		<li><?php _e('Database password'); ?></li>
		<li><?php _e('Database host'); ?></li>
		<li><?php _e('Table prefix (if you want to run more than one DCRM in a single database)'); ?></li>
	</ol>
	<p><strong><?php _e('If for any reason you can&#8217;t enter next step, don&#8217;t worry. All this does is fill in the database information to a configuration file. You may also simply open <code>/system/config/connect.inc.default.php</code> in a text editor, fill in your information, and save it as <code>connect.inc.php</code>.'); ?></strong></p>
	<p><?php _e('Recommendations before the installation read the accompanying documentation (readme.html), if still not understand of place, can post them to <a href="https://github.com/Lessica/WEIPDCRM/issues/new">GitHub Issues</a>.'); ?></p>
	<p><?php _e('If you&#8217;re all ready&hellip;'); ?></p>

	<?php if ($notice != '') { ?>
			<p class="step"><a href="setup-config.php?<?php echo $step_language; ?>" class="button"><?php _e('Can&#8217;t start!'); ?></a></p>
	<?php } else { ?>
			<p class="step"><a href="setup-config.php?step=1&amp;<?php echo $step_language; ?>" class="button"><?php _e('Let&#8217;s go!'); ?></a></p>
	<?php } ?>
			<div align="center"><small>Copyright&copy; 2013–<?php echo date('Y'); ?> <strong>i_82</strong> &amp; <strong>Hintay</strong>. All rights reserved.</small></div>
	<?PHP break;

	case 1:
		display_header($notice);
		include_once 'setup-check.php';
?>
	<p class="step"><a href="setup-config.php?step=1&amp;<?php echo $step_language; ?>" class="button"><?php _e( 'Check again' ); ?></a>  
	<?php if ($disabled == true && $notice !== ''):?>
		<a href="setup-config.php?<?php echo $step_language; ?>" class="button"><?php _e( 'Error!' ); ?></a>
	<?php else:?>
		<a href="setup-config.php?step=2&amp;<?php echo $step_language; ?>" class="button"><?php _ex( 'Next', 'Button' ); ?></a>
	<?php endif;?>
	</p>

<?php
		break;

	case 2:
		display_header($notice);
?>
	<form method="post" action="setup-config.php?step=3&amp;<?php echo $step_language; ?>">
	<p><?php _e('Below you should enter your database connection details. If you&#8217;re not sure about these, contact your host.') ?></p>
	<table class="form-table">
		<tr><th scope="row"><label for="dbhost"><?php _e( 'Database Host' ); ?></label></th><td><input name="dbhost" id="dbhost" type="text" value="localhost" size="35" /></td><td><?php _e('Required, generally is \'localhost\'') ?></td></tr>
		<tr><th scope="row"><label for="dbport"><?php _e( 'Database Port' ); ?></label></th><td><input name="dbport" id="dbport" type="text" value="3306" size="35" /></td><td><?php _e('Required, generally is \'3306\'') ?></td></tr>
		<tr><th scope="row"><label for="uname"><?php _e( 'User Name' ); ?></label></th><td><input name="uname" id="uname" type="text" size="35" value="root" /></td><td><?php _e( 'Your MySQL username' ); ?></td></tr>
		<tr><th scope="row"><label for="pwd"><?php _e( 'Password' ); ?></label></th><td><input name="pwd" id="pwd" type="text" size="35" value="" /></td><td><?php _e( '&hellip;and your MySQL password.' ); ?></td></tr>
		<tr><th scope="row"><label for="dbname"></label><?php _e('Database Name'); ?></th><td><input name="dbname" id="dbname" type="text" size="35" value="cydia" /></td><td><?php _e( 'The name of the database you want to run DCRM in.' ); ?></td></tr>
		<tr><th scope="row"><label for="prefix"><?php _e( 'Table Prefix' ); ?></label></th><td><input name="prefix" id="prefix" type="text" size="35" value="apt_" /></td><td><?php _e( 'If you want to run multiple DCRM installations in a single database, change this.' ); ?></td></tr>
		<?php if(function_exists('mysql_pconnect') && !defined('USE_MYSQLI')){ ?><tr><th scope="row"></th><td><input type="checkbox" value="1" name="pconnect"><?php _e( 'Keep the connection to the database server' ); ?></td><td></td></tr><?php } ?>
	</table>
	<?php if ($notice !== '') { ?>
		<p class="step"><a href="setup-config.php?<?php echo $step_language; ?>" class="button"><?php _e( 'Error!' ); ?></a></p>
	<?php } else { ?>
		<p class="step"><input name="submit" type="submit" value="<?php _e('Submit'); ?>" class="button" /></p>
	<?php } ?>

	</form>

<?php
		break;

	case 3:
		if (!isset($_POST['submit'])) {
			header('location: setup-config.php?' . $step_language);
			exit;
		}

		display_header($notice);

		$tryagain_link = '<p class="step"><a href="setup-config.php?step=2&amp;' . $step_language . '" onclick="javascript:history.go(-1);return false;" class="button">' . __('Try again') . '</a>';
		$db->tryagain_link = $tryagain_link;

		$dbhost = trim($_POST['dbhost']);
		$dbport = intval(trim($_POST['dbport']));
		$prefix = trim($_POST['prefix']);
		$pwd = trim($_POST['pwd']);
		$dbname = trim($_POST['dbname']);
		$uname = trim($_POST['uname']);
		$pconnect = isset($_POST['pconnect']);

		if ( empty( $prefix ) ) {
			_e('<strong>ERROR</strong>: "Table Prefix" must not be empty.') . $tryagain_link;
			break;
		}

		// Validate $prefix: it can only contain letters, numbers and underscores.
		if ( preg_match( '|[^a-z0-9_]|i', $prefix ) ) {
			_e('<strong>ERROR</strong>: "Table Prefix" can only contain numbers, letters, and underscores.') . $tryagain_link;
			break;
		}

		// Test the database connection.
		define('DCRM_CON_SERVER', $dbhost);
		define('DCRM_CON_SERVER_PORT', $dbport);
		define('DCRM_CON_USERNAME', $uname);
		define('DCRM_CON_PASSWORD', $pwd);
		define('DCRM_CON_DATABASE', $dbname);
		define('DCRM_CON_PREFIX', $prefix);
		define('DCRM_CON_PCONNECT', $pconnect);

		// Connect to Server
		$db->_dbconnect();

		$result = $db->query("CREATE DATABASE IF NOT EXISTS `".DCRM_CON_DATABASE."`");
		if (!$result)
			$db->halt(__('<strong>ERROR</strong>: Can&#8217;t create database, please check your input information.'));

		$result = $db->select_db(DCRM_CON_DATABASE);
		if (!$result)
			$db->halt(__('<strong>ERROR</strong>: Can&#8217;t select database, please check your input information.'));

		$config_file = file( CONF_PATH . 'connect.inc.default.php' );
		foreach ( $config_file as $line_num => $line ) {
			if ( ! preg_match( '/^.*?define\([\'"]([A-Z_]+)[\'"],([ ]+)/', $line, $match ) )
				continue;

			$constant = $match[1];
			$padding  = $match[2];

			switch ( $constant ) {
				case 'DCRM_CON_SERVER':
				case 'DCRM_CON_SERVER_PORT':
				case 'DCRM_CON_PREFIX':
				case 'DCRM_CON_USERNAME':
				case 'DCRM_CON_PASSWORD':
				case 'DCRM_CON_DATABASE':
					$config_file[ $line_num ] = "define('" . $constant . "'," . $padding . "'" . addcslashes( constant( $constant ), "\\'" ) . "');\r\n";
					break;
				case 'DCRM_CON_PCONNECT':
					$config_file[ $line_num ] = "define('" . $constant . "', " . var_export( constant( $constant ), true ) .");\r\n";
					break;
			}
		}
		unset( $line );

		$path_to_config = CONF_PATH . 'connect.inc.php';
		$handle = fopen( $path_to_config, 'w' );
		foreach( $config_file as $line ) {
			fwrite( $handle, $line );
		}
		fclose( $handle );
		@chmod( $path_to_config, 0666 );

		if (!file_exists(ABSPATH.'CydiaIcon.png'))
			copy("CydiaIcon.png", "../CydiaIcon.png");
		if (!file_exists(ABSPATH.'favicon.ico'))
			copy("favicon.ico", "../favicon.ico");
		if ( file_exists(ABSPATH.'CydiaIcon.png') && file_exists(ABSPATH.'favicon.ico') ){
			@chmod( ABSPATH.'CydiaIcon.png', 0666 );
			@chmod( ABSPATH.'favicon.ico', 0666 );
		}
?>
<p><?php _e("All right, sparky! You&#8217;ve made it through this part of the installation. DCRM can now communicate with your database. If you are ready, time now to&hellip;"); ?></p>

<p class="step"><a href="setup-install.php?<?php echo $step_language; ?>" class="button button-large"><?php _e('Run the install'); ?></a></p>
<?php
		break;
}
?>