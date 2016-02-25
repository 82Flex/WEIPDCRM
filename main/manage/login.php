<?php
/**
 * DCRM Login Page
 * Copyright (c) 2015 Hintay <hintay@me.com>
 * Copyright (c) 2014 i_82 <i.82@me.com>
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

session_cache_expire(30);
session_cache_limiter("private");
session_start();
define('ROOT_PATH', dirname(__FILE__));
define('ABSPATH', dirname(ROOT_PATH).'/');

if (isset($_GET['authpic'])) {
	switch(trim($_GET['authpic'])){
		case 'png':
			define('IN_DCRM', true);
			include_once ABSPATH.'system/class/validatecode.php';
			$_vc = new ValidateCode();
			$_vc->doimg();
			$_SESSION['VCODE'] = $_vc->getCode();
			exit();
			break;
		default:
			exit();
			break;
	}
} else {
	$localetype = 'manage';
	include_once ABSPATH.'system/common.inc.php';
	header("Cache-Control: no-store");
}
if(!isset($_SESSION['connected']) || $_SESSION['connected'] != true) {
	session_regenerate_id(true);
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
			$_SESSION['lasttry'] = time();
			$error = "authcode";
			goto endlabel;
		} else {
			unset($_SESSION['VCODE']);
		}
		if (!preg_match("#^[0-9a-zA-Z\_]*$#i", $_POST['username'])) {
			$_SESSION['try'] = $_SESSION['try'] + 1;
			$_SESSION['lasttry'] = time();
			$error = "badlogin";
			goto endlabel;
		}
		$login = DB::fetch_first("SELECT * FROM `".DCRM_CON_PREFIX."Users` WHERE `Username` = '".DB::real_escape_string($_POST['username'])."' LIMIT 1");
		if (!empty($login)) {
			if ($login['Username'] === $_POST['username'] AND strtoupper($login['SHA1']) === strtoupper(sha1($_POST['password']))) {
				$login_query = DB::update(DCRM_CON_PREFIX.'Users', array('LastLoginTime' => date('Y-m-d H:i:s')), array('ID' => $login['ID']));
				$_SESSION['power'] = $login['Power'];
				$_SESSION['userid'] = $login['ID'];
				$_SESSION['username'] = $login['Username'];
				$_SESSION['token'] = sha1(time()*rand(140,320));
				$_SESSION['try'] = 0;
				$_SESSION['connected'] = true;
				if(isset($_SESSION['referer'])){
					$referer = $_SESSION['referer'];
					unset($_SESSION['referer']);
					header("Location: ".$referer);
				} else {
					header("Location: center.php");
				}
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
<!DOCTYPE html>
<html class="backend">
	<!-- 开始 头部 -->
	<head>
		<!-- 开始 META 标签 -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title><?php _e('Login'); ?></title>
		<meta name="author" content="WEIPDCRM">
		<meta name="license" content="AGPLv3">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

		<link rel="shortcut icon" href="../favicon.ico">
		<!--/ 结束 META 标签 -->

		<!-- 开始 样式表 -->
		<!--  插件样式表：可选 -->
		<!--/ 插件样式表 -->

		<!-- 应用样式表：强制 -->
		<link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap.min.css">
		<link rel="stylesheet" href="./stylesheet/layout.min.css">
		<link rel="stylesheet" href="./stylesheet/uielement.min.css">
<?php if(is_rtl()){ ?>		<link rel="stylesheet" type="text/css" href="./stylesheet/uielement-rtl.min.css"><?php } ?>
<?php if(file_exists(ROOT.'css/font/'.($local_css = substr($locale, 0, 2)).'.css') || file_exists(ROOT.'css/font/' . ($local_css = $locale) . '.css')): ?>		<link rel="stylesheet" type="text/css" href="../css/font/<?php echo $local_css; ?>.css"><?php echo("\n"); endif; ?>
		<!--/ 应用样式表 -->

		<!-- modernizr 脚本 -->
		<script type="text/javascript" src="./plugins/modernizr/modernizr.min.js"></script>
		<!--/ modernizr 脚本-->
		<!-- 结束 样式表 -->
    </head>
	<!--/ 结束 头部 -->

	<!-- 开始 页面主体 -->
	<body>
		<!-- 开始 模板主内容 -->
		<section id="main" role="main">
			<!-- 开始 模板容器 -->
			<section class="container">
				<!-- 开始 行 -->
				<div class="row">
					<div class="col-lg-4 col-lg-offset-4">
						<!-- 商标 -->
						<div class="text-center" style="margin-bottom:10px; margin-top:30px;">
<?php
if (file_exists('../CydiaIcon.png')) :
?>
							<img src="../CydiaIcon.png" style="width: 72px; height: 72px; border-radius: 6px; margin-bottom:10px;" />
<?php
endif;
?>
							<h5 class="semibold text-muted mt-5"><?php _e('Login'); ?></h5>
						</div>
						<!--/ 商标 -->

						<hr><!-- 水平线 -->

						<!-- 登录表单 -->
<?php
if (!isset($_SESSION['try']) OR $_SESSION['try'] <= DCRM_MAXLOGINFAIL) {
?>
						<div class="panel">
							<div class="panel-body">
								<div class="form-group">
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
                                </div>
								<form name="form-login" action="login.php" method="POST">
									<div class="form-group">
										<div class="form-stack has-icon pull-left">
											<input name="username" type="text" class="form-control input-lg" placeholder="<?php _e('Username'); ?>" data-parsley-errors-container="#error-container" data-parsley-error-message="<?php _e('Please fill in your username'); ?>" data-parsley-required>
											<i class="ico-user2 form-control-icon"></i>
										</div>
										<div class="form-stack has-icon pull-left">
											<input name="password" type="password" class="form-control input-lg" placeholder="<?php _e('Password'); ?>" data-parsley-errors-container="#error-container" data-parsley-error-message="<?php _e('Please fill in your password'); ?>" data-parsley-required>
											<i class="ico-lock2 form-control-icon"></i>
										</div>
										<div class="form-stack has-icon pull-left">
											<div class="input-group image-input-group">
												<input type="text" name="authcode" class="form-control input-lg authcode" placeholder="<?php _e('Verify Code'); ?>" data-parsley-errors-container="#error-container" data-parsley-error-message="<?php _e('Please fill in the verify code'); ?>" data-parsley-required />
												<span class="input-group-addon verifycode">
													<img src="login.php?authpic=png&amp;rand=<?php echo(time()); ?>" style="height: 44px;" onclick="this.src='login.php?authpic=png&amp;rand=' + new Date().getTime();" />
												</span>
											</div>
											<i class="ico-quill3 form-control-icon"></i>
										</div>
									</div>

									<!-- 错误容器 -->
									<div id="error-container" class="mb15">
<?php
	if (isset($_GET['error']))
		$error = $_GET['error'];
	if(isset($error)){
		if ($error == "notenough") {
			echo '<ul class="parsley-errors-list filled"><li>'.__('Please input your username and password!').'</li></ul>';
		} elseif ($error == "badlogin") {
			echo '<ul class="parsley-errors-list filled"><li>'.__('Unknown username or bad password!').'</li></ul>';
		} elseif ($error == "bear") {
			echo '<ul class="parsley-errors-list filled"><li>'.__('Bear!').'</li></ul>';
		} elseif ($error == "authcode") {
			echo '<ul class="parsley-errors-list filled"><li>'.__('Verification code error!').'</li></ul>';
		}
	}
?>
									</div>
									<!--/ 错误容器 -->

									<input name="submit" style="display: none;"/>
									<div class="form-group nm">
										<button type="submit" class="btn btn-block btn-success"><span class="semibold"><?php _ex('Login', 'Buttom'); ?></span></button>
									</div>
								</form>
                            </div>
						</div>
						<!-- 登录表单 -->
<?php
} else {
?>
						<div class="panel-body">
							<h1 class="text-center"><?php _e('Error'); ?></h1>
							<p><?php printf(__('You\'ve tried to login too many times unsuccessfully, please close the session or wait %s minutes to try again.'), ceil(($_SESSION['lasttry']+DCRM_LOGINFAILRESETTIME - time())/60)); ?></p>
						</div>
<?php
}
?>
						<hr><!-- 水平线 -->

						<p class="text-muted text-center">Copyrght &copy; <?php echo(date('Y')); echo defined("AUTOFILL_SEO") ? ' '.htmlspecialchars(AUTOFILL_SEO) : ''; ?></p>
						<p class="text-muted text-center">Powered by <a style="color: #777;" href="https://github.com/82Flex/WEIPDCRM">WEIPDCRM</a></p>
					</div>
				</div>
				<!--/ 结束 行 -->
			</section>
			<!--/ 结束 模板容器 -->
		</section>
		<!--/ 结束 模板主内容 -->

		<!-- 开始 JAVASCRIPT 部分 (底部加载javascript以减少载入时间) -->
		<!-- 应用及底层脚本：强制 -->
		<script src="//cdn.bootcss.com/jquery/1.11.2/jquery.min.js"></script>
		<script src="//cdn.bootcss.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
		<!--/ 应用及底层脚本：强制 -->

		<!-- 插件及页面脚本：可选 -->
		<script type="text/javascript" src="//cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
		<script type="text/javascript" src="./javascript/pace.min.js"></script>
		<script type="text/javascript" src="./plugins/parsley/parsley.min.js"></script>
		<script type="text/javascript" src="./javascript/backend/login.min.js"></script>
<?php
if(isset($error))
	echo '<script type="text/javascript">$(document).ready(function(){animation();});</script>'
?>
		<!--/ 插件及页面脚本：可选 -->
		<!--/ 结束 JAVASCRIPT 部分 -->
	</body>
	<!--/ 结束 页面主体 -->
</html>