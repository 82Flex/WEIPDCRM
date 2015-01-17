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
	define('DCRM',true);
	require_once('manage/include/config.inc.php');
	require_once('manage/include/connect.inc.php');
	require_once('manage/include/autofill.inc.php');
	include_once("lang/e.php");
	require_once('manage/include/func.php');
	require_once('manage/include/Mobile_Detect.php');
	header('Content-Type: text/html; charset=UTF-8');
	date_default_timezone_set('Asia/Shanghai');
	$detect = new Mobile_Detect;
	if(!$detect->isiOS()){
		if (DCRM_PCINDEX == 2) {
			header('Location: misc.php');
			exit();
		} else {
			$isCydia = false;
		}
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
		httpinfo(500);
		exit();
	}
	mysql_query('SET NAMES utf8');
	$select = mysql_select_db(DCRM_CON_DATABASE);
	if (!$select) {
		httpinfo(500);
		exit();
	}
	if (file_exists('Release')) {
		$release = file('Release');
		$release_origin = $_e['NONAME'];
		$release_mtime = filemtime('Release');
		$release_time = date('Y-m-d H:i:s',$release_mtime);
		foreach ($release as $line) {
			if(preg_match('#^Origin#', $line)) {
				$release_origin = trim(preg_replace("#^(.+):\\s*(.+)#","$2", $line));
			}
			if(preg_match("#^Description#", $line)) {
				$release_description = trim(preg_replace("#^(.+):\\s*(.+)#","$2", $line));
			}
		}
	} else {
		$release_origin = $_e['EMPTY_PAGE'];
	}
	if (isset($_GET['pid'])) {
		if (ctype_digit($_GET['pid']) && intval($_GET['pid']) <= 10000) {
			if (isset($_GET['method']) && $_GET['method'] == 'screenshot') {
				$index = 2;
				$title = $_e['VIEW_SCREENSHOTS'];
			} elseif (isset($_GET['method']) && $_GET['method'] == 'report') {
				$device_type = array('iPhone','iPod','iPad');
				for ($i = 0; $i < count($device_type); $i++) {
					$check = $detect->version($device_type[$i]);
					if ($check !== false) {
						if (isset($_SERVER['HTTP_X_MACHINE'])) {
							$DEVICE = substr($_SERVER['HTTP_X_MACHINE'],0,-3);
						} else {
							$DEVICE = 'Unknown';
						}
						$OS = str_replace('_', '.', $check);
						break;
					}
				}
				if (!isset($_GET['support'])) {
					$index = 3;
				} else {
					if ($_GET['support'] == '1') {
						$support = 1;
					} elseif ($_GET['support'] == '2') {
						$support = 2;
					} elseif ($_GET['support'] == '3') {
						$support = 3;
					} else {
						$support = 0;
					}
					$index = 4;
				}
				$title = $_e['REPORT_PROBLEMS'];
			} elseif (isset($_GET['method']) && $_GET['method'] == 'history') {
				$index = 5;
				$title = $_e['VERSION_HISTORY'];
			} elseif (isset($_GET['method']) && $_GET['method'] == 'contact') {
				$index = 6;
				$title = $_e['CONTACT_US'];
			} elseif (isset($_GET['method']) && $_GET['method'] == 'section') {
				$index = 7;
				$title = $_e['PACKAGE_CATEGORY'];
			} elseif (!isset($_GET['method']) || (isset($_GET['method']) && $_GET['method'] == 'view')) {
				$index = 1;
				$title = $_e['VIEW_PACKAGE'];
			} else {
				httpinfo(405);
				exit();
			}
		} else {
			httpinfo(405);
			exit();
		}
	} elseif (!isset($_GET['method'])) {
		$index = 0;
		$title = $release_origin;
	} else {
		httpinfo(405);
		exit();
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo($title); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="apple-mobile-web-app-title" content="<?php echo($release_origin); ?>" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
		<meta name="viewport" content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" />
		<meta name="HandheldFriendly" content="true" />
		<meta name="format-detection" content="telephone=no" />
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
	if ($isCydia) {
?>
		<base target="_blank">
<?php
	} else {
?>
		<base target="_top">
<?php
	}
?>
		<link rel="apple-touch-icon" href="CydiaIcon.png">
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="stylesheet" href="css/menes.min.css">
		<link rel="stylesheet" href="css/scroll.min.css">
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
				<img class="icon" src="icons/default/cydia.png" />
					<div>
						<div>
							<label>
								<p>
									<?php echo(_t('ADD_IN_CYDIA')); ?>
								</p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
<?php
		}
		$repo_url = base64_decode(DCRM_REPOURL);
?>
			<fieldset>
				<div>
					<div style="float: right; vertical-align: middle; text-align: center; width: 200px">
						<span style="font-size: 24px">
							<?php echo($release_origin); ?>
						</span>
						<br/>
						<span style="font-size: 16px">
							<a class="panel" href="<?php echo(AUTOFILL_SITE); ?>"><?php echo(AUTOFILL_FULLNAME); ?></a>
							<br />
							<a class="panel" href="mailto:<?php echo(AUTOFILL_EMAIL); ?>"><?php echo(AUTOFILL_EMAIL); ?></a>
						</span>
					</div>
					<img class="icon" src="CydiaIcon.png" style="width:64px; height:64px; vertical-align: top;" />
					<hr />
					<p>
						<?php echo(_t('USE_CYDIA_TO_ADD_URL')); ?>
						<br />
						<strong><a href="<?php echo($repo_url); ?>"><?php echo($repo_url); ?></a></strong>
					</p>
				</div>
			</fieldset>
<?php
		$q_info = mysql_query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages` WHERE `Stat` = '1'");
		$info = mysql_fetch_row($q_info);
		$num = (int)$info[0];
?>
			<block>
				<p>
					<?php echo(_t('TOTAL_PACKAGES', $num)); ?> 
				</p>
				<p>
					<?php echo(_t('LAST_UPDATE_TIME', $release_time)); ?> 
				</p>
			</block>
			<fieldset>
<?php
				if (defined("AUTOFILL_SITE")) {
?>
				<a href="<?php echo(AUTOFILL_SITE); ?>" target="_blank">
				<img class="icon" src="CydiaIcon.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('VISIT_HOME_PAGE')); ?></p>
							</label>
						</div>
					</div>
				</a><?php
				}
				if (defined("AUTOFILL_EMAIL")) {
?>
				<a href="mailto:<?php echo(AUTOFILL_EMAIL); ?>?subject=<?php echo($release_origin); ?>" target="_blank">
				<img class="icon" src="icons/default/mail_forward.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('CONTACT_US')); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_TENCENT") && defined("AUTOFILL_TENCENT_NAME")) {
?>
				<a href="<?php echo(AUTOFILL_TENCENT); ?>" target="_blank">
				<img class="icon" src="icons/default/qq.png" />
					<div>
						<div>
							<label>
								<p><?php echo(AUTOFILL_TENCENT_NAME); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_WEIBO") && defined("AUTOFILL_WEIBO_NAME")) {
?>
				<a href="<?php echo(AUTOFILL_WEIBO); ?>" target="_blank">
				<img class="icon" src="icons/default/weibo.png" />
					<div>
						<div>
							<label>
								<p>@<?php echo(AUTOFILL_WEIBO_NAME); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_TWITTER") && defined("AUTOFILL_TWITTER_NAME")) {
?>
				<a href="<?php echo(AUTOFILL_TWITTER); ?>" target="_blank">
				<img class="icon" src="icons/default/twitter.png" />
					<div>
						<div>
							<label>
								<p>@<?php echo(AUTOFILL_TWITTER_NAME); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_FACEBOOK") && defined("AUTOFILL_FACEBOOK_NAME")) {
?>
				<a href="<?php echo(AUTOFILL_FACEBOOK); ?>" target="_blank">
				<img class="icon" src="icons/default/facebook.png" />
					<div>
						<div>
							<label>
								<p>@<?php echo(AUTOFILL_FACEBOOK_NAME); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_PAYPAL")) {
?>
				<a href="<?php echo(AUTOFILL_PAYPAL); ?>" target="_blank">
				<img class="icon" src="icons/default/paypal.png" />
					<div>
						<div>
							<label>
								<p>
									<?php echo(_t('DONATE_VIA_PAYPAL')); ?>
								</p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
?>
			</fieldset>
<?php
		if (DCRM_SHOWLIST == 2) {
			$section_query = mysql_query("SELECT `ID`, `Name`, `Icon` FROM `".DCRM_CON_PREFIX."Sections`");
			if (!$section_query) {
?>
			<block>
				<p>
					<?php echo(_t('MYSQL_ERROR')); ?>
				</p>
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
				<a href="cydia://package/<?php echo($package_assoc['Package']); ?>" target="_blank">
<?php
						} else {
?>
				<a href="index.php?pid=<?php echo($package_assoc['ID']); ?>">
<?php
						}
						if (!empty($section_assoc['Icon'])) {
?>
					<img class="icon" src="icons/<?php echo($section_assoc['Icon']); ?>" />
<?php
						} else {
?>
					<img class="icon" src="icons/default/unknown.png" />
<?php
						}
?>
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
					if (DCRM_ALLOW_FULLLIST == 2) {
?>
				<a href="index.php?pid=<?php echo($section_assoc['ID']); ?>&method=section">
					<img class="icon" src="icons/default/moreinfo.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('MORE')); ?></p>
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
		} else {
			if (DCRM_ALLOW_FULLLIST == 2) {
				$section_query = mysql_query("SELECT `ID`, `Name`, `Icon` FROM `".DCRM_CON_PREFIX."Sections`");
				if (!$section_query) {
?>
			<block>
				<p>
					<?php echo(_t('MYSQL_ERROR')); ?>
				</p>
			</block>
<?php
				} else {
?>
			<label><?php echo(_t('PACKAGE_CATEGORY')); ?></label>
			<fieldset>
<?php
					while ($section_assoc = mysql_fetch_assoc($section_query)) {
?>
				<a href="index.php?pid=<?php echo($section_assoc['ID']); ?>&method=section">
<?php
						if (!empty($section_assoc['Icon'])) {
?>
					<img class="icon" src="icons/<?php echo($section_assoc['Icon']); ?>" />
<?php
						} else {
?>
					<img class="icon" src="icons/default/unknown.png" />
<?php
						}
?>
					<div>
						<div>
							<label>
								<p><?php echo($section_assoc['Name']); ?></p>
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
		}
		if (!$isCydia) {
?>
			<label class="source">
				<p><?php echo(_t('SOURCE_INFO')); ?></p>
			</label>
			<fieldset class="source">
				<a href="/">
					<img class="icon" src="CydiaIcon.png" />
					<div>
						<div>
							<label>
								<p id="source-name">
									<?php echo($release_origin); ?>
								</p>
							</label>
						</div>
					</div>
				</a>
				<div class="source-description" id="source-description">
					<p><?php echo($release_description); ?></p>
				</div>
			</fieldset>
			<footer id="footer" style="display: none;">
				<p>
					<span id="id"><?php echo(_t('INDEX')); ?></span>
					<br />
					<span class="source-name"><?php if(defined("AUTOFILL_FOOTER_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_FOOTER_NAME)));}else{echo($release_origin);} ?></span>·
					<span id="section"><?php if(defined("AUTOFILL_FOOTER_YEAR")){echo(_t('COPYRIGHT', htmlspecialchars(stripslashes(AUTOFILL_FOOTER_YEAR)).'-'.date("Y")));}else{echo(_t('COPYRIGHT', date("Y")));} ?></span>
					<?php if(defined("AUTOFILL_FOOTER_CODE")){ ?>
					<br />
					<span id="code"><?php echo(stripslashes(AUTOFILL_FOOTER_CODE));?></span>
					<?php } ?>
				</p>
			</footer>
<?php
		}
	} elseif ($index == 1) {
		$pkg = (int)mysql_real_escape_string($_GET['pid']);
		$pkg_query = mysql_query("SELECT `Name`, `Version`, `Author`, `Package`, `Description`, `DownloadTimes`, `Multi`, `CreateStamp`, `Size`, `Installed-Size`, `Section`, `Homepage` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".$pkg."' LIMIT 1");
		if (!$pkg_query) {
?>
			<block>
				<p>
					<?php echo(_t('MYSQL_ERROR')); ?>
				</p>
			</block>
<?php
		} else {
			$pkg_assoc = mysql_fetch_assoc($pkg_query);
			if (!$pkg_assoc) {
?>
			<block>
				<p>
					<?php echo(_t('NO_PACKAGE_SELECTED')); ?>
				</p>
			</block>
<?php
			} else {
				if (!$isCydia) {
?>
			<fieldset id="cydialink" style="display: none;">
				<a href="cydia://package/<?php echo($pkg_assoc['Package']); ?>" target="_blank">
				<img class="icon" src="icons/default/cydia.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('VIEW_IN_CYDIA')); ?></p>
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
						}
					}
?>
			<div id="header" style="display: none;">
<?php
						if (!empty($section_assoc['Icon'])) {
?>
				<img class="icon" src="icons/<?php echo($section_assoc['Icon']); ?>" style="width: 64px; height: 64px; vertical-align: top;" />
<?php
						} else {
?>
				<img class="icon" src="icons/default/unknown.png" style="width: 64px; height: 64px; vertical-align: top;" />
<?php
						}
?>
				<div id="content">
					<p id="name"><?php echo($pkg_assoc['Name']); ?></p>
					<p id="latest"><?php echo($pkg_assoc['Version']); ?></p>
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
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&method=contact">
					<img class="icon" src="icons/default/mail_forward.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('AUTHOR')); ?></p>
							</label>
							<label class="detail">
									<p id="contact">
										<?php echo($author_name); ?>
									</p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
<?php
				}
				if (DCRM_DIRECT_DOWN == 1 && !$isCydia) {
?>
			<fieldset>
				<a href="debs/<?php echo($_GET['pid']); ?>.deb" id="downloadlink" style="display: none;" target="_blank">
				<img class="icon" src="icons/default/packages.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('DOWNLOAD')); ?></p>
							</label>
							<label class="detail">
									<p>
										<?php if(!empty($pkg_assoc['Size'])){echo(sizeext($pkg_assoc['Size']));} ?>
									</p>
							</label>
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
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&method=screenshot">
				<img class="icon" src="icons/default/screenshots.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('VIEW_SCREENSHOTS')); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
?>
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&method=history" id="historylink">
				<img class="icon" src="icons/default/changelog.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('VERSION_HISTORY')); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				if ($isCydia && DCRM_REPORTING == 2) {
?>
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&method=report" id="reportlink">
				<img class="icon" src="icons/default/report.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('REPORT_PROBLEMS')); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_TENCENT") && defined("AUTOFILL_TENCENT_NAME")) {
?>
				<a href="<?php echo(AUTOFILL_TENCENT); ?>" target="_blank">
				<img class="icon" src="icons/default/qq.png" />
					<div>
						<div>
							<label>
								<p><?php echo(AUTOFILL_TENCENT_NAME); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_WEIBO") && defined("AUTOFILL_WEIBO_NAME")) {
?>
				<a href="<?php echo(AUTOFILL_WEIBO); ?>" target="_blank">
				<img class="icon" src="icons/default/weibo.png" />
					<div>
						<div>
							<label>
								<p>@<?php echo(AUTOFILL_WEIBO_NAME); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_TWITTER") && defined("AUTOFILL_TWITTER_NAME")) {
?>
				<a href="<?php echo(AUTOFILL_TWITTER); ?>" target="_blank">
				<img class="icon" src="icons/default/twitter.png" />
					<div>
						<div>
							<label>
								<p>@<?php echo(AUTOFILL_TWITTER_NAME); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_FACEBOOK") && defined("AUTOFILL_FACEBOOK_NAME")) {
?>
				<a href="<?php echo(AUTOFILL_FACEBOOK); ?>" target="_blank">
				<img class="icon" src="icons/default/facebook.png" />
					<div>
						<div>
							<label>
								<p>@<?php echo(AUTOFILL_FACEBOOK_NAME); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_PAYPAL")) {
?>
				<a href="<?php echo(AUTOFILL_PAYPAL); ?>" target="_blank">
				<img class="icon" src="icons/default/paypal.png" />
					<div>
						<div>
							<label>
								<p>
									<?php echo(_t('DONATE_VIA_PAYPAL')); ?>
								</p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (!empty($pkg_assoc['Homepage']) && DCRM_MOREINFO == 2) {
?>
				<a href="<?php echo($pkg_assoc['Homepage']); ?>" target="_blank">
				<img class="icon" src="icons/default/moreinfo.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('MORE_INFO')); ?></p>
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
							<?php echo(EMERGENCY); ?>
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
				<div style="position: relative;">
					<div style="position: absolute; right: 10px; top: 2px;">
						<img src="css/closebox@2x.png" style="width: 30px; height: 29px;" onclick="hide()" />
					</div>
					<div>
						<?php echo(AUTOFILL_ADVERTISEMENT); ?>
					</div>
				</div>
			</block>
<?php	
				}
?>
			<block>
<?php
					if (DCRM_MULTIINFO == 2) {
?>
					<p><?php echo(_t('VERSION')); ?> <strong><?php echo($pkg_assoc['Version']); ?></strong> | <?php echo(_t('DOWNLOAD_TIMES')); ?> <strong><?php echo($pkg_assoc['DownloadTimes']); ?></strong></p>
					<p><?php echo(_t('PACKAGE_UPDATE_TIME')); ?> <strong><?php echo($pkg_assoc['CreateStamp']); ?></strong></p>
					<hr />
<?php
					}
?>
					<p><?php echo(nl2br($pkg_assoc['Description'])); ?></p>
			</block>
<?php
				if (!empty($pkg_assoc['Multi']) && DCRM_MULTIINFO == 2) {
?>
			<fieldset>
				<div>
					<?php echo($pkg_assoc['Multi']); ?>
				</div>
			</fieldset>
<?php
				}
				if (defined("AUTOFILL_DUOSHUO_KEY")) {
?>
			<fieldset>
				<div class="ds-thread" data-thread-key="<?php echo($pkg_assoc['Package']); ?>" data-title="<?php echo($pkg_assoc['Name']); ?>" data-url="<?php echo('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"></div>
			</fieldset>
			<script type="text/javascript">
			var duoshuoQuery = {short_name:"<?php echo(AUTOFILL_DUOSHUO_KEY); ?>"};
				(function() {
					var ds = document.createElement('script');
					ds.type = 'text/javascript';ds.async = true;
					ds.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') + '//static.duoshuo.com/embed.js';
					ds.charset = 'UTF-8';
					(document.getElementsByTagName('head')[0] 
					 || document.getElementsByTagName('body')[0]).appendChild(ds);
				})();
			</script>
<?php
				}
?>
			<label class="source">
				<p><?php echo(_t('SOURCE_INFO')); ?></p>
			</label>
			<fieldset class="source">
				<a href="/">
					<img class="icon" src="CydiaIcon.png" />
					<div>
						<div>
							<label>
								<p id="source-name">
									<?php echo($release_origin); ?>
								</p>
							</label>
						</div>
					</div>
				</a>
				<div class="source-description" id="source-description">
					<p>
						<?php echo($release_description); ?>
					</p>
				</div>
			</fieldset>
<?php
				if (!$isCydia) {
?>
			<footer id="footer" style="display: none;">
				<p>
					<span id="id"><?php echo($pkg_assoc['Package']); ?></span>
					<br />
					<span class="source-name"><?php echo($release_origin); ?></span>·
					<span id="section"><?php echo($pkg_assoc['Section']); ?></span>
					<br />
					<?php if(defined("AUTOFILL_FOOTER_CODE")){ ?>
					<br />
					<span id="code"><?php echo(stripslashes(AUTOFILL_FOOTER_CODE));?></span>
					<?php } ?>
				</p>
			</footer>
<?php
				}
			}
		}
	} elseif ($index == 2) {
		if (DCRM_SCREENSHOTS == 2) {
			$pkg = (int)mysql_real_escape_string($_GET['pid']);
			$pkg_query = mysql_query("SELECT `PID`, `Image` FROM `".DCRM_CON_PREFIX."ScreenShots` WHERE `PID` = '".$pkg."'");
			if (!$pkg_query) {
?>
			<block>
				<p>
					<?php echo(_t('MYSQL_ERROR')); ?>
				</p>
			</block>
<?php
			} else {
				$num = mysql_affected_rows();
				if ($num != 0) {
					$preview = array();
					$i = 0;
					while ($pkg_assoc = mysql_fetch_assoc($pkg_query)) {
						$preview[$i] = $pkg_assoc['Image'];
						$i++;
					}
?>
			<!--label><?php echo(_t('VIEW_SCREENSHOTS')); ?></label-->
			<div class="horizontal-scroll-wrapper" style="background: transparent; position: relative;">
				<div class="horizontal-scroll-wrapper" style="background: transparent url(<?php echo($preview[0]); ?>); background-size: 150%; background-position: center; -webkit-filter: blur(5px); position: absolute; z-index: 1;"></div>
				<div class="horizontal-scroll-wrapper" id="scroller" style="background: transparent; position: absolute; z-index: 2;">
					<div class="horizontal-scroll-area" style="width:<?php echo($num * 240); ?>px;">
<?php
					for ($t = 0; $t < count($preview); $t++) {
?>
						<img src="<?php echo($preview[$t]); ?>" />
<?php
					}
?>
					</div>
					<div class="horizontal-scroll-pips"></div>
				</div>
			</div>
<?php
				} else {
?>
			<label><?php echo(_t('NO_SCREENSHOTS')); ?></label>
<?php
				}
			}
		} else {
?>
			<label><?php echo(_t('FUNCTION_DISABLED', _t("VIEW_SCREENSHOTS"))); ?></label>
<?php
		}
	} elseif ($index == 3) {
?>
			<label><?php echo(_t('DEVICE_INFO')); ?></label>
<?php
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
					<p>
						<strong><?php echo(_t('CURRENT_DEVICE_INFO')); ?></strong>
					</p>
					<hr />
					<p>
						<?php echo($DEVICE." &amp; ".$OS); ?>
					</p>
				</div>
			</fieldset>
			<label><?php echo(_t('SUBMIT_YOUR_REQUEST')); ?></label>
			<fieldset>
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&method=report&support=3">
					<img class="icon" src="icons/default/support_3.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('REQUEST_FOR_UPDATE')); ?></p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
			<label><?php echo(_t('COMPATIBILITY_REPORTS')); ?></label>
			<fieldset>
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&method=report&support=1">
					<img class="icon" src="icons/default/support_1.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('FULLY_COMPATIBILE')); ?><?php echo($s_1); ?></p>
							</label>
						</div>
					</div>
				</a>
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&method=report&support=0">
					<img class="icon" src="icons/default/support_0.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('PARTLY_COMPATIBILE')); ?><?php echo($s_0); ?></p>
							</label>
						</div>
					</div>
				</a>
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&method=report&support=2">
					<img class="icon" src="icons/default/support_2.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_t('NOT_COMPATIBILE')); ?><?php echo($s_2); ?></p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
			<fieldset>
				<div>
					<?php echo(_t('COMPATIBILE_INTRO')); ?>
				</div>
			</fieldset>
<?php
		} else {
?>
			<label><?php echo(_t('FUNCTION_DISABLED', _t("REPORT_PROBLEMS"))); ?></label>
<?php
		}
	} elseif ($index == 4) {
		if (DCRM_REPORTING == 2) {
			$result = mysql_query("SELECT `ID` FROM `".DCRM_CON_PREFIX."Reports` WHERE (`Remote` = '".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."' AND `PID`='".$_GET['pid']."') LIMIT 3");
			if (mysql_affected_rows() < DCRM_REPORT_LIMIT) {
				if (!empty($_SERVER['REMOTE_ADDR']) && !empty($DEVICE) && !empty($OS) && $isCydia) {
					$result = mysql_query("INSERT INTO `".DCRM_CON_PREFIX."Reports`(`Remote`, `Device`, `iOS`, `Support`, `TimeStamp`, `PID`) VALUES('".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."', '".$DEVICE."', '".$OS."', '".$support."', '".date('Y-m-d H:i:s')."', '".(int)$_GET['pid']."')");
?>
			<fieldset style="background-color: #ccffcc;">
				<div>
					<p>
						<strong>
							<?php echo(_t('THANKS_FOR_REPORTING')); ?>
						</strong>
					</p>
<?php
				} else {
?>
			<fieldset style="background-color: #ffdddd;">
				<div>
					<p>
						<strong>
							<?php echo(_t('VOTING_RESTRICTIONS')); ?>
						</strong>
					</p>
<?php
				}
			} else {
?>
			<fieldset style="background-color: #ffdddd;">
				<div>
					<p>
						<strong>
							<?php echo(_t('VOTING_RETRY')); ?>
						</strong>
					</p>
<?php
			}
?>
				</div>
			</fieldset>
<?php
		} else {
?>
			<label><?php echo(_t('FUNCTION_DISABLED', _t("REPORT_PROBLEMS"))); ?></label>
<?php
		}
	} elseif ($index == 5) {
		$history_query = mysql_query("SELECT `ID`, `Version` FROM `".DCRM_CON_PREFIX."Packages` WHERE `Package` = (SELECT `Package` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".(int)$_GET['pid']."' LIMIT 1) ORDER BY `ID` DESC LIMIT 1,20");
		if (mysql_affected_rows() > 0) {
?>
			<label><?php echo(_t('VERSION_HISTORY')); ?></label>
			<fieldset>
<?php
			while ($history = mysql_fetch_assoc($history_query)) {
?>
				<a href="index.php?pid=<?php echo($history['ID']); ?>&addr=nohistory">
					<img class="icon" src="icons/default/changelog.png">
					<div>
						<div>
							<label>
								<p>
									<?php echo(_t('VERSION')); ?> <?php echo($history['Version']); ?>
								</p>
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
			<label><?php echo(_t('NO_VERSION_HISTORY')); ?></label>
			<br />
<?php
		}
	} elseif ($index == 6) {
		$pkg = (int)mysql_real_escape_string($_GET['pid']);
		$pkg_query = mysql_query("SELECT `Name`, `Version`, `Author`, `Sponsor`, `Maintainer` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".$pkg."' LIMIT 1");
		if (!$pkg_query) {
?>
			<block>
				<p>
					<?php echo(_t('MYSQL_ERROR')); ?>
				</p>
			</block>
<?php
		} else {
			$pkg_assoc = mysql_fetch_assoc($pkg_query);
			if (!$pkg_assoc) {
?>
			<block>
				<p>
					<?php echo(_t('NO_PACKAGE_SELECTED')); ?>
				</p>
			</block>
<?php
			} else {
				if (!empty($pkg_assoc['Author'])) {
					$author_name = trim(preg_replace("#^(.+)<(.+)>#","$1", $pkg_assoc['Author']));
					$author_mail = trim(preg_replace("#^(.+)<(.+)>#","$2", $pkg_assoc['Author']));
?>
			<fieldset class="author">
				<div>
					<p>
						<?php echo(_t('CONTACT_AUTHOR_INTRO')); ?>
					</p>
				</div>
				<a href="mailto:<?php echo($author_mail); ?>?subject=<?php echo(urlencode("Cydia/APT(A): ".$pkg_assoc['Name']." (".$pkg_assoc['Version'].")")); ?>" target="_blank">
				<img class="icon" src="icons/default/mail_forward.png">
					<div>
						<div>
							<label><p><?php echo(_t('AUTHOR')); ?></p></label>
							<label class="detail">
								<p><?php echo($author_name); ?></p>
							</label>
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
				<div>
					<p>
						<?php echo(_t('CONTACT_SPONSOR_INTRO')); ?>
					</p>
				</div>
				<a href="<?php echo($sponsor_url); ?>" target="_blank">
				<img class="icon" src="icons/default/mail_forward.png">
					<div>
						<div>
							<label><p><?php echo(_t('SPONSOR')); ?></p></label>
							<label class="detail">
								<p><?php echo($sponsor_name); ?></p>
							</label>
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
				<div>
					<p><?php echo(_t('CONTACT_MAINTAINER_INTRO')); ?></p>
				</div>
				<a href="mailto:<?php echo($maintainer_mail); ?>?subject=<?php echo(urlencode("Cydia/APT(A): ".$pkg_assoc['Name']." (".$pkg_assoc['Version'].")")); ?>" target="_blank">
				<img class="icon" src="icons/default/mail_forward.png">
					<div>
						<div>
							<label><p><?php echo(_t('MAINTAINER')); ?></p></label>
							<label class="detail">
								<p><?php echo($maintainer_name); ?></p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
<?php
				}
			}
		}
	} elseif ($index == 7) {
		if (DCRM_ALLOW_FULLLIST == 2) {
			$section_query = mysql_query("SELECT `Name`, `Icon`, `TimeStamp` FROM `".DCRM_CON_PREFIX."Sections` WHERE `ID` = '".(int)$_GET['pid']."'");
			if (!$section_query) {
?>
			<block>
				<p>
					<?php echo(_t('MYSQL_ERROR')); ?>
				</p>
			</block>
<?php
			} else {
				$section_assoc = mysql_fetch_assoc($section_query);
				if (!$section_assoc) {
?>
			<block>
				<p>
					<?php echo(_t('NO_SECTION_SELECTED')); ?>
				</p>
			</block>
<?php
				} else {
?>
			<label><?php echo($section_assoc['Name']); ?></label>
<?php
					$package_query = mysql_query("SELECT `ID`, `Name`, `Package` FROM `".DCRM_CON_PREFIX."Packages` WHERE (`Stat` = '1' AND `Section` = '".mysql_real_escape_string($section_assoc['Name'])."') ORDER BY `ID` DESC");
					$s_num = mysql_affected_rows();
?>
			<block>
					<p><?php echo(_t('TOTAL_SECTION_PACKAGES', $s_num)); ?></p>
					<p><?php echo(_t('CREATE_TIME', $section_assoc['TimeStamp'])); ?></strong></p>
			</block>
			<fieldset>
<?php
					while ($package_assoc = mysql_fetch_assoc($package_query)) {
						if ($isCydia) {
?>
				<a href="cydia://package/<?php echo($package_assoc['Package']); ?>" target="_blank">
<?php
						} else {
?>
				<a href="index.php?pid=<?php echo($package_assoc['ID']); ?>">
<?php
						}
						if (!empty($section_assoc['Icon'])) {
?>
					<img class="icon" src="icons/<?php echo($section_assoc['Icon']); ?>">
<?php
						} else {
?>
					<img class="icon" src="icons/default/unknown.png">
<?php
						}
?>
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
		} else {
?>
			<label><?php echo(_t('FUNCTION_DISABLED', _t("PACKAGE_CATEGORY"))); ?></label>
<?php
		}
	}
?>
		</panel>
<?php
	if ($index == 2) {
?>
		<script src="js/scroll.js" type="text/javascript"></script>
<?php
	}
?>
		<script src="js/main.js" type="text/javascript"></script>
<?php
	if (defined("AUTOFILL_STATISTICS")) {
?>
		<div style="text-align: center; display: none;">
			<?php echo(AUTOFILL_STATISTICS); ?>
		</div>
<?php
	}
?>
	</body>
</html>
