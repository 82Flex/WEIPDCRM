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
	
	/* DCRM Debian Output */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once('include/tar.php');
	require_once('include/func.php');
	require_once("include/config.inc.php");
	require_once('include/connect.inc.php');
	header("Content-Type: text/html; charset=UTF-8");
	
	if (!isset($_SESSION['connected']) || $_SESSION['connected'] != true) {
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
	if ($request_id <= 0) {
		$alert = "无效的参数。";
		goto endlabel;
	}
	$m_query = mysql_query("SELECT `Package`, `Source`, `Version`, `Priority`, `Section`, `Essential`, `Maintainer`, `Pre-Depends`, `Depends`, `Recommends`, `Suggests`, `Conflicts`, `Provides`, `Replaces`, `Enhances`, `Architecture`, `Installed-Size`, `Origin`, `Bugs`, `Name`, `Author`, `Sponsor`, `Icon`, `Tag`, `Filename` FROM `Packages` WHERE `ID` = '" . (string)$request_id . "' LIMIT 1");
	if ($m_query == false) {
		$alert = "数据库错误： " . mysql_error();
		goto endlabel;
	}
	$m_array = mysql_fetch_assoc($m_query);
	if ($m_array == false) {
		$alert = "找不到指定的内容。";
		goto endlabel;
	}
	$deb_path = $m_array['Filename'];
	if (!file_exists($deb_path)) {
		$alert = "找不到指定的文件！";
		goto endlabel;
	}
	unset($m_array['Filename']);
	$f_Package = "";
	foreach ($m_array as $m_key => $m_value) {
		if (strlen($m_value) > 0 AND $m_value != NULL) {
			$f_Package .= $m_key . ": " . trim(str_replace("\n","\n ",$m_value)) . "\n";
		}
	}
	$r_id = randstr(40);
	if (!is_dir("../tmp/")) {
		mkdir("../tmp/");
	}
	if (!is_dir("../tmp/" . $r_id)) {
		mkdir("../tmp/" . $r_id);
	}
	$raw_data = new phpAr($deb_path);
	$filename_array = $raw_data -> listfiles();
	foreach ($filename_array as $filename) {
		if (is_int(stripos($filename, 'control.tar.gz'))) {
			$control_c_raw_data = $raw_data -> getfile($filename);
			$innername = $filename;
			goto nextstep;
		}
	}
	nextstep:
	if (is_int(stripos($control_c_raw_data[0][0], 'control.tar.gz'))) {
		$control_tar_path = "../tmp/" . $r_id . "/old.tar.gz";
		$control_tar_handle = fopen($control_tar_path, 'w');
		fputs($control_tar_handle,$control_c_raw_data[0][6]);
		fclose($control_tar_handle);
		$control_tar = new Tar();
		$new_tar = new Tar();
		$control_tar -> load($control_tar_path);
		$control_array = $control_tar -> contents();
		foreach ($control_array as $c_key => $c_value) {
			if ($c_key != "control") {
				$new_tar -> add_file($c_key, "", $control_array[$c_key]['data']);
			}
		}
		$new_path = "../tmp/" . $r_id . "/control.tar.gz";
		$new_tar -> add_file("control", "", $f_Package);
		$new_tar -> save($new_path);
	}
	$replace_result = $raw_data -> replace($innername,$new_path);
	$success = true;
	if ($replace_result) {
		$alert .= "· 安装包写入成功！<br />";
	}
	else {
		$alert .= "· 警告：安装包写入失败！<br />";
		$success = false;
	}
	$new_md5 = md5_file($deb_path);
	$new_size = filesize($deb_path);
	$md5_query = mysql_query("UPDATE `Packages` SET `MD5sum` = '" . $new_md5 . "' WHERE `ID` = '" . (string)$request_id . "'",$con);
	$size_query = mysql_query("UPDATE `Packages` SET `Size` = '" . $new_size . "' WHERE `ID` = '" . (string)$request_id . "'",$con);
	if ($md5_query == FALSE OR $size_query == FALSE) {
		$alert .= "· MD5sum 更新失败！请检查安装包是否成功生成！<br />";
		$success = false;
	}
	else {
		$alert .= "· MD5sum 更新成功！<br />";
	}
	
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
			<h2>更新软件包</h2>
			<br />
			<h3 class="alert alert-<?php if($success){echo("success");}else{echo("error");} ?>">提示：该操作不会自动修改安装包文件名，升级时请上传新的软件包项目。<br />
			<?php echo $alert; ?>
			<a href="javascript:history.go(-2);">返回</a></h3>
			</div>
		</div>
	</div>
</body>
</html>