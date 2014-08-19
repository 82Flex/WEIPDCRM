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
	
	/* DCRM Login Page */
	
	error_reporting(0);
	session_cache_expire(30);
	session_cache_limiter("private");
	session_start();
	session_regenerate_id(true);
	ob_start();
	define("DCRM",true);
	define('ROOT_PATH', dirname(__FILE__));
	require_once("include/config.inc.php");
	require_once("include/connect.inc.php");
	require_once("include/func.php");
	date_default_timezone_set('Asia/Shanghai');
	header("Cache-Control: max-age=0");
	
	if (isset($_GET['authpic']) AND $_GET['authpic'] == 'png') {
		$_vc = new ValidateCode();
		$_vc->doimg();
		$_SESSION['VCODE'] = $_vc->getCode();
		exit();
	} else {
		header("Content-Type: text/html; charset=UTF-8");
	}
	if (!isset($_SESSION['try'])) {
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
			}
			$con = mysql_connect($server,$username,$password);
			if (!$con) {
				$error = "bear";
				goto endlabel;
			}
			mysql_query("SET NAMES utf8");
			$select  = mysql_select_db($database);
			if (!$select) {
				$error = "bear";
				goto endlabel;
			}
			$login_query = mysql_query("SELECT * FROM `Users` WHERE `Username` = '".mysql_real_escape_string($_POST['username'])."' LIMIT 1");
			if (mysql_affected_rows() > 0) {
				$login = mysql_fetch_assoc($login_query);
				if ($login['Username'] === $_POST['username'] AND strtoupper($login['SHA1']) === strtoupper(sha1($_POST['password']))) {
					$login_query = mysql_query("UPDATE `Users` SET `LastLoginTime` = '".date('Y-m-d H:i:s')."' WHERE `ID` = '".$login['ID']."'");
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
					$error = "badlogin";
					goto endlabel;
				}
			} else {
				$_SESSION['try'] = $_SESSION['try'] + 1;
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
		<title>DCRM - 登录</title>
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<style type="text/css" media="screen">
			body {
				margin: 100px;
				background: #ffffff;
				background: -moz-radial-gradient(center, ellipse cover, #ffffff 0%, #e5e5e5 100%);
				background: -webkit-gradient(radial, center center, 0px, center center, 100%, color-stop(0%,#ffffff), color-stop(100%,#e5e5e5));
				background: -webkit-radial-gradient(center, ellipse cover, #ffffff 0%,#e5e5e5 100%);
				background: -o-radial-gradient(center, ellipse cover, #ffffff 0%,#e5e5e5 100%);
				background: -ms-radial-gradient(center, ellipse cover, #ffffff 0%,#e5e5e5 100%);
				background: radial-gradient(center, ellipse cover, #ffffff 0%,#e5e5e5 100%);
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#e5e5e5',GradientType=1 );
				font-family: Arial,Helvetica,sans-serif;
				font-size: 10pt;
			}
			.well {
				margin-left: auto;
				margin-right: auto;
				width: 400px;
				text-align: center;
			}
		</style>
	</head>
	<body>
		<?php
			if (!isset($_SESSION['try']) OR $_SESSION['try'] <= DCRM_MAXLOGINFAIL) {
		?>
		<form class="well" action="login.php" method="POST">
			<?php
				if (isset($_GET['error'])) {
					$error = $_GET['error'];
				}
				if ($error == "notenough") {
					echo '请填写用户名和密码！';
				}
				elseif ($error == "badlogin") {
					echo '用户名或密码错误！';
				}
				elseif ($error == "bear") {
					echo '熊出没注意！';
				}
				elseif ($error == "authcode") {
					echo '验证码错误！';
				}
				else {
					echo '源管理系统 - 登录';
				}
			?>
			<hr>
			<p><input type="text" name="username" required="required" placeholder="用户名" /></p>
			<p><input type="password" name="password" placeholder="密码" required="required" /></p>
			<p><input type="text" name="authcode" required="required" placeholder="验证码" style="width:120px;" />&nbsp;<img src="login.php?authpic=png&rand=<?php echo(time()); ?>" style="height:36px;width:88px;" onclick="this.src='login.php?authpic=png&rand=' + new Date().getTime();" /></p>
				<input type="submit" class="btn btn-success" name="submit" value="立即登录" />
		</form>
		<?php
			}
			else {
		?>
		<div class="well">
			错误：<hr>
			您的登录错误次数太多，关闭会话后再试。
		</div>
		<?php
			}
		?>
	</body>
</html>