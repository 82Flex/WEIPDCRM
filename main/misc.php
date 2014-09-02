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
	
	/* DCRM PC Index Page */
	
	error_reporting(0);
	ob_start();
	define("DCRM",true);
	require_once("manage/include/config.inc.php");
	require_once("manage/include/autofill.inc.php");
	header("Content-Type: text/html; charset=UTF-8");
	
	if (file_exists("Release")) {
		$release = file("Release");
		$release_origin = "未命名";
		foreach ($release as $line) {
			if(preg_match("#^Origin#", $line)) {
				$release_origin = trim(preg_replace("#^(.+): (.+)#","$2", $line));
			}
			if(preg_match("#^Description#", $line)) {
				$release_description = trim(preg_replace("#^(.+): (.+)#","$2", $line));
			}
		}
	} else {
		$release_origin = '空白页';
		if (file_exists('init/install.php')) {
			$first = true;
		} else {
			$first = false;
		}
	}
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $release_origin; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<!-- 搜索引擎检索 -->
		<meta name="robots" content="index, follow" />
<?php
	if (defined("AUTOFILL_SEO")) {
?>
		<meta name="title" content="<?php echo(AUTOFILL_SEO); ?>" />
<?php
	}
	if (!empty($release_description)) {
?>
		<meta name="description" content="<?php echo($release_description); ?>" />
<?php
	}
	if (defined("AUTOFILL_KEYWORDS")) {
?>
		<meta name ="keywords" content="<?php echo(AUTOFILL_KEYWORDS); ?>" />
<?php
	}
?>
		<!-- 相关文件引用 -->
		<link rel="shortcut icon" href="favicon.ico" />
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/misc.min.css" rel="stylesheet" media="screen">
	</head>
	<body>
		<!-- 欢迎信息 -->
		<div class="well">
<?php
			if (file_exists('CydiaIcon.png')) {
?>
			<p><img src="CydiaIcon.png" style="width: 64px; height: 64px; border-radius: 5px;" /></p>
<?php
			}
?>
			<p><?php echo $release_origin; ?></p>
			<hr />
			欢迎通过 Cydia <a href="cydia://sources/add">添加</a> <code><?php echo(base64_decode(DCRM_REPOURL)); ?></code> 访问本源。
<?php
			if ($first) {
?>
			<hr />
			站长您好，如果您是首次安装 DCRM 专业版，请运行 <a href="init/index.html">快速安装脚本</a> 。
<?php
			}
?>
		</div>
<?php
	if (defined("AUTOFILL_STATISTICS")) {
?>
		<!-- 统计代码 -->
		<div style="text-align: center; display: none;"><?php echo AUTOFILL_STATISTICS; ?></div>
<?php
	}
?>
	</body>
</html>