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

/* DCRM Manage Header */

if (!defined("DCRM")) {
	header('HTTP/1.1 403 Forbidden');
	exit('HTTP/1.1 403 Forbidden');
}

$localetype = 'manage';
define('MANAGE_ROOT', dirname(__FILE__).'/');
define('ABSPATH', dirname(MANAGE_ROOT).'/');
require_once ABSPATH.'system/common.inc.php';

$sidebars = array(
	array(
        'title' => 'PACKAGES',
        'type'  => 'title'
    ),
	array(
        'name'  => __('Upload Packages'),
        'id'    => 'upload',
        'type'  => 'subtitle',
    ),
	array(
        'name'  => __('Import Packages'),
        'id'    => 'manage',
        'type'  => 'subtitle',
    ),
	array(
        'name'  => __('Manage Packages'),
        'id'    => 'center',
        'type'  => 'subtitle',
    ),
	array(
        'name'  => __('Manage UDID'),
        'id'    => 'udid',
        'type'  => 'subtitle',
    ),
	array(
        'title' => 'REPOSITORY',
        'type'  => 'title'
    ),
	array(
        'name'  => __('Manage Sections'),
        'id'    => 'sections',
        'type'  => 'subtitle',
    ),
	array(
        'name'  => __('Manage Repository'),
        'id'    => 'release',
        'type'  => 'subtitle',
    ),
	array(
        'title' => 'SYSTEM',
        'type'  => 'title'
    ),
	array(
        'name'  => __('Running Status'),
        'id'    => 'stats',
        'type'  => 'subtitle',
    ),
	array(
        'name'  => __('About'),
        'id'    => 'about',
        'type'  => 'subtitle',
    )
);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>DCRM - <?php _e('Repository Manager');?></title>
	<meta name="viewport" content="width=600px, minimal-ui">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
<?php if(is_rtl()){ ?>	<link rel="stylesheet" type="text/css" href="css/bootstrap-rtl.min.css"><?php echo "\n"; } ?>
<?php if(file_exists(ROOT.'css/font/'.($local_css = substr($locale, 0, 2)).'.css') || file_exists(ROOT.'css/font/' . ($local_css = $locale) . '.css')): ?>	<link rel="stylesheet" type="text/css" href="../css/font/<?php echo $local_css; ?>.css"><?php echo("\n"); endif; ?>
	<script src="http://cdn.bootcss.com/jquery/1.11.2/jquery.min.js"></script>
	<script type="text/javascript" src="./javascript/pace.min.js"></script>
	<script type="text/javascript">
	$(document).ready(function(){
		var loaded = true;
		var top = $("#sidebar").offset().top;
		function Add_Data() {
			var scrolla=$(window).scrollTop();
			var cha=parseInt(top)-parseInt(scrolla)-10;
			if(loaded && cha<=0) {                
				$("#sidebar").addClass("sticky");
				loaded=false;
			}
			if(!loaded && cha>0) {
				$("#sidebar").removeClass("sticky");
				loaded=true;
			}
		}
		$(window).scroll(Add_Data);
	});
	$(document).ready(function setmargin(){
		var div = document.getElementById('sidebar');
		var width = div.style.width || div.clientWidth || div.offsetWidth || div.scrollWidth;
		<?php if(is_rtl()){ ?>document.getElementById('content').style.marginRight = width+1+'px';<?php } else { ?>document.getElementById('content').style.marginLeft = width+1+'px';<?php } ?>
	});
	</script>
<?php
if ( isset($activeid) && ( 'manage' == $activeid || 'sections' == $activeid || 'center' == $activeid) ) 
	echo '	<link rel="stylesheet" type="text/css" href="css/corepage.css">';
if ( isset($activeid) && ( 'view' == $activeid || 'edit' == $activeid || 'center' == $activeid) ) 
	echo '	<script src="javascript/backend/mbar.js" type="text/javascript"></script>';
?>
</head>
<body class="manage">
	<div class="container">
		<div class="row">
			<div class="span" id="logo">
				<p class="title">DCRM</p>
				<h6 class="underline">Darwin Cydia Repository Manager</h6>
			</div>
			<div class="top-secondary">
				<div class="btn-group pull-right">
					<a href="build.php" class="btn btn-inverse"><?php _e('Rebuild the list');?></a>
					<a href="settings.php" class="btn btn-info<?php if ( isset($activeid) && 'settings' == $activeid ) echo ' disabled'; ?>"><?php _ex('Preferences', 'Header');?></a>
					<a href="login.php?action=logout" class="btn btn-info"><?php _e('Logout');?></a>
				</div>
			</div>
		</div>
		<br />
		<div class="row">
			<div class="span2.5" id="sidebar" style="margin-left:0 !important;">
				<div class="well sidebar-nav">
					<ul class="nav nav-list">
<?php
foreach ($sidebars as $value) {
	switch ( $value['type'] ) {
		case 'title':
			echo "\t\t\t\t\t\t<li class=\"nav-header\">" . $value['title'] . "</li>\n";
			break;
		case 'subtitle':
			if( ( isset($activeid) && $value['id'] == $activeid ) || ( isset($highactiveid) && $value['id'] == $highactiveid ) ){
				echo "\t\t\t\t\t\t\t<li class=\"active\">";
			} else {
				echo "\t\t\t\t\t\t\t<li>";
			}
			echo '<a href="' . $value['id'] . '.php">' . $value['name'] . "</a></li>\n";
	}
}
?>
					</ul>
				</div>
<?php
if ( isset($activeid) && ( 'view' == $activeid || 'edit' == $activeid || 'center' == $activeid) ){
?>
				<div class="well sidebar-nav" id="mbar" <?php if ( isset($activeid) && 'center' == $activeid ) echo 'style="display: none;"'; ?>>
					<ul class="nav nav-list">
						<li class="nav-header">OPERATIONS</li>
							<li<?php if ( isset($activeid) && 'view' == $activeid ) echo ' class="active"'; ?>><a href="javascript:opt(1)"><?php _e('View Details'); ?></a></li>
							<li<?php if ( isset($activeid) && 'edit' == $activeid && !isset($_GET['action']) ) echo ' class="active"'?>><a href="javascript:opt(2)"><?php _e('General Editing'); ?></a></li>
							<li<?php if ( isset($activeid) && 'edit' == $activeid && isset($_GET['action']) && ($_GET['action'] == 'advance' || $_GET['action'] == 'advance_set') ) echo ' class="active"'?>><a href="javascript:opt(3)"><?php _e('Advance Editing'); ?></a></li>
							<li id="sli"></li>
					</ul>
				</div>
<?php
}
?>
			</div>
			<div class="content" id="content">
				<div class="wrap">
