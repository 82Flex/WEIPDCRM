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
	
	error_reporting(0);
	ob_start();
	$inst_success = false;
	
	if (!isset($_GET['skip']) OR $_GET['skip'] != "yes") {
		if (empty($_POST['db_host']) || empty($_POST['db_user']) || empty($_POST['db_database'])) {
			header("Location: index.html");
			exit();
		}
		$put = file_put_contents("../manage/include/connect.inc.php", "<?php\n\tif (!defined(\"DCRM\")) {\n\t\texit();\n\t}\n\t\n\t\$server = '".$_POST['db_host']."';\n\t\$username = '".$_POST['db_user']."';\n\t\$password = '".$_POST['db_password']."';\n\t\$database = '".$_POST['db_database']."';\n?>");
		if (!$put) {
			$inst_alert = "数据库配置写入失败，请检查文件权限！";
			goto endlabel;
		}
	}
	
	define("DCRM",true);
	require_once("../manage/include/config.inc.php");
	require_once("../manage/include/connect.inc.php");
	header("Content-Type: text/html; charset=UTF-8");
	
	if (!defined('PHP_VERSION_ID')) {
		$version = explode('.', PHP_VERSION);
		define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
	}
	if (PHP_VERSION_ID < 50300) {
		$inst_alert = "PHP 版本必须为 5.3 及以上！";
		goto endlabel;
	}
	if (!extension_loaded("gd")) {
		$inst_alert = "gd 库未加载，验证码模块无法安装！";
		goto endlabel;
	}
	
	// Connect to Server
	$con = mysql_connect($server,$username,$password);
	
	if (!$con) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	$result = mysql_query("SET FOREIGN_KEY_CHECKS=0");
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	$result = mysql_query("CREATE DATABASE IF NOT EXISTS `$database`");
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	$result = mysql_select_db($database);
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	/*
	-- ----------------------------
	--  Table structure for `Packages`
	-- ----------------------------
	*/
	
	$result = mysql_query("DROP TABLE IF EXISTS `Packages`");
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	$result = mysql_query("CREATE TABLE `Packages` (
	  `ID` int(8) NOT NULL AUTO_INCREMENT,
	  `Package` varchar(512) NOT NULL,
	  `Source` varchar(512) NOT NULL,
	  `Version` varchar(512) NOT NULL,
	  `Priority` varchar(512) NOT NULL,
	  `Section` varchar(512) NOT NULL,
	  `Essential` varchar(512) NOT NULL,
	  `Maintainer` varchar(512) NOT NULL,
	  `Pre-Depends` varchar(512) NOT NULL,
	  `Depends` varchar(512) NOT NULL,
	  `Recommends` varchar(512) NOT NULL,
	  `Suggests` varchar(512) NOT NULL,
	  `Conflicts` varchar(512) NOT NULL,
	  `Provides` varchar(512) NOT NULL,
	  `Replaces` varchar(512) NOT NULL,
	  `Enhances` varchar(512) NOT NULL,
	  `Architecture` varchar(512) NOT NULL DEFAULT 'iphoneos-arm',
	  `Filename` varchar(512) NOT NULL,
	  `Size` int(11) NOT NULL,
	  `Installed-Size` varchar(512) NOT NULL,
	  `Description` varchar(512) NOT NULL,
	  `Multi` varchar(2048) NOT NULL,
	  `Origin` varchar(512) NOT NULL,
	  `Bugs` varchar(512) NOT NULL,
	  `Name` varchar(512) NOT NULL,
	  `Author` varchar(512) NOT NULL,
	  `Sponsor` varchar(512) NOT NULL,
	  `Homepage` varchar(512) NOT NULL,
	  `Website` varchar(512) NOT NULL,
	  `Depiction` varchar(512) NOT NULL,
	  `Icon` varchar(512) NOT NULL,
	  `MD5sum` varchar(512) NOT NULL,
	  `Stat` int(1) NOT NULL,
	  `Tag` varchar(512) NOT NULL,
	  `UUID` varchar(512) NOT NULL,
	  `TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	  `DownloadTimes` int(8) NOT NULL,
	  `CreateStamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	  PRIMARY KEY (`ID`)
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	/*
	-- ----------------------------
	--  Table structure for `Sections`
	-- ----------------------------
	*/
	
	$result = mysql_query("DROP TABLE IF EXISTS `Sections`");
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	$result = mysql_query("CREATE TABLE `Sections` (
	  `ID` int(8) NOT NULL AUTO_INCREMENT,
	  `Name` varchar(512) NOT NULL,
	  `Icon` varchar(512) NOT NULL,
	  `TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	  PRIMARY KEY (`ID`)
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	/*
	-- ----------------------------
	--  Table structure for `ScreenShots`
	-- ----------------------------
	*/
	
	$result = mysql_query("DROP TABLE IF EXISTS `ScreenShots`");
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	$result = mysql_query("CREATE TABLE `ScreenShots` (
	  `ID` int(8) NOT NULL AUTO_INCREMENT,
	  `PID` int(8) NOT NULL,
	  `Image` varchar(512) NOT NULL,
	  PRIMARY KEY (`ID`)
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	/*
	-- ----------------------------
	--  Table structure for `Reports`
	-- ----------------------------
	*/
	
	$result = mysql_query("DROP TABLE IF EXISTS `Reports`");
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	$result = mysql_query("CREATE TABLE `Reports` (
	  `ID` int(8) NOT NULL AUTO_INCREMENT,
	  `PID` int(8) NOT NULL,
	  `Remote` varchar(64) NOT NULL,
	  `Device` varchar(64) NOT NULL,
	  `iOS` varchar(64) NOT NULL,
	  `Version` varchar(64) NOT NULL,
	  `Support` int(8) NOT NULL,
	  `TimeStamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	  PRIMARY KEY (`ID`)
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	/*
	-- ----------------------------
	--  Table structure for `Users`
	-- ----------------------------
	*/
	
	$result = mysql_query("DROP TABLE IF EXISTS `Users`");
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	$result = mysql_query("CREATE TABLE `Users` (
	  `ID` int(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	  `Username` varchar(64) NOT NULL,
	  `SHA1` varchar(128) NOT NULL,
	  `LastLoginTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `Power` int(8) NOT NULL
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}

	$result = mysql_query("INSERT INTO `Users` (`Username`, `SHA1`, `LastLoginTime`, `Power`)
	VALUES ('root', 'DC76E9F0C0006E8F919E0C515C66DBBA3982F785', '0000-00-00 00:00:00', '1')");
	
	if (!$result) {
		$inst_alert = mysql_error();
		goto endlabel;
	}
	
	if(!mkdir("../tmp") || !copy("CydiaIcon.png", "../CydiaIcon.png")) {
		$inst_alert = "文件权限错误，请赋予根目录读取与写入权限。";
		goto endlabel;
	}
	else {
		unlink("index.html");
		unlink("install.php");
		unlink("CydiaIcon.png");
		rmdir("../init");
		$inst_success = true;
	}
	
	endlabel:
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width; initial-scale=1.3;  minimum-scale=1.0; maximum-scale=2.0" />
	<meta name="MobileOptimized" content="240" />
	<title>DCRM - 快捷安装向导</title>
</head>
<body>
	<?php
		if ($inst_success == true) {
	?>
<h4>安装成功</h4>
	<h5>默认用户名：root，密码：root。<br />登录后请您及时修改用户名和密码。<br />您可以放置新的 CydiaIcon.png 到根目录下作为您的源图标。<br /><br />安装程序已自毁。</h5>
	<h5><a href="../manage/login.php">立即登录</a></h5>
	<?php
		}
		else {
	?>
<h4>安装失败</h4>
	<h5><?php echo $inst_alert; ?></h5>
	<h5><a href="./index.html">返回</a></h5>
	<?php
		}
	?>
</body>
</html>