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
	define("DCRM", true);
	require_once("manage/include/config.inc.php");
	require_once("manage/include/autofill.inc.php");
	header("Content-Type: text/html; charset=UTF-8");
	
	if (file_exists("Release")) {
		$release = file("Release");
		$release_origin = $_e['NONAME'];
		foreach ($release as $line) {
			if(preg_match("#^Origin#", $line)) {
				$release_origin = trim(preg_replace("#^(.+):\\s*(.+)#","$2", $line));
			}
			if(preg_match("#^Description#", $line)) {
				$release_description = trim(preg_replace("#^(.+):\\s*(.+)#","$2", $line));
			}
		}
	} else {
		$release_origin = $_e['EMPTY_PAGE'];
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
			<p><?php echo(_e('WELCOME_MESSAGE', $release_url)); ?></p>
			<p><img src="css/preview.png" alt="preview" style="width: 300px; border-radius: 6px;" /></p>
			<hr />
			<p><?php echo(_e('SAFARI_ONLY')); ?></p>
<?php
			if (defined("DCRM_DUOSHUO_KEY")) {
?>
			<div class="ds-recent-comments" data-num-items="6" data-show-avatars="1" data-show-time="1" data-show-title="1" data-show-admin="1" data-excerpt-length="70"></div>
			<div class="ds-share flat" 
			data-thread-key="apt-index" 
			data-title="<?php echo $release_origin; ?>" 
			data-images="" 
			data-content="<?php echo(_e('MY_FAVOURITE_REPO', $release_origin, $release_url)); ?>" 
			data-url="<?php echo($release_url); ?>">
			    <div class="ds-share-aside-right">
			      <div class="ds-share-aside-inner">
			      </div>
			      <div class="ds-share-aside-toggle"><?php echo(_e('SHARE_TO')); ?></div>
			    </div>
			</div>
<?php
			}
?>
			<hr />
			<p>© <?php echo(date('Y')); ?> <a href="http://82flex.com">82Flex</a>. <?php echo(_e('POWERED_BY')); ?></p>
<?php
			if ($first) {
?>
			<hr />
			<?php echo(_e('INSTALL_INTRO')); ?>
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
	if (defined("DCRM_DUOSHUO_KEY")) {
?>
		<script type="text/javascript">
		var duoshuoQuery = {short_name:"<?php echo(DCRM_DUOSHUO_KEY); ?>"};
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
