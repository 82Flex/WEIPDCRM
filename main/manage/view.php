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
	require_once('include/connect.inc.php');
	header("Content-Type: text/html; charset=UTF-8");
	
	if (!isset($_SESSION['connected'])) {
		header("Location: login.php");
		exit();
	}
	$con = mysql_connect($server,$username,$password);
	if (!$con) {
		$alert = "数据库错误！";
		goto endlabel;
	}
	mysql_query("SET NAMES utf8",$con);
	$select  = mysql_select_db($database,$con);
	if (!$select) {
		$alert = mysql_error();
		goto endlabel;
	}
	$request_id = (int)$_GET['id'];
	$m_query = mysql_query("SELECT * FROM `Packages` WHERE `ID` = '" . $request_id . "'");
	if ($m_query == false) {
		$alert = "数据库错误！";
		goto endlabel;
	}
	$m_array = mysql_fetch_assoc($m_query);
	if ($m_array == false) {
		$alert = "查询不到指定的项目。";
		goto endlabel;
	}
	
	foreach ($m_array as $m_key => $m_value) {
		if ($m_value != NULL) {
			$f_Package .= $m_key . ": " . trim(str_replace("\n","\n ",$m_value)) . "\n";
		}
	}
	$f_Package = str_replace("../","./",$f_Package);
	endlabel:
	mysql_close($con);
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
							<li class="active"><a href="center.php">管理软件包</a></li>
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
			<h2>查看软件包信息</h2>
			<br />
			<div class="alert alert-info"><?php echo nl2br(htmlspecialchars($f_Package)); ?></div>
			</div>
		</div>
	</div>
</body>
</html>