<?php
/**
 * This file is part of WEIPDCRM.
 * 
 * WEIPDCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * WEIPDCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with WEIPDCRM.  If not, see <http://www.gnu.org/licenses/>.
 */

/* DCRM Mobile Page */

require_once('system/common.inc.php');

class_loader('Mobile_Detect');
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
		exit('Access Denied');	
	}
}
if (file_exists('Release')) {
	$release = file('Release');
	$release_origin = __('No Name');
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
	$release_origin = __('Empty Page');
}
if (isset($_GET['pid'])) {
	if (ctype_digit($_GET['pid']) && intval($_GET['pid']) <= 10000) {
		if (isset($_GET['method']) && $_GET['method'] == 'screenshot') {
			$index = 2;
			$title = __('View Screenshots');
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
			$title = __('Report Problems');
		} elseif (isset($_GET['method']) && $_GET['method'] == 'history') {
			$index = 5;
			$title = __('Version History');
		} elseif (isset($_GET['method']) && $_GET['method'] == 'contact') {
			$index = 6;
			$title = __('Contact us');
		} elseif (isset($_GET['method']) && $_GET['method'] == 'section') {
			$index = 7;
			$title = __('Package Category');
		} elseif (!isset($_GET['method']) || (isset($_GET['method']) && $_GET['method'] == 'view')) {
			$index = 1;
			$title = __('View Package');
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
<?php if(is_rtl()){ ?>		<link rel="stylesheet" href="css/menes-rtl.min.css"><?php } ?>
<?php if(file_exists(ROOT.'css/font/'.($local_css = substr($locale, 0, 2)).'.css') || file_exists(ROOT.'css/font/' . ($local_css = $locale) . '.css')): ?>	<link rel="stylesheet" type="text/css" href="./css/font/<?php echo $local_css; ?>.css"><?php echo("\n"); endif; ?>
		<script src="js/fastclick.js" type="text/javascript"></script>
		<script src="js/menes.js" type="text/javascript"></script>
		<script src="js/cytyle.js" type="text/javascript"></script>
		<script src="http://cdn.bootcss.com/jquery/1.11.2/jquery.min.js"></script>
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
									<?php _e('Add in Cydia<sup><small>™</small></sup>'); ?>
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
						<?php _e('Add this URL via Cydia<sup><small>™</small></sup>: '); ?>
						<br />
						<strong><a href="<?php echo($repo_url); ?>"><?php echo($repo_url); ?></a></strong>
					</p>
				</div>
			</fieldset>
<?php
	$q_info = DB::query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages` WHERE `Stat` = '1'");
	$info = mysql_fetch_row($q_info);
	$num = (int)$info[0];
?>
			<block>
				<p>
					<?php printf( __( '<strong>%d</strong> packages in total.' ) , $num ); ?> 
				</p>
				<p>
					<?php printf( __( 'Last updated: <strong>%s</strong>' ) , $release_time ); ?> 
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
								<p><?php _e('Visit Home Page'); ?></p>
							</label>
						</div>
					</div>
				</a><?php
	}
	if (defined("AUTOFILL_EMAIL")) {
?>
				<a href="mailto:<?php echo(AUTOFILL_EMAIL); ?>?subject=<?php echo($release_origin); ?>" target="_blank">
				<img class="icon" src="icons/default/email.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Contact us'); ?></p>
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
									<?php _e('Donate via <span style="font-style: italic; font-weight: bold"><span style="color: #1a3665">Pay</span><span style="color: #32689a">Pal</span><sup><small>™</small></sup></span>'); ?>
								</p>
							</label>
						</div>
					</div>
				</a>
<?php
	}
	if (defined("AUTOFILL_ALIPAY")) {
?>
				<a href="<?php echo(AUTOFILL_ALIPAY); ?>" target="_blank">
				<img class="icon" src="icons/default/alipay.png" />
					<div>
						<div>
							<label>
								<p>
									<?php _e('Donate via <span style="font-style: italic; font-weight: bold"><img class="alipay" src="icons/default/alipay_text_en.png" /><sup><small >™</small></sup></span>'); ?>
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
		$section_query = DB::query("SELECT `ID`, `Name`, `Icon` FROM `".DCRM_CON_PREFIX."Sections`");
		if (!$section_query) {
?>
			<block>
				<p>
					<?php _e('MySQL Error!'); ?>
				</p>
			</block>
<?php
		} else {
			while ($section_assoc = mysql_fetch_assoc($section_query)) {
?>
			<label><p><?php echo($section_assoc['Name']); ?></p></label>
			<fieldset>
<?php
				$package_query = DB::query("SELECT `ID`, `Name`, `Package` FROM `".DCRM_CON_PREFIX."Packages` WHERE (`Stat` = '1' AND `Section` = '".DB::real_escape_string($section_assoc['Name'])."') ORDER BY `ID` DESC LIMIT " . DCRM_SHOW_NUM);
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
				<a href="index.php?pid=<?php echo($section_assoc['ID']); ?>&amp;method=section">
					<img class="icon" src="icons/default/moreinfo.png" />
					<div>
						<div>
							<label>
								<p><?php _e('More...'); ?></p>
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
			$section_query = DB::query("SELECT `ID`, `Name`, `Icon` FROM `".DCRM_CON_PREFIX."Sections`");
			if (!$section_query) {
?>
			<block>
				<p>
					<?php _e('MySQL Error!'); ?>
				</p>
			</block>
<?php
			} else {
?>
			<label><?php _e('Package Category'); ?></label>
			<fieldset>
<?php
				while ($section_assoc = mysql_fetch_assoc($section_query)) {
?>
				<a href="index.php?pid=<?php echo($section_assoc['ID']); ?>&amp;method=section">
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
				<p><?php _e('Source Info'); ?></p>
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
					<span id="id"><?php _e('Index'); ?></span>
					<br />
					<span class="source-name"><?php echo( defined("AUTOFILL_FOOTER_NAME") ? htmlspecialchars(stripslashes(AUTOFILL_FOOTER_NAME)) : $release_origin ); ?></span>·
					<span id="section"><?php printf( __( 'Copyright &copy; %s' ) , defined("AUTOFILL_FOOTER_YEAR") ? htmlspecialchars(stripslashes(AUTOFILL_FOOTER_YEAR)).'-'.date("Y") : date("Y") ); ?></span>
					<?php if(defined("AUTOFILL_FOOTER_CODE")){ ?>
					<br />
					<span id="code"><?php echo(stripslashes(AUTOFILL_FOOTER_CODE));?></span>
					<?php } ?>
				</p>
			</footer>
<?php
	}
} elseif ($index == 1) {
		$pkg = (int)DB::real_escape_string($_GET['pid']);
		$pkg_assoc = DB::fetch_first("SELECT `Name`, `Version`, `Author`, `Package`, `Description`, `DownloadTimes`, `Multi`, `CreateStamp`, `Size`, `Installed-Size`, `Section`, `Homepage`, `Tag`, `Level`, `Price`, `Purchase_Link`  FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".$pkg."' LIMIT 1");
	if (!$pkg_assoc) {
?>
			<block>
				<p>
					<?php _e('Invalid package!<br />Perhaps this package has been deleted by us.'); ?>
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
								<p><?php _e('View in Cydia<sup><small>™</small></sup>'); ?></p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
<?php
			if (!empty($pkg_assoc['Section'])) {
				$section_icon = DB::result_first("SELECT `Icon` FROM `".DCRM_CON_PREFIX."Sections` WHERE `Name` = '".$pkg_assoc['Section']."' LIMIT 1");
			}
?>
			<div id="header" style="display: none;">
<?php
			if (!empty($section_icon)) {
?>
				<img class="icon" src="icons/<?php echo($section_icon); ?>" style="width: 64px; height: 64px; vertical-align: top;" />
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
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&amp;method=contact">
					<img class="icon" src="icons/default/email.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Author'); ?></p>
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
		if (DCRM_DIRECT_DOWN == 1 && !$isCydia && !check_commercial_tag($pkg_assoc['Tag'])) {
?>
			<fieldset>
				<a href="debs/<?php echo($_GET['pid']); ?>.deb" id="downloadlink" style="display: none;" target="_blank">
				<img class="icon" src="icons/default/packages.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Download'); ?></p>
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
		$package_info = $pkg_assoc;
		//require_once('commercial.php');
?>
			<fieldset>
<?php
		if (DCRM_SCREENSHOTS == 2) {
?>
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&amp;method=screenshot">
				<img class="icon" src="icons/default/screenshots.png" />
					<div>
						<div>
							<label>
								<p><?php _e('View Screenshots'); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
		}
?>
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&amp;method=history" id="historylink">
				<img class="icon" src="icons/default/changelog.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Version History'); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
		if ($isCydia && DCRM_REPORTING == 2) {
?>
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&amp;method=report" id="reportlink">
				<img class="icon" src="icons/default/report.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Report Problems'); ?></p>
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
									<?php _e('Donate via <span style="font-style: italic; font-weight: bold"><span style="color: #1a3665">Pay</span><span style="color: #32689a">Pal</span><sup><small>™</small></sup></span>'); ?>
								</p>
							</label>
						</div>
					</div>
				</a>
<?php
		}
		if (defined("AUTOFILL_ALIPAY")) {
?>
				<a href="<?php echo(AUTOFILL_ALIPAY); ?>" target="_blank">
				<img class="icon" src="icons/default/alipay.png" />
					<div>
						<div>
							<label>
								<p>
									<?php _e('Donate via <span style="font-style: italic; font-weight: bold"><img class="alipay" src="icons/default/alipay_text_en.png" /><sup><small >™</small></sup></span>'); ?>
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
				<img class="icon" src="icons/default/web.png" />
					<div>
						<div>
							<label>
								<p><?php _e('More Info'); ?></p>
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
				<div style="position: relative; text-align: center;">
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
		if (DCRM_MOREINFO == 2) {
?>
					<p><?php _e('Version'); ?> <strong><?php echo($pkg_assoc['Version']); ?></strong> | <?php _e('Downloads'); ?> <strong><?php echo($pkg_assoc['DownloadTimes']); ?></strong></p>
					<p><?php _e('Last Updated'); ?> <strong><?php echo($pkg_assoc['CreateStamp']); ?></strong></p>
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
					<p>
						<?php echo($pkg_assoc['Multi']); ?>
					</p>
				</div>
			</fieldset>
<?php
		}
		if (defined("AUTOFILL_DUOSHUO_KEY")) {
?>
			<fieldset>
				<div id="foldcomments">
					<p style="height: 21px;"><span class="cmsswitch"><?php _e('Fold Comments'); ?></span></p>
				</div>
				<div id="comments">
					<div class="ds-thread" data-thread-key="<?php echo($pkg_assoc['Package']); ?>" data-title="<?php echo($pkg_assoc['Name']); ?>" data-url="<?php echo('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"></div>
				</div>
			</fieldset>
			<script type="text/javascript">
			var duoshuoQuery = {short_name:"<?php echo(AUTOFILL_DUOSHUO_KEY); ?>"};
			(function() {
				var ds = document.createElement('script');
				ds.type = 'text/javascript';ds.async = true;
				ds.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') + '//static.duoshuo.com/embed.js';
				ds.charset = 'UTF-8';
				(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ds);
			})();

			$(document).ready(function(){
				var comments = $("#comments");
				var cmsswitch = $(".cmsswitch");
				$("#foldcomments").click(function(){
					cmsswitch.fadeOut(400);
					if(comments.css("display")=="none"){
						comments.slideDown(800);
						setTimeout(function(){cmsswitch.html('<?php _e('Fold Comments'); ?>').fadeIn(400);}, 400);
					} else {
						comments.slideUp(800);
						setTimeout(function(){cmsswitch.html('<?php _e('Unfold Comments'); ?>').fadeIn(400);}, 400);
					}
				});
			});
			</script>
<?php
		}
?>
			<label class="source">
				<p><?php _e('Source Info'); ?></p>
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
					<?php if(defined("AUTOFILL_FOOTER_CODE")){ ?>
					<br />
					<span id="code"><?php echo(stripslashes(AUTOFILL_FOOTER_CODE));?></span>
					<?php } ?>
				</p>
			</footer>
<?php
		}
	}
} elseif ($index == 2) {
	if (DCRM_SCREENSHOTS == 2) {
		$pkg = (int)DB::real_escape_string($_GET['pid']);
		$pkg_query = DB::query("SELECT `PID`, `Image` FROM `".DCRM_CON_PREFIX."ScreenShots` WHERE `PID` = '".$pkg."'");
		if (!$pkg_query) {
?>
			<block>
				<p>
					<?php _e('MySQL Error!'); ?>
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
			<!--label><?php _e('View Screenshots'); ?></label-->
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
			<label><?php _e('No screenshots now.'); ?></label>
<?php
			}
		}
	} else {
?>
			<label><?php printf( __( 'The %s function is disabled.' ) , __('View Screenshots') ); ?></label>
<?php
	}
} elseif ($index == 3) {
?>
			<label><?php _e('Device Info'); ?></label>
<?php
	if (DCRM_REPORTING == 2) {
		$q_count = DB::query("SELECT `Support`, COUNT(*) AS 'num' FROM `".DCRM_CON_PREFIX."Reports` WHERE (`Device` = '".$DEVICE."' AND `iOS` = '".$OS."' AND `PID` = '".$_GET['pid']."') GROUP BY `Support`");
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
						<strong><?php _e('Current Device Info'); ?></strong>
					</p>
					<hr />
					<p>
						<?php echo($DEVICE." &amp; ".$OS); ?>
					</p>
				</div>
			</fieldset>
			<label><?php _e('Submit Your Request'); ?></label>
			<fieldset>
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&amp;method=report&amp;support=3">
					<img class="icon" src="icons/default/support_3.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Request for updating'); ?></p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
			<label><?php _e('Compatibility Reports'); ?></label>
			<fieldset>
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&amp;method=report&amp;support=1">
					<img class="icon" src="icons/default/support_1.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Fully Compatibile'); ?><?php echo($s_1); ?></p>
							</label>
						</div>
					</div>
				</a>
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&amp;method=report&amp;support=0">
					<img class="icon" src="icons/default/support_0.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Partly Compatibile'); ?><?php echo($s_0); ?></p>
							</label>
						</div>
					</div>
				</a>
				<a href="index.php?pid=<?php echo($_GET['pid']); ?>&amp;method=report&amp;support=2">
					<img class="icon" src="icons/default/support_2.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Not Compatibile'); ?><?php echo($s_2); ?></p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
			<fieldset>
				<div>
					<?php _e('<p><strong>The software package compatibility report is voted by the majority of users, and we generated these statistical data, for reference only.</strong></p><hr /><p>If you have compatibility issues after installation, your vote, may be able to help tens of thousands of users from safe mode and many other threats.</p>'); ?>
				</div>
			</fieldset>
<?php
	} else {
?>
			<label><?php printf( __( 'The %s function is disabled.' ) , __('Report Problems') ); ?></label>
<?php
	}
} elseif ($index == 4) {
	if (DCRM_REPORTING == 2) {
		$result = DB::query("SELECT `ID` FROM `".DCRM_CON_PREFIX."Reports` WHERE (`Remote` = '".DB::real_escape_string($_SERVER['REMOTE_ADDR'])."' AND `PID`='".$_GET['pid']."') LIMIT 3");
		if (mysql_affected_rows() < DCRM_REPORT_LIMIT) {
			if (!empty($_SERVER['REMOTE_ADDR']) && !empty($DEVICE) && !empty($OS) && $isCydia) {
				$result = DB::query("INSERT INTO `".DCRM_CON_PREFIX."Reports`(`Remote`, `Device`, `iOS`, `Support`, `TimeStamp`, `PID`) VALUES('".DB::real_escape_string($_SERVER['REMOTE_ADDR'])."', '".$DEVICE."', '".$OS."', '".$support."', '".date('Y-m-d H:i:s')."', '".(int)$_GET['pid']."')");
?>
			<fieldset style="background-color: #ccffcc;">
				<div>
					<p>
						<strong>
							<?php _e('Your report has been submitted.<br />Thanks for your support!'); ?>
						</strong>
					</p>
<?php
			} else {
?>
			<fieldset style="background-color: #ffdddd;">
				<div>
					<p>
						<strong>
							<?php _e('Please use Cydia to vote.<br />Each device is limited to vote for 2 times!'); ?>
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
							<?php _e('You have reached the voting limit.'); ?>
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
			<label><?php printf( __( 'The %s function is disabled.' ) , __('Report Problems') ); ?></label>
<?php
	}
} elseif ($index == 5) {
	$history_query = DB::query("SELECT `ID`, `Version` FROM `".DCRM_CON_PREFIX."Packages` WHERE `Package` = (SELECT `Package` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".(int)$_GET['pid']."' LIMIT 1) ORDER BY `ID` DESC LIMIT 1,20");
	if (mysql_affected_rows() > 0) {
?>
			<label><?php _e('Version History'); ?></label>
			<fieldset>
<?php
		while ($history = mysql_fetch_assoc($history_query)) {
?>
				<a href="index.php?pid=<?php echo($history['ID']); ?>&amp;addr=nohistory">
					<img class="icon" src="icons/default/changelog.png">
					<div>
						<div>
							<label>
								<p>
									<?php _e('Version'); ?> <?php echo($history['Version']); ?>
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
			<label><?php _e('No version history now.'); ?></label>
			<br />
<?php
	}
} elseif ($index == 6) {
		$pkg = (int)DB::real_escape_string($_GET['pid']);
		$pkg_query = DB::query("SELECT `Name`, `Version`, `Author`, `Sponsor`, `Maintainer` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".$pkg."' LIMIT 1");
	if (!$pkg_query) {
?>
			<block>
				<p>
					<?php _e('MySQL Error!'); ?>
				</p>
			</block>
<?php
	} else {
		$pkg_assoc = mysql_fetch_assoc($pkg_query);
		if (!$pkg_assoc) {
?>
			<block>
				<p>
					<?php _e('Invalid package!<br />Perhaps this package has been deleted by us.'); ?>
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
						<?php _e('Source manager can <strong>NOT</strong> solve the function problem of package: you <strong>MUST</strong> contact the author.'); ?>
					</p>
				</div>
				<a href="mailto:<?php echo($author_mail); ?>?subject=<?php echo(urlencode("Cydia/APT(A): ".$pkg_assoc['Name']." (".$pkg_assoc['Version'].")")); ?>" target="_blank">
				<img class="icon" src="icons/default/email.png">
					<div>
						<div>
							<label><p><?php _e('Author'); ?></p></label>
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
						<?php _e('If the package is a commercial package, you can contact the sponsor to obtain commercial support.'); ?>
					</p>
				</div>
				<a href="<?php echo($sponsor_url); ?>" target="_blank">
				<img class="icon" src="icons/default/email.png">
					<div>
						<div>
							<label><p><?php _e('Sponsor'); ?></p></label>
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
					<p><?php _e('If you have questions when install or uninstall this package, please contact the maintainer.'); ?></p>
				</div>
				<a href="mailto:<?php echo($maintainer_mail); ?>?subject=<?php echo(urlencode("Cydia/APT(A): ".$pkg_assoc['Name']." (".$pkg_assoc['Version'].")")); ?>" target="_blank">
				<img class="icon" src="icons/default/email.png">
					<div>
						<div>
							<label><p><?php _e('Maintainer'); ?></p></label>
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
		$section_query = DB::query("SELECT `Name`, `Icon`, `TimeStamp` FROM `".DCRM_CON_PREFIX."Sections` WHERE `ID` = '".(int)$_GET['pid']."'");
		if (!$section_query) {
?>
			<block>
				<p>
					<?php _e('MySQL Error!'); ?>
				</p>
			</block>
<?php
		} else {
			$section_assoc = mysql_fetch_assoc($section_query);
			if (!$section_assoc) {
?>
			<block>
				<p>
					<?php _e('Invalid section!<br />Perhaps this section has been deleted by us.'); ?>
				</p>
			</block>
<?php
			} else {
?>
			<label><p><?php echo($section_assoc['Name']); ?></p></label>
<?php
				$package_query = DB::query("SELECT `ID`, `Name`, `Package` FROM `".DCRM_CON_PREFIX."Packages` WHERE (`Stat` = '1' AND `Section` = '".DB::real_escape_string($section_assoc['Name'])."') ORDER BY `ID` DESC");
				$s_num = mysql_affected_rows();
?>
			<block>
					<p><?php printf( __( '<strong>%s</strong> packages in this section.' ) , $s_num ); ?></p>
					<p><?php printf( __( 'Create time: <strong>%s</strong>' ) , $section_assoc['TimeStamp'] ); ?></strong></p>
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
			<label><?php printf( __( 'The %s function is disabled.' ) , __('Package Category') ); ?></label>
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
