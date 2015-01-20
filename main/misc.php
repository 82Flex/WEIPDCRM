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

	/* Base Configuration File Check */
	if (!file_exists('./manage/include/connect.inc.php')) {
		$root .= ($directory = trim(dirname($_SERVER["SCRIPT_NAME"]), "/\,")) ? "/$directory/" : "/";

		header('Location: '.$root.'init');
		exit;
	}

	define("DCRM", true);
	require_once("manage/include/config.inc.php");
	require_once("manage/include/autofill.inc.php");
	
	/* Language Switch */
	require_once("lang/l10n.php");
	$link_language = localization_load();

	header("Content-Type: text/html; charset=UTF-8");
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
		if (file_exists('init/install.php')) {
			$first = true;
		} else {
			$first = false;
		}
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
			<p><?php printf( __( 'Welcome to <a href="cydia://sources/add">Add</a> <code>%s</code> via Cydia.' ) , $release_url ); ?></p>
			<p><img src="css/preview.png" alt="preview" style="width: 300px; border-radius: 6px;" /></p>
			<hr />
			<p><?php _e('This page is available to Safari only.'); ?></p>
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
			<p>© <?php echo defined("AUTOFILL_FOOTER_YEAR") ? htmlspecialchars(stripslashes(AUTOFILL_FOOTER_YEAR)).'-' : '';echo date('Y'); ?> <a href="<?php echo htmlspecialchars(base64_decode(DCRM_REPOURL)); ?>"><?php echo defined("AUTOFILL_FOOTER_NAME") ? htmlspecialchars(stripslashes(AUTOFILL_FOOTER_NAME)) : $release_origin; ?></a> · <?php _e('Powered by <a href="http://82flex.com/projects">DCRM</a>.'); echo defined("AUTOFILL_FOOTER_CODE") ? stripslashes(" · ".AUTOFILL_FOOTER_CODE) : ''; ?></p>
<?php
			if ($first) {
?>
			<hr />
			<?php _e('Dear manager, please follow the steps on <a href="init/index.html">Install Introduction</a> first.'); ?>
<?php
			}
?>
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
	</body>
</html>
