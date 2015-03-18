<?php
if(!defined('IN_DCRM')) exit();
function showmessage($msg = '', $redirect = '', $delay = 3){
	global $siteurl;
?>
<!DOCTYPE html>
<html>
<meta charset=utf-8>
<title><?php _e('System Message'); ?></title>
<meta name=viewport content="initial-scale=1, minimum-scale=1, width=device-width">
<style>
*{margin:0;padding:0}
html,code{font:15px/22px arial,sans-serif}
html{background:#fff;color:#222;padding:15px}
body{margin:7% auto 0;max-width:390px;min-height:220px;padding:75px 0 15px}
* > body{background:url(<?php echo($siteurl); ?>icons/default/msg_bg.png) 100% 5px no-repeat;padding-right:205px}
p{margin:11px 0 22px;overflow:hidden}
ins, ins a{color:#777;text-decoration:none;font-size:10px;}
a img{border:0}
@media screen and (max-width:772px){body{background:none;margin-top:0;max-width:none;padding-right:0}}
</style>
<body>
<p><b><?php _e('System Message'); ?> - DCRM</b></p>
<?php
echo "<p>{$msg}</p>";
if($redirect)
	echo "<ins><a href=\"{$redirect}\">".__('If your browser does not automatically redirect, please click here.')."</a></ins><meta http-equiv=\"refresh\" content=\"{$delay};url={$redirect}\" />";
?>
</body>
</html>
<?
	exit();
}
function saveVersion($version){
	if (!$version) return;
	$content = '<?php'.PHP_EOL.'/* Auto-generated version file */'.PHP_EOL.'$_version = \''.$version.'\';'.PHP_EOL.'?>';
	if(!is_writable(SYSTEM_ROOT.'./version.inc.php')) throw new Exception('Version file is not writable!');
	file_put_contents(SYSTEM_ROOT.'./version.inc.php', $content);
}
function update_final($version){
	saveVersion($version);
	showmessage(sprintf(__( 'Successfully updated to %s' ), $version), './');
}
function sizeext($size) {
	return ($size < 1048576) ? round($size/1024,2).' KB' : round($size/1048576,2).' MB';
}
function httpinfo($info_type) {
	switch ($info_type) {
		case 304:
			$info = "304 Not Modified";
			break;
		case 400:
			$info = "400 Bad Request";
			break;
		case 404:
			$info = "404 Not Found";
			break;
		case 4030:
			$info = "403 Forbidden";
			break;
		case 4031:
			$info = "403 Payment Required";
			break;
		case 405:
			$info = "405 Method Not Allowed";
			break; 
		case 500:
			$info = "500 Internal Server Error";
			break;
		default:
			$info = "501 Not Implemented";
			break;
	}
	header("HTTP/1.1 ".$info);
	header("Status: ".$info);
	header("Content-Type: text/html; charset=UTF-8");
?>
<html>
<head>
	<title><?php echo($info); ?></title>
</head>
<body bgcolor="white">
	<center>
		<h1><?php echo($info); ?></h1>
	</center>
<hr />
	<center>DCRM</center>
</body>
</html>
<?php
	exit();
}
// Function link
function randstr($len = 40) {
	require_once SYSTEM_ROOT.'./function/manage.php';
	return _randstr($len);
}
function deldir($dir) {
	require_once SYSTEM_ROOT.'./function/manage.php';
	return _deldir($dir);
}
function dirsize($dirName) {
	require_once SYSTEM_ROOT.'./function/manage.php';
	return _dirsize($dirName);
}
function execInBackground($cmd) {
	require_once SYSTEM_ROOT.'./function/manage.php';
	return _execInBackground(cmd);
}
function utf8_unicode($name){
	require_once SYSTEM_ROOT.'./function/manage.php';
	return _utf8_unicode($name);
}
function downFile($fileName, $fancyName = '', $forceDownload = true, $speedLimit = DCRM_SPEED_LIMIT, $contentType = '') {
	require_once SYSTEM_ROOT.'./function/downfile.php';
	return _downFile($fileName, $fancyName, $forceDownload, $speedLimit, $contentType);
}
?>
