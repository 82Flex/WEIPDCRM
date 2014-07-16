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
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once("include/connect.inc.php");
	require_once("include/func.php");
	require_once("include/tar.php");
	header("Content-Type: text/html; charset=UTF-8");
	
	// Connect to Server
	$con = mysql_connect($server,$username,$password);
	
	if (!$con) {
		goto endlabel;
	}
	
	mysql_query("SET NAMES utf8",$con);
	mysql_select_db($database,$con);
	
	if (isset($_SESSION['connected'])) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>DCRM - 源管理系统</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<style type="text/css">
	.ctl {text-overflow:ellipsis;overflow:hidden;white-space: nowrap;padding:2px} 
	</style>
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
					<a href="settings.php" class="btn btn-info">设置</a>
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
							<li class="active"><a href="sections.php">分类管理</a></li>
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
				<h2>分类管理</h2>
				<br />
				<h3 class="navbar">分类列表　<a href="sections.php?action=add">添加分类</a>　<a href="sections.php?action=create">生成图标包</a></h3>
				<?php
						$list_query = mysql_query("SELECT * FROM `Sections` ORDER BY `ID` DESC LIMIT 50",$con);
						if ($list_query == FALSE) {
							goto endlabel;
						}
						else {
							echo '<table class="table"><thead><tr>';
							echo '<th><ul class="ctl">编辑</ul></th>';
							echo '<th><ul class="ctl">名称</ul></th>';
							echo '<th><ul class="ctl">图标</ul></th>';
							echo '<th><ul class="ctl">最后修改</ul></th>';
							echo '</tr></thead><tbody>';
							while ($list = mysql_fetch_assoc($list_query)) {
								echo '<tr>';
								echo '<td><a href="sections.php?action=delete_confirmation&id=' . $list['ID'] . '&name=' . $list['Name'] . '" class="close" style="line-height: 12px;">&times</a></td>';
								echo '<td><ul class="ctl" style="width:400px;"><a href="center.php?action=search&contents=' . urlencode($list['Name']) . '&type=7">' . htmlspecialchars($list['Name']) . '</a></ul></td>';
								if ($list['Icon'] != "") {
									echo '<td><ul class="ctl" style="width:150px;"><a href="../icons/' . $list['Icon'] . '">' . $list['Icon'] . '</a></ul></td>';
								}
								else {
									echo '<td><ul class="ctl" style="width:150px;">无图标</ul></td>';
								}
								echo '<td><ul class="ctl" style="width:150px;">' . $list['TimeStamp'] . '</ul></td>';
								echo '</tr>';	
							}
							echo '</tbody></table>';
						}
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "add") {
						?>
						<h2>分类管理</h2>
						<br />
						<h3 class="navbar"><a href="sections.php">分类列表</a>　添加分类　<a href="sections.php?action=create">生成图标包</a></h3>
						<br />
						<form class="form-horizontal" method="POST" enctype="multipart/form-data" action="sections.php?action=add_now" >
						<div class="group-control">
							<label class="control-label">分类名称</label>
							<div class="controls">
								<input class="input-xlarge" name="contents" required="required" />
								<input type="hidden" name="action" value="add_now" />
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">分类图标</label>
							<div class="controls">
								<input type="file" class="span6" name="icon" accept="image/x-png" />
								<p class="help-block">允许上传的格式：png，保存到根目录的 icons 目录下</p>
							</div>
						</div>
						<br />
						<div class="form-actions">
							<div class="controls">
								<button type="submit" class="btn btn-success">提交</button>
							</div>
						</div>
						</form>
						<?php
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "add_now" AND !empty($_POST['contents'])) {
						$new_name = mysql_real_escape_string($_POST['contents']);
						$q_info = mysql_query("SELECT count(*) FROM `Sections`");
						if (!$q_info) {
							goto endlabel;
						}
						$info = mysql_fetch_row($q_info);
						$num = (int)$info[0];
						if ($num < 50) {
							if (pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION) == "png") {
								if (file_exists("../icons/" . $_FILES['icon']['name'])) {
									unlink("../icons/" . $_FILES['icon']['name']);
								}
								$move = rename($_FILES['icon']['tmp_name'],"../icons/" . $_FILES['icon']['name']);
								if (!$move) {
									$alert = "图标上传失败，请检查文件权限。";
									goto endlabel;
								}
								else {
									$n_query = mysql_query("INSERT INTO `Sections`(`Name`, `Icon`) VALUES('" . $new_name . "', '" . $_FILES['icon']['name'] . "')");
								}
							}
							else {
								$n_query = mysql_query("INSERT INTO `Sections`(`Name`) VALUES('" . $new_name . "')");
							}
						}
						else {
							$alert = "您最多只能添加 50 个分类！";
							goto endlabel;
						}
						if (!$n_query) {
							goto endlabel;
						}
						else {
							header("Location: sections.php");
							exit();
						}
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "create") {
						$new_name = mysql_real_escape_string($_POST['contents']);
						$q_info = mysql_query("SELECT count(*) FROM `Sections` WHERE `Icon` != ''");
						if (!$q_info) {
							goto endlabel;
						}
						$info = mysql_fetch_row($q_info);
						$num = (int)$info[0];
						if ($num < 1) {
							$alert = "找不到存在图标的分类条目，请先添加一个图标，再执行生成。";
							goto endlabel;
						}
						if (file_exists("include/empty_icon.deb")) {
							$r_id = randstr(40);
							if (!is_dir(DCRM_TEMP)) {
								$result = mkdir(DCRM_TEMP);
							}
							if (!is_dir(DCRM_TEMP . $r_id)) {
								$result = mkdir(DCRM_TEMP . $r_id);
								if (!$result) {
									$alert = "临时目录创建失败，请检查文件权限！";
									goto endlabel;
								}
							}
							$deb_path = DCRM_TEMP . $r_id . "/icon_" . time() . ".deb";
							$result = copy("include/empty_icon.deb", $deb_path);
							if (!$result) {
								$alert = "图标包模板复制失败，请检查文件权限！";
								goto endlabel;
							}
							$raw_data = new phpAr($deb_path);
							$new_tar = new Tar();
							$new_path = DCRM_TEMP . $r_id . "/data.tar.gz";
							$icon_query = mysql_query("SELECT * FROM `Sections`");
							while ($icon_assoc = mysql_fetch_assoc($icon_query)) {
								mkdir(DCRM_TEMP . $r_id . "/Applications");
								mkdir(DCRM_TEMP . $r_id . "/Applications/Cydia.app");
								mkdir(DCRM_TEMP . $r_id . "/Applications/Cydia.app/Sections");
								if ($icon_assoc['Icon'] != "") {
									$new_filename = str_replace("[", "", str_replace("]", "", str_replace(" ", "_", $icon_assoc['Name']))) . ".png";
									$new_filepath = DCRM_TEMP . $r_id . "/Applications/Cydia.app/Sections/" . $new_filename;
									copy("../icons/" . $icon_assoc['Icon'], $new_filepath);
									$new_tar -> add_file("/Applications/Cydia.app/Sections/" . $new_filename, "", file_get_contents($new_filepath));
								}
							}
							$new_tar -> save($new_path);
							$result = $raw_data -> replace("data.tar.gz", $new_path);
							if (!$result) {
								$alert = "图标包模板改写失败！";
								goto endlabel;
							}
							else {
								$result = rename($deb_path, DCRM_DEBIAN_CACHE . "icon_" . time() . ".deb");
								if (!$result) {
									$alert = "图标包重定位失败！";
									goto endlabel;
								}
								header("Location: manage.php");
								exit();
							}
						}
						else {
							$alert = "图标包模板丢失，请重新安装 DCRM 专业版！";
							goto endlabel;
						}
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "delete_confirmation" AND !empty($_GET['id']) AND !empty($_GET['name'])) {
						echo '<div class="alert">您确定要删除： ' . htmlspecialchars($_GET['name']) . ' ？<br />';
						echo '<a class="btn btn-warning" href="sections.php?action=delete&id='.$_GET['id'].'">确定</a>';
						echo '　';
						echo '<a class="btn btn-success" href="sections.php">取消</a></div>';
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "delete" AND !empty($_GET['id'])) {
						$delete_id = (int)$_GET['id'];
						$d_query = mysql_query("DELETE FROM `Sections` WHERE `ID` = '" . $delete_id . "'",$con);
						if (!$d_query) {
							goto endlabel;
						}
						header("Location: sections.php");
						exit();
					}
					if (!$con) {
						endlabel:
						echo '<h3 class="alert alert-error">数据库出现错误！<br />';
						if (isset($alert)) {
							echo $alert . '<br />';
						}
						echo '<a href="sections.php">返回</a></h3>';
					}
					else {
						mysql_close($con);
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