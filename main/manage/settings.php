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
				<h2><?php echo $lang_release['title'][DCRM_LANG]; ?></h2>
				<br />
				<form class="form-horizontal" method="POST" action="settings.php?action=set">
					<fieldset>
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
							$config_text .= "\tdefine(\"DCRM_DEBIAN_CACHE\",\""."../upload/"."\");\n";
							$config_text .= "\tdefine(\"DCRM_TEMP\",\""."../tmp/"."\");\n";
							$config_text .= "\tdefine(\"DCRM_DOWNLOADS\",\""."../downloads/"."\");\n";
							$config_text .= "\tdefine(\"TAGS\",\""."#^Package|Source|Version|Priority|Section|Essential|Maintainer|Pre-Depends|Depends|Recommends|Suggests|Conflicts|Provides|Replaces|Enhances|Architecture|Filename|Size|Installed-Size|Description|Origin|Bugs|Name|Author|Homepage|Website|Depiction|Icon|Tag|Sponsor#"."\");\n?>";
							$config_handle = fopen("include/config.inc.php", "w");
							fputs($config_handle,stripslashes($config_text));
							fclose($config_handle);
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