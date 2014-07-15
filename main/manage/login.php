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
	
	error_reporting(0);
	session_cache_expire(30);
	session_cache_limiter("private");
	session_start();
	session_regenerate_id(true);
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	
	if (isset($_GET['authpic']) AND $_GET['authpic'] == 'png') {
		header("Content-type: image/png");
		/*
		* 初始化
		*/
		$border = 0; //是否要边框
		$how = 4; //验证码位数
		$w = $how * 15; //图片宽度
		$h = 18; //图片高度
		$fontsize = 6; //字体大小
		$alpha = "abcdefghijkmnopqrstuvwxyz"; //验证码内容1:字母
		$number = "023456789"; //验证码内容2:数字
		$randcode = ""; //验证码字符串初始化
		srand((double)microtime()*1000000);
		$im = ImageCreate($w, $h);
		/*
		* 绘制基本框架
		*/
		$bgcolor = ImageColorAllocate($im, 255, 255, 255);
		ImageFill($im, 0, 0, $bgcolor);
		if ($border) {
		    $black = ImageColorAllocate($im, 0, 0, 0);
		    ImageRectangle($im, 0, 0, $w-1, $h-1, $black);
		}
		/*
		* 逐位产生随机字符
		*/
		for ($i=0; $i<$how; $i++) {   
		    $alpha_or_number = mt_rand(0, 1);
		    $str = $alpha_or_number ? $alpha : $number;
		    $which = mt_rand(0, strlen($str)-1);
		    $code = substr($str, $which, 1);
		    $j = !$i ? 4 : $j+15;
		    $color3 = ImageColorAllocate($im, mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
		    ImageChar($im, $fontsize, $j, 3, $code, $color3);
		    $randcode .= $code;
		}
		/*
		* 添加干扰
		*/
		for ($i=0; $i<5; $i++) {   
		    $color1 = ImageColorAllocate($im, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
		    ImageArc($im, mt_rand(-5,$w), mt_rand(-5,$h), mt_rand(20,300), mt_rand(20,200), 55, 44, $color1);
		}   
		for ($i=0; $i<$how*40; $i++) {   
		    $color2 = ImageColorAllocate($im, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
		    ImageSetPixel($im, mt_rand(0,$w), mt_rand(0,$h), $color2);
		}
		$_SESSION['VCODE'] = $randcode;
		ImagePNG($im);
		ImageDestroy($im);
		/*绘图结束*/
		exit();
	}
	else {
		header("Content-Type: text/html; charset=UTF-8");
	}
	if (!isset($_SESSION['try'])) {
		$_SESSION['try'] = 0;
	}
	if (!empty($_SESSION['connected']) AND isset($_GET['action']) AND $_GET['action'] == "logout") {
		session_unset();
		session_destroy();
		header("Location: center.php");
		exit();
	}
	elseif (isset($_SESSION['connected'])) {
		header("Location: center.php");
		exit();
	} 
	elseif (!empty($_POST['username']) AND !empty($_POST['password']) AND !empty($_POST['authcode'])) {
		if ($_POST['authcode'] != $_SESSION['VCODE']) {
			header("Location: login.php?error=authcode");
			exit();
		}
		if ($_POST['username'] == DCRM_USERNAME AND sha1($_POST['password']) == DCRM_PASSWORD) {
			$_SESSION['connected'] = true;
			$_SESSION['token'] = sha1(time()*rand(140,320));
			$_SESSION['try'] = 0;
			header("Location: center.php");
			exit();
		}
		else {
			$_SESSION['try'] = $_SESSION['try'] + 1;
			header("Location: login.php?error=badlogin");
			exit();
		}
	}
	elseif (isset($_POST['submit']) AND (empty($_POST['username']) OR empty($_POST['password']))) {
		header("Location: login.php?error=notenough");
		exit();
	}
	else {
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
				if (isset($_GET['error']) AND $_GET["error"] == "notenough") {
					echo '请填写用户名和密码！';
				}
				elseif (isset($_GET['error']) AND $_GET["error"] == "badlogin") {
					echo '用户名或密码错误！';
				}
				elseif (isset($_GET['error']) AND $_GET["error"] == "bear") {
					echo '熊出没注意！';
				}
				elseif (isset($_GET['error']) AND $_GET["error"] == "authcode") {
					echo '验证码错误！';
				}
				else {
					echo '源管理系统 - 登录';
				}
			?>
			<hr>
			<p><input type="text" name="username" required="required" placeholder="用户名" /></p>
			<p><input type="password" name="password" placeholder="密码" required="required" /></p>
			<p><input type="text" name="authcode" required="required" placeholder="验证码" style="width:120px;" />&nbsp;<img src="login.php?authpic=png" style="height:36px;width:88px;" onclick="this.src='login.php?authpic=png&rand=' + new Date().getTime();" /></p>
				<input type="submit" class="btn btn-success" name="submit" value="立即登录" />
		</form>
		<?php
			}
			else {
		?>
		<div class="well">
			错误：<hr>
			您的登录次数太多，请联系管理员解锁。
		</div>
		<?php
			}
		?>
	</body>
</html>
<?php
	}
?>