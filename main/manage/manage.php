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
	require_once("include/func.php");
	header("Content-Type: text/html; charset=UTF-8");

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
							<li class="active"><a href="manage.php">导入软件包</a></li>
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
				<h2>导入软件包</h2>
				<br />
				<?php
					$folder = opendir(DCRM_DEBIAN_CACHE);
					$files = array();
					while ($element = readdir($folder)) {
						if (preg_match("#.\.deb#", $element)) {
							$files[] = $element;
						}
					}
					if (empty($files)) {
						echo '<h3 class="alert alert-info">上传目录为空。<br />您可以通过上传功能或 FTP 服务将软件包上传到根目录的 upload 目录下。</h3>';
					}
					else {
						sort($files);
						echo '<h3>目录列表</h3>';
						echo '<table class="table"><thead><tr>';
						echo '<th><ul class="ctl">删除</ul></th>';
						echo '<th><ul class="ctl">名称</ul></th>';
						echo '<th><ul class="ctl">尺寸</ul></th>';
						echo '</tr></thead><tbody>';
						foreach ($files as $file) {
							$filesize = filesize(DCRM_DEBIAN_CACHE . $file);
							$filesize_withext = sizeext($filesize);
							echo '<tr>';
							echo '<td><a href="manage.php?action=delete_confirmation&file='.urlencode($file).'" class="close" style="line-height: 12px;">&times;</a></td>';
							echo '<td><a href = "import.php?filename='.$file.'"><ul class="ctl" style="width:450px;">'.$file.'</a></ul></td>';
							echo '<td><ul class="ctl" style="width:100px;">'.$filesize_withext.'</ul></td>';
							echo '</tr>';	
						}
						echo '</tbody></table>';
					}
					}
					elseif ($_GET['action'] == "delete_confirmation" AND !empty($_GET['file']) AND file_exists(DCRM_DEBIAN_CACHE . urldecode($_GET['file']))) {
						echo '<h3 class="alert">您确定要删除：'.urldecode($_GET['file']).' ？</h3>';
						echo '<a class="btn btn-warning" href="manage.php?action=delete&file='.urlencode($_GET['file']).'">确定</a>';
						echo '　';
						echo '<a class="btn btn-success" href="manage.php">取消</a>';
					}
					elseif ($_GET['action'] == "delete" AND !empty($_GET['file']) AND file_exists(DCRM_DEBIAN_CACHE . urldecode($_GET['file']))) {
						if (is_writable(DCRM_DEBIAN_CACHE . urldecode($_GET['file']))) {
							unlink(DCRM_DEBIAN_CACHE . urldecode($_GET['file']));
							header("Location: manage.php");
							exit();
						}
						else {
							echo '<h3 class="alert alert-error">无法删除，请检查文件系统权限。<br /><a href="sections.php">返回</a></h3>';
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