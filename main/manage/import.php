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
	
	/* DCRM Debian Read */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once('include/config.inc.php');
	require_once('include/tar.php');
	require_once('include/connect.inc.php');
	require_once('include/func.php');
	header("Content-Type: text/html; charset=UTF-8");
	date_default_timezone_set('Asia/Shanghai');
	
	if (!isset($_SESSION['connected']) || $_SESSION['connected'] != true) {
		header("Location: login.php");
		exit();
	}
	$diff = FALSE;
	$replace = FALSE;
	$success = true;
	$con = mysql_connect($server,$username,$password);
	if (!$con) {
		$alert = "数据库错误！";
		$success = false;
		goto endlabel;
	}
	mysql_query("SET NAMES utf8",$con);
	$select  = mysql_select_db($database,$con);
	if (!$select) {
		$alert = mysql_error();
		$success = false;
		goto endlabel;
	}
	if (empty($_GET['filename'])) {
		$alert = "无效的参数：`Filename`";
		$success = false;
		goto endlabel;
	}
	
	$r_path = "../upload/" . str_replace("\\", "",str_replace("/", "",$_GET['filename']));
	if (pathinfo($r_path, PATHINFO_EXTENSION) != "deb") {
		$alert = "无效的文件类型！";
		$success = false;
		goto endlabel;
	}
	$r_id = randstr(40);
	if (!$r_path || !$r_id) {
		$alert = '无效的参数！';
		$success = false;
		goto endlabel;
	}
	if (file_exists($r_path) == FALSE) {
		$alert = '找不到文件： ' . $r_path;
		$success = false;
		goto endlabel;
	}
	$file_md5 = md5_file($r_path);
	$md5_query = mysql_query("SELECT `MD5sum` FROM `Packages` WHERE `MD5sum` = '" . $file_md5 . "'");
	if ($md5_query == FALSE) {
		$alert = "无效的请求： " . mysql_error();
		$success = false;
		goto endlabel;
	}
	$md5_row = mysql_fetch_row($md5_query);
	if ($md5_row != FALSE) {
		$alert = "文件已存在： " . $file_md5;
		$success = false;
		goto endlabel;
	}
	
	$raw_data = new phpAr($r_path);
	$filename_array = $raw_data -> listfiles();
	
	foreach ($filename_array as $filename) {
		if (is_int(stripos($filename, 'control.tar.gz'))) {
			$control_c_raw_data = $raw_data -> getfile($filename);
			goto nextstep;
		}
	}
	
	nextstep:
	if (is_int(stripos($control_c_raw_data[0][0], 'control.tar.gz'))) {
		if (!is_dir("../tmp/")) {
			mkdir("../tmp/");
		}
		if (!is_dir("../tmp/" . $r_id)) {
			mkdir("../tmp/" . $r_id);
		}
		$t_path = "../tmp/" . $r_id . '/control.tar.gz';
		if (file_exists($t_path)) {
			unlink($t_path);
		}
		$control_tar_handle = fopen($t_path, 'w');
		fputs($control_tar_handle,$control_c_raw_data[0][6]);
		fclose($control_tar_handle);
		$control_tar = new Tar();
		$control_tar -> load($t_path);
		$control_array = $control_tar -> contents();
		$control_data = $control_array['control']['data'];
		
		$plain_array = explode("\n",$control_data);
		foreach ($plain_array as $line) {
			if(preg_match("#^Package|Source|Version|Priority|Section|Essential|Maintainer|Pre-Depends|Depends|Recommends|Suggests|Conflicts|Provides|Replaces|Enhances|Architecture|Filename|Size|Installed-Size|Description|Origin|Bugs|Name|Author|Homepage|Website|Depiction|Icon|Tag|Sponsor#",$line)) {
				$t_package[trim(preg_replace("#^(.+): (.+)#","$1", $line))] = trim(preg_replace("#^(.+): (.+)#","$2", $line));
			}
		}
		
		if (strlen((string)$t_package['Package']) > 0 && strlen((string)$t_package['Version']) > 0 && strlen((string)$t_package['Architecture']) > 0) {
			goto vaildPackage;
		}
		else {
			goto invaildPackage;
		}
	}
	else {
		goto invaildControl;
	}
	
	goto endlabel;
	
	invaildControl:
	$alert = "无效的软件包信息！<br />" . json_encode($filename_array) . "<br />" . json_encode($control_c_raw_data);
	$success = false;
	goto endlabel;
	
	vaildPackage:
	$same_query = mysql_query("SELECT `ID`, `Package`, `Name`, `Version`, `CreateStamp`, `MD5sum` FROM `Packages` WHERE `Package` = '" . mysql_real_escape_string($t_package['Package']) . "' ORDER BY `ID` DESC LIMIT 1");
	if ($same_query == FALSE) {
		$alert = "无效的请求： " . mysql_error();
		$success = false;
		goto endlabel;
	}
	$same_row = mysql_fetch_assoc($same_query);
	if ($same_row != FALSE) {
		if ($same_row['Version'] == $t_package['Version']) {
			$alert = "已经存在相同软件包及版本号，导入失败： " . $same_row['Package'] . " &amp; " . $same_row['Version'] . "<br />导入时间：" . $same_row['CreateStamp'];
			$success = false;
			goto endlabel;
		}
		else {
			if (!isset($_GET['type'])) {
				$alert = "已经存在相同软件包，但版本号不同，请选择一个操作。<br />原软件包：" . $same_row['Package'] . " &amp; " . $same_row['Version'] . "<br />导入时间：" . $same_row['CreateStamp'] . "<br />MD5sum：" . $same_row['MD5sum'] . "<br /><br />新软件包：" . $t_package['Package'] . " &amp; " . $t_package['Version'] . "<br />MD5sum：" . $file_md5;
				$diff = true;
				$success = false;
				goto endlabel;
			}
			else {
				if ($_GET['type'] == '1') {
					$p_query = mysql_query("SELECT `Package`, `Source`, `Priority`, `Section`, `Essential`, `Maintainer`, `Pre-Depends`, `Depends`, `Recommends`, `Suggests`, `Conflicts`, `Provides`, `Replaces`, `Enhances`, `Architecture`, `Installed-Size`, `Description`, `Origin`, `Bugs`, `Name`, `Author`, `Sponsor`, `Homepage`, `Website`, `Depiction`, `Icon`, `Tag` FROM `Packages` WHERE `Package` = '" . $same_row['Package'] . "' ORDER BY `ID` DESC LIMIT 1");
					$p_row = mysql_fetch_assoc($p_query);
					foreach ($p_row as $p_key => $p_value) {
						$t_package[$p_key] = $p_value;
					}
					$replace = true;
					goto importnow;
				}
				elseif ($_GET['type'] == '2') {
					$replace = true;
					goto importnow;
				}
				elseif ($_GET['type'] == '3') {
					goto importnow;
				}
				else {
					$alert = "错误的导入请求类型！";
					$success = false;
					goto endlabel;
				}
			}
		}
	}
	
	importnow:
	$new_daily = "../downloads/" . date("Ymd");
	$new_path = $new_daily . "/" . $t_package['Package'] . "_" . $t_package['Version'] . "_" . $t_package['Architecture'] . ".deb";
	if (!is_dir($new_daily)) {
		$mkdir = mkdir($new_daily);
		if (!$mkdir) {
			$alert = "新建目录失败：" . $new_daily;
			$success = false;
			goto endlabel;
		}
	}
	if (file_exists($new_path)) {
		$alert = "文件已存在： " . $new_path;
		$success = false;
		goto endlabel;
	}
	if (rename($r_path,$new_path) == FALSE) {
		$alert = "文件移动失败： " . $new_path;
		$success = false;
		goto endlabel;
	}
	$query = mysql_query("INSERT INTO `Packages`(`UUID`) VALUES('" . $r_id . "')");
	if ($query != false) {
		$t_package['Size'] = filesize($new_path);
		$t_package['Filename'] = $new_path;
		$t_package['MD5sum'] = $file_md5;
		$t_package['CreateStamp'] = date('Y-m-d H:i:s');
		$t_package['Stat'] = 2;
		$new_id = mysql_insert_id();
		foreach ($t_package as $t_key => $t_value) {
			if (strlen($t_key) > 0) {
				$main_query = mysql_query("UPDATE `Packages` SET `" . mysql_real_escape_string($t_key) . "` = '" . mysql_real_escape_string($t_value) . "' WHERE `ID` = '" . (string)$new_id . "'");
			}
		}
	}
	else {
		$alert = '导入失败！请检查数据库配置！';
		$success = false;
	}
	if ($replace == true) {
		mysql_query("UPDATE `Packages` SET `Stat` = '-1' WHERE (`Package` = '" . $same_row['Package'] . "' AND `Version` = '" . $same_row['Version'] . "')",$con);
		header("Location: output.php?id=".(string)$new_id);
		exit();
	}
	else {
		$alert = '导入成功！现在您可以进入数据库管理页面对其进行审核！<br />软件包地址： ' . $new_path;
	}
	goto endlabel;
	
	invaildPackage:
	$alert = "无效的软件包：在软件包信息中找不到 `Package`、`Version`、`Architecture` 字段。";
	$success = false;
	goto endlabel;
	
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
			<h2>导入软件包</h2>
			<br />
			<?php
				if ($diff == FALSE) {
					if ($success) {
						echo '<h4 class="alert alert-success">';
					}
					else {
						echo '<h4 class="alert alert-error">';
					}
					echo $alert;
					echo '<br /><a href="manage.php">返回</a></h4>';
				}
				else {
					echo '<h4 class="alert alert-info">';
					echo $alert;
					echo '</h4>';
					echo '<a class="btn btn-warning" href="import.php?type=1&filename='.urlencode($_GET['filename']).'">继承并替换</a>';
					echo '　';
					echo '<a class="btn btn-warning" href="import.php?type=2&filename='.urlencode($_GET['filename']).'">直接替换</a>';
					echo '　';
					echo '<a class="btn btn-warning" href="import.php?type=3&filename='.urlencode($_GET['filename']).'">新增条目</a>';
					echo '　';
					echo '<a class="btn btn-success" href="manage.php">取消</a>';
				}
			?>
			</div>
		</div>
	</div>
</body>
</html>