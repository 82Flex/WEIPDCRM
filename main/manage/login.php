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

/* DCRM Login Page */

session_cache_expire(30);
session_cache_limiter("private");
session_start();
session_regenerate_id(true);
$localetype = 'manage';
define('ROOT_PATH', dirname(__FILE__));
define('ABSPATH', dirname(ROOT_PATH).'/');
include_once ABSPATH.'system/common.inc.php';
header("Cache-Control: no-store");
class_loader('ValidateCode');

if (isset($_GET['authpic'])) {
	if (trim($_GET['authpic']) == 'png') {
		$_vc = new ValidateCode();
		$_vc->doimg();
		$_SESSION['VCODE'] = $_vc->getCode();
		exit();
	} else {
		exit();
	}
} else {
	header("Content-Type: text/html; charset=UTF-8");
}
if (!isset($_SESSION['try'])) {
	$_SESSION['try'] = 0;
} elseif (isset($_SESSION['lasttry']) && $_SESSION['lasttry']+DCRM_LOGINFAILRESETTIME <= time()) {
	$_SESSION['try'] = 0;
}
if (isset($_GET['action']) AND $_GET['action'] == "logout") {
	session_unset();
	session_destroy();
	goto endlabel;
}
if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
	header("Location: center.php");
	exit();
}
if(isset($_POST['language']) && !empty($_POST['language'])) {
	$_SESSION['language'] = $_POST['language'];
	if(!isset($_POST['submit']))
		exit();
}
if(isset($_POST['submit'])) {
	if (!empty($_POST['username']) AND !empty($_POST['password'])) {
		if (empty($_POST['authcode'])) {
			unset($_SESSION['VCODE']);
			$error = "authcode";
			goto endlabel;
		}
		if (strtolower($_POST['authcode']) != strtolower($_SESSION['VCODE'])) {
			unset($_SESSION['VCODE']);
			$_SESSION['try'] = $_SESSION['try'] + 1;
			$error = "authcode";
			goto endlabel;
		} else {
			unset($_SESSION['VCODE']);
		}
		if (!preg_match("#^[0-9a-zA-Z\_]*$#i", $_POST['username'])) {
			$_SESSION['try'] = $_SESSION['try'] + 1;
			$error = "badlogin";
			goto endlabel;
		}
		$login_query = DB::query("SELECT * FROM `".DCRM_CON_PREFIX."Users` WHERE `Username` = '".DB::real_escape_string($_POST['username'])."' LIMIT 1");
		if (DB::affected_rows() > 0) {
			$login = mysql_fetch_assoc($login_query);
			if ($login['Username'] === $_POST['username'] AND strtoupper($login['SHA1']) === strtoupper(sha1($_POST['password']))) {
				$login_query = DB::query("UPDATE `".DCRM_CON_PREFIX."Users` SET `LastLoginTime` = '".date('Y-m-d H:i:s')."' WHERE `ID` = '".$login['ID']."'");
				$_SESSION['power'] = $login['Power'];
				$_SESSION['userid'] = $login['ID'];
				$_SESSION['username'] = $login['Username'];
				$_SESSION['token'] = sha1(time()*rand(140,320));
				$_SESSION['try'] = 0;
				$_SESSION['connected'] = true;
				header("Location: center.php");
				exit();
			} else {
				$_SESSION['try'] = $_SESSION['try'] + 1;
				$_SESSION['lasttry'] = time();
				$error = "badlogin";
				goto endlabel;
			}
		} else {
			$_SESSION['try'] = $_SESSION['try'] + 1;
			$_SESSION['lasttry'] = time();
			$error = "badlogin";
			goto endlabel;
		}
	} else {
		$error = "notenough";
		goto endlabel;
	}
}
endlabel:
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>DCRM - <?php _e('Login'); ?></title>
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/misc.min.css" rel="stylesheet" media="screen">
		<link href="css/animation-shake.css" rel="stylesheet" media="screen">
<?php if(is_rtl()){ ?>		<link rel="stylesheet" type="text/css" href="css/bootstrap-rtl.min.css"><?php } ?>
<?php if(file_exists(ROOT.'css/font/'.($local_css = substr($locale, 0, 2)).'.css') || file_exists(ROOT.'css/font/' . ($local_css = $locale) . '.css')): ?>	<link rel="stylesheet" type="text/css" href="../css/font/<?php echo $local_css; ?>.css"><?php echo("\n"); endif; ?>
		<script src="http://cdn.bootcss.com/jquery/1.11.2/jquery.min.js"></script>
	</head>
	<body>
<?php
if (!isset($_SESSION['try']) OR $_SESSION['try'] <= DCRM_MAXLOGINFAIL) {
?>
		<div class="well">
<?php
if (file_exists('../CydiaIcon.png')) {
?>
			<p><img src="<?php echo(base64_decode(DCRM_REPOURL)); ?>/CydiaIcon.png" style="width: 72px; height: 72px; border-radius: 6px;" /></p>
			<div id="error-container" class="mb15">
<?php
}
if (isset($_GET['error'])) {
	$error = $_GET['error'];
}
if ($error == "notenough") {
	echo '<ul class="parsley-errors-list filled"><li>'.__('Please input your username and password!').'</li></ul>';
} elseif ($error == "badlogin") {
	echo '<ul class="parsley-errors-list filled"><li>'.__('Unknown username or bad password!').'</li></ul>';
} elseif ($error == "bear") {
	echo '<ul class="parsley-errors-list filled"><li>'.__('Bear!').'</li></ul>';
} elseif ($error == "authcode") {
	echo '<ul class="parsley-errors-list filled"><li>'.__('Verification code error!').'</li></ul>';
} else {
	echo '<div id="loginlogo">DCRM - ' . __('Login').'</div>';
}
?>
			</div>
			<hr />
			<p class="form-group">
				<select name="language" class="form-control" onchange="set_language()">
<?php
$languages = get_available_languages();
$langtext = '<option value="Detect"';
if (!isset($_SESSION['language']) || $_SESSION['language'] == 'Detect')
	$langtext .= ' selected="selected"';
$langtext .= '>'._x( 'Select language', 'language' );
if ( substr( $locale, 0, 2 ) != 'en' )
	$langtext .= ' - Languages';
$langtext .= "</option>\n";

$languages_list = languages_list();
$languages_self_list = languages_self_list();
if(!in_array('en', $languages, true) && !in_array('en_US', $languages, true) && !in_array('en_GB', $languages, true)){
	$langtext .= '<option value="en_US"';
	if ($_SESSION['language'] == 'en_US')
		$langtext .= ' selected="selected"';
	$langtext .= '>' . _x('English', 'language') . " - English</option>\n";
}

foreach( $languages as $language ) {
	$langtext .= "<option value=\"$language\"";
	if ($_SESSION['language'] == $language)
		$langtext .= ' selected="selected"';;
	$langtext .= '>' . (isset($languages_list[$language]) ? $languages_list[$language] : $language);
	$langtext .= " - " . (isset($languages_self_list[$language]) ? $languages_self_list[$language] : $languages_list[$language]) . "</option>\n";
}
		
echo $langtext;
?>
				</select>
			</p>
			<form name="form-login" action="login.php" method="POST">
				<p><input type="text" name="username" placeholder="<?php _e('Username'); ?>"  data-parsley-errors-container="#error-container" data-parsley-error-message="<?php _e('Please fill in your username'); ?>" data-parsley-required /></p>
				<p><input type="password" name="password" placeholder="<?php _e('Password'); ?>" data-parsley-errors-container="#error-container" data-parsley-error-message="<?php _e('Please fill in your password'); ?>" data-parsley-required /></p>
				<p><input type="text" name="authcode" placeholder="<?php _e('Verify Code'); ?>" style="margin-top: 8px; height: 24px; width:120px;" data-parsley-errors-container="#error-container" data-parsley-error-message="<?php _e('Please fill in the verify code'); ?>" data-parsley-required />&nbsp;<img src="login.php?authpic=png&amp;rand=<?php echo(time()); ?>" style="height: 36px; width: 88px; border-radius: 6px;" onclick="this.src='login.php?authpic=png&amp;rand=' + new Date().getTime();" /></p>
				<hr />
				<input type="submit" class="btn btn-success" name="submit" value="<?php _ex('Login', 'Buttom'); ?>" />
			</form>
		</div>
<?php
} else {
?>
		<div class="well">
			<?php _e('Error'); ?><hr />
			<?php printf(__('Your login wrong too many times , close the session or wait %s minutes and try again later.'), ceil(($_SESSION['lasttry']+DCRM_LOGINFAILRESETTIME - time())/60)); ?>
		</div>
<?php
}
?>
	<script type="text/javascript" src="./js/pace.min.js"></script>
	<script type="text/javascript" src="./js/parsley.min.js"></script>
	<script type="text/javascript" src="./js/login.min.js"></script>
<?php
if(isset($error))
	echo '<script type="text/javascript">$(document).ready(function(){animation();});</script>'
?>
	</body>
</html>