<?php
if (!defined("DCRM")) {
	header('HTTP/1.1 403 Forbidden');
	exit();
}
header("Content-Type: text/html; charset=UTF-8");

// 设定绝对目录
$root = str_replace('/manage/', '', str_replace('\\', '/', dirname(__FILE__).'/')).'/';
define('ABSPATH', $root);
unset( $root );

// 载入语言
$localetype = 'manage';
include_once ABSPATH . 'lang/l10n.php';
localization_load();

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
        'name'  => __('Running State'),
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
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<script type="text/javascript" src="http://libs.useso.com/js/jquery/1.4.2/jquery.min.js"></script>
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
	</script>
	<script type="text/javascript">
	function setmargin(){
		var div = document.getElementById('sidebar');
		var width = div.style.width || div.clientWidth || div.offsetWidth || div.scrollWidth;
		document.getElementById('content').style.marginLeft = width+1+'px';
	}
	window.onload=setmargin;
	</script>
<?php
if ( isset($activeid) && ( 'manage' == $activeid || 'sections' == $activeid || 'center' == $activeid) ) 
	echo '	<link rel="stylesheet" type="text/css" href="css/corepage.css">';
if ( isset($activeid) && ( 'view' == $activeid || 'edit' == $activeid || 'center' == $activeid) ) 
	echo '	<script src="js/mbar.js" type="text/javascript"></script>';
?>
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="span6" id="logo">
				<p class="title">DCRM</p>
				<h6 class="underline">Darwin Cydia Repository Manager</h6>
			</div>
			<div class="top-secondary">
				<div class="btn-group pull-right">
					<a href="build.php" class="btn btn-inverse"><?php _e('Refresh the list');?></a>
					<a href="settings.php" class="btn btn-info<?php if ( isset($activeid) && 'settings' == $activeid ) echo ' disabled'; ?>"><?php _e('Settings');?></a>
					<a href="login.php?action=logout" class="btn btn-info"><?php _e('Logout');?></a>
				</div>
			</div>
		</div>
		<br />
		<div class="row">
			<div class="span2.5" id="sidebar" style="margin-left:0!important;">
				<div class="well sidebar-nav">
					<ul class="nav nav-list">
<?php
foreach ($sidebars as $value) {
	switch ( $value['type'] ) {
		case 'title':
			echo '<li class="nav-header">' . $value['title'] . '</li>';
			break;
		case 'subtitle':
			if( ( isset($activeid) && $value['id'] == $activeid ) || ( isset($highactiveid) && $value['id'] == $highactiveid ) ){
				echo '<li class="active">';
			} else {
				echo '<li>';
			}
			echo '<a href="' . $value['id'] . '.php">' . $value['name'] . '</a></li>';
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
							<li<?php if ( isset($activeid) && 'edit' == $activeid && !isset($_GET['action']) ) echo ' class="active"'?>><a href="javascript:opt(2)"><?php _e('General Editor'); ?></a></li>
							<li<?php if ( isset($activeid) && 'edit' == $activeid && isset($_GET['action']) && ($_GET['action'] == 'advance' || $_GET['action'] == 'advance_set') ) echo ' class="active"'?>><a href="javascript:opt(3)"><?php _e('Advance Editor'); ?></a></li>
							<?php if ( isset($activeid) && 'center' == $activeid ) echo '<li id="sli"></li>'; ?>
					</ul>
				</div>
<?php
}
?>
			</div>
			<div class="content" id="content">
				<div class="wrap">