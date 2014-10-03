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
	
	/* DCRM About Page */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once("include/connect.inc.php");
	header("Content-Type: text/html; charset=UTF-8");

	if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
		$con = mysql_connect(DCRM_CON_SERVER, DCRM_CON_USERNAME, DCRM_CON_PASSWORD);
		if (!$con) {
			goto endlabel;
		}
		mysql_query("SET NAMES utf8");
		$select  = mysql_select_db(DCRM_CON_DATABASE);
		if (!$select) {
			$alert = mysql_error();
			goto endlabel;
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
							<li><a href="sections.php">分类管理</a></li>
							<li><a href="release.php">源信息设置</a></li>
						<li class="nav-header">SYSTEM</li>
							<li><a href="stats.php">运行状态</a></li>
							<li class="active"><a href="about.php">关于程序</a></li>
					</ul>
				</div>
			</div>
			<div class="span10">
				<?php
					if (!isset($_GET['action'])) {
				?>
				<h2>关于程序</h2>
				<br /><div class="alert alert-info"><h3>
				Darwin Cydia Repository Manager (DCRM)<br />
				版本：1.5 Pro<br />
				开源的 Cydia™/APT 个人源解决方案</h3><br /><h4>
				开发人员</h4>
				主程序：威锋网测试组 <a href="http://weibo.cn/u/3246210680">@i_82</a><br />
				界面风格：<a href="http://weibo.com/u/1921596413">@大蝦酥CAMZ</a><br /><br /><h4>
				特别感谢</h4>
				威锋网 <a href="http://weibo.cn/375584554">@飄Sir</a> 的支持<br />
				网友 <a href="http://weibo.com/u/1766730601">@zsm1703</a>、<a href="http://weibo.com/u/2175594103">@Naville</a>、<a href="http://weibo.com/u/1931192555">@Q某某某某</a>、<a href="http://weibo.com/u/3254325910">@摇滚米饭_</a><br />
				威锋网测试组 <a href="http://weibo.com/u/1675423275">@Sunbelife</a>、威锋网技术组 <a href="http://weibo.cn/nivalxer">@NivalXer</a>、<a href="http://weibo.com/u/1417725530">@ioshack</a> 提供测试意见<br />
				威锋网技术组 <a href="http://weibo.com/u/2004244347">@autopear</a> 撰写的：<a href="http://bbs.weiphone.com/read-htm-tid-669283.html">从零开始搭建 Cydia™ 软件源，制作 Debian 安装包</a><br />
				Cydia™ 之父 <a href="http://www.saurik.com">Saurik</a> 撰写的：<a href="http://www.saurik.com/id/7">How to host a Cydia™ Repository</a>，移动版首页样式来自 Saurik IT.<br /><br /><h4>
				<a href="http://82flex.com/DCRM/Buy">获取 DCRM Pro</a></h4><br /><h4>
				版权所有 © 2013–2014 威锋网测试组<br />
				本程序是自由软件，修改和重新发布应该遵照自由软件基金会出版的 <a href="http://www.gnu.org/licenses">GNU 通用公共许可证条款第三版</a>。</h4></div>
				<?php
				}
				else {
					endlabel:
					echo $alert;
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