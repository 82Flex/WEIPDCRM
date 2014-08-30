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
	require_once('manage/include/connect.inc.php');
	require_once("manage/include/autofill.inc.php");
	require_once("manage/include/func.php");
	require_once("manage/include/Mobile_Detect.php");
	header("Content-Type: text/html; charset=UTF-8");
	date_default_timezone_set('Asia/Shanghai');
	
	$detect = new Mobile_Detect;
	if(!$detect->isiOS()){
		header("Location: misc.php");
		exit();
	} else {
		if (DCRM_MOBILE == 2) {
			if (!strpos($detect->getUserAgent(), 'Cydia')) {
				$isCydia = false;
			} else {
				$isCydia = true;
			}
		} else {
			exit();	
		}
	}
	$con = mysql_connect(DCRM_CON_SERVER, DCRM_CON_USERNAME, DCRM_CON_PASSWORD);
	if (!$con) {
		echo 'MYSQL ERROR!<br />数据库错误！<br />请联系管理员检查问题。';
		exit();
	}
	mysql_query("SET NAMES utf8");
	$select = mysql_select_db(DCRM_CON_DATABASE);
	if (!$select) {
		echo 'MYSQL ERROR!<br />数据库错误！<br />请联系管理员检查问题。';
		exit();
	}
	if (file_exists("Release")) {
		$release = file("Release");
		$release_origin = "未命名";
		$release_mtime = filemtime("Release");
		$release_time = date("Y-m-d H:i:s",$release_mtime);
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
				$title = "报告问题";
			} else {
				$index = 3;
				$title = "报告问题";
			}
		} elseif (isset($_GET['method']) && $_GET['method'] == "history") {
			$index = 5;
			$title = "历史版本";
		} elseif (isset($_GET['method']) && $_GET['method'] == "contact") {
			$index = 6;
			$title = "联系方式";
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
		if (!$isCydia) {
?>
			<fieldset>
				<a href="cydia://sources/add" target="_blank">
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
<?php
		}
?>
			<fieldset>
				<div>
					<div style="float: right; vertical-align: middle; text-align: center; width: 200px">
						<span style="font-size: 24px"><?php echo $release_origin; ?></span><br/>
						<span style="font-size: 16px">
							<a class="panel" href="<?php echo AUTOFILL_SITE; ?>"><?php echo AUTOFILL_FULLNAME; ?></a>
							<br />
							<a class="panel" href="mailto:<?php echo AUTOFILL_EMAIL; ?>"><?php echo AUTOFILL_EMAIL; ?></a>
						</span>
					</div>
					<img src="CydiaIcon.png" style="vertical-align: top;" width="64" height="64"/>
				</div>
			</fieldset>
			<block>
				<p>欢迎来到 <?php echo AUTOFILL_MASTER; ?> 的软件源！</p>
<?php
		$q_info = mysql_query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages` WHERE `Stat` = '1'");
		$info = mysql_fetch_row($q_info);
		$num[0] = (int)$info[0];
?>
				<p>目前有 <strong><?php echo $num[0]; ?></strong> 个软件包可供下载喔！</p>
				<p><?php echo $release_description; ?></p>
				<p><strong>请使用 Cydia<sup><small>™</small></sup> 添加地址：<br /><a href="<?php echo(base64_decode(DCRM_REPOURL)); ?>"><?php echo(base64_decode(DCRM_REPOURL)); ?></a></strong></p>
			</block>
			<block>
				<p>更新时间：<?php echo $release_time; ?></p>
			</block>
			<fieldset>
<?php
				if (defined("AUTOFILL_SITE")) {
?>
				<a href="<?php echo AUTOFILL_SITE; ?>" target="_blank">
				<img class="icon" src="CydiaIcon.png">
					<div>
						<div>
							<label>
							<p>访问首页</p>
							</label>
						</div>
					</div>
				</a>

<?php
				}
				if (defined("AUTOFILL_EMAIL")) {
?>
				<a href="mailto:<?php echo AUTOFILL_EMAIL; ?>?subject=<?php echo $release_origin; ?>" target="_blank">
				<img class="icon" src="icons/mail_forward.png">
					<div>
						<div>
							<label>
							<p>联系我们</p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_TENCENT") && defined("AUTOFILL_TENCENT_NAME")) {
?>
				<a href="<?php echo AUTOFILL_TENCENT; ?>" target="_blank">
				<img class="icon" src="icons/qq.png">
					<div>
						<div>
							<label>
							<p><?php echo AUTOFILL_TENCENT_NAME; ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_WEIBO") && defined("AUTOFILL_WEIBO_NAME")) {
?>
				<a href="<?php echo AUTOFILL_WEIBO; ?>" target="_blank">
				<img class="icon" src="icons/weibo.png">
					<div>
						<div>
							<label>
							<p>@<?php echo AUTOFILL_WEIBO_NAME; ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_TWITTER") && defined("AUTOFILL_TWITTER_NAME")) {
?>
				<a href="<?php echo AUTOFILL_TWITTER; ?>" target="_blank">
				<img class="icon" src="icons/twitter.png">
					<div>
						<div>
							<label>
							<p>@<?php echo AUTOFILL_TWITTER_NAME; ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_FACEBOOK") && defined("AUTOFILL_FACEBOOK_NAME")) {
?>
				<a href="<?php echo AUTOFILL_FACEBOOK; ?>" target="_blank">
				<img class="icon" src="icons/facebook.png">
					<div>
						<div>
							<label>
							<p>@<?php echo AUTOFILL_FACEBOOK_NAME; ?></p>
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
?>
			</fieldset>
<?php
		$section_query = mysql_query("SELECT `Name`, `Icon` FROM `".DCRM_CON_PREFIX."Sections`");
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
				$package_query = mysql_query("SELECT `ID`, `Name`, `Package` FROM `".DCRM_CON_PREFIX."Packages` WHERE (`Stat` = '1' AND `Section` = '".mysql_real_escape_string($section_assoc['Name'])."') ORDER BY `ID` DESC LIMIT " . DCRM_SHOW_NUM);
				while ($package_assoc = mysql_fetch_assoc($package_query)) {
					if ($isCydia) {
?>
				<a href="cydia://package/<?php echo $package_assoc['Package']; ?>" target="_blank">
<?php
					} else {
?>
				<a href="index.php?pid=<?php echo($package_assoc['ID']); ?>">
<?php
					}
?>
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
			if (!$isCydia) {
?>
			<label class="source">
				<p>软件源信息</p>
			</label>
			<fieldset class="source">
				<a href="/">
					<img class="icon" src="CydiaIcon.png"></div>
					<div>
						<div>
							<label>
							<p id="source-name"><?php echo $release_origin; ?></p>
							</label>
						</div>
					</div>
				</a>
				<div class="source-description" id="source-description">
					<p><?php echo $release_description; ?></p>
				</div>
			</fieldset>
			<footer id="footer" style="display: none;"><p><span id="id">首页</span><br><span class="source-name"><?php echo $release_origin; ?></span>
			·
			<span id="section">版权所有 &copy; 2014</span></p></footer>
<?php
			}
		}
	} elseif ($index == 1) {
		$pkg = (int)mysql_real_escape_string($_GET['pid']);
		$pkg_query = mysql_query("SELECT `Name`, `Version`, `Author`, `Package`, `Description`, `DownloadTimes`, `Multi`, `CreateStamp`, `Installed-Size`, `Section`, `Homepage` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".$pkg."' LIMIT 1");
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
				if (!$isCydia) {
?>
			<fieldset id="cydialink" style="display: none;">
				<a href="cydia://package/<?php echo $pkg_assoc['Package']; ?>" target="_blank">
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
<?php
					if (!empty($pkg_assoc['Section'])) {
						$section_query = mysql_query("SELECT `Name`, `Icon` FROM `".DCRM_CON_PREFIX."Sections` WHERE `Name` = '".$pkg_assoc['Section']."' LIMIT 1");
						if (!$section_query) {
							$icon_url = "";
						} else {
							$section_assoc = mysql_fetch_assoc($section_query);
							$icon_url = "icons/".$section_assoc['Icon'];
						}
					}
?>
			<div id="header" style="display: none;">
				<img src="<?php echo $icon_url; ?>" style="vertical-align: top;" width="64" height="64"/>
				<div id="content">
					<p id="name"><?php echo $pkg_assoc['Name']; ?></p>
					<p id="latest"><?php echo $pkg_assoc['Version']; ?></p>
					<div id="extra">
						<p><?php if(!empty($pkg_assoc['Installed-Size'])){echo(sizeext($pkg_assoc['Installed-Size'] * 1024));} ?></p>
					</div>
				</div>
			</div>
			<?php
					if (!empty($pkg_assoc['Author'])) {
						$author_name = trim(preg_replace("#^(.+)<(.+)>#","$1", $pkg_assoc['Author']));
						$author_mail = trim(preg_replace("#^(.+)<(.+)>#","$2", $pkg_assoc['Author']));
					}
			?>
			<fieldset id="contact" style="display: none;">
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=contact">
					<img class="icon" src="icons/mail_forward.png">
					<div>
						<div>
							<label><p>作者</p></label>
							<label class="detail"><p id="contact"><?php echo $author_name; ?></p></label>
						</div>
					</div>
				</a>
			</fieldset>
<?php
				}
?>
			<fieldset>
<?php
				if (DCRM_SCREENSHOTS == 2) {
?>
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
<?php
				}
?>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=history" id="historylink">
				<img class="icon" src="icons/changelog.png">
					<div>
						<div>
							<label>
							<p>历史版本</p>
							</label>
						</div>
					</div>
				</a>
<?php
				if (DCRM_DIRECT_DOWN == 1 && !$isCydia) {
?>
				<a href="debs/<?php echo $_GET['pid']; ?>.deb" id="downloadlink" style="display: none;" target="_blank">
				<img class="icon" src="icons/packages.png">
					<div>
						<div>
							<label>
							<p>下载软件包</p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if ($isCydia && DCRM_REPORTING == 2) {
?>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=report" id="reportlink">
				<img class="icon" src="icons/report.png">
					<div>
						<div>
							<label>
							<p>报告问题</p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_TENCENT") && defined("AUTOFILL_TENCENT_NAME")) {
?>
				<a href="<?php echo AUTOFILL_TENCENT; ?>" target="_blank">
				<img class="icon" src="icons/qq.png">
					<div>
						<div>
							<label>
							<p><?php echo AUTOFILL_TENCENT_NAME; ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_WEIBO") && defined("AUTOFILL_WEIBO_NAME")) {
?>
				<a href="<?php echo AUTOFILL_WEIBO; ?>" target="_blank">
				<img class="icon" src="icons/weibo.png">
					<div>
						<div>
							<label>
							<p>@<?php echo AUTOFILL_WEIBO_NAME; ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_TWITTER") && defined("AUTOFILL_TWITTER_NAME")) {
?>
				<a href="<?php echo AUTOFILL_TWITTER; ?>" target="_blank">
				<img class="icon" src="icons/twitter.png">
					<div>
						<div>
							<label>
							<p>@<?php echo AUTOFILL_TWITTER_NAME; ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_FACEBOOK") && defined("AUTOFILL_FACEBOOK_NAME")) {
?>
				<a href="<?php echo AUTOFILL_FACEBOOK; ?>" target="_blank">
				<img class="icon" src="icons/facebook.png">
					<div>
						<div>
							<label>
							<p>@<?php echo AUTOFILL_FACEBOOK_NAME; ?></p>
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
				if (!empty($pkg_assoc['Homepage']) && DCRM_MOREINFO == 2) {
?>
				<a href="<?php echo $pkg_assoc['Homepage']; ?>" target="_blank">
				<img class="icon" src="icons/moreinfo.png">
					<div>
						<div>
							<label>
							<p>更多信息</p>
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
				if (defined("AUTOFILL_ADVERTISEMENT") && $isCydia) {
?>
			<block id="advertisement">
				<div>
					<div>
						<?php echo AUTOFILL_ADVERTISEMENT; ?>
					</div>
				</div>
			</block>
<?php	
				}
?>
			<fieldset>
				<div>
				<?php
					if (DCRM_MULTIINFO == 2) {
				?>
					<p><?php echo "版本 ".$pkg_assoc['Version']." 下载次数 ".$pkg_assoc['DownloadTimes']; ?></p>
					<p><?php echo "更新时间：".$pkg_assoc['CreateStamp']; ?></p>
					<hr />
				<?php
					}
				?>
					<p><?php echo nl2br(htmlspecialchars($pkg_assoc['Description'])); ?></p>
				</div>
			</fieldset>
<?php
				if (!empty($pkg_assoc['Multi']) && DCRM_MULTIINFO == 2) {
?>
			<fieldset>
				<div>
						<?php echo $pkg_assoc['Multi']; ?>
				</div>
			</fieldset>
<?php
				}
?>
			<label class="source">
				<p>软件源信息</p>
			</label>
			<fieldset class="source">
				<a href="/">
					<img class="icon" src="CydiaIcon.png"></div>
					<div>
						<div>
							<label>
							<p id="source-name"><?php echo $release_origin; ?></p>
							</label>
						</div>
					</div>
				</a>
				<div class="source-description" id="source-description">
					<p><?php echo $release_description; ?></p>
				</div>
			</fieldset>
<?php
				if (!$isCydia) {
?>
			<footer id="footer" style="display: none;"><p><span id="id"><?php echo $pkg_assoc['Package']; ?></span><br><span class="source-name"><?php echo $release_origin; ?></span>
			·
			<span id="section"><?php echo $pkg_assoc['Section']; ?></span></p></footer>
<?php
				}
			}
		}
	} elseif ($index == 2) {
		if (DCRM_SCREENSHOTS == 2) {
			$pkg = (int)mysql_real_escape_string($_GET['pid']);
			$pkg_query = mysql_query("SELECT `PID`, `Image` FROM `".DCRM_CON_PREFIX."ScreenShots` WHERE `PID` = '".$pkg."'");
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
		} else {
?>
			<label>管理员关闭了预览截图功能</label>
			<br />
<?php
		}
	} elseif ($index == 3) {
		if (DCRM_REPORTING == 2) {
			$q_count = mysql_query("SELECT `Support`, COUNT(*) AS 'num' FROM `".DCRM_CON_PREFIX."Reports` WHERE (`Device` = '".$DEVICE."' AND `iOS` = '".$OS."' AND `PID` = '".$_GET['pid']."') GROUP BY `Support`");
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
			<label>提交请求</label>
			<fieldset>
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
			<label>兼容性报告</label>
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
		} else {
?>
			<label>管理员关闭了报告问题功能</label>
			<br />
<?php
		}
	} elseif ($index == 4) {
		if (DCRM_REPORTING == 2) {
			$result = mysql_query("SELECT `ID` FROM `".DCRM_CON_PREFIX."Reports` WHERE (`Remote` = '".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."' AND `PID`='".$_GET['pid']."') LIMIT 3");
			if (mysql_affected_rows() < 3) {
				if (!empty($_SERVER['REMOTE_ADDR']) && !empty($DEVICE) && !empty($OS) && $isCydia) {
					$result = mysql_query("INSERT INTO `".DCRM_CON_PREFIX."Reports`(`Remote`, `Device`, `iOS`, `Support`, `TimeStamp`, `PID`) VALUES('".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."', '".$DEVICE."', '".$OS."', '".$support."', '".date('Y-m-d H:i:s')."', '".(int)$_GET['pid']."')");
?>
			<fieldset style="background-color: #ccffcc;">
			<div>
			<p><strong>您的报告已经提交完成。<br />感谢您的支持！</strong></p>
<?php
				} else {
?>
			<fieldset style="background-color: #ffdddd;">
			<div>
			<p><strong>请使用 Cydia 进行投票。<br />每台设备限制投票 2 次！</strong></p>
<?php
				}
			} else {
?>
			<fieldset style="background-color: #ffdddd;">
			<div>
			<p><strong>投票次数超过系统限制。<br />请稍后再试！</strong></p>
<?php
			}
?>
			</div>
			</fieldset>
<?php
		} else {
?>
			<label>管理员关闭了报告问题功能</label>
			<br />
<?php
		}
	} elseif ($index == 5) {
		$history_query = mysql_query("SELECT `ID`, `Version` FROM `".DCRM_CON_PREFIX."Packages` WHERE `Package` = (SELECT `Package` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".(int)$_GET['pid']."' LIMIT 1) ORDER BY `ID` DESC LIMIT 1,20");
		if (mysql_affected_rows() > 0) {
?>
			<label>历史版本</label>
			<fieldset>
<?php
			while ($history = mysql_fetch_assoc($history_query)) {
?>
				<a href="index.php?pid=<?php echo($history['ID']); ?>&addr=nohistory">
					<img class="icon" src="icons/changelog.png" width="58" height="58">
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
?>
			<label>该软件包暂无历史版本</label>
			<br />
<?php
		}
	} elseif ($index == 6) {
		$pkg = (int)mysql_real_escape_string($_GET['pid']);
		$pkg_query = mysql_query("SELECT `Author`, `Sponsor`, `Maintainer` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".$pkg."' LIMIT 1");
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
				if (!empty($pkg_assoc['Author'])) {
					$author_name = trim(preg_replace("#^(.+)<(.+)>#","$1", $pkg_assoc['Author']));
					$author_mail = trim(preg_replace("#^(.+)<(.+)>#","$2", $pkg_assoc['Author']));
?>
			<fieldset class="author">
				<div><p>源管理者<strong>无法</strong>为你解决软件包功能上出现的问题：你<strong>必须</strong>联系其开发者或设计者。</p></div>
				<a href="mailto:<?php echo($author_mail); ?>" target="_blank">
				<img class="icon" src="icons/mail_forward.png">
				<div>
					<div>
						<label><p>作者</p></label>
						<label class="detail"><p><?php echo($author_name); ?></p></label>
						</div>
					</div>
				</a>
			</fieldset>
<?php
				}
				if (!empty($pkg_assoc['Sponsor'])) {
					$sponsor_name = trim(preg_replace("#^(.+)<(.+)>#","$1", $pkg_assoc['Sponsor']));
					$sponsor_url = trim(preg_replace("#^(.+)<(.+)>#","$2", $pkg_assoc['Sponsor']));
?>
			<fieldset class="maintainer">
				<div><p>如果该软件包为商业软件包，你可以联系其担保人获取商业支持。</p></div>
				<a href="<?php echo($sponsor_url); ?>" target="_blank">
				<img class="icon" src="icons/mail_forward.png">
				<div>
					<div>
						<label><p>担保人</p></label>
						<label class="detail"><p><?php echo($sponsor_name); ?></p></label>
						</div>
					</div>
				</a>
			</fieldset>
<?php
				}
				if (!empty($pkg_assoc['Maintainer'])) {
					$maintainer_name = trim(preg_replace("#^(.+)<(.+)>#","$1", $pkg_assoc['Maintainer']));
					$maintainer_mail = trim(preg_replace("#^(.+)<(.+)>#","$2", $pkg_assoc['Maintainer']));
?>
			<fieldset class="maintainer">
				<div><p>安装、卸载过程中出现的问题，你需要将其发送给制作软件包的提供者。</p></div>
				<a href="mailto:<?php echo($maintainer_mail); ?>" target="_blank">
				<img class="icon" src="icons/mail_forward.png">
				<div>
					<div>
						<label><p>提供者</p></label>
						<label class="detail"><p><?php echo($maintainer_name); ?></p></label>
						</div>
					</div>
				</a>
			</fieldset>
<?php
				}
			}
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