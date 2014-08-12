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
	
	/* DCRM Mobile Page */
	
	error_reporting(0);
	ob_start();
	define("DCRM",true);
	require_once("manage/include/config.inc.php");
	require_once("manage/include/autofill.inc.php");
	require_once("manage/include/Mobile_Detect.php");
	require_once('manage/include/connect.inc.php');
	header("Content-Type: text/html; charset=UTF-8");
	date_default_timezone_set('Asia/Shanghai');
	
	$detect = new Mobile_Detect;
	if(!$detect->isiOS()){
		header("Location: misc.php");
	}
	$con = mysql_connect($server,$username,$password);
	if (!$con) {
		echo 'MYSQL ERROR!<br />数据库错误！<br />请联系管理员检查问题。';
		exit();
	}
	mysql_query("SET NAMES utf8",$con);
	$select = mysql_select_db($database,$con);
	if (!$select) {
		echo 'MYSQL ERROR!<br />数据库错误！<br />请联系管理员检查问题。';
		exit();
	}
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
	}
	if (isset($_GET['pid']) && is_numeric($_GET['pid'])) {
		if (isset($_GET['method']) && $_GET['method'] == "screenshot") {
			$index = 2;
			$title = "预览截图";
		} elseif (isset($_GET['method']) && $_GET['method'] == "report") {
			$device_type = array("iPhone","iPod","iPad");
			for ($i = 0; $i < count($device_type); $i++) {
				$check = $detect->version($device_type[$i]);
				if ($check !== false) {
					if (isset($_SERVER['HTTP_X_MACHINE'])) {
						$DEVICE = substr($_SERVER['HTTP_X_MACHINE'],0,-3);
					} else {
						$DEVICE = "Unknown";
					}
					$OS = str_replace("_", ".", $check);
					break;
				}
			}
			if (isset($_GET['support'])) {
				if ($_GET['support'] == "1") {
					$support = 1;
				} elseif ($_GET['support'] == "2") {
					$support = 2;
				} elseif ($_GET['support'] == "3") {
					$support = 3;
				} else {
					$support = 0;
				}
				$index = 4;
				$title = "提交报告";
			} else {
				$index = 3;
				$title = "兼容报告";
			}
		} elseif (isset($_GET['method']) && $_GET['method'] == "history") {
			$index = 5;
			$title = "历史版本";
		} else {
			$index = 1;
			$title = "查看软件包";
		}
	} else {
		$index = 0;
		$title = $release_origin;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $title; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />
		<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
<?php
	if (defined("AUTOFILL_KEYWORDS")) {
?>
		<meta name ="keywords" content="<?php echo(AUTOFILL_KEYWORDS); ?>" />
<?php
	}
	if (defined("AUTOFILL_SEO")) {
?>
		<meta name ="description" content="<?php echo(AUTOFILL_SEO); ?>" />
<?php
	}
?>
		<base target="_top">
		<link rel="shortcut icon" href="favicon.ico" />
		<link href="css/menes.min.css" rel="stylesheet">
		<link href="css/scroll.min.css" rel="stylesheet">
		<script src="js/fastclick.js" type="text/javascript"></script>
		<script src="js/menes.js" type="text/javascript"></script>
		<script src="js/cytyle.js" type="text/javascript"></script>
	</head>
	<body class="pinstripe">
		<panel>
<?php
	if ($index == 0) {
?>
			<fieldset>
				<a href="cydia://sources/add">
				<img class="icon" src="CydiaIcon.png">
					<div>
						<div>
						<label>
						<p><?php echo $release_origin; ?></p>
						</label>
						</div>
					</div>
				</a>
			</fieldset>
			<fieldset>
				<div>
					<div style="float: right; margin-top: 8px; text-align: center; width: 200px">
						<span style="font-size: 20px"><?php echo $release_origin; ?></span><br/>
						<span style="font-size: 17px">
							<a class="panel" href="<?php echo AUTOFILL_SITE; ?>"><?php echo AUTOFILL_FULLNAME; ?></a>
							<br />
							<a class="panel" href="mailto:<?php echo AUTOFILL_EMAIL; ?>"><?php echo AUTOFILL_EMAIL; ?></a>
						</span>
					</div>
					<img src="CydiaIcon.png" style="vertical-align: middle" width="60" height="60"/>
				</div>
			</fieldset>
			<block>
				<p>欢迎来到 <?php echo AUTOFILL_MASTER; ?> 的软件源！</p>
<?php
		$q_info = mysql_query("SELECT count(*) FROM `Packages` WHERE `Stat` = '1'");
		$info = mysql_fetch_row($q_info);
		$num[0] = (int)$info[0];
?>
				<p>目前有 <strong><?php echo $num[0]; ?></strong> 个软件包可供下载喔！</p>
				<p><?php echo $release_description; ?></p>
				<p><strong>请使用 Cydia<sup><small>™</small></sup> 添加地址：<br /><a href="<?php echo(base64_decode(DCRM_REPOURL)); ?>"><?php echo(base64_decode(DCRM_REPOURL)); ?></a></strong></p>
			</block>
<?php
		$section_query = mysql_query("SELECT `Name`, `Icon` FROM `Sections`");
		if (!$section_query) {
?>
			<block>
			<p>MYSQL ERROR!<br />数据库错误！<br />请联系管理员检查问题。</p>
			</block>
<?php
		} else {
			while ($section_assoc = mysql_fetch_assoc($section_query)) {
?>
			<label><?php echo($section_assoc['Name']); ?></label>
			<fieldset>
<?php
				$package_query = mysql_query("SELECT `ID`, `Name`, `Package` FROM `Packages` WHERE (`Stat` = '1' AND `Section` = '".mysql_real_escape_string($section_assoc['Name'])."') ORDER BY `ID` DESC LIMIT " . DCRM_SHOW_NUM);
				while ($package_assoc = mysql_fetch_assoc($package_query)) {
?>
				<a href="index.php?pid=<?php echo($package_assoc['ID']); ?>">
					<img class="icon" src="icons/<?php echo($section_assoc['Icon']); ?>" width="58" height="58">
					<div>
						<div>
							<label>
							<p><?php echo($package_assoc['Name']); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
?>
			</fieldset>
<?php
			}
		}
	} elseif ($index == 1) {
		$pkg = (int)mysql_real_escape_string($_GET['pid']);
		$pkg_query = mysql_query("SELECT `Name`, `Version`, `Package`, `Description`, `DownloadTimes`, `Multi`, `CreateStamp` FROM `Packages` WHERE `ID` = '".$pkg."' LIMIT 1");
		if (!$pkg_query) {
?>
			<block>
			<p>MYSQL ERROR!<br />数据库错误！<br />请联系管理员检查问题。</p>
			</block>
<?php
		} else {
			$pkg_assoc = mysql_fetch_assoc($pkg_query);
			if (!$pkg_assoc) {
?>
			<block>
			<p>NO PACKAGE SELECTED!<br />无效的软件包信息！<br />可能是该软件包已被删除，如有疑问，请联系管理员。</p>
			</block>
<?php
			} else {
?>
			<fieldset id="cydialink" style="display: none;">
				<a href="cydia://package/<?php echo $pkg_assoc['Package']; ?>">
				<img class="icon" src="icons/cydia.png">
					<div>
						<div>
							<label>
							<p>在 Cydia<sup><small>™</small></sup> 中查看</p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
			<fieldset>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=screenshot">
				<img class="icon" src="icons/screenshots.png">
					<div>
						<div>
							<label>
							<p>预览截图</p>
							</label>
						</div>
					</div>
				</a>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=history" id="historylink">
				<img class="icon" src="icons/clock.png">
					<div>
						<div>
							<label>
							<p>历史版本</p>
							</label>
						</div>
					</div>
				</a>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=report" id="reportlink">
				<img class="icon" src="icons/report.png">
					<div>
						<div>
							<label>
							<p>兼容报告</p>
							</label>
						</div>
					</div>
				</a>
<?php
				if (defined("AUTOFILL_WEIBO") && defined("AUTOFILL_WEIBO_NAME")) {
?>
				<a href="<?php echo AUTOFILL_WEIBO; ?>">
				<img class="icon" src="icons/weibo.png">
					<div>
						<div>
							<label>
							<p>@<?php echo AUTOFILL_WEIBO_NAME; ?> 官方微博</p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_PAYPAL")) {
?>
				<a href="<?php echo AUTOFILL_PAYPAL; ?>" target="_blank">
				<img class="icon" src="icons/paypal.png">
					<div>
						<div>
							<label>
							<p>前往 <span style="font-style: italic; font-weight: bold"><span style="color: #1a3665">Pay</span><span style="color: #32689a">Pal</span><sup><small>™</small></sup></span> 捐助</p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("EMERGENCY")) {
?>
				<a>
					<div>
						<div>
						<?php echo EMERGENCY; ?>
						</div>
					</div>
				</a>
<?php
				}
?>
			</fieldset>
<?php
				if (defined("AUTOFILL_ADVERTISEMENT")) {
?>
			<fieldset id="advertisement">
				<div>
					<div>
						<?php echo AUTOFILL_ADVERTISEMENT; ?>
					</div>
				</div>
			</fieldset>
<?php	
				}
?>
			<fieldset>
				<div>
					<p><?php echo "版本 ".$pkg_assoc['Version']." 下载次数 ".$pkg_assoc['DownloadTimes']; ?></p>
					<p><?php echo "更新时间：".$pkg_assoc['CreateStamp']; ?></p>
					<hr />
					<p><strong><?php echo htmlspecialchars($pkg_assoc['Description']); ?></strong></p>
				</div>
			</fieldset>
<?php
				if (!empty($pkg_assoc['Multi'])) {
?>
			<fieldset>
				<div>
					<div>
						<?php echo $pkg_assoc['Multi']; ?>
					</div>
				</div>
			</fieldset>
<?php
				}
			}
		}
	} elseif ($index == 2) {
		$pkg = (int)mysql_real_escape_string($_GET['pid']);
		$pkg_query = mysql_query("SELECT `PID`, `Image` FROM `ScreenShots` WHERE `PID` = '".$pkg."'");
		if (!$pkg_query) {
			echo 'MYSQL ERROR!<br />数据库错误！<br />请联系管理员检查问题。';
			exit();
		} else {
			$num = mysql_affected_rows();
			if ($num != 0) {
?>
			<label>预览截图</label>
			<br />
			<div class="horizontal-scroll-wrapper" id="scroller">
				<div class="horizontal-scroll-area" style="width:<?php echo($num * 200); ?>px;">
					<?php
						while ($pkg_assoc = mysql_fetch_assoc($pkg_query)) {
							echo '<img src="'.$pkg_assoc['Image'].'" />';
						}
					?>
				</div>
				<div class="horizontal-scroll-pips"></div>
			</div>
<?php
			} else {
?>
			<label>该软件包暂无截图</label>
			<br />
<?php
			}
		}
	} elseif ($index == 3) {
		$q_count = mysql_query("SELECT `Support`, COUNT(*) AS 'num' FROM `Reports` WHERE (`Device` = '".$DEVICE."' AND `iOS` = '".$OS."' AND `PID` = '".$_GET['pid']."') GROUP BY `Support`");
		if (mysql_affected_rows() > 0) {
			while ($s_count = mysql_fetch_assoc($q_count)) {
				switch ($s_count['Support']) {
					case 1:
						$s_1 = " (".$s_count['num'].")";
						$i_1 = $s_count['num'];
						break;
					case 2:
						$s_2 = " (".$s_count['num'].")";
						$i_2 = $s_count['num'];
						break;
					case 0:
						$s_0 = " (".$s_count['num'].")";
						$i_0 = $s_count['num'];
						break;
				}
			}
		}
		$check_int = $i_1 * 3 + $i_2 - $i_0 * 2;
		if ($check_int >= 10) {
?>
			<fieldset style="background-color: #ccffcc;">
<?php
		} elseif ($check_int <= -6) {
?>
			<fieldset style="background-color: #ffdddd;">
<?php
		} else {
?>
			<fieldset>
<?php
		}
?>
				<div>
					<p><strong>当前设备信息</strong></p>
					<hr />
					<p><?php echo $DEVICE." &amp; ".$OS; ?></p>
				</div>
			</fieldset>
			<fieldset>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=report&support=1">
					<img class="icon" src="icons/support_1.png" width="58" height="58">
					<div>
						<div>
							<label>
							<p>完美兼容<?php echo $s_1; ?></p>
							</label>
						</div>
					</div>
				</a>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=report&support=0">
					<img class="icon" src="icons/support_0.png" width="58" height="58">
					<div>
						<div>
							<label>
							<p>部分兼容<?php echo $s_0; ?></p>
							</label>
						</div>
					</div>
				</a>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=report&support=2">
					<img class="icon" src="icons/support_2.png" width="58" height="58">
					<div>
						<div>
							<label>
							<p>不兼容<?php echo $s_2; ?></p>
							</label>
						</div>
					</div>
				</a>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=report&support=3">
					<img class="icon" src="icons/support_3.png" width="58" height="58">
					<div>
						<div>
							<label>
							<p>请求升级</p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
			<fieldset>
				<div>
				<p><strong>软件包兼容性报告是由广大用户投票，系统统计生成的数据，仅供参考。</strong></p>
				<hr />
				<p>如果您，安装以后出现兼容性问题，您的一票，也许能够帮助成千上万的用户免于安全模式、白苹果等诸多威胁。</p>
				<p>当然，如果您安装以后能够完美使用，也请您投上一票，它能够让大家更放心地安装软件包。</p>
				</div>
			</fieldset>
<?php
	} elseif ($index == 4) {
		$result = mysql_query("SELECT `ID` FROM `Reports` WHERE (`Remote` = '".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."' AND `PID`='".$_GET['pid']."') LIMIT 3");
		if (mysql_affected_rows() < 3) {
			if (!empty($_SERVER['REMOTE_ADDR']) && !empty($DEVICE) && !empty($OS)) {
				$result = mysql_query("INSERT INTO `Reports`(`Remote`, `Device`, `iOS`, `Support`, `TimeStamp`, `PID`) VALUES('".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."', '".$DEVICE."', '".$OS."', '".$support."', '".date('Y-m-d H:i:s')."', '".(int)$_GET['pid']."')");
			}
?>
			<fieldset class="success">
			<div>
			<p><strong>您的报告已经提交完成。<br />感谢您的支持！</strong></p>
<?php
		} else {
?>
			<fieldset class="warning">
			<div>
			<p><strong>投票次数超过系统限制。<br />请稍后再试！</strong></p>
<?php
		}
?>
			</div>
			</fieldset>
<?php
	} elseif ($index == 5) {
		$history_query = mysql_query("SELECT `ID`, `Version` FROM `Packages` WHERE (`Package` = (SELECT `Package` FROM `Packages` WHERE `ID` = '".(int)$_GET['pid']."' LIMIT 1) AND `Version` != (SELECT `Version` FROM `Packages` WHERE `ID` = '".(int)$_GET['pid']."' LIMIT 1)) ORDER BY `ID` DESC LIMIT 20");
		if (mysql_affected_rows() > 0) {
?>
			<label>历史版本</label>
			<fieldset>
<?php
			while ($history = mysql_fetch_assoc($history_query)) {
?>
				<a href="index.php?pid=<?php echo($history['ID']); ?>&addr=nohistory">
					<img class="icon" src="icons/clock.png" width="58" height="58">
					<div>
						<div>
							<label>
							<p>版本 <?php echo($history['Version']); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
			}
?>
			</fieldset>
<?php
		} else {
			echo '<label>该软件包暂无历史版本</label>';
		}
	}
?>
		<script src="js/scroll.js" type="text/javascript"></script> 
		<script src="js/main.js" type="text/javascript"></script>
<?php
	if (defined("AUTOFILL_STATISTICS")) {
?>
		<div style="text-align: center; display: none;"><?php echo AUTOFILL_STATISTICS; ?></div>
<?php
	}
?>
		</panel>
	</body>
</html>