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
	
	//安全：禁用错误报告
	error_reporting(0);
	//效率：开启页面缓存
	ob_start();
	//安全：引用来源定义
	define('DCRM',true);
	//载入配置
	require_once('manage/include/config.inc.php');
	require_once('manage/include/connect.inc.php');
	require_once('manage/include/autofill.inc.php');
	//载入功能
	require_once('manage/include/func.php');
	require_once('manage/include/Mobile_Detect.php');
	//传输类型与编码
	header('Content-Type: text/html; charset=UTF-8');
	//设置时区
	date_default_timezone_set('Asia/Shanghai');
	
	//检测浏览环境
	$detect = new Mobile_Detect;
	if(!$detect->isiOS()){
		//开关：跳转到PC版页面
		if (DCRM_PCINDEX == 2) {
			header('Location: misc.php');
			exit();
		} else {
			$isCydia = false;
		}
	} else {
		//开关：是否启用移动版页面
		if (DCRM_MOBILE == 2) {
			//是否为 Cydia
			if (!strpos($detect->getUserAgent(), 'Cydia')) {
				$isCydia = false;
			} else {
				$isCydia = true;
			}
		} else {
			exit();	
		}
	}
	
	//连接数据库
	$con = mysql_connect(DCRM_CON_SERVER, DCRM_CON_USERNAME, DCRM_CON_PASSWORD);
	if (!$con) {
		httpinfo(500);
		exit();
	}
	//设置数据库传输编码
	mysql_query('SET NAMES utf8');
	$select = mysql_select_db(DCRM_CON_DATABASE);
	if (!$select) {
		httpinfo(500);
		exit();
	}
	
	//从 Release 文件读取源信息
	if (file_exists('Release')) {
		$release = file('Release');
		$release_origin = $_e['NONAME'];
		//获取最后修改时间并格式化
		$release_mtime = filemtime('Release');
		$release_time = date('Y-m-d H:i:s',$release_mtime);
		//读入源名称与描述
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
	
	//根据参数跳转到指定索引
	if (isset($_GET['pid'])) {
		//安全：限制为整数参数
		if (ctype_digit($_GET['pid']) && intval($_GET['pid']) <= 10000) {
			if (isset($_GET['method']) && $_GET['method'] == 'screenshot') {
				$index = 2;
				$title = $_e['VIEW_SCREENSHOTS'];
			} elseif (isset($_GET['method']) && $_GET['method'] == 'report') {
				//尝试设备类型
				$device_type = array('iPhone','iPod','iPad');
				for ($i = 0; $i < count($device_type); $i++) {
					//获取设备版本
					$check = $detect->version($device_type[$i]);
					if ($check !== false) {
						//获取机型
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
	
	//载入网页头部信息
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<!-- 首页标题 -->
		<title><?php echo($title); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<!-- 移动版页面属性 -->
		<meta name="apple-mobile-web-app-title" content="<?php echo($release_origin); ?>" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
		<meta name="viewport" content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" />
		<meta name="HandheldFriendly" content="true" />
		<meta name="format-detection" content="telephone=no" />
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
		<!-- 相关文件引用 -->
		<link rel="apple-touch-icon" href="CydiaIcon.png">
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="stylesheet" href="css/menes.min.css">
		<link rel="stylesheet" href="css/scroll.min.css">
		<script src="js/fastclick.js" type="text/javascript"></script>
		<script src="js/menes.js" type="text/javascript"></script>
		<script src="js/cytyle.js" type="text/javascript"></script>
	</head>
	<!-- 头部信息结束 -->
	<body class="pinstripe">
		<panel>
<?php
	if ($index == 0) {
?>
		<!-- 移动版首页 -->
<?php
		if (!$isCydia) {
?>
			<!-- 跳转到 Cydia 的顶部按钮 -->
			<fieldset>
				<a href="cydia://sources/add" target="_blank">
				<img class="icon" src="icons/default/cydia.png" />
					<div>
						<div>
							<label>
								<p>
									<?php echo(_e('ADD_IN_CYDIA')); ?>
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
			<!-- 软件源简介 -->
			<fieldset>
				<div>
					<div style="float: right; vertical-align: middle; text-align: center; width: 200px">
						<span style="font-size: 24px">
							<?php echo($release_origin); ?>
						</span>
						<br/>
						<span style="font-size: 16px">
							<a class="panel" href="<?php echo AUTOFILL_SITE; ?>"><?php echo AUTOFILL_FULLNAME; ?></a>
							<br />
							<a class="panel" href="mailto:<?php echo AUTOFILL_EMAIL; ?>"><?php echo AUTOFILL_EMAIL; ?></a>
						</span>
					</div>
					<img class="icon" src="CydiaIcon.png" style="vertical-align: top;" width="64" height="64" />
					<hr />
					<p>
						<?php echo(_e('USE_CYDIA_TO_ADD_URL')); ?>
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
					<?php echo(_e('TOTAL_PACKAGES', $num)); ?> 
				</p>
				<p>
					<?php echo(_e('LAST_UPDATE_TIME', $release_time)); ?> 
				</p>
			</block>
			<!-- 相关链接 -->
			<fieldset>
<?php
				if (defined("AUTOFILL_SITE")) {
?>
				<a href="<?php echo AUTOFILL_SITE; ?>" target="_blank">
				<img class="icon" src="CydiaIcon.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('VISIT_HOME_PAGE')); ?></p>
							</label>
						</div>
					</div>
				</a><?php
				}
				if (defined("AUTOFILL_EMAIL")) {
?>
				<a href="mailto:<?php echo AUTOFILL_EMAIL; ?>?subject=<?php echo $release_origin; ?>" target="_blank">
				<img class="icon" src="icons/default/mail_forward.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('CONTACT_US')); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_TENCENT") && defined("AUTOFILL_TENCENT_NAME")) {
?>
				<a href="<?php echo AUTOFILL_TENCENT; ?>" target="_blank">
				<img class="icon" src="icons/default/qq.png" />
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
				<img class="icon" src="icons/default/weibo.png" />
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
				<img class="icon" src="icons/default/twitter.png" />
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
				<img class="icon" src="icons/default/facebook.png" />
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
				<img class="icon" src="icons/default/paypal.png" />
					<div>
						<div>
							<label>
								<p>
									<?php echo(_e('DONATE_VIA_PAYPAL')); ?>
								</p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
?>
			</fieldset>
			<!-- 首页展示部分 -->
<?php
		if (DCRM_SHOWLIST == 2) {
			$section_query = mysql_query("SELECT `ID`, `Name`, `Icon` FROM `".DCRM_CON_PREFIX."Sections`");
			if (!$section_query) {
?>
			<block>
				<p>
					<?php echo(_e('MYSQL_ERROR')); ?>
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
				<a href="cydia://package/<?php echo $package_assoc['Package']; ?>" target="_blank">
<?php
						} else {
?>
				<a href="index.php?pid=<?php echo($package_assoc['ID']); ?>">
<?php
						}
						if (!empty($section_assoc['Icon'])) {
?>
					<img class="icon" src="icons/<?php echo($section_assoc['Icon']); ?>" width="58" height="58" />
<?php
						} else {
?>
					<img class="icon" src="icons/default/unknown.png" width="58" height="58" />
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
					<img class="icon" src="icons/default/moreinfo.png" width="58" height="58" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('MORE')); ?></p>
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
					<?php echo(_e('MYSQL_ERROR')); ?>
				</p>
			</block>
<?php
				} else {
?>
			<!-- 软件包分类列表 -->
			<label><?php echo(_e('PACKAGE_CATEGORY')); ?></label>
			<fieldset>
<?php
					while ($section_assoc = mysql_fetch_assoc($section_query)) {
?>
				<a href="index.php?pid=<?php echo($section_assoc['ID']); ?>&method=section">
<?php
						if (!empty($section_assoc['Icon'])) {
?>
					<img class="icon" src="icons/<?php echo($section_assoc['Icon']); ?>" width="58" height="58" />
<?php
						} else {
?>
					<img class="icon" src="icons/default/unknown.png" width="58" height="58" />
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
			<!-- 底部导航 -->
			<label class="source">
				<p><?php echo(_e('SOURCE_INFO')); ?></p>
			</label>
			<fieldset class="source">
				<a href="/">
					<img class="icon" src="CydiaIcon.png" />
					<div>
						<div>
							<label>
								<p id="source-name">
									<?php echo $release_origin; ?>
								</p>
							</label>
						</div>
					</div>
				</a>
				<div class="source-description" id="source-description">
					<p><?php echo $release_description; ?></p>
				</div>
			</fieldset>
			<!-- 页脚及版权信息 -->
			<footer id="footer" style="display: none;">
				<p>
					<span id="id"><?php echo(_e('INDEX')); ?></span>
					<br />
					<span class="source-name"><?php echo $release_origin; ?></span>·
					<span id="section"><?php echo(_e('COPYRIGHT', date("Y"))); ?></span>
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
					<?php echo(_e('MYSQL_ERROR')); ?>
				</p>
			</block>
<?php
		} else {
			$pkg_assoc = mysql_fetch_assoc($pkg_query);
			if (!$pkg_assoc) {
?>
			<block>
				<p>
					<?php echo(_e('NO_PACKAGE_SELECTED')); ?>
				</p>
			</block>
<?php
			} else {
				if (!$isCydia) {
?>
			<!-- 跳转到 Cydia 软件包的顶部按钮 -->
			<fieldset id="cydialink" style="display: none;">
				<a href="cydia://package/<?php echo $pkg_assoc['Package']; ?>" target="_blank">
				<img class="icon" src="icons/default/cydia.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('VIEW_IN_CYDIA')); ?></p>
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
			<!-- Safari 软件包头部信息展示 -->
			<div id="header" style="display: none;">
<?php
						if (!empty($section_assoc['Icon'])) {
?>
				<img class="icon" src="icons/<?php echo($section_assoc['Icon']); ?>" style="vertical-align: top;" width="64" height="64" />
<?php
						} else {
?>
				<img class="icon" src="icons/default/unknown.png" style="vertical-align: top;" width="64" height="64" />
<?php
						}
?>
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
			<!-- Safari 软件包作者联系方式按钮 -->
			<fieldset id="contact" style="display: none;">
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=contact">
					<img class="icon" src="icons/default/mail_forward.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('AUTHOR')); ?></p>
							</label>
							<label class="detail">
									<p id="contact">
										<?php echo $author_name; ?>
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
				<a href="debs/<?php echo $_GET['pid']; ?>.deb" id="downloadlink" style="display: none;" target="_blank">
				<img class="icon" src="icons/default/packages.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('DOWNLOAD')); ?></p>
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
			<!-- 相关链接 -->
			<fieldset>
<?php
				if (DCRM_SCREENSHOTS == 2) {
?>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=screenshot">
				<img class="icon" src="icons/default/screenshots.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('VIEW_SCREENSHOTS')); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
?>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=history" id="historylink">
				<img class="icon" src="icons/default/changelog.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('VERSION_HISTORY')); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				if ($isCydia && DCRM_REPORTING == 2) {
?>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=report" id="reportlink">
				<img class="icon" src="icons/default/report.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('REPORT_PROBLEMS')); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("AUTOFILL_TENCENT") && defined("AUTOFILL_TENCENT_NAME")) {
?>
				<a href="<?php echo AUTOFILL_TENCENT; ?>" target="_blank">
				<img class="icon" src="icons/default/qq.png" />
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
				<img class="icon" src="icons/default/weibo.png" />
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
				<img class="icon" src="icons/default/twitter.png" />
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
				<img class="icon" src="icons/default/facebook.png" />
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
				<img class="icon" src="icons/default/paypal.png" />
					<div>
						<div>
							<label>
								<p>
									<?php echo(_e('DONATE_VIA_PAYPAL')); ?>
								</p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (!empty($pkg_assoc['Homepage']) && DCRM_MOREINFO == 2) {
?>
				<a href="<?php echo $pkg_assoc['Homepage']; ?>" target="_blank">
				<img class="icon" src="icons/default/moreinfo.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('MORE_INFO')); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (defined("EMERGENCY")) {
?>
				<!-- 紧急通知模块 -->
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
			<!-- 可关闭广告模块 -->
			<block id="advertisement">
				<div style="position: relative;">
					<div style="position: absolute; right: 10px; top: 2px;">
						<img src="css/closebox@2x.png" style="width: 30px; height: 29px;" onclick="hide()" />
					</div>
					<div>
						<?php echo AUTOFILL_ADVERTISEMENT; ?>
					</div>
				</div>
			</block>
<?php	
				}
?>
			<!-- 软件包摘要信息 -->
			<block>
<?php
					if (DCRM_MULTIINFO == 2) {
?>
					<p><?php echo(_e('VERSION')); ?> <strong><?php echo($pkg_assoc['Version']); ?></strong> | <?php echo(_e('DOWNLOAD_TIMES')); ?> <strong><?php echo($pkg_assoc['DownloadTimes']); ?></strong></p>
					<p><?php echo(_e('PACKAGE_UPDATE_TIME')); ?> <strong><?php echo($pkg_assoc['CreateStamp']); ?></strong></p>
					<hr />
<?php
					}
?>
					<p><?php echo nl2br($pkg_assoc['Description']); ?></p>
			</block>
<?php
				if (!empty($pkg_assoc['Multi']) && DCRM_MULTIINFO == 2) {
?>
			<!-- 软件包详细信息 -->
			<fieldset>
				<div>
					<?php echo $pkg_assoc['Multi']; ?>
				</div>
			</fieldset>
<?php
				}
?>
			<!-- 底部导航 -->
			<label class="source">
				<p><?php echo(_e('SOURCE_INFO')); ?></p>
			</label>
			<fieldset class="source">
				<a href="/">
					<img class="icon" src="CydiaIcon.png" />
					<div>
						<div>
							<label>
								<p id="source-name">
									<?php echo $release_origin; ?>
								</p>
							</label>
						</div>
					</div>
				</a>
				<div class="source-description" id="source-description">
					<p>
						<?php echo $release_description; ?>
					</p>
				</div>
			</fieldset>
<?php
				if (!$isCydia) {
?>
			<!-- Safari 展示页脚 -->
			<footer id="footer" style="display: none;">
				<p>
					<span id="id"><?php echo $pkg_assoc['Package']; ?></span>
					<br />
					<span class="source-name"><?php echo $release_origin; ?></span>·
					<span id="section"><?php echo $pkg_assoc['Section']; ?></span>
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
					<?php echo(_e('MYSQL_ERROR')); ?>
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
			<!--label><?php echo(_e('VIEW_SCREENSHOTS')); ?></label-->
			<!-- 截图展示部分 -->
			<div class="horizontal-scroll-wrapper" style="background: transparent; position: relative;">
				<!-- 取首张截图高斯滤镜置于底层 -->
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
			<label><?php echo(_e('NO_SCREENSHOTS')); ?></label>
<?php
				}
			}
		} else {
?>
			<label><?php echo(_e('FUNCTION_DISABLED', _e("VIEW_SCREENSHOTS"))); ?></label>
<?php
		}
	} elseif ($index == 3) {
?>
			<!-- 报告问题界面 -->
			<label><?php echo(_e('DEVICE_INFO')); ?></label>
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
						<strong><?php echo(_e('CURRENT_DEVICE_INFO')); ?></strong>
					</p>
					<hr />
					<p>
						<?php echo $DEVICE." &amp; ".$OS; ?>
					</p>
				</div>
			</fieldset>
			<!-- 请求提交与统计展示 -->
			<label><?php echo(_e('SUBMIT_YOUR_REQUEST')); ?></label>
			<fieldset>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=report&support=3">
					<img class="icon" src="icons/default/support_3.png" width="58" height="58" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('REQUEST_FOR_UPDATE')); ?></p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
			<label><?php echo(_e('COMPATIBILITY_REPORTS')); ?></label>
			<fieldset>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=report&support=1">
					<img class="icon" src="icons/default/support_1.png" width="58" height="58" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('FULLY_COMPATIBILE')); ?><?php echo $s_1; ?></p>
							</label>
						</div>
					</div>
				</a>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=report&support=0">
					<img class="icon" src="icons/default/support_0.png" width="58" height="58" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('PARTLY_COMPATIBILE')); ?><?php echo $s_0; ?></p>
							</label>
						</div>
					</div>
				</a>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=report&support=2">
					<img class="icon" src="icons/default/support_2.png" width="58" height="58" />
					<div>
						<div>
							<label>
								<p><?php echo(_e('NOT_COMPATIBILE')); ?><?php echo $s_2; ?></p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
			<!-- 报告问题计划说明 -->
			<fieldset>
				<div>
					<?php echo(_e('COMPATIBILE_INTRO')); ?>
				</div>
			</fieldset>
<?php
		} else {
?>
			<label><?php echo(_e('FUNCTION_DISABLED', _e("REPORT_PROBLEMS"))); ?></label>
<?php
		}
	} elseif ($index == 4) {
?>
			<!-- 报告问题结果 -->
<?php
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
							<?php echo(_e('THANKS_FOR_REPORTING')); ?>
						</strong>
					</p>
<?php
				} else {
?>
			<fieldset style="background-color: #ffdddd;">
				<div>
					<p>
						<strong>
							<?php echo(_e('VOTING_RESTRICTIONS')); ?>
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
							<?php echo(_e('VOTING_RETRY')); ?>
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
			<label><?php echo(_e('FUNCTION_DISABLED', _e("REPORT_PROBLEMS"))); ?></label>
<?php
		}
	} elseif ($index == 5) {
		$history_query = mysql_query("SELECT `ID`, `Version` FROM `".DCRM_CON_PREFIX."Packages` WHERE `Package` = (SELECT `Package` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".(int)$_GET['pid']."' LIMIT 1) ORDER BY `ID` DESC LIMIT 1,20");
		if (mysql_affected_rows() > 0) {
?>
			<!-- 历史版本展示 -->
			<label><?php echo(_e('VERSION_HISTORY')); ?></label>
			<fieldset>
<?php
			while ($history = mysql_fetch_assoc($history_query)) {
?>
				<a href="index.php?pid=<?php echo($history['ID']); ?>&addr=nohistory">
					<img class="icon" src="icons/default/changelog.png" width="58" height="58">
					<div>
						<div>
							<label>
								<p>
									<?php echo(_e('VERSION')); ?> <?php echo($history['Version']); ?>
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
			<label><?php echo(_e('NO_VERSION_HISTORY')); ?></label>
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
					<?php echo(_e('MYSQL_ERROR')); ?>
				</p>
			</block>
<?php
		} else {
			$pkg_assoc = mysql_fetch_assoc($pkg_query);
			if (!$pkg_assoc) {
?>
			<block>
				<p>
					<?php echo(_e('NO_PACKAGE_SELECTED')); ?>
				</p>
			</block>
<?php
			} else {
				if (!empty($pkg_assoc['Author'])) {
					$author_name = trim(preg_replace("#^(.+)<(.+)>#","$1", $pkg_assoc['Author']));
					$author_mail = trim(preg_replace("#^(.+)<(.+)>#","$2", $pkg_assoc['Author']));
?>
			<!-- Safari 软件包作者联系方式 -->
			<fieldset class="author">
				<div>
					<p>
						<?php echo(_e('CONTACT_AUTHOR_INTRO')); ?>
					</p>
				</div>
				<a href="mailto:<?php echo($author_mail); ?>?subject=<?php echo(urlencode("Cydia/APT(A): ".$pkg_assoc['Name']." (".$pkg_assoc['Version'].")")); ?>" target="_blank">
				<img class="icon" src="icons/default/mail_forward.png">
					<div>
						<div>
							<label><p><?php echo(_e('AUTHOR')); ?></p></label>
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
						<?php echo(_e('CONTACT_SPONSOR_INTRO')); ?>
					</p>
				</div>
				<a href="<?php echo($sponsor_url); ?>" target="_blank">
				<img class="icon" src="icons/default/mail_forward.png">
					<div>
						<div>
							<label><p><?php echo(_e('SPONSOR')); ?></p></label>
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
					<p><?php echo(_e('CONTACT_MAINTAINER_INTRO')); ?></p>
				</div>
				<a href="mailto:<?php echo($maintainer_mail); ?>?subject=<?php echo(urlencode("Cydia/APT(A): ".$pkg_assoc['Name']." (".$pkg_assoc['Version'].")")); ?>" target="_blank">
				<img class="icon" src="icons/default/mail_forward.png">
					<div>
						<div>
							<label><p><?php echo(_e('MAINTAINER')); ?></p></label>
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
					<?php echo(_e('MYSQL_ERROR')); ?>
				</p>
			</block>
<?php
			} else {
				$section_assoc = mysql_fetch_assoc($section_query);
				if (!$section_assoc) {
?>
			<block>
				<p>
					<?php echo(_e('NO_SECTION_SELECTED')); ?>
				</p>
			</block>
<?php
				} else {
?>
			<!-- 软件包分类展示 -->
			<label><?php echo($section_assoc['Name']); ?></label>
<?php
					$package_query = mysql_query("SELECT `ID`, `Name`, `Package` FROM `".DCRM_CON_PREFIX."Packages` WHERE (`Stat` = '1' AND `Section` = '".mysql_real_escape_string($section_assoc['Name'])."') ORDER BY `ID` DESC");
					$s_num = mysql_affected_rows();
?>
			<block>
					<p><?php echo(_e('TOTAL_SECTION_PACKAGES', $s_num)); ?></p>
					<p><?php echo(_e('CREATE_TIME', $section_assoc['TimeStamp'])); ?></strong></p>
			</block>
			<fieldset>
<?php
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
						if (!empty($section_assoc['Icon'])) {
?>
					<img class="icon" src="icons/<?php echo($section_assoc['Icon']); ?>" width="58" height="58">
<?php
						} else {
?>
					<img class="icon" src="icons/default/unknown.png" width="58" height="58">
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
			<label><?php echo(_e('FUNCTION_DISABLED', _e("PACKAGE_CATEGORY"))); ?></label>
<?php
		}
	}
?>
			<!-- 页面风格修正 -->
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
			<!-- 移动版统计代码 -->
			<div style="text-align: center; display: none;"><?php echo AUTOFILL_STATISTICS; ?></div>
<?php
	}
?>
        </panel>
	</body>
</html>
