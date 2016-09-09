<?php
/**
 * DCRM Installer
 * Copyright (c) 2014 i_82 <i.82@me.com>
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
define("DCRM",true);

error_reporting(E_ALL ^ E_WARNING);
require_once('function.php');

$header_title = __( 'Installer' );

// 检查数据库配置文件
if( !file_exists(CONF_PATH.'connect.inc.php') ){
	header('location: setup-config.php?'.$step_language);
	exit();
}

// 检查环境
$disabled = true;
$env_vars = check_env($disabled);
$dir_file_vars = check_dir($disabled);
$func_vars = check_func($disabled);
$notice = check_notice( true );

$step = isset($_GET['step']) ? $_GET['step'] : 0;

require_once(CONF_PATH.'connect.inc.php');

// Test Connect
$db->_dbconnect();
$db->set_charset($db->curlink, 'utf8');

// Make sure DCRM is not already installed.
// Check installed.lock file.
if(!defined("DEVELOP_ENABLED")):
if(file_exists(CONF_PATH.'installed.lock')) {
	display_header();
	echo '<h1>' . __('Already Installed') . '</h1><p>' . __('You appear to have already installed DCRM. You should delete /system/config/installed.lock before reinstall.') . '</p><p class="step"><a href="../manage/login.php" class="button button-large">' . __('Log In') . '</a></p></body></html>';
	exit();
}
// Check database.
$result = $db->query("SHOW TABLES FROM `".DCRM_CON_DATABASE."` LIKE '".DCRM_CON_PREFIX."Packages'");
if ($result && $db->num_rows($result) != 0) {
	$db->error();

	display_header();
	echo '<h1>' . __('Already Installed') . '</h1><p>' . __('You appear to have already installed DCRM. You should clear your old database tables before reinstall.') . '</p><p class="step"><a href="../manage/login.php" class="button button-large">' . __('Log In') . '</a></p></body></html>';
	exit();
}
endif;

if( $step != 99 && ($disabled == false || $notice != '') ){
	header('location: setup-install.php?step=99&'.$step_language);
	exit();
}

/**
 * Display installer setup form.
 */
function display_setup_form( $error = null ) {
	global $step_language;

	$repo_title = isset( $_POST['repo_title'] ) ? trim( dcrm_unslash( $_POST['repo_title'] ) ) : '';
	$repo_url = isset( $_POST['repo_url'] ) ? trim( dcrm_unslash( $_POST['repo_url'] ) ) : BASE_URL;
	$user_name = isset($_POST['user_name']) ? trim( dcrm_unslash( $_POST['user_name'] ) ) : '';
	$admin_email  = isset( $_POST['admin_email']  ) ? trim( dcrm_unslash( $_POST['admin_email'] ) ) : '';

	if ( ! is_null( $error ) ) {
?>
<p class="message"><?php echo $error; ?></p>
<?php } ?>
<form id="setup" method="post" action="setup-install.php?step=1&amp;<?php echo $step_language; ?>" novalidate="novalidate">
	<table class="form-table">
		<tr>
			<th scope="row"><label for="repo_title"><?php _e( 'Repository Title' ); ?></label></th>
			<td><input name="repo_title" type="text" id="repo_title" size="25" value="<?php echo($repo_title); ?>" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="repo_url"><?php _e( 'Repository URL' ); ?></label></th>
			<td>
				<input name="repo_url" type="text" id="repo_url" size="25" value="<?php echo($repo_url); ?>" />
				<p><?php _e( 'Displayed on the homepage for the user to add, and used for the autofill package\'s Depiction when import.' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="user_login"><?php _e('Username'); ?></label></th>
			<td>
				<input name="user_name" type="text" id="user_login" size="25" value="<?php echo($user_name); ?>" />
				<p><?php _e( 'Usernames can have only alphanumeric characters, spaces, underscores, hyphens, periods, and the @ symbol.' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="pass1"><?php _e( 'Password, twice' ); ?></label>
				<p><?php _e('A password will be automatically generated for you if you leave this blank.'); ?></p>
			</th>
			<td>
				<input name="admin_password" type="password" id="pass1" size="25" value="" />
				<p><input name="admin_password2" type="password" id="pass2" size="25" value="" /></p>
				<div id="pass-strength-result"><?php _e( 'Strength indicator' ); ?></div>
				<p><?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; ).' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="admin_email"><?php _e( 'Your E-mail' ); ?></label></th>
			<td><input name="admin_email" type="email" id="admin_email" size="25" value="<?php echo( $admin_email ); ?>" />
			<p><?php _e( 'Double-check your email address before continuing.' ); ?></p></td>
		</tr>
	</table>
	<p class="step"><input type="submit" name="Submit" value="<?php _e( 'Install DCRM' ); ?>" class="button button-large" /></p>
</form>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script src="../js/password-strength.min.js" type="text/javascript"></script>
<script src="../js/zxcvbn-async.min.js" type="text/javascript"></script>
<script src="../js/zxcvbn.min.js" type="text/javascript"></script>
<script type='text/javascript'>
var pwsL10n = {"empty":"<?php echo( utf8_unicode( __( 'Strength indicator' ) ) ); ?>","short":"<?php echo( utf8_unicode( _x( 'Short', 'Password' ) ) ); ?>","bad":"<?php echo( utf8_unicode( _x( 'Bad', 'Password' ) ) ); ?>","good":"<?php echo( _x( 'Good', 'Password' ) ); ?>","strong":"<?php echo( utf8_unicode( _x( 'Strong', 'Password' ) ) ); ?>","mismatch":"<?php echo( utf8_unicode( _x( 'Mismatch', 'Password' ) ) ); ?>"};
</script>
<?php
} // end display_setup_form()


switch($step) {
	case 0:
		display_header();
?>
<h1><?php _ex( 'Welcome', 'Howdy' ); ?></h1>
<?php
		if(isset($_GET['redirect']) && $_GET['redirect'] ):
?>
	<div class="alert alert-info"><strong><?php _e('Note: '); ?></strong><?php _e('If you want to recreate <code>connect.inc.php</code>, please click <a href="./setup-config.php">here</a>.');?></div>
<?php
		endif;
		// Rewrite Mod Check
		if (available(BASE_URL.'rewritetest') !== 200):
?>
		<div class="alert alert-warning"><strong><?php _e('Notice: '); ?></strong><?php _e('The server does not support URL Rewrite or cannot detect automatically. DCRM will disable this function and you can change this option later in Settings.'); ?></div>
<?php 	elseif(available(BASE_URL.'misc') !== 200): ?>
		<div class="alert alert-warning"><strong><?php _e('Notice: '); ?></strong><?php _e('You should update your Rewrite config to enable Elegant Mod.'); ?></div>
<?php 	endif; ?>
<p><?php _e( 'Welcome to the DCRM installation process! Just fill in the information below.' ); ?></p>

<h1><?php _e( 'Information needed' ); ?></h1>
<p><?php _e( 'Please provide the following information. Don&#8217;t worry, you can always change these settings later.' ); ?></p>
<?php
		display_setup_form();
		break;

	case 1:
		display_header();
		
		// Fill in the data we gathered
		$repo_title = isset( $_POST['repo_title'] ) ? trim( dcrm_unslash( $_POST['repo_title'] ) ) : '';
		$repo_url = isset( $_POST['repo_url'] ) ? trim( dcrm_unslash( $_POST['repo_url'] ) ) : BASE_URL;
		$user_name = isset($_POST['user_name']) ? trim( dcrm_unslash( $_POST['user_name'] ) ) : '';
		$admin_password = isset($_POST['admin_password']) ? dcrm_unslash( $_POST['admin_password'] ) : '';
		$admin_password_check = isset($_POST['admin_password2']) ? dcrm_unslash( $_POST['admin_password2'] ) : '';
		$admin_email  = isset( $_POST['admin_email'] ) ?trim( dcrm_unslash( $_POST['admin_email'] ) ) : '';
		
		// Check
		$error = false;
		if ( empty( $user_name ) ) {
			display_setup_form( __( 'Please provide a valid username.' ) );
			$error = true;
		} elseif ( $user_name != sanitize_user( $user_name, false ) ) {
			display_setup_form( __( 'The username you provided has invalid characters.' ) );
			$error = true;
		} elseif ( $admin_password != $admin_password_check ) {
			display_setup_form( __( 'Your passwords do not match. Please try again.' ) );
			$error = true;
		} else if ( empty( $admin_email ) ) {
			display_setup_form( __( 'You must provide an email address.' ) );
			$error = true;
		} elseif ( ! is_email( $admin_email ) ) {
			display_setup_form( __( 'Sorry, that isn&#8217;t a valid email address. Email addresses look like <code>username@example.com</code>.' ) );
			$error = true;
		}
		
		if ( $error === false ) {
			$db->query("SET FOREIGN_KEY_CHECKS=0");
			//$db->set_charset($db->curlink, 'utf8');

			$db->_query("CREATE DATABASE IF NOT EXISTS `".DCRM_CON_DATABASE."`");

			$result = $db->select_db(DCRM_CON_DATABASE);
			if (!$result) $db->halt();

			$db->_query("DROP TABLE IF EXISTS `".DCRM_CON_PREFIX."Packages`");
			$db->_query("CREATE TABLE `".DCRM_CON_PREFIX."Packages` (
			 `ID` int(8) NOT NULL AUTO_INCREMENT,
			 `Package` varchar(512) NOT NULL,
			 `Source` varchar(512) NOT NULL,
			 `Version` varchar(512) NOT NULL,
			 `Priority` varchar(512) NOT NULL,
			 `Section` varchar(512) NOT NULL,
			 `Essential` varchar(512) NOT NULL,
			 `Maintainer` varchar(512) NOT NULL,
			 `Pre-Depends` varchar(512) NOT NULL,
			 `Depends` varchar(512) NOT NULL,
			 `Recommends` varchar(512) NOT NULL,
			 `Suggests` varchar(512) NOT NULL,
			 `Conflicts` varchar(512) NOT NULL,
			 `Provides` varchar(512) NOT NULL,
			 `Replaces` varchar(512) NOT NULL,
			 `Enhances` varchar(512) NOT NULL,
			 `Architecture` varchar(512) NOT NULL DEFAULT 'iphoneos-arm',
			 `Filename` varchar(512) NOT NULL,
			 `Size` int(11) NOT NULL,
			 `Installed-Size` varchar(512) NOT NULL,
			 `Description` varchar(512) NOT NULL,
			 `Multi` varchar(2048) NOT NULL,
			 `Origin` varchar(512) NOT NULL,
			 `Bugs` varchar(512) NOT NULL,
			 `Name` varchar(512) NOT NULL,
			 `Author` varchar(512) NOT NULL,
			 `Sponsor` varchar(512) NOT NULL,
			 `Homepage` varchar(512) NOT NULL,
			 `Website` varchar(512) NOT NULL,
			 `Depiction` varchar(512) NOT NULL,
			 `Icon` varchar(512) NOT NULL,
			 `MD5sum` varchar(512) NOT NULL,
			 `SHA1` varchar(512) NOT NULL,
			 `SHA256` varchar(512) NOT NULL,
			 `Stat` int(1) NOT NULL,
			 `Tag` varchar(512) NOT NULL,
			 `UUID` varchar(512) NOT NULL,
			 `Level` CHAR( 8 ) NOT NULL,
			 `Price` CHAR( 8 ) NOT NULL,
			 `Purchase_Link` VARCHAR( 512 ) NOT NULL,
			 `Purchase_Link_Stat` INT NOT NULL DEFAULT '0',
			 `Changelog` varchar( 512 ) NOT NULL,
			 `Changelog_Older_Shows` INT NOT NULL DEFAULT '0',
			 `Video_Preview` varchar(512) NOT NULL,
			 `System_Support` longtext NOT NULL,
			 `ScreenShots` longtext NOT NULL,
			 `TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			 `DownloadTimes` int(8) NOT NULL,
			 `CreateStamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			 PRIMARY KEY (`ID`)
			) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");

			$db->_query("DROP TABLE IF EXISTS `".DCRM_CON_PREFIX."Sections`");
			$db->_query("CREATE TABLE `".DCRM_CON_PREFIX."Sections` (
			 `ID` int(8) NOT NULL AUTO_INCREMENT,
			 `Name` varchar(512) NOT NULL,
			 `Icon` varchar(512) NOT NULL,
			 `TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			 PRIMARY KEY (`ID`)
			) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");

			$db->_query("DROP TABLE IF EXISTS `".DCRM_CON_PREFIX."ScreenShots`");

			$db->_query("DROP TABLE IF EXISTS `".DCRM_CON_PREFIX."Reports`");
			$db->_query("CREATE TABLE `".DCRM_CON_PREFIX."Reports` (
			 `ID` int(8) NOT NULL AUTO_INCREMENT,
			 `PID` int(8) NOT NULL,
			 `Remote` varchar(64) NOT NULL,
			 `Device` varchar(64) NOT NULL,
			 `iOS` varchar(64) NOT NULL,
			 `Version` varchar(64) NOT NULL,
			 `Support` int(8) NOT NULL,
			 `TimeStamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			 PRIMARY KEY (`ID`)
			) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");

			$db->_query("DROP TABLE IF EXISTS `".DCRM_CON_PREFIX."Users`");
			$db->_query("CREATE TABLE `".DCRM_CON_PREFIX."Users` (
			 `ID` int(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			 `Username` varchar(64) NOT NULL,
			 `SHA1` varchar(128) NOT NULL,
			 `LastLoginTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			 `Power` int(8) NOT NULL
			) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");

			$db->_query("DROP TABLE IF EXISTS `".DCRM_CON_PREFIX."UDID`");
			$db->_query('CREATE TABLE `'.DCRM_CON_PREFIX.'UDID` (
				`ID` int(8) NOT NULL AUTO_INCREMENT,
				`UDID` varchar(128) NOT NULL,
				`Level` int(8) NOT NULL DEFAULT \'0\',
				`Packages` text NOT NULL,
				`Comment` varchar(512) NOT NULL,
				`TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`Downloads` int(8) NOT NULL,
				`IP` bigint NOT NULL,
				`CreateStamp` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\',
				PRIMARY KEY (`ID`)
				) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8');

			$db->_query("DROP TABLE IF EXISTS `".DCRM_CON_PREFIX."Options`");
			$db->_query('CREATE TABLE `'.DCRM_CON_PREFIX.'Options` (
				`option_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`option_name` varchar(64) NOT NULL,
				`option_value` longtext NOT NULL,
				`autoload` varchar(20) NOT NULL DEFAULT \'yes\',
				PRIMARY KEY (`option_id`)
				) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8');

			// Insert Options SQL
			$db->_query("INSERT INTO `".DCRM_CON_PREFIX."Options` (`option_name`, `option_value`) VALUES ('udid_level', '" . serialize(array( __('Guest'), '')) . "') ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`)");
			$db->_query("INSERT INTO `".DCRM_CON_PREFIX."Options` (`option_name`, `option_value`) VALUES ('autofill_depiction', '2') ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`)");
			// Rewrite Option
			if (available(BASE_URL.'rewritetest') !== 200)
				$db->_query("INSERT INTO `".DCRM_CON_PREFIX."Options` (`option_name`, `option_value`) VALUES ('rewrite_mod', '1') ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`)");
			elseif (available(BASE_URL.'misc') !== 200 && available(BASE_URL.'misc') !== 500)
				$db->_query("INSERT INTO `".DCRM_CON_PREFIX."Options` (`option_name`, `option_value`) VALUES ('rewrite_mod', '2') ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`)");
			else
				$db->_query("INSERT INTO `".DCRM_CON_PREFIX."Options` (`option_name`, `option_value`) VALUES ('rewrite_mod', '3') ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`)");


			if ( empty($admin_password) ) {
				$admin_password = dcrm_generate_password( 12, false );
				$password_message = ('<strong><em>Note that password</em></strong> carefully! It is a <em>random</em> password that was generated just for you.');
			} else {
				$password_message = '<em>'.__('Your chosen password.').'</em>';
			}
			$db->_query("INSERT INTO `".DCRM_CON_PREFIX."Users` (`Username`, `SHA1`, `LastLoginTime`, `Power`)
			  VALUES ('". $user_name ."', '" . sha1( dcrm_slash( $admin_password ) ) . "', '0000-00-00 00:00:00', '1')");

			// Copy *.inc.default.php to *.inc.php and config it.
			define("AUTOFILL_SEO", $repo_title);
			define("AUTOFILL_MASTER", $user_name);
			define("AUTOFILL_EMAIL", $admin_email);
			define("DCRM_REPOURL", base64_encode($repo_url));
			
			// autofill.inc.php
			$autofill_file = file( CONF_PATH . 'autofill.inc.default.php' );
			foreach ( $autofill_file as $line_num => $line ) {
				if ( ! preg_match( '/^.*?define\([\'"]([A-Z_]+)[\'"],([ ]+)/', $line, $match ) )
					continue;

				$constant = $match[1];
				$padding  = $match[2];

				switch ( $constant ) {
					case 'AUTOFILL_SEO':
					case 'AUTOFILL_MASTER':
					case 'AUTOFILL_EMAIL':
						$autofill_file[ $line_num ] = "	define('" . $constant . "'," . $padding . "'" . addcslashes( constant( $constant ), "\\'" ) . "');\r\n";
						break;
				}
			}
			unset( $line );
			$autofill_new = CONF_PATH . 'autofill.inc.php';
			$handle = fopen( $autofill_new, 'w' );
			foreach( $autofill_file as $line ) {
				fwrite( $handle, $line );
			}
			fclose( $handle );
			
			// config.inc.php
			$config_file = file( CONF_PATH . 'config.inc.default.php' );
			foreach ( $config_file as $line_num => $line ) {
				if ( ! preg_match( '/^.*?define\([\'"]([A-Z_]+)[\'"],([ ]+)/', $line, $match ) )
					continue;

				$constant = $match[1];
				$padding  = $match[2];

				switch ( $constant ) {
					case 'DCRM_REPOURL':
						$config_file[ $line_num ] = "define('" . $constant . "'," . $padding . "'" . addcslashes( constant( $constant ), "\\'" ) . "');\r\n";
						break;
				}
			}
			unset( $line );
			$config_new = CONF_PATH . 'config.inc.php';
			$handle = fopen( $config_new, 'w' );
			foreach( $config_file as $line ) {
				fwrite( $handle, $line );
			}
			fclose( $handle );

			copy(CONF_PATH . 'gnupg.inc.default.php', CONF_PATH . 'gnupg.inc.php');
			file_put_contents(CONF_PATH . 'installed.lock', time());
			
			$version_file = ABSPATH.'system/version.inc.php';
			@touch($version_file);
			$version_content = '<?php'.PHP_EOL.'/* Auto-generated version file */'.PHP_EOL.'$_version = \''.VERSION.'\';'.PHP_EOL.'?>';
			file_put_contents($version_file, $version_content);

			mkdir("../tmp");
			if (!file_exists(ABSPATH.'CydiaIcon.png'))
				copy("CydiaIcon.png", "../CydiaIcon.png");
			if (!file_exists(ABSPATH.'favicon.ico'))
				copy("favicon.ico", "../favicon.ico");

			if ( file_exists(CONF_PATH.'installed.lock') && file_exists(CONF_PATH.'config.inc.php') && file_exists(CONF_PATH.'gnupg.inc.php') && file_exists(CONF_PATH.'autofill.inc.php') && file_exists(ABSPATH.'system/version.inc.php') && file_exists(ABSPATH.'CydiaIcon.png') && file_exists(ABSPATH.'favicon.ico') && file_exists(ABSPATH.'tmp') ){
				@chmod( $autofill_new, 0666 );
				@chmod( $config_new, 0666 );
				@chmod( CONF_PATH.'gnupg.inc.php', 0666 );
				@chmod( $version_file, 0666 );
				@chmod( ABSPATH.'CydiaIcon.png', 0666 );
				@chmod( ABSPATH.'favicon.ico', 0666 );
				@chmod( ABSPATH.'tmp', 0755 );
?>

<h1><?php _e( 'Success!' ); ?></h1>

<p><?php _e( 'DCRM has been installed. Were you expecting more steps? Sorry to disappoint.' ); ?></p>

<?php
			} else {
?>
<h1><?php _e( 'Something wrong...' ); ?></h1>

<p><?php _e( 'DCRM has been installed, but some files have not been copied. Please copy all <code>*.inc.default.php</code> to <code>*.inc.php</code> in <code>/system/config/</code> and chmod to 0666.' ); ?></p>
<?php
			}
?>
<table class="form-table install-success">
	<tr>
		<th><?php _e( 'Username' ); ?></th>
		<td><?php echo esc_html( sanitize_user( $user_name, true ) ); ?></td>
	</tr>
	<tr>
		<th><?php _e( 'Password' ); ?></th>
		<td><?php
		if ( ! empty( $admin_password ) && empty( $admin_password_check ) ): ?>
			<code><?php echo esc_html( $admin_password ) ?></code><br />
		<?php endif ?>
			<p><?php echo $password_message ?></p>
		</td>
	</tr>
</table>

<p class="step"><a href="../manage/login.php" class="button button-large"><?php _e( 'Log In' ); ?></a></p>

<?php
		}
		break;
	
	case 99:
		display_header($notice);
		include_once 'setup-check.php';
?>
	<p class="step"><a href="setup-install.php?step=check&amp;<?php echo $step_language; ?>" class="button"><?php _e( 'Check again' ); ?></a>  
	<?php if ($disabled == true && $notice !== ''):?>
		<a href="setup-install.php?<?php echo $step_language; ?>" class="button"><?php _e( 'Error!' ); ?></a>
	<?php else:?>
		<a href="setup-install.php?<?php echo $step_language; ?>" class="button"><?php _e( 'Continue' ); ?></a>
	<?php endif;?>
	</p>

<?php
		break;
}
?>
