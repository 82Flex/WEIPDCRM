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
		}
	}
	else {
		$release_origin = '空白页';
	}
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo $release_origin; ?></title>
	<link rel="shortcut icon" href="favicon.ico" />
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/misc.min.css" rel="stylesheet" media="screen">
</head>
<body>
	<div class="well">
		<?php
			echo $release_origin;
			echo "<br><br>";
			echo str_replace("//URL//", "<code>".base64_decode(DCRM_REPOURL)."</code>", "您可以通过 Cydia <a href = \"cydia://sources/add\">添加</a> //URL// 访问该源。");
			if (DCRM_SHOWLIST == 1) {
				require_once('manage/include/connect.inc.php');
				$con = mysql_connect($server,$username,$password);
				if (!$con) {
					echo '<br />数据库错误！如果您是首次安装，请运行 <a href="init/index.html">快速安装脚本</a> 。';
					goto endlabel;
				}
				mysql_query("SET NAMES utf8",$con);
						$select  = mysql_select_db($database,$con);
				if (!$select) {
					$alert = mysql_error();
					echo('<br />数据库错误！');
					goto endlabel;
				}
				echo "<br><br><div class=\"wrapper\">";
				echo "<ul class=\"breadcrumb\"><i class=\"icon\" id=\"source_triangle\" onclick=\"wrapper('source_triangle','item_source'); return false;\">&#9658;</i>&nbsp;"."最新软件包"."</ul>";
				echo '<table class="table" id="item_source" style="display: none;"><thead><tr><th class="span5">'."最新软件包".'</th></tr></thead><tbody>';
				$new_query = mysql_query("SELECT `Name`, `Package` FROM `Packages` WHERE `Stat` = '1' ORDER BY `ID` DESC LIMIT " . DCRM_SHOW_NUM,$con);
				while ($daily = mysql_fetch_assoc($new_query)) {
					echo '<tr><td><a href="cydia://package/' . $daily['Package'] . '">' . $daily['Name'] . '</a></td></tr>';
				}
						
				echo '</tbody></table>';
				echo "</div>";
			}
			endlabel:
		?>
	</div>
	<script src="js/misc.js"></script>
<?php
	if (defined("AUTOFILL_STATISTICS")) {
?>
		<div style="text-align: center; display: none;"><?php echo AUTOFILL_STATISTICS; ?></div>
<?php
	}
?>
</body>
</html>