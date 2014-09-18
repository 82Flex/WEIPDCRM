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
	
	/* DCRM Debian Simple View */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once('include/connect.inc.php');
	require_once("include/func.php");
	header("Content-Type: text/html; charset=UTF-8");
	
	if (!isset($_SESSION['connected']) || $_SESSION['connected'] != true) {
		header("Location: login.php");
		exit();
	}
	$con = mysql_connect(DCRM_CON_SERVER, DCRM_CON_USERNAME, DCRM_CON_PASSWORD);
	if (!$con) {
		httpinfo(500);
		exit();
	}
	mysql_query("SET NAMES utf8");
	$select  = mysql_select_db(DCRM_CON_DATABASE);
	if (!$select) {
		httpinfo(500);
		exit();
	}
	if (is_numeric($_GET['id'])) {
		$request_id = (int)$_GET['id'];
	} else {
		httpinfo(405);
		exit();
	}
	$m_query = mysql_query("SELECT * FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $request_id . "'");
	if (!$m_query) {
		httpinfo(500);
		exit();
	}
	if (isset($_GET['action']) && $_GET['action'] == "image" && isset($_POST['image']) && strlen($_POST['image']) > 0) {
		mysql_query("INSERT INTO `".DCRM_CON_PREFIX."ScreenShots`(`PID`, `Image`) VALUES('".$request_id."', '".mysql_real_escape_string($_POST['image'])."')");
	}
	elseif (isset($_GET['action']) && $_GET['action'] == "del" && is_numeric($_GET['image'])) {
		mysql_query("DELETE FROM `".DCRM_CON_PREFIX."ScreenShots` WHERE `ID` = '".mysql_real_escape_string($_GET['image'])."'");
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>DCRM - 源管理系统</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<script src="js/mbar.js" type="text/javascript"></script>
	<script type="text/javascript">
		function delimage(pid) {
			if(confirm("您确定要彻底删除这张截图？")){
			   window.location.href = "view.php?id=<?php echo($request_id); ?>&action=del&image=" + pid;
			}
		}
	</script>
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
							<li><a href="about.php">关于程序</a></li>
					</ul>
				</div>
				<div class="well sidebar-nav">
					<ul class="nav nav-list">
						<li class="nav-header">OPERATIONS</li>
							<li class="active"><a href="javascript:opt(1)">查看详情</a></li>
							<li><a href="javascript:opt(2)">常规编辑</a></li>
							<li><a href="javascript:opt(3)">高级编辑</a></li>
					</ul>
				</div>
			</div>
			<div class="span10">
			<input type="radio" name="package" value="<?php echo($request_id); ?>" style="display: none;" checked="checked" />
			<h2>查看软件包信息</h2>
			<br />
<?php
	$m_array = mysql_fetch_assoc($m_query);
	if (!$m_array) {
		$alert = "查询不到指定的项目。";
	} else {
		unset($m_array['Multi']);
		foreach ($m_array as $m_key => $m_value) {
			if (!empty($m_value)) {
				$f_Package .= $m_key . ": " . trim(str_replace("\n","\n ",$m_value)) . "\n";
			}
		}
?>
			<div class="alert alert-info">
<?php echo nl2br(htmlspecialchars($f_Package)); ?>
			</div>
<?php
	}
	$m_query = mysql_query("SELECT * FROM `".DCRM_CON_PREFIX."ScreenShots` WHERE `PID` = '".$request_id."'");
	if (!$m_query) {
		httpinfo(500);
		exit();
	}
	if (mysql_affected_rows() <= 0) {
?>
			<div class="alert" id="tips">
				该软件包暂无截图<br />
			</div>
<?php
	} else {
?>
			<div class="alert alert-success" id="tips">
				软件包截图 <?php echo(mysql_affected_rows()); ?> 张<br />
<?php
		while ($m_array = mysql_fetch_assoc($m_query)) {
?>
				<li><a href="<?php echo($m_array["Image"]); ?>"><?php if(strlen($m_array["Image"]) > 72){echo(mb_substr($m_array["Image"],0,72,"UTF-8").' ...');}else{echo($m_array["Image"]);} ?></a>&emsp;<a href="javascript:delimage(<?php echo($m_array["ID"]); ?>);">&times;</a></li>
<?php
		}
?>
			</div>
<?php
	}
?>
			<form class="form-horizontal" method="POST" action="view.php?id=<?php echo($request_id); ?>&action=image">
				<fieldset>
					<div class="group-control">
						<label class="control-label">* 新增截图</label>
						<div class="controls">
							<input type="text" style="width: 400px;" required="required" name="image" />
						</div>
					</div>
				</fieldset>
			</form>
			</div>
		</div>
	</div>
</body>
</html>