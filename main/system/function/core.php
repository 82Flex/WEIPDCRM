<?php
if(!defined('IN_DCRM')) exit();
function showmessage($msg = '', $redirect = '', $delay = 3){
	base_url();
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
* > body{background:url(<?php echo(SITE_URL); ?>icons/default/msg_bg.png) 100% 5px no-repeat;padding-right:205px}
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
<?php 
	exit();
}
function saveVersion($version){
	if (!$version) return;
	$content = '<?php'.PHP_EOL.'/* Auto-generated version file */'.PHP_EOL.'$_version = \''.$version.'\';'.PHP_EOL.'?>';
	if(!is_writable(SYSTEM_ROOT.'./version.inc.php')) throw new Exception('Version file is not writable!');
	if(!file_put_contents(SYSTEM_ROOT.'./version.inc.php', $content)) throw new Exception('Version file is not writable, please change $_version to '.$version.'in version.inc.php');
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
		case 4032:
			if(!defined("AUTOFILL_EMAIL"))
				require_once(CONF_PATH.'autofill.inc.php');
			$info = sprintf('403 Payment Required: This package is either paid or requires a paid package to function. If you already paid for this package, try again later, or email %s if this problem keeps happening.', AUTOFILL_EMAIL);
			break;
		case 4033:
			$info = '403 Forbidden: This package is protected.';
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
function _ip2long( $ip_address ) {
	return sprintf("%u",ip2long($ip_address));
}
function getIp() {
	if(getenv('HTTP_CLIENT_IP')&&strcasecmp(getenv('HTTP_CLIENT_IP'),'unknown'))
		$onlineip = getenv('HTTP_CLIENT_IP'); 
	elseif(getenv('HTTP_X_FORWARDED_FOR')&&strcasecmp(getenv('HTTP_X_FORWARDED_FOR'),'unknown'))
		$onlineip = getenv('HTTP_X_FORWARDED_FOR'); 
	elseif(getenv('REMOTE_ADDR')&&strcasecmp(getenv('REMOTE_ADDR'),'unknown'))
		$onlineip = getenv('REMOTE_ADDR'); 
	elseif(isset($_SERVER['REMOTE_ADDR'])&&$_SERVER['REMOTE_ADDR']&&strcasecmp($_SERVER['REMOTE_ADDR'],'unknown'))
		$onlineip = $_SERVER['REMOTE_ADDR']; 
	else
		$onlineip = null;
	return $onlineip; 
} 
/**
 * Check value to find if it was serialized.
 *
 * If $data is not an string, then returned value will always be false.
 * Serialized data is always a string.
 *
 * @param string $data   Value to check to see if was serialized.
 * @param bool   $strict Optional. Whether to be strict about the end of the string. Default true.
 * @return bool False if not serialized and true if it was.
 */
function is_serialized( $data, $strict = true ) {
	// if it isn't a string, it isn't serialized.
	if ( ! is_string( $data ) ) {
		return false;
	}
	$data = trim( $data );
 	if ( 'N;' == $data ) {
		return true;
	}
	if ( strlen( $data ) < 4 ) {
		return false;
	}
	if ( ':' !== $data[1] ) {
		return false;
	}
	if ( $strict ) {
		$lastc = substr( $data, -1 );
		if ( ';' !== $lastc && '}' !== $lastc ) {
			return false;
		}
	} else {
		$semicolon = strpos( $data, ';' );
		$brace     = strpos( $data, '}' );
		// Either ; or } must exist.
		if ( false === $semicolon && false === $brace )
			return false;
		// But neither must be in the first X characters.
		if ( false !== $semicolon && $semicolon < 3 )
			return false;
		if ( false !== $brace && $brace < 4 )
			return false;
	}
	$token = $data[0];
	switch ( $token ) {
		case 's' :
			if ( $strict ) {
				if ( '"' !== substr( $data, -2, 1 ) ) {
					return false;
				}
			} elseif ( false === strpos( $data, '"' ) ) {
				return false;
			}
			// or else fall through
		case 'a' :
		case 'O' :
			return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
		case 'b' :
		case 'i' :
		case 'd' :
			$end = $strict ? '$' : '';
			return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
	}
	return false;
}
/**
 * Serialize data, if needed.
 *
 * @param string|array|object $data Data that might be serialized.
 * @return mixed A scalar data
 */
function maybe_serialize( $data ) {
	if ( is_array( $data ) || is_object( $data ) )
		return serialize( $data );

	if ( is_serialized( $data, false ) )
		return serialize( $data );

	return $data;
}
/**
 * Unserialize value only if it was serialized.
 *
 * @param string $original Maybe unserialized original, if is needed.
 * @return mixed Unserialized data can be any type.
 */
function maybe_unserialize( $original ) {
	if ( is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
		return @unserialize( $original );
	return $original;
}
/**
 * Retrieve option value based on name of option.
 *
 * If the option does not exist or does not have a value, then the return value
 * will be false. This is useful to check whether you need to install an option
 * and is commonly used during installation of plugin options and to test
 * whether upgrading is required.
 *
 * If the option was serialized then it will be unserialized when it is returned.
 *
 * @param string $option Name of option to retrieve. Expected to not be SQL-escaped.
 * @param mixed $default Optional. Default value to return if the option does not exist.
 * @return mixed Value set for the option.
 */
function get_option( $option, $default = false ) {
	$option = trim( $option );
	if ( empty( $option ) )
		return false;

	//$row = mysql_fetch_object( DB::query( DB::prepare( "SELECT option_value FROM `".DCRM_CON_PREFIX."options` WHERE option_name = %s LIMIT 1", $option ) ) );
	$row = DB::result_first( DB::prepare( "SELECT option_value FROM `".DCRM_CON_PREFIX."Options` WHERE option_name = %s LIMIT 1", $option ) );

	/*if ( is_object( $row ) ) {
		$value = $row->option_value;
	} else { // option does not exist
		return $default;
	}*/
	if ($row) {
		$value = $row;
	} else {
		return $default;
	}

	return maybe_unserialize( $value );
}
/**
 * Update the value of an option that was already added.
 *
 * You do not need to serialize values. If the value needs to be serialized, then
 * it will be serialized before it is inserted into the database. Remember,
 * resources can not be serialized or added as an option.
 *
 * If the option does not exist, then the option will be added with the option
 * value, but you will not be able to set whether it is autoloaded. If you want
 * to set whether an option is autoloaded, then you need to use the add_option().
 *
 * @param string $option Option name. Expected to not be SQL-escaped.
 * @param mixed $value Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
 * @return bool False if value was not updated and true if value was updated.
 */
function update_option( $option, $value ) {
	$option = trim($option);
	if ( empty($option) )
		return false;

	if ( is_object( $value ) )
		$value = clone $value;

	$old_value = get_option( $option );

	// If the new and old values are the same, no need to update.
	if ( $value === $old_value )
		return false;

	if ( false === $old_value )
		return add_option( $option, $value );

	$serialized_value = maybe_serialize( $value );

	$result = DB::update( DCRM_CON_PREFIX.'Options', array( 'option_value' => $serialized_value ), array( 'option_name' => $option ) );
	if ( ! $result )
		return false;

	return true;
}
/**
 * Add a new option.
 *
 * You do not need to serialize values. If the value needs to be serialized, then
 * it will be serialized before it is inserted into the database. Remember,
 * resources can not be serialized or added as an option.
 *
 * You can create options without values and then update the values later.
 * Existing options will not be updated and checks are performed to ensure that you
 * aren't adding a protected WordPress option. Care should be taken to not name
 * options the same as the ones which are protected.
 *
 * @param string         $option      Name of option to add. Expected to not be SQL-escaped.
 * @param mixed          $value       Optional. Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
 * @param string         $deprecated  Optional. Description. Not used anymore.
 * @return bool False if option was not added and true if option was added.
 */
function add_option( $option, $value = '', $deprecated = '') {
	$option = trim($option);
	if ( empty($option) )
		return false;

	if ( is_object($value) )
		$value = clone $value;

	//$value = sanitize_option( $option, $value );

	$serialized_value = maybe_serialize( $value );

	$result = DB::query( DB::prepare( "INSERT INTO `".DCRM_CON_PREFIX."Options` (`option_name`, `option_value`) VALUES (%s, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`)", $option, $serialized_value) );
	if ( ! $result )
		return false;

	return true;
}
function xss_clean($data) {
	// Fix &entity\n;
	$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
	$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
	$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
	$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

	// Remove any attribute starting with "on" or xmlns
	$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

	// Remove javascript: and vbscript: protocols
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

	// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

	// Remove namespaced elements (we do not need them)
	$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
	do{
		// Remove really unwanted tags
		$old_data = $data;
		$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
	}
	while ($old_data !== $data);
	// we are done...
	return $data;
}
// Get url code
function url_code($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_NOBODY, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_USERAGENT, 'DCRM-RewriteTest');
	curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	return $code;
}
function base_url($is_subdir = false) {
	//$sitepath = str_replace(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '', str_replace('\\', '/', ROOT));
	if(defined('SITE_URL')) return;
	if(defined('CUSTOM_SITEPATH')){
		define('SITE_PATH', CUSTOM_SITEPATH);
	} else {
		if ($is_subdir || strstr($_SERVER['PHP_SELF'], '/manage/'))
			$sitepath = dirname(dirname($_SERVER['PHP_SELF']));
		else
			$sitepath = dirname($_SERVER['PHP_SELF']);
		define('SITE_PATH', (strlen($sitepath) === 1 ? '/' : $sitepath.'/'));
	}

	$siteurl = htmlspecialchars('//'.$_SERVER['HTTP_HOST'].($_SERVER['SERVER_PORT'] == ('443' ||  '80') ? '' : ':'.$_SERVER['SERVER_PORT']).SITE_PATH);
	define('SITE_URL', $siteurl);
}
function is_ssl(){  
	if ( isset($_SERVER['HTTPS']) ) {
		if ( 'on' == strtolower($_SERVER['HTTPS']) )
			return true;
		if ( '1' == $_SERVER['HTTPS'] )
			return true;
	} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}
	return false;
}  
function url_scheme(){
	return is_ssl() ? 'https:' : 'http:';
}
/**
 * Move directory.
 * Copyright (c) 2015 Hintay <hintay@me.com>
 *
 * @param  string         $oldDir     Old directory.
 * @param  string         $tgtDir     Target directory.
 * @param  bool           $overWrite  Optional. Overwrite files?
 * @param  bool           $deleteOld  Optional. Delete old files?
 * @return bool False if an error occurred.
 */
function moveDir($oldDir, $tgtDir, $overWrite = false, $deleteOld = true){
	$tgtDir .= '/';
	$oldDir .= '/';
	if (!is_dir($oldDir) || empty($tgtDir))
		return false;
	if (!file_exists($tgtDir))
		return rename($oldDir, $tgtDir);

	@$dirHandle = opendir($oldDir);
	if (!$dirHandle)
		return false;
	while (false !== ($file = readdir($dirHandle))) {
		if ($file == '.' || $file == '..')
			continue;

		if (!is_dir($oldDir . $file)) {
			// This is a file.
			if(file_exists($tgtDir . $file)) {
				if(!$overWrite) {
					if($deleteOld)
						unlink($oldDir . $file);
					continue;
				}
				unlink($tgtDir . $file);
			}
			if($deleteOld)
				rename($oldDir . $file, $tgtDir . $file);
			else
				copy($oldDir . $file, $tgtDir . $file);
		} else {
			moveDir($oldDir . $file, $tgtDir . $file, $overWrite, $deleteOld);
		}
	}
	closedir($dirHandle);
	if($deleteOld)
		return rmdir($oldDir);
	else
		return true;
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
function TrimArray($input){
	require_once SYSTEM_ROOT.'./function/manage.php';
	return _TrimArray($input);
}
function string_handle($input, $switch, $switch_string = 'cydia::commercial', $separator = ','){
	require_once SYSTEM_ROOT.'./function/manage.php';
	return _string_handle($input, $switch, $switch_string, $separator);
}
function check_commercial_tag($tag){
	require_once SYSTEM_ROOT.'./function/manage.php';
	return _check_commercial_tag($tag);
}
function downFile($fileName, $fancyName = '', $forceDownload = true, $speedLimit = DCRM_SPEED_LIMIT, $contentType = '') {
	require_once SYSTEM_ROOT.'./function/downfile.php';
	return _downFile($fileName, $fancyName, $forceDownload, $speedLimit, $contentType);
}
?>
