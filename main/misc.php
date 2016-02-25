<?php
/**
 * DCRM PC Index Page
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

// For Rewrite Test
if($_SERVER['HTTP_USER_AGENT'] == 'DCRM-RewriteTest'){
	echo('OK');
	exit();
}

require_once('system/common.inc.php');

if (file_exists("Release")) {
	$release = file("Release");
	$release_origin = __('No Name');
	foreach ($release as $line) {
		if(preg_match("#^Origin#", $line)) {
			$release_origin = trim(preg_replace("#^(.+):\\s*(.+)#","$2", $line));
		}
		if(preg_match("#^Description#", $line)) {
			$release_description = trim(preg_replace("#^(.+):\\s*(.+)#","$2", $line));
		}
	}
} else {
	$release_origin = __('Empty Page');
}
$release_url = base64_decode(DCRM_REPOURL);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $release_origin; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
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
		<link rel="shortcut icon" href="favicon.ico" />
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/misc.min.css" rel="stylesheet" media="screen">
<?php if(is_rtl()){ ?>		<link rel="stylesheet" type="text/css" href="css/bootstrap-rtl.min.css"><?php } ?>
<?php if(file_exists(ROOT.'css/font/'.substr($locale, 0, 2).'.css')){ ?>		<link rel="stylesheet" type="text/css" href="./css/font/<?php echo substr($locale, 0, 2); ?>.css"><?php echo "\n"; } ?>
<?php if(file_exists(ROOT.'css/font/'.$locale.'.css')){ ?>		<link rel="stylesheet" type="text/css" href="./css/font/<?php echo $locale; ?>.css"><?php echo "\n"; } ?>
	</head>
	<body>
		<div class="well">
<?php
if (file_exists('CydiaIcon.png')) {
?>
		  <p><img src="CydiaIcon.png" style="width: 72px; height: 72px; border-radius: 6px;" /></p>
<?php
}
?>
			<p><?php echo $release_origin; ?></p>
			<hr />
			<p><?php printf( __( 'Welcome! <a href="%1$s">Click to add</a> <code>%2$s</code> via Cydia.' ) , 'cydia://url/https://cydia.saurik.com/api/share#?source='.$release_url , $release_url ); ?></p>
			<div class="add_image">
				<div class="input_text"><?php _e('Enter Cydia/APT URL'); ?></div>
				<div class="repo_text">http://<span class="txt"><?php echo $release_url; ?></span></div>
				<div class="cancel button">
					<div class="text"><?php _e('Cancel'); ?></div>
				</div>
				<div class="add button">
					<div class="text"><?php _e('Add Source'); ?></div>
				</div>
				<div class="repo">
					<span class="repo_image">
						<span class="mask"> </span>
						<img src="CydiaIcon.png"/>
					</span>
					<span class="repo_title"><?php echo $release_origin; ?></span>
					<span class="repo_url"><?php echo $release_url; ?></span>
				</div>
			</div>
			<hr />
			<p><?php _e('This page is restricted to Safari only!'); ?></p>
<?php
if (defined("AUTOFILL_DUOSHUO_KEY")) {
?>
			<div class="ds-recent-comments" data-num-items="6" data-show-avatars="1" data-show-time="1" data-show-title="1" data-show-admin="1" data-excerpt-length="70"></div>
			<div class="ds-share flat" 
			data-thread-key="apt-index" 
			data-title="<?php echo $release_origin; ?>" 
			data-images="" 
			data-content="<?php printf( __( 'My Favourite Repo: %1$s(%2$s)' ) , $release_origin, $release_url ); ?>" 
			data-url="<?php echo($release_url); ?>">
				<div class="ds-share-aside-right">
					<div class="ds-share-aside-inner">
					</div>
					<div class="ds-share-aside-toggle"><?php _e('Share To'); ?></div>
				</div>
			</div>
<?php
}
?>
			<hr />
			<p>© <?php echo defined("AUTOFILL_FOOTER_YEAR") ? htmlspecialchars(stripslashes(AUTOFILL_FOOTER_YEAR)).'-' : '';echo date('Y'); ?> <a href="<?php echo htmlspecialchars(base64_decode(DCRM_REPOURL)); ?>"><?php echo defined("AUTOFILL_FOOTER_NAME") ? htmlspecialchars(stripslashes(AUTOFILL_FOOTER_NAME)) : $release_origin; ?></a></p>
			<p><?php _e('Powered by <a href="http://82flex.com/projects">DCRM</a>.'); echo defined("AUTOFILL_FOOTER_CODE") ? stripslashes(" · ".AUTOFILL_FOOTER_CODE) : ''; ?></p>
		</div>
<?php
if (defined("AUTOFILL_STATISTICS")) {
?>
	<div style="text-align: center; display: none;"><?php echo AUTOFILL_STATISTICS; ?></div>
<?php
}
if (defined("AUTOFILL_DUOSHUO_KEY")) {
?>
		<script type="text/javascript">
		var duoshuoQuery = {short_name:"<?php echo(AUTOFILL_DUOSHUO_KEY); ?>"};
			(function() {
				var ds = document.createElement('script');
				ds.type = 'text/javascript';ds.async = true;
				ds.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') + '//static.duoshuo.com/embed.unstable.js';
				ds.charset = 'UTF-8';
				(document.getElementsByTagName('head')[0] 
				 || document.getElementsByTagName('body')[0]).appendChild(ds);
			})();
		</script>
<?php
}
?>
	<script src="//cdn.bootcss.com/jquery/1.11.2/jquery.min.js"></script>
	<script src="./js/misc.js"></script>
	</body>
</html>
