<?php
/**
 * DCRM Mobile Page
 *
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

require_once('system/common.inc.php');
base_url();

// URL For Rewrite 
$rewrite_mod = get_option('rewrite_mod');
switch($rewrite_mod){
	case 3:
		$rewrite_url = array('view' => 'view/%d', 'view_nohistory' => 'view/%d/nohistory', 'screenshot' => 'screenshot/%d', 'history' => 'history/%d', 'contact' => 'contact/%d', 'section' => 'section/%d', 'report' => 'report/%d', 'report_support' => 'report/%1$d/%2$d', 'more' => 'more/%d', 'more_offset' => 'more/%1$d/%2$d', 'misc' => 'misc');
		break;
	case 1:
	case 2:
	default:
		$rewrite_url = array('view' => 'index.php?method=view&amp;pid=%d', 'view_nohistory' => 'index.php?pid=%d&amp;addr=nohistory', 'screenshot' => 'index.php?method=screenshot&amp;pid=%d', 'history' => 'index.php?method=history&amp;pid=%d', 'contact' => 'index.php?method=contact&amp;pid=%d', 'section' => 'index.php?method=section&amp;pid=%d', 'report' => 'index.php?method=report&amp;pid=%d', 'report_support' => 'index.php?method=report&amp;pid=%1$d&amp;support=%2$d', 'more' => 'index.php?method=more&amp;pid=%d', 'more_offset' => 'index.php?method=more&amp;pid=%1$d&amp;offset=%2$d', 'misc' => 'misc.php');
		break;
}
function echo_rewrite_url($type, $variable1, $variable2=''){
	global $rewrite_url;
	echo SITE_URL;
	printf($rewrite_url[$type], $variable1, $variable2);
}

class_loader('Mobile_Detect');
$detect = new Mobile_Detect;
if(!$detect->isiOS()){
	if (DCRM_PCINDEX == 2) {
		header("Location: ".SITE_URL.$rewrite_url['misc']);
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
		function device_check(){
			global $detect;
			$device_type = array('iPhone', 'iPod', 'iPad');
			for ($i = 0; $i < count($device_type); $i++) {
				$check = $detect->version($device_type[$i]);
				if ($check !== false) {
					if (isset($_SERVER['HTTP_X_MACHINE'])) {
						$DEVICE = $_SERVER['HTTP_X_MACHINE'];
					} else {
						$DEVICE = 'Unknown';
					}
					$OS = str_replace('_', '.', $check);
					break;
				}
			}
			return array('DEVICE' => $DEVICE, 'OS' => $OS);
		}

		if (isset($_GET['method']) && $_GET['method'] == 'screenshot') {
			$index = 2;
			$title = __('View Screenshots');
		} elseif (isset($_GET['method']) && $_GET['method'] == 'report') {
			$device_info = device_check();
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
		} elseif (isset($_GET['method']) && $_GET['method'] == 'more') {
			$index = 8;
			$section = DB::fetch_first("SELECT `Name`, `Icon` FROM `".DCRM_CON_PREFIX."Sections` WHERE `ID` = '".(int)$_GET['pid']."'");
			$q_name = DB::real_escape_string($section['Name']);
			if (isset($_GET['offset']) && !empty($_GET['offset']) && ctype_digit($_GET['offset'])) {
				$offset = intval($_GET['offset']);
			} else {
				$offset = 0;
			}
			$packages = DB::fetch_all("SELECT `ID`, `Name`, `Package` FROM `".DCRM_CON_PREFIX."Packages` WHERE (`Stat` = '1' AND `Section` = '".$q_name."') ORDER BY `ID` DESC LIMIT 10 OFFSET ".$offset);
			foreach($packages as $package){
				if(!empty($package)){
					if ($isCydia) { ?>
				<a href="cydia://package/<?php echo($package['Package']); ?>" target="_blank">
<?php					} else { ?>
				<a href="<?php echo_rewrite_url('view', $package['ID']);?>">
<?php					} ?>
					<img class="icon" src="<?php echo(SITE_URL); ?>icon/<?php echo(empty($section['Icon']) ? 'default/unknown.png' : $section['Icon']); ?>">
					<div>
						<div>
							<label>
								<p><?php echo($package['Name']); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
			}
			exit();
		} elseif (!isset($_GET['method']) || (isset($_GET['method']) && $_GET['method'] == 'view')) {
			$index = 1;
			$title = __('View Package');
			$package_id = (int)DB::real_escape_string($_GET['pid']);
			$package_info = DB::fetch_first("SELECT `Name`, `Version`, `Author`, `Package`, `Description`, `DownloadTimes`, `Multi`, `CreateStamp`, `Size`, `Installed-Size`, `Section`, `Homepage`, `Tag`, `Level`, `Price`, `Purchase_Link`, `Changelog`, `Changelog_Older_Shows`, `Video_Preview`, `System_Support`, `ScreenShots` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".$package_id."' LIMIT 1");
			if ($package_info) $title = $title.' - '.$package_info['Name'];
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
		<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
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
		<link rel="apple-touch-icon" href="<?php echo(SITE_URL); ?>CydiaIcon.png">
		<link rel="shortcut icon" href="<?php echo(SITE_URL); ?>favicon.ico">
		<link rel="stylesheet" href="<?php echo(SITE_URL); ?>css/menes.min.css">
		<link rel="stylesheet" href="<?php echo(SITE_URL); ?>css/scroll.min.css">
<?php if(is_rtl()){ ?>		<link rel="stylesheet" href="<?php echo(SITE_URL); ?>css/menes-rtl.min.css"><?php } ?>
<?php if(file_exists(ROOT.'css/font/'.($local_css = substr($locale, 0, 2)).'.css') || file_exists(ROOT.'css/font/' . ($local_css = $locale) . '.css')): ?>	<link rel="stylesheet" type="text/css" href="<?php echo(SITE_URL); ?>css/font/<?php echo $local_css; ?>.css"><?php echo("\n"); endif; ?>
		<script src="<?php echo(SITE_URL); ?>js/fastclick.js" type="text/javascript"></script>
		<script src="<?php echo(SITE_URL); ?>js/menes.js" type="text/javascript"></script>
		<script src="<?php echo(SITE_URL); ?>js/cytyle.js" type="text/javascript"></script>
		<script src="//cdn.bootcss.com/jquery/1.11.2/jquery.min.js"></script>
	</head>
	<body class="pinstripe">
		<panel>
<?php
$repo_url = base64_decode(DCRM_REPOURL);
if ($index == 0) {
	if (!$isCydia) {
?>
			<fieldset>
				<a href="cydia://url/https://cydia.saurik.com/api/share#?source=<?php echo($repo_url); ?>" target="_blank">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/cydia.png" />
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
?>
			<fieldset>
				<div>
					<div style="float: right; vertical-align: middle; text-align: center; width: 200px">
						<span style="font-size: 24px">
							<?php echo($release_origin); ?>
						</span>
						<br/>
						<span style="font-size: 16px">
							<a class="panel" href="<?php echo(AUTOFILL_SITE); ?>"><?php echo(defined(AUTOFILL_FULLNAME) ? AUTOFILL_FULLNAME : AUTOFILL_MASTER); ?></a>
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
	$num = DB::result_first("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages` WHERE `Stat` = '1'");
?>
			<block>
				<p>
					<?php printf( __( '<strong>%d</strong> packages in total.' ) , $num ); ?> 
				</p>
				<?php if(isset($release_time)): ?>
				<p>
					<?php printf( __( 'Last updated: <strong>%s</strong>' ) , $release_time ); ?> 
				</p>
				<?php endif; ?>
			</block>
			<fieldset>
<?php
	if (defined("AUTOFILL_SITE")) {
?>
				<a href="<?php echo(AUTOFILL_SITE); ?>" target="_blank">
				<img class="icon" src="<?php echo(SITE_URL); ?>CydiaIcon.png" />
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
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/email.png" />
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
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/qq.png" />
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
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/weibo.png" />
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
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/twitter.png" />
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
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/facebook.png" />
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
				<a href="<?php echo(htmlspecialchars(AUTOFILL_PAYPAL)); ?>" target="_blank">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/paypal.png" />
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
				<a href="<?php echo(htmlspecialchars(AUTOFILL_ALIPAY)); ?>" target="_blank">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/alipay.png" />
					<div>
						<div>
							<label>
								<p>
									<?php printf(__('Donate via <span style="font-style: italic; font-weight: bold"><img class="alipay" src="%salipay_text_en.png" /><sup><small >™</small></sup></span>'), SITE_URL.'icon/default/'); ?>
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
		$sections = DB::fetch_all("SELECT `ID`, `Name`, `Icon` FROM `".DCRM_CON_PREFIX."Sections`");
		if (empty($sections)) {
?>
			<block>
				<p>
					<?php _e('No Section.'); ?>
				</p>
			</block>
<?php
		} else {
			foreach($sections as $section){
?>
		<label><p><?php echo($section['Name']); ?></p></label>
			<fieldset>
<?php
				$packages = DB::fetch_all("SELECT `ID`, `Name`, `Package` FROM `".DCRM_CON_PREFIX."Packages` WHERE (`Stat` = '1' AND `Section` = '".DB::real_escape_string($section['Name'])."') ORDER BY `ID` DESC LIMIT " . DCRM_SHOW_NUM);
				foreach($packages as $package) {
					if ($isCydia) { ?>
				<a href="cydia://package/<?php echo($package['Package']); ?>" target="_blank">
<?php				} else { ?>
				<a href="<?php echo_rewrite_url('view', $package['ID']);?>">
<?php				} ?>
					<img class="icon" src="<?php echo(SITE_URL); ?>icon/<?php echo(empty($section['Icon']) ? 'default/unknown.png' : $section['Icon']); ?>">
					<div>
						<div>
							<label>
								<p><?php echo($package['Name']); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
				}
				if (DCRM_ALLOW_FULLLIST == 2) {
?>
				<a href="<?php echo_rewrite_url('section', $section['ID']); ?>">
					<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/moreinfo.png" />
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
			$sections = DB::fetch_all("SELECT `ID`, `Name`, `Icon` FROM `".DCRM_CON_PREFIX."Sections`");
			if (empty($sections)) {
?>
			<block>
				<p>
					<?php _e('No Section.'); ?>
				</p>
			</block>
<?php
			} else {
?>
			<label><?php _e('Package Category'); ?></label>
			<fieldset>
<?php
				foreach($sections as $section){
?>
				<a href="<?php echo_rewrite_url('section', $section['ID']); ?>">
					<img class="icon" src="<?php echo(SITE_URL); ?>icon/<?php echo(empty($section['Icon']) ? 'default/unknown.png' : $section['Icon']); ?>" />
					<div>
						<div>
							<label>
								<p><?php echo($section['Name']); ?></p>
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
					<img class="icon" src="<?php echo(SITE_URL); ?>CydiaIcon.png" />
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
	if (!$package_info) {
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
				<a href="cydia://url/https://cydia.saurik.com/api/share#?source=<?php echo($repo_url); ?>&amp;package=<?php echo($package_info['Package']); ?>" target="_blank">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/cydia.png" />
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
			if (!empty($package_info['Section'])) {
				$section_icon = DB::result_first("SELECT `Icon` FROM `".DCRM_CON_PREFIX."Sections` WHERE `Name` = '".$package_info['Section']."' LIMIT 1");
			}
?>
			<div id="header" style="display: none;">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/<?php echo(empty($section_icon) ? 'default/unknown.png' : $section_icon); ?>" style="width: 64px; height: 64px; vertical-align: top;" />
				<div id="content">
					<p id="name"><?php echo($package_info['Name']); ?></p>
					<p id="latest"><?php echo($package_info['Version']); ?></p>
					<div id="extra">
						<p><?php if(!empty($package_info['Installed-Size'])){echo(sizeext($package_info['Installed-Size'] * 1024));} ?></p>
					</div>
				</div>
			</div>
<?php
			if (!empty($package_info['Author'])) {
				$author_name = trim(preg_replace("#^(.+)<(.+)>#","$1", $package_info['Author']));
				$author_mail = trim(preg_replace("#^(.+)<(.+)>#","$2", $package_info['Author']));
			}
?>
			<fieldset id="contact" style="display: none;">
				<a href="<?php echo_rewrite_url('contact', $_GET['pid']); ?>">
					<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/email.png" />
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
		if (DCRM_DIRECT_DOWN == 1 && !$isCydia && !check_commercial_tag($package_info['Tag'])) {
?>
			<fieldset>
				<a href="<?php echo(SITE_URL); ?>debs/<?php echo($_GET['pid']); ?>.deb" id="downloadlink" style="display: none;" target="_blank">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/packages.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Download'); ?></p>
							</label>
							<label class="detail">
									<p>
										<?php if(!empty($package_info['Size'])){echo(sizeext($package_info['Size']));} ?>
									</p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
<?php
		}
		require_once('commercial.php');
?>
			<fieldset>
<?php
		if (DCRM_SCREENSHOTS == 2) {
			if (!empty($package_info['ScreenShots'])){
				$screenshots_count = count(maybe_unserialize($package_info['ScreenShots']));
?>
				<a href="<?php echo_rewrite_url('screenshot', $_GET['pid']); ?>">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/screenshots.png" />
					<div>
						<div>
							<label>
								<p><?php echo(_n('View Screenshot', 'View Screenshots', $screenshots_count)); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
			}
		}
		if (!empty($package_info['Video_Preview'])) {
?>
				<a href="<?php echo(htmlspecialchars($package_info['Video_Preview'])); ?>">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/video.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Video Preview'); ?></p>
							</label>
						</div>
					</div>
				</a>
<?
		}
		$changelogs_count = DB::result_first("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages` WHERE `Package` = '".$package_info['Package']."'");
		if ($changelogs_count != 1){
?>
				<a href="<?php echo_rewrite_url('history', $_GET['pid']); ?>" id="historylink">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/changelog.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Version History'); ?></p>
							</label>
						</div>
					</div>
				</a>
<?php
		}
		if ($isCydia && DCRM_REPORTING == 2) {
?>
				<a href="<?php echo_rewrite_url('report', $_GET['pid']); ?>" id="reportlink">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/report.png" />
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
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/qq.png" />
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
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/weibo.png" />
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
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/twitter.png" />
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
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/facebook.png" />
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
				<a href="<?php echo(htmlspecialchars(AUTOFILL_PAYPAL)); ?>" target="_blank">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/paypal.png" />
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
				<a href="<?php echo(htmlspecialchars(AUTOFILL_ALIPAY)); ?>" target="_blank">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/alipay.png" />
					<div>
						<div>
							<label>
								<p>
									<?php printf(__('Donate via <span style="font-style: italic; font-weight: bold"><img class="alipay" src="%salipay_text_en.png" /><sup><small >™</small></sup></span>'), SITE_URL.'icon/default/'); ?>
								</p>
							</label>
						</div>
					</div>
				</a>
<?php
		}
		if (!empty($package_info['Homepage']) && DCRM_MOREINFO == 2) {
?>
				<a href="<?php echo($package_info['Homepage']); ?>" target="_blank">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/web.png" />
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
?>
			</fieldset>
<?php
		if (defined("AUTOFILL_ADVERTISEMENT") && $isCydia) {
?>
			<block id="advertisement">
				<div style="position: relative; text-align: center;">
					<div style="position: absolute; right: 10px; top: 2px;">
						<img src="<?php echo(SITE_URL); ?>css/closebox@2x.png" style="width: 30px; height: 29px;" onclick="hide()" />
					</div>
					<div>
						<?php echo(AUTOFILL_ADVERTISEMENT); ?>
					</div>
				</div>
			</block>
<?php
		}
		if (defined("AUTOFILL_EMERGENCY")) {
?>
			<label><p><?php _e('Notice'); ?></p></label>
			<fieldset class="emergency">
				<a>
					<div>
						<div>
							<?php echo(AUTOFILL_EMERGENCY); ?>
						</div>
					</div>
				</a>
			</fieldset>
<?php
		}
		// Compatibility Check
		if (!empty($package_info['System_Support'])){
			$system_support = unserialize($package_info['System_Support']);
			if ($isCydia){
				$device_info = device_check();

				if (version_compare($system_support['Minimum'], $device_info['OS'], '<=') && version_compare($device_info['OS'], $system_support['Maxmum'], '<=')){
					$Compatibility_Settings = array('color' => '#66B3FF', 'text' => __('Your device supports this package'));
				} else {
					$Compatibility_Settings = array('color' => '#FF4500', 'text' => __('Your device doesn\'t support this package'));
				}
?>
			<fieldset style="background-color:<?php echo($Compatibility_Settings['color']); ?>">
				<div>
					<p>
					<span style='color:white'><?php echo($Compatibility_Settings['text']); ?></span>
					</p>
				</div>
			</fieldset>
<?php
			}
		}
		if(!defined('DCRM_DESCRIPTION')) define('DCRM_DESCRIPTION', 2);
		if (DCRM_MOREINFO == 2 || DCRM_DESCRIPTION == 2 || (empty($package_info['Multi']) && DCRM_MULTIINFO == 2)) {
?>
			<block>
<?php
			if (DCRM_MOREINFO == 2) {
?>
					<p><?php _e('Version'); ?> <strong><?php echo($package_info['Version']); ?></strong> | <?php _e('Downloads'); ?> <strong><?php echo($package_info['DownloadTimes']); ?></strong></p>
					<?php if(!empty($package_info['System_Support'])): ?><p><?php _e('Compatible with: '); ?><strong>iOS <?php echo($system_support['Minimum']); if($system_support['Maxmum']): ?> ~ iOS <?php echo($system_support['Maxmum']); endif; ?></strong></p><?php endif; ?>
					<p><?php _e('Last Updated'); ?> <strong><?php echo($package_info['CreateStamp']); ?></strong></p>
<?php
			}
			if (DCRM_MOREINFO == 2 && (DCRM_DESCRIPTION == 2 || (empty($package_info['Multi']) && DCRM_MULTIINFO == 2))) echo '<hr />';
			if (DCRM_DESCRIPTION == 2 || (empty($package_info['Multi']) && DCRM_MULTIINFO == 2)) {
?>
					<p><?php echo(nl2br($package_info['Description'])); ?></p>
<?php
			}
?>
			</block>
<?php
		}
		if (!empty($package_info['Multi']) && DCRM_MULTIINFO == 2) {
?>
			<fieldset>
				<div>
					<p>
						<?php echo($package_info['Multi']); ?>
					</p>
				</div>
			</fieldset>
<?php
		}
		if($package_info['Changelog_Older_Shows'] == 0 && !empty($package_info['Changelog'])) {
?>
			<label><p><?php _e('In this version'); ?></p></label>
			<fieldset class="changelog">
				<a>
					<div>
						<div>
							<label>
								<p>
									<?php echo($package_info['Changelog']); ?>
								</p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
<?php
		} elseif($package_info['Changelog_Older_Shows'] != 0) {
			$changelogs = DB::fetch_all("SELECT `Version`, `Changelog` FROM `".DCRM_CON_PREFIX."Packages` WHERE `Package` = '".$package_info['Package']."' ORDER BY `Version` DESC LIMIT 0,".(int)$package_info['Changelog_Older_Shows']);
			if(count($changelogs) > 0){
?>			
			<label><p><?php _e('Changelogs'); ?></p></label>
			<fieldset class="changelog">
<?php
				foreach ($changelogs as $changelog) {
					if(!empty($changelog['Changelog'])){
?>
				<a>
					<div>
						<div>
							<label>
								<p>
									<strong><?php echo($changelog['Version']); ?>:</strong><br/>
									<?php echo($changelog['Changelog']); ?>
								</p>
							</label>
						</div>
					</div>
				</a>
<?php
					}
				}
?>
			</fieldset>
<?php
			}
		}
		if (defined("AUTOFILL_DUOSHUO_KEY")) {
?>
			<fieldset>
				<div id="foldcomments">
					<p style="height: 21px;"><span class="cmsswitch"><?php _e('Fold Comments'); ?></span></p>
				</div>
				<div id="comments">
					<div class="ds-thread" data-thread-key="<?php echo($package_info['Package']); ?>" data-title="<?php echo($package_info['Name']); ?>" data-url="<?php echo('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"></div>
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
					<img class="icon" src="<?php echo(SITE_URL); ?>CydiaIcon.png" />
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
					<span id="id"><?php echo($package_info['Package']); ?></span>
					<br />
					<span class="source-name"><?php echo($release_origin); ?></span>&nbsp;·&nbsp;
					<span id="section"><?php echo($package_info['Section']); ?></span>
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
		$package_id = (int)DB::real_escape_string($_GET['pid']);
		$screenshots_query = DB::result_first("SELECT `ScreenShots` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".$package_id."'");
		$screenshots = maybe_unserialize($screenshots_query);
		if (!empty($screenshots)) {
			$screenshots_count = count($screenshots);
?>
			<!--label><?php _e('View Screenshots'); ?></label-->
			<div class="screenshot-wrapper" style="background: transparent; position: relative;">
				<div class="background blur" style="background-image: url(<?php echo($screenshots[0]); ?>);"></div>
				<div class="horizontal-scroll-wrapper" id="scroller" style="background: transparent; position: absolute; z-index: 2;">
					<div class="horizontal-scroll-area" style="width:<?php echo($screenshots_count * 240); ?>px;">
<?php
			foreach ($screenshots as $screenshot) {
?>
						<img src="<?php echo($screenshot); ?>" />
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
	} else {
?>
			<label><?php printf( __( 'The %s function is disabled.' ) , __('View Screenshots') ); ?></label>
<?php
	}
} elseif ($index == 3) {
?>
			<label><?php _e('Current Device Info'); ?></label>
<?php
	if (DCRM_REPORTING == 2) {
		$reports = DB::fetch_all("SELECT `Support`, COUNT(*) AS 'num' FROM `".DCRM_CON_PREFIX."Reports` WHERE (`Device` = '".$device_info['DEVICE']."' AND `iOS` = '".$device_info['OS']."' AND `PID` = '".$_GET['pid']."') GROUP BY `Support`");
		$reports_num = array(0, 0, 0);
		if (count($reports) > 0) {
			foreach($reports as $report)
				$reports_num[$report['Support']] = $report['num'];
		}
		$check_int = $reports_num[1] * 3 + $reports_num[2] - $reports_num[0] * 2;
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
						<?php echo($device_info['DEVICE']." &amp; iOS ".$device_info['OS']."<br />IP: ".$_SERVER['REMOTE_ADDR']); ?>
					</p>
				</div>
			</fieldset>
			<label><?php _e('Submit Your Request'); ?></label>
			<fieldset>
				<a href="<?php echo_rewrite_url('report_support', $_GET['pid'], 3); ?>">
					<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/support_3.png" />
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
				<a href="<?php echo_rewrite_url('report_support', $_GET['pid'], 1); ?>">
					<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/support_1.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Fully Compatibile'); ?><?php echo(' ('.$reports_num[1].')'); ?></p>
							</label>
						</div>
					</div>
				</a>
				<a href="<?php echo_rewrite_url('report_support', $_GET['pid'], 0); ?>">
					<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/support_0.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Partly Compatibile'); ?><?php echo(' ('.$reports_num[0].')'); ?></p>
							</label>
						</div>
					</div>
				</a>
				<a href="<?php echo_rewrite_url('report_support', $_GET['pid'], 2); ?>">
					<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/support_2.png" />
					<div>
						<div>
							<label>
								<p><?php _e('Not Compatibile'); ?><?php echo(' ('.$reports_num[2].')'); ?></p>
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
		$result = DB::result_first("SELECT COUNT(ID) FROM `".DCRM_CON_PREFIX."Reports` WHERE (`Remote` = '".DB::real_escape_string($_SERVER['REMOTE_ADDR'])."' AND `PID`='".$_GET['pid']."') LIMIT 3");
		if ($result < DCRM_REPORT_LIMIT) {
			if (!empty($_SERVER['REMOTE_ADDR']) && !empty($device_info['DEVICE']) && !empty($device_info['OS']) && $isCydia) {
				$result = DB::insert(DCRM_CON_PREFIX.'Reports', array('Remote' => DB::real_escape_string($_SERVER['REMOTE_ADDR']), 'Device' => $device_info['DEVICE'], 'iOS' => $device_info['OS'], 'Support' => $support, 'TimeStamp' => date('Y-m-d H:i:s'), 'PID' => (int)$_GET['pid']));
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
	$historys = DB::fetch_all("SELECT `ID`, `Version`, UNIX_TIMESTAMP(`CreateStamp`), `Changelog` FROM `".DCRM_CON_PREFIX."Packages` WHERE `Package` = (SELECT `Package` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".(int)$_GET['pid']."' LIMIT 1) ORDER BY `Version` DESC LIMIT 0,20");
	if (count($historys) > 0) {
?>
			<!--<label><?php _e('Version History'); ?></label>-->
<?php
		foreach ($historys as $history) {
?>
			<label><p><?php echo($history['Version']); ?>: <?php echo(date($dcrm_locale->date_format['date'], $history['UNIX_TIMESTAMP(`CreateStamp`)'])); ?></p></label>
			<fieldset class="changelog">
				<a>
					<div>
						<div>
							<label>
								<p>
									<?php echo empty($history['Changelog'])?'- '.__('Unknown change.'):$history['Changelog']; ?>
								</p>
							</label>
						</div>
					</div>
				</a>
				<a href="<?php echo_rewrite_url('view_nohistory', $history['ID']); ?>">
					<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/changelog.png">
					<div>
						<div>
							<label>
								<p>
									<?php _e('View This Version'); ?>
								</p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
<?php
		}
?>
<?php
	} else {
?>
			<label><?php _e('No version history now.'); ?></label>
			<br />
<?php
	}
} elseif ($index == 6) {
	$package_id = (int)DB::real_escape_string($_GET['pid']);
	$package_info = DB::fetch_first("SELECT `Name`, `Version`, `Author`, `Sponsor`, `Maintainer` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '".$package_id."' LIMIT 1");
	if (empty($package_info)) {
?>
			<block>
				<p>
					<?php _e('Invalid package!<br />Perhaps this package has been deleted by us.'); ?>
				</p>
			</block>
<?php
	} else {
		if (!empty($package_info['Author'])) {
			$author_name = trim(preg_replace("#^(.+)<(.+)>#","$1", $package_info['Author']));
			$author_mail = trim(preg_replace("#^(.+)<(.+)>#","$2", $package_info['Author']));
?>
			<fieldset class="author">
				<div>
					<p>
						<?php _e('Source manager can <strong>NOT</strong> solve the function problem of package: you <strong>MUST</strong> contact the author.'); ?>
					</p>
				</div>
				<a href="mailto:<?php echo($author_mail); ?>?subject=<?php echo(urlencode("Cydia/APT(A): ".$package_info['Name']." (".$package_info['Version'].")")); ?>" target="_blank">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/email.png">
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
		if (!empty($package_info['Sponsor'])) {
			$sponsor_name = trim(preg_replace("#^(.+)<(.+)>#","$1", $package_info['Sponsor']));
			$sponsor_url = trim(preg_replace("#^(.+)<(.+)>#","$2", $package_info['Sponsor']));
?>
			<fieldset class="maintainer">
				<div>
					<p>
						<?php _e('If the package is a commercial package, you can contact the sponsor to obtain commercial support.'); ?>
					</p>
				</div>
				<a href="<?php echo($sponsor_url); ?>" target="_blank">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/email.png">
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
		if (!empty($package_info['Maintainer'])) {
			$maintainer_name = trim(preg_replace("#^(.+)<(.+)>#","$1", $package_info['Maintainer']));
			$maintainer_mail = trim(preg_replace("#^(.+)<(.+)>#","$2", $package_info['Maintainer']));
?>
			<fieldset class="maintainer">
				<div>
					<p><?php _e('If you have questions when install or uninstall this package, please contact the maintainer.'); ?></p>
				</div>
				<a href="mailto:<?php echo($maintainer_mail); ?>?subject=<?php echo(urlencode("Cydia/APT(A): ".$package_info['Name']." (".$package_info['Version'].")")); ?>" target="_blank">
				<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/email.png">
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
} elseif ($index == 7) {
	if (DCRM_ALLOW_FULLLIST == 2) {
		$sections = DB::fetch_first("SELECT `Name`, `Icon`, `TimeStamp` FROM `".DCRM_CON_PREFIX."Sections` WHERE `ID` = '".(int)$_GET['pid']."'");
		if (!$sections) {
?>
			<block>
				<p>
					<?php _e('Invalid section!<br />Perhaps this section has been deleted by us.'); ?>
				</p>
			</block>
<?php
		} else {
?>
			<label><p><?php echo($sections['Name']); ?></p></label>
<?php
				$s_num = DB::result_first(DB::prepare("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages` WHERE (`Stat` = '1' AND `Section` = '%s')", $sections['Name']));
?>
			<block>
					<p><?php printf( __( '<strong>%s</strong> packages in this section.' ) , $s_num ); ?></p>
					<p><?php printf( __( 'Create time: <strong>%s</strong>' ) , $sections['TimeStamp'] ); ?></p>
			</block>
			<fieldset id="section"></fieldset>
			<fieldset id="loadmore" name="<?php echo($_GET['pid']); ?>">
				<a href="javascript:loadPackages();">
					<img class="icon" src="<?php echo(SITE_URL); ?>icon/default/moreinfo.png" />
					<div>
						<div>
							<label>
								<p><?php _e('More...'); ?></p>
							</label>
						</div>
					</div>
				</a>
			</fieldset>
			<script type="text/javascript">var siteurl = '<?php echo(SITE_URL); ?>';</script>
<?php
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
		<script src="<?php echo(SITE_URL); ?>js/scroll.js" type="text/javascript"></script>
<?php
}
?>
		<script src="<?php echo(SITE_URL); ?>js/main.js" type="text/javascript"></script>
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
