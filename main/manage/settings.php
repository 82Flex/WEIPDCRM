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
	
	/* DCRM System Settings */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once("include/connect.inc.php");
	require_once("include/autofill.inc.php");
	header("Content-Type: text/html; charset=UTF-8");

	if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
		$con = mysql_connect($server,$username,$password);
		if (!$con) {
			echo(mysql_error());
			exit();
		}
		mysql_query("SET NAMES utf8",$con);
		$select  = mysql_select_db($database,$con);
		if (!$select) {
			echo(mysql_error());
			exit();
		}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>DCRM - 源管理系统</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="span6" id="logo">
				<p class="title">DCRM</p>
				<h6 class="underline">Darwin Cydia Repository Manager</h6>
			</div>
			<div class="span6">
				<div class="btn-group pull-right">
					<a href="build.php" class="btn btn-inverse">刷新列表</a>
					<a href="settings.php" class="btn btn-info disabled">设置</a>
					<a href="login.php?action=logout" class="btn btn-info">注销</a>
				</div>
			</div>
		</div>
		<br />
		<div class="row">
			<div class="span2.5" style="margin-left:0!important;">
				<div class="well sidebar-nav">
					<ul class="nav nav-list">
						<li class="nav-header">PACKAGES</li>
							<li><a href="upload.php">上传软件包</a></li>
							<li><a href="manage.php">导入软件包</a></li>
							<li><a href="center.php">管理软件包</a></li>
						<li class="nav-header">REPOSITORY</li>
							<li><a href="sections.php">分类管理</a></li>
							<li><a href="release.php">源信息设置</a></li>
						<li class="nav-header">SYSTEM</li>
							<li><a href="stats.php">运行状态</a></li>
							<li><a href="about.php">关于程序</a></li>
					</ul>
				</div>
			</div>
			<div class="span10">
				<?php
					if (!isset($_GET['action'])) {
				?>
				<h2>系统设置</h2>
				<br />
				<form class="form-horizontal" method="POST" action="settings.php?action=set">
					<fieldset>
						<h3>登录信息</h3>
						<br />
						<div class="group-control">
							<label class="control-label">用户名</label>
							<div class="controls">
								<input type="text" required="required" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">密码</label>
							<div class="controls">
								<input type="text" name="newpassword"/>
								<p class="help-block">无需修改密码请留空</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">最大尝试次数</label>
							<div class="controls">
								<input type="text" required="required" name="trials" value="<?php echo htmlspecialchars(DCRM_MAXLOGINFAIL); ?>"/>
							</div>
						</div>
						<br />
						<h3>下载设置</h3>
						<br />
						<div class="group-control">
							<label class="control-label">防盗链</label>
							<div class="controls">
								<select name="directdown">
									<?php
										if (DCRM_DIRECT_DOWN == 2) {
											echo '<option value="2" selected="selected">开启</option>\n<option value="1">关闭</option>';
										}
										else {
											echo '<option value="1" selected="selected">关闭</option>\n<option value="2">开启</option>';
										}
									?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">最大下载速度</label>
							<div class="controls">
								<input type="text" required="required" name="speedlimit" value="<?php echo htmlspecialchars(DCRM_SPEED_LIMIT); ?>"/>
								<p class="help-block">字节每秒，不限制请填写 0</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">Packages 压缩</label>
							<div class="controls">
								<select name="listsmethod">
									<?php
										function getmethod($opt) {
											switch ($opt) {
												case 0:
													$opt_text = "隐藏列表";
													break;
												case 1:
													$opt_text = "仅文本";
													break;
												case 2:
													$opt_text = "仅 gz";
													break;
												case 3:
													$opt_text = "文本与 gz";
													break;
												case 4:
													$opt_text = "仅 bz2";
													break;
												case 5:
													$opt_text = "文本与 bz2";
													break;
												case 6:
													$opt_text = "gz 与 bz2";
													break;
												case 7:
													$opt_text = "全部";
													break;
												default:
													$opt_text = "";
											}
											return $opt_text;
										}
										for ($opt = 0; $opt <= 7; $opt++) {
											if (DCRM_LISTS_METHOD == $opt) {
												echo '<option value="' . $opt . '" selected="selected">' . getmethod($opt) . '</option>\n';
											}
											else {
												echo '<option value="' . $opt . '">' . getmethod($opt) . '</option>\n';
											}
										}
									?>
								</select>
								<p class="help-block">若修改后发现刷新列表出错，请更换压缩方式</p>
							</div>
						</div>
						<br />
						<h3>首页展示</h3>
						<br />
						<div class="group-control">
							<label class="control-label">首页更新列表</label>
							<div class="controls">
								<select name="list">
									<?php
										if (DCRM_SHOWLIST == 1) {
											echo '<option value="1" selected="selected">开启</option>\n<option value="2">关闭</option>';
										}
										else {
											echo '<option value="2" selected="selected">关闭</option>\n<option value="1">开启</option>';
										}
									?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">更新列表数量</label>
							<div class="controls">
								<input type="text"  name="listnum" value="<?php echo htmlspecialchars(DCRM_SHOW_NUM); ?>"/>
								<p class="help-block">最大不得超过 20 条</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">源地址</label>
							<div class="controls">
								<input type="text" required="required" name="url_repo" value="<?php echo htmlspecialchars(base64_decode(DCRM_REPOURL)); ?>"/>
								<p class="help-block">展示在首页供用户添加</p>
							</div>
						</div>
						<br />
						<h3>自动填充</h3>
						<br />
						<div class="group-control">
							<label class="control-label">软件包默认标识符</label>
							<div class="controls">
								<input type="text" name="PRE" value="<?php if(defined("AUTOFILL_PRE")){echo(htmlspecialchars(AUTOFILL_PRE));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">软件包默认名称</label>
							<div class="controls">
								<input type="text" name="NONAME" value="<?php if(defined("AUTOFILL_NONAME")){echo(htmlspecialchars(AUTOFILL_NONAME));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">软件包默认描述</label>
							<div class="controls">
								<input type="text" name="DESCRIPTION" value="<?php if(defined("AUTOFILL_DESCRIPTION")){echo(htmlspecialchars(AUTOFILL_DESCRIPTION));} ?>"/>
							</div>
						</div>
						<br />
						<h3>SEO 优化</h3>
						<br />
						<div class="group-control">
							<label class="control-label">SEO 名称</label>
							<div class="controls">
								<input type="text" name="SEO" value="<?php if(defined("AUTOFILL_SEO")){echo(htmlspecialchars(AUTOFILL_SEO));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">SEO 关键词</label>
							<div class="controls">
								<input type="text" name="KEYWORDS" value="<?php if(defined("AUTOFILL_KEYWORDS")){echo(htmlspecialchars(AUTOFILL_KEYWORDS));} ?>"/>
								<p class="help-block">以英文半角逗号分隔</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">SEO 域名</label>
							<div class="controls">
								<input type="text" name="SITE" value="<?php if(defined("AUTOFILL_SITE")){echo(htmlspecialchars(AUTOFILL_SITE));} ?>"/>
							</div>
						</div>
						<br />
						<h3>管理员信息</h3>
						<br />
						<div class="group-control">
							<label class="control-label">管理员名称</label>
							<div class="controls">
								<input type="text" name="MASTER" value="<?php if(defined("AUTOFILL_MASTER")){echo(htmlspecialchars(AUTOFILL_MASTER));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">管理员全名</label>
							<div class="controls">
								<input type="text" name="FULLNAME" value="<?php if(defined("AUTOFILL_FULLNAME")){echo(htmlspecialchars(AUTOFILL_FULLNAME));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">管理员邮箱</label>
							<div class="controls">
								<input type="text" name="EMAIL" value="<?php if(defined("AUTOFILL_EMAIL")){echo(htmlspecialchars(AUTOFILL_EMAIL));} ?>"/>
							</div>
						</div>
						<br />
						<h3>分享设定</h3>
						<br />
						<div class="group-control">
							<label class="control-label">微博地址</label>
							<div class="controls">
								<input type="text" name="WEIBO" value="<?php if(defined("AUTOFILL_WEIBO")){echo(htmlspecialchars(AUTOFILL_WEIBO));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">微博名称</label>
							<div class="controls">
								<input type="text" name="WEIBO_NAME" value="<?php if(defined("AUTOFILL_WEIBO_NAME")){echo(htmlspecialchars(AUTOFILL_WEIBO_NAME));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">Paypal 捐助地址</label>
							<div class="controls">
								<input type="text" name="PAYPAL" value="<?php if(defined("AUTOFILL_PAYPAL")){echo(htmlspecialchars(AUTOFILL_PAYPAL));} ?>"/>
							</div>
						</div>
						<br />
						<h3>统计与广告</h3>
						<br />
						<div class="group-control">
							<label class="control-label">外部统计代码</label>
							<div class="controls">
								<textarea type="text" style="height: 40px;" name="STATISTICS" ><?php if(defined("AUTOFILL_STATISTICS")){echo(htmlspecialchars(AUTOFILL_STATISTICS));} ?></textarea>
								<p class="help-block">不可见的统计代码</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">内部统计代码</label>
							<div class="controls">
								<textarea type="text" style="height: 40px;" name="STATISTICS_INFO" ><?php if(defined("AUTOFILL_STATISTICS_INFO")){echo(htmlspecialchars(AUTOFILL_STATISTICS_INFO));} ?></textarea>
								<p class="help-block">查看信息的统计代码</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">广告支持</label>
							<div class="controls">
								<textarea type="text" style="height: 40px;" name="ADVERTISEMENT" ><?php if(defined("AUTOFILL_ADVERTISEMENT")){echo(htmlspecialchars(AUTOFILL_ADVERTISEMENT));} ?></textarea>
							</div>
						</div>
						<br />
						<h3>通知</h3>
						<br />
						<div class="group-control">
							<label class="control-label">紧急通知</label>
							<div class="controls">
								<textarea type="text" style="height: 40px;" name="EMERGENCY" ><?php if(defined("AUTOFILL_EMERGENCY")){echo(htmlspecialchars(AUTOFILL_EMERGENCY));} ?></textarea>
							</div>
						</div>
						<br />
						<div class="form-actions">
							<div class="controls">
								<button type="submit" class="btn btn-success">保存</button>
							</div>
						</div>
					</fieldset>
				</form>
				<?php
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "set") {
						$error_stat = false;
						$logout = false;
						if (!isset($_POST['username']) OR empty($_POST['username'])) {
							$error_text .= "用户名不得设置为空！\n";
							$error_stat = true;
						}
						if (!isset($_POST['trials']) OR !is_numeric($_POST['trials'])) {
							$error_text .= "最大尝试次数必须为整数！\n";
							$error_stat = true;
						}
						if (!isset($_POST['speedlimit']) OR !is_numeric($_POST['speedlimit'])) {
							$error_text .= "下载限速必须为整数！\n";
							$error_stat = true;
						}
						if (!isset($_POST['directdown']) OR !is_numeric($_POST['directdown'])) {
							$error_text .= "请设置防盗链开关！\n";
							$error_stat = true;
						}
						if (!isset($_POST['listsmethod']) OR !is_numeric($_POST['listsmethod']) OR (int)$_POST['listsmethod'] > 7) {
							$error_text .= "请设置正确的 Packages 压缩方式！\n";
							$error_stat = true;
						}
						if (!isset($_POST['list']) OR !is_numeric($_POST['list'])) {
							$error_text .= "请设置首页更新列表开关！\n";
							$error_stat = true;
						}
						else {
							if (!isset($_POST['listnum']) OR !is_numeric($_POST['listnum']) OR (int)$_POST['listnum'] > 20) {
								$error_text .= "请设置首页更新列表数量，最大不得超过 20 条！\n";
								$error_stat = true;
							}
						}
						if (!isset($_POST['url_repo']) OR empty($_POST['url_repo'])) {
							$error_text .= "源地址不得设置为空！\n";
							$error_stat = true;
						}
						if ($error_stat === false) {
							$result = mysql_query("SELECT `ID` FROM `Users` WHERE (`Username` = '".mysql_real_escape_string($_POST['username'])."' AND `ID` != '".$_SESSION['userid']."')");
							if (!$result OR mysql_affected_rows() != 0) {
								$error_text .= "存在相同的用户名！\n";
								$error_stat = true;
							}
							else {
								$result = mysql_query("UPDATE `Users` SET `Username` = '".mysql_real_escape_string($_POST['username'])."' WHERE `ID` = '".$_SESSION['userid']."'");
								if (!empty($_POST['newpassword'])) {
									$logout = true;
									$result = mysql_query("UPDATE `Users` SET `SHA1` = '".sha1($_POST['newpassword'])."' WHERE `ID` = '".$_SESSION['userid']."'");
								}
							}
						}
						if ($error_stat == true) {
							echo '<h3 class="alert alert-error">';
							echo $error_text;
							echo '<br /><a href="settings.php">返回</a></h3>';
						}
						else {
							$config_text = "<?php\n\tif (!defined(\"DCRM\")) {\n\t\texit;\n\t}\n";
							$config_text .= "\tdefine(\"DCRM_MAXLOGINFAIL\",".$_POST['trials'].");\n";
							$config_text .= "\tdefine(\"DCRM_SHOWLIST\",".$_POST['list'].");\n";
							$config_text .= "\tdefine(\"DCRM_SHOW_NUM\",".$_POST['listnum'].");\n";
							$config_text .= "\tdefine(\"DCRM_SPEED_LIMIT\",".$_POST['speedlimit'].");\n";
							$config_text .= "\tdefine(\"DCRM_DIRECT_DOWN\",".$_POST['directdown'].");\n";
							$config_text .= "\tdefine(\"DCRM_LISTS_METHOD\",".$_POST['listsmethod'].");\n";
							$config_text .= "\tdefine(\"DCRM_REPOURL\",\"".base64_encode($_POST['url_repo'])."\");\n";
							$config_text .= "?>";
							$autofill_text = "<?php\n\tif (!defined(\"DCRM\")) {\n\t\texit;\n\t}\n";
							$autofill_list = array("EMERGENCY", "PRE", "NONAME", "MASTER", "FULLNAME", "EMAIL", "SITE", "WEIBO", "WEIBO_NAME", "DESCRIPTION", "SEO", "KEYWORDS", "PAYPAL", "STATISTICS", "STATISTICS_INFO", "ADVERTISEMENT");
							foreach ($autofill_list as $value) {
								if (!empty($_POST[$value])) {
									$autofill_text .= "\tdefine(\"AUTOFILL_".$value."\",\"".addslashes(str_replace(array("\r","\n"), '',nl2br(htmlspecialchars_decode($_POST[$value]))))."\");\n";
								}
							}
							$autofill_text .= "?>";
							$config_handle = fopen("include/config.inc.php", "w");
							fputs($config_handle,stripslashes($config_text));
							fclose($config_handle);
							$autofill_handle = fopen("include/autofill.inc.php", "w");
							fputs($autofill_handle,$autofill_text);
							fclose($autofill_handle);
							echo '<h3 class="alert alert-success">设置修改成功。<br/><a href="settings.php">返回</a></h3>';
							if ($logout) {
								header("Location: login.php?action=logout");
							}
						}
					}
				?>
			</div>
		</div>
	</div>
</body>
</html>
<?php
	}
	else {
		header("Location: login.php");
		exit();
	}
?>