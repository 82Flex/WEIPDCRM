<?php
/**
 * DCRM Installer Functions
 * Copyright (c) 2015 Hintay <hintay@me.com>
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

if (!defined("DCRM")) {
	exit();
}

/* 设定参数 */
define('ABSPATH', dirname(dirname(__FILE__)).'/');
define('CONF_PATH', ABSPATH.'system/config/');
define('VERSION', '1.7.15.12.12');
if ( function_exists( 'mysqli_connect' ) )
	define('USE_MYSQLI', true);

/* 错误抑制 */
define('DEBUG_ENABLED', isset($_GET['debug']));
error_reporting(DEBUG_ENABLED ? E_ALL & !E_NOTICE & !E_STRICT : E_ERROR | E_PARSE);
@ini_set('display_errors', DEBUG_ENABLED);

/* 数据库类 */
include_once './installer-db.php';
$db = new DB();

/* 载入语言 */
$localetype = 'install';
include_once ABSPATH . 'system/languages/l10n.php';
$step_language = localization_load();

if(isset($_GET['dev']) && md5(sha1($_GET['dev']).'dcrm') == '71365b4b2ea908e80e69aef0b4e892cb'){
	$step_language .= '&amp;dev='.$_GET['dev'];
	define('DEVELOP_ENABLED', true);
	print_r(file_get_contents(CONF_PATH.'connect.inc.php'));
}

/**
 * 环境检查
 */
function check_env(&$result)
{
	$env_vars = array();

	/// 检查操作系统
	$env_vars['php_os'] = array('required' => __( 'No Limit' ) , 'best' => __( 'Unix-like' ), 'curr' => PHP_OS, 'state' => true);

	/// 检查php版本
	$env_vars['php_vers'] = array('required' => '5.3', 'best' => '5.3', 'curr' => PHP_VERSION);
	if ((int)$env_vars['php_vers']['required'] > (int)$env_vars['php_vers']['curr']) {
		$env_vars['php_vers']['state'] = false;
		$result = false;
	} else {
		$env_vars['php_vers']['state'] = true;
	}

	/// 检查上传附件大小
	/*
	$env_vars['upload'] = array('required' => '1M', 'best' => '2M', 'curr' => ini_get('upload_max_filesize'));
	$u = substr($env_vars['upload']['curr'], -1, 1);
	$max_upload = $u == 'M' ? (int)$env_vars['upload']['curr'] : ($u == 'K' ? (int)$env_vars['upload']['curr'] / 1024 : (int)$env_vars['upload']['curr'] / (1024 * 1024));
	if ((int)$env_vars['upload']['required'] > $max_upload) {
		$env_vars['upload']['state'] = false;
		$result = false;
	} else {
		$env_vars['upload']['state'] = true;
	}
	 */

	/// 检查gd库版本
	if (dcrm_function_exists('gd_info')) {
		$gd_info = gd_info();
	} else {
		$gd_info['GD Version'] = __( 'The GD module cannot be loaded' );
	}
	$env_vars['gd_vers'] = array('required' => __( 'No Limit' ) , 'best' => '2.0', 'curr' => $gd_info['GD Version']);
	$match = array();
	preg_match('/\d/', $env_vars['gd_vers']['curr'], $match);
	$gd_vers = $match[0];
	$env_vars['gd_vers']['state'] = true;

	/// 检查可用磁盘空间
	$env_vars['disk'] = array('required' => '10M', 'best' => __( 'No Limit' ) , 'curr' => floor(diskfreespace(ABSPATH) / (1024 * 1024)).'M');
	if ((int)$env_vars['disk']['required'] > (int)$env_vars['disk']['curr']) {
		$env_vars['disk']['state'] = false;
		$result = false;
	} else {
		$env_vars['disk']['state'] = true;
	}

	return $env_vars;
}

/**
 * 检查目录文件
 */
function check_dir(&$result) {
	/// 需要检查的目录和文件
	$dir_files = array(
					'./',
					'./system/config');

	$check_dir_files = array();
	foreach ($dir_files as $var) {
		if (is_dir($var)) {
			if ($fp = @fopen(ABSPATH.$var.'/text.txt', 'w')) {
				@fclose($fp);
				@unlink(ABSPATH.$var.'/text.txt');
				$check_dir_files[$var] = array('w' => 'writeable', 'state' => true);
			} else {
				$check_dir_files[$var] = array('w' => 'unwriteable', 'state' => false);
				$result = false;
			}
		} else {
			if (file_exists(ABSPATH.$var)) {
				if (is_writable(ABSPATH.$var)) {
					$check_dir_files[$var] = array('w' => 'writeable', 'state' => true);
				} else {
					$check_dir_files[$var] = array('w' => 'unwriteable', 'state' => false);
					$result = false;
				}
			} else {
				$check_dir_files[$var] = array('w' => 'unwriteable', 'state' => false);
				$result = false;
			}
		}
	}
	return $check_dir_files;
}

/**
 * 检查函数依赖
 */
function check_func(&$result)
{
	$func_items = array('mysql' => 'mysql_connect',
						'file' => 'file_get_contents',
						'bz2' => 'bzcompress',
						'zlib' => 'gzcompress',
						'mhash' => array('mhash', 'hash_hmac')
						);
	if (defined('USE_MYSQLI'))
		$func_items['mysql'] = 'mysqli_connect';
	$check_func_items = array();
	foreach ($func_items as $key => $var) {
		if ('mhash' == $key) {
			if (!dcrm_function_exists($var[0]) && !dcrm_function_exists($var[1])) {
				$check_func_items[implode(', ', $var).'( )'] = array('s' => 'unsupportted_both', 'state' => false);
			} else {
				$check_func_items[$var[0].'( ), '.$var[1].'( )'] = array('s' => 'supportted', 'state' => true);
			}
		} elseif('bz2' == $key || 'zlib' == $key){
			if (dcrm_function_exists($var)) {
				$check_func_items[$var.'( )'] = array('s' => 'supportted', 'state' => true);
			} else {
				$check_func_items[$var.'( )'] = array('s' => 'unsupportted', 'state' => false);
				//$result = false;
			}
		} else{
			if (dcrm_function_exists($var)) {
				$check_func_items[$var.'( )'] = array('s' => 'supportted', 'state' => true);
			} else {
				$check_func_items[$var.'( )'] = array('s' => 'unsupportted', 'state' => false);
				$result = false;
			}
		}
	}
	return $check_func_items;
}

/**
 * 检查方法是否可用
 *
 * @param string $func 函数名或扩展模块名
 * @param array $ext 扩展模块，具体的函数扩展名，可以多个
 *
 * @return bool
 */
function dcrm_function_exists($func, $ext = false) {
	/// 获取被禁用的方法
	$disable_functions = ini_get('disable_functions');
	$result = true;
	if ($ext) {
		foreach ($ext as $var) {
			$func_name = $func.'_'.$var;
			if (strpos($disable_functions, $func) !== false || !function_exists($func_name)) {
				$result = false;
			}
		}
	} else {
		if (strpos($disable_functions, $func) !== false || !function_exists($func)) {
			$result = false;
		}
	}

	return $result;
}

/**
 * url检测
 */
function available($url) {
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

/**
 * 头部
 */
function display_header($n='') {
	global $header_title;

?>
<!doctype html>
<meta charset=utf-8 /><title><?php echo $header_title; ?> - DCRM</title>
<link rel="stylesheet" href="css/main.css" type="text/css" />
<h1 id="logo">DCRM</h1>
<h6 class="underline">Darwin Cydia Repository Manager</h6>

<?php if ($n != ''): ?>
		<div class="alert alert-danger"><?php echo $n; ?></div>
<?php endif;
}

function base_url(){
	$sitepath = dirname(dirname($_SERVER['PHP_SELF']));
	$sitepath = strlen($sitepath) === 1 ? '/' : $sitepath.'/';
	$siteurl = htmlspecialchars(($_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$sitepath);
	define('BASE_URL', $siteurl);
}

function check_notice( $install = false ) {
	base_url();
	$notice = '';
	if (!function_exists("curl_init"))
		$notice = '<strong>'.__('Warning: ').'</strong>'.__('The server does not support the cURL function, DCRM will not be able to use.');
	if( $notice == '' ){
		if( $install == false ){
			if (file_exists(CONF_PATH.'connect.inc.php')) {
				$notice = '<strong>'.__('Warning: ').'</strong>'.__('The file \'/system/config/connect.inc.php\' already exists. If you need to reset any of the configuration items in this file, please delete it first.');
			} elseif (!file_exists(CONF_PATH.'connect.inc.default.php')) {
				$notice = '<strong>'.__('Warning: ').'</strong>'.__('Sorry, I need a /system/config/connect.inc.default.php file to work from. Please re-upload this file from your DCRM installation.');
			} elseif (!is_writable(CONF_PATH)) {
				$notice = '<strong>'.__('Warning: ').'</strong>'.__('<code>/system/config/</code> directory is not writable. Please change the directory properties or create the connect.inc.php manually (you can see connect.inc.default.php)');
			}
		} else {
			if (!file_exists(CONF_PATH.'config.inc.default.php')) {
				$notice = '<strong>'.__('Warning: ').'</strong>'.__('Sorry, I need a /system/config/config.inc.default.php file to work from. Please re-upload this file from your DCRM installation.');
			} elseif (!file_exists(CONF_PATH.'gnupg.inc.default.php')) {
				$notice = '<strong>'.__('Warning: ').'</strong>'.__('Sorry, I need a /system/config/gnupg.inc.default.php file to work from. Please re-upload this file from your DCRM installation.');
			} elseif (!file_exists(CONF_PATH.'autofill.inc.default.php')) {
				$notice = '<strong>'.__('Warning: ').'</strong>'.__('Sorry, I need a /system/config/autofill.inc.default.php file to work from. Please re-upload this file from your DCRM installation.');
			} elseif (!is_writable(CONF_PATH)) {
				$notice = '<strong>'.__('Warning: ').'</strong>'.__('<code>/system/config/</code> directory is not writable. Please change the directory properties.');
			}
		}
	}
	return $notice;
}

/** 
 * utf-8 转unicode 
 * 
 * @param string $name 
 * @return string 
 */  
function utf8_unicode($name){  
	$name = iconv('UTF-8', 'UCS-2', $name);  
	$len  = strlen($name);  
	$str  = '';  
	for ($i = 0; $i < $len - 1; $i = $i + 2){  
		$c  = $name[$i];  
		$c2 = $name[$i + 1];  
		if (ord($c) > 0){   //两个字节的文字  
			$str .= '\u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);  
			//$str .= base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);  
		} else {  
			$str .= '\u'.str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);  
			//$str .= str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);  
		}  
	}  
	//$str = strtoupper($str);//转换为大写  
	return $str;  
}

/**
 * Properly strip all HTML tags including script and style
 *
 * This differs from strip_tags() because it removes the contents of
 * the `<script>` and `<style>` tags. E.g. `strip_tags( '<script>something</script>' )`
 * will return 'something'. wp_strip_all_tags will return ''
 *
 * @param string $string String containing HTML tags
 * @param bool $remove_breaks optional Whether to remove left over line breaks and white space chars
 * @return string The processed string.
 */
function strip_all_tags($string, $remove_breaks = false) {
	$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
	$string = strip_tags($string);

	if ( $remove_breaks )
		$string = preg_replace('/[\r\n\t ]+/', ' ', $string);

	return trim( $string );
}

/**
 * Sanitizes a username, stripping out unsafe characters.
 *
 * Removes tags, octets, entities, and if strict is enabled, will only keep
 * alphanumeric, _, space, ., -, @. After sanitizing, it passes the username,
 * raw username (the username in the parameter), and the value of $strict as
 * parameters for the 'sanitize_user' filter.
 *
 * @param string $username The username to be sanitized.
 * @param bool $strict If set limits $username to specific characters. Default false.
 * @return string The sanitized username, after passing through filters.
 */
function sanitize_user( $username, $strict = false ) {
	$raw_username = $username;
	$username = strip_all_tags( $username );
	// Kill octets
	$username = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $username );
	$username = preg_replace( '/&.+?;/', '', $username ); // Kill entities

	// If strict, reduce to ASCII for max portability.
	if ( $strict )
		$username = preg_replace( '|[^a-z0-9 _.\-@]|i', '', $username );

	$username = trim( $username );
	// Consolidate contiguous whitespace
	$username = preg_replace( '|\s+|', ' ', $username );

	return $raw_username;
}

/**
 * Verifies that an email is valid.
 *
 * Does not grok i18n domains. Not RFC compliant.
 *
 * @param string $email Email address to verify.
 * @return string|bool Either false or the valid email address.
 */
function is_email( $email, $deprecated = false ) {
	// Test for the minimum length the email can be
	if ( strlen( $email ) < 3 ) {
		return false; //email_too_short
	}

	// Test for an @ character after the first position
	if ( strpos( $email, '@', 1 ) === false ) {
		return false; //email_no_at
	}

	// Split out the local and domain parts
	list( $local, $domain ) = explode( '@', $email, 2 );

	// LOCAL PART
	// Test for invalid characters
	if ( !preg_match( '/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+$/', $local ) ) {
		return false; //local_invalid_chars
	}

	// DOMAIN PART
	// Test for sequences of periods
	if ( preg_match( '/\.{2,}/', $domain ) ) {
		return false; //domain_period_sequence
	}

	// Test for leading and trailing periods and whitespace
	if ( trim( $domain, " \t\n\r\0\x0B." ) !== $domain ) {
		return false; //domain_period_limits
	}

	// Split the domain into subs
	$subs = explode( '.', $domain );

	// Assume the domain will have at least two subs
	if ( 2 > count( $subs ) ) {
		return false; //domain_no_periods
	}

	// Loop through each sub
	foreach ( $subs as $sub ) {
		// Test for leading and trailing hyphens and whitespace
		if ( trim( $sub, " \t\n\r\0\x0B-" ) !== $sub ) {
			return false; //sub_hyphen_limits
		}

		// Test for invalid characters
		if ( !preg_match('/^[a-z0-9-]+$/i', $sub ) ) {
			return false; //sub_invalid_chars
		}
	}

	// Congratulations your email made it!
	return true;
}

function dcrm_slash( $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $k => $v ) {
			if ( is_array( $v ) ) {
				$value[$k] = dcrm_slash( $v );
			} else {
				$value[$k] = addslashes( $v );
			}
		}
	} else {
		$value = addslashes( $value );
	}

	return $value;
}

function dcrm_unslash( $value ) {
	if ( is_array($value) ) {
		$value = array_map('dcrm_unslash', $value);
	} elseif ( is_object($value) ) {
		$vars = get_object_vars( $value );
		foreach ($vars as $key=>$data) {
			$value->{$key} = dcrm_unslash( $data );
		}
	} elseif ( is_string( $value ) ) {
		$value = stripslashes($value);
	}

	return $value;
}

/**
 * Generates a random number
 *
 * @param int $min Lower limit for the generated number
 * @param int $max Upper limit for the generated number
 * @return int A random number between min and max
 */
function dcrm_rand( $min = 0, $max = 0 ) {
	global $rnd_value;

	// Reset $rnd_value after 14 uses
	// 32(md5) + 40(sha1) + 40(sha1) / 8 = 14 random numbers from $rnd_value
	if ( strlen($rnd_value) < 8 ) {
		static $seed = '';
		$rnd_value = md5( uniqid(microtime() . mt_rand(), true ) . $seed );
		$rnd_value .= sha1($rnd_value);
		$rnd_value .= sha1($rnd_value . $seed);
		$seed = md5($seed . $rnd_value);
	}

	// Take the first 8 digits for our value
	$value = substr($rnd_value, 0, 8);

	$rnd_value = substr($rnd_value, 8);

	$value = abs(hexdec($value));

	// Some misconfigured 32bit environments (Entropy PHP, for example) truncate integers larger than PHP_INT_MAX to PHP_INT_MAX rather than overflowing them to floats.
	$max_random_number = 3000000000 === 2147483647 ? (float) "4294967295" : 4294967295; // 4294967295 = 0xffffffff

	// Reduce the value to be within the min - max range
	if ( $max != 0 )
		$value = $min + ( $max - $min + 1 ) * $value / ( $max_random_number + 1 );

	return abs(intval($value));
}

/**
 * Generates a random password drawn from the defined set of characters.
 *
 * @param int  $length              Optional. The length of password to generate. Default 12.
 * @param bool $special_chars       Optional. Whether to include standard special characters.
 *                                  Default true.
 * @param bool $extra_special_chars Optional. Whether to include other special characters.
 *                                  Used when generating secret keys and salts. Default false.
 * @return string The random password.
 */
function dcrm_generate_password( $length = 12, $special_chars = true, $extra_special_chars = false ) {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	if ( $special_chars )
		$chars .= '!@#$%^&*()';
	if ( $extra_special_chars )
		$chars .= '-_ []{}<>~`+=,.;:/?|';

	$password = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$password .= substr($chars, dcrm_rand(0, strlen($chars) - 1), 1);
	}

	return $password;
}

/**
 * Checks for invalid UTF8 in a string.
 *
 * @param string $string The text which is to be checked.
 * @param boolean $strip Optional. Whether to attempt to strip out invalid UTF8. Default is false.
 * @return string The checked text.
 */
function check_invalid_utf8( $string, $strip = false ) {
	$string = (string) $string;

	if ( 0 === strlen( $string ) ) {
		return '';
	}

	// Check for support for utf8 in the installed PCRE library once and store the result in a static
	static $utf8_pcre;
	if ( !isset( $utf8_pcre ) ) {
		$utf8_pcre = @preg_match( '/^./u', 'a' );
	}
	// We can't demand utf8 in the PCRE installation, so just return the string in those cases
	if ( !$utf8_pcre ) {
		return $string;
	}

	// preg_match fails when it encounters invalid UTF8 in $string
	if ( 1 === @preg_match( '/^./us', $string ) ) {
		return $string;
	}

	// Attempt to strip the bad chars if requested (not recommended)
	if ( $strip && function_exists( 'iconv' ) ) {
		return iconv( 'utf-8', 'utf-8', $string );
	}

	return '';
}

/**
 * Converts a number of HTML entities into their special characters.
 *
 * Specifically deals with: &, <, >, ", and '.
 *
 * $quote_style can be set to ENT_COMPAT to decode " entities,
 * or ENT_QUOTES to do both " and '. Default is ENT_NOQUOTES where no quotes are decoded.
 *
 * @param string $string The text which is to be decoded.
 * @param mixed $quote_style Optional. Converts double quotes if set to ENT_COMPAT, both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES. Also compatible with old _wp_specialchars() values; converting single quotes if set to 'single', double if set to 'double' or both if otherwise set. Default is ENT_NOQUOTES.
 * @return string The decoded text without HTML entities.
 */
function dcrm_specialchars_decode( $string, $quote_style = ENT_NOQUOTES ) {
	$string = (string) $string;

	if ( 0 === strlen( $string ) ) {
		return '';
	}

	// Don't bother if there are no entities - saves a lot of processing
	if ( strpos( $string, '&' ) === false ) {
		return $string;
	}

	// Match the previous behaviour of _wp_specialchars() when the $quote_style is not an accepted value
	if ( empty( $quote_style ) ) {
		$quote_style = ENT_NOQUOTES;
	} elseif ( !in_array( $quote_style, array( 0, 2, 3, 'single', 'double' ), true ) ) {
		$quote_style = ENT_QUOTES;
	}

	// More complete than get_html_translation_table( HTML_SPECIALCHARS )
	$single = array( '&#039;'  => '\'', '&#x27;' => '\'' );
	$single_preg = array( '/&#0*39;/'  => '&#039;', '/&#x0*27;/i' => '&#x27;' );
	$double = array( '&quot;' => '"', '&#034;'  => '"', '&#x22;' => '"' );
	$double_preg = array( '/&#0*34;/'  => '&#034;', '/&#x0*22;/i' => '&#x22;' );
	$others = array( '&lt;'   => '<', '&#060;'  => '<', '&gt;'   => '>', '&#062;'  => '>', '&amp;'  => '&', '&#038;'  => '&', '&#x26;' => '&' );
	$others_preg = array( '/&#0*60;/'  => '&#060;', '/&#0*62;/'  => '&#062;', '/&#0*38;/'  => '&#038;', '/&#x0*26;/i' => '&#x26;' );

	if ( $quote_style === ENT_QUOTES ) {
		$translation = array_merge( $single, $double, $others );
		$translation_preg = array_merge( $single_preg, $double_preg, $others_preg );
	} elseif ( $quote_style === ENT_COMPAT || $quote_style === 'double' ) {
		$translation = array_merge( $double, $others );
		$translation_preg = array_merge( $double_preg, $others_preg );
	} elseif ( $quote_style === 'single' ) {
		$translation = array_merge( $single, $others );
		$translation_preg = array_merge( $single_preg, $others_preg );
	} elseif ( $quote_style === ENT_NOQUOTES ) {
		$translation = $others;
		$translation_preg = $others_preg;
	}

	// Remove zero padding on numeric entities
	$string = preg_replace( array_keys( $translation_preg ), array_values( $translation_preg ), $string );

	// Replace characters according to translation table
	return strtr( $string, $translation );
}

/**
 * Converts a number of special characters into their HTML entities.
 *
 * Specifically deals with: &, <, >, ", and '.
 *
 * $quote_style can be set to ENT_COMPAT to encode " to
 * &quot;, or ENT_QUOTES to do both. Default is ENT_NOQUOTES where no quotes are encoded.
 *
 * @access private
 *
 * @param string $string The text which is to be encoded.
 * @param int $quote_style Optional. Converts double quotes if set to ENT_COMPAT, both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES. Also compatible with old values; converting single quotes if set to 'single', double if set to 'double' or both if otherwise set. Default is ENT_NOQUOTES.
 * @param string $charset Optional. The character encoding of the string. Default is false.
 * @param boolean $double_encode Optional. Whether to encode existing html entities. Default is false.
 * @return string The encoded text with HTML entities.
 */
function _dcrm_specialchars( $string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false ) {
	$string = (string) $string;

	if ( 0 === strlen( $string ) )
		return '';

	// Don't bother if there are no specialchars - saves some processing
	if ( ! preg_match( '/[&<>"\']/', $string ) )
		return $string;

	// Account for the previous behaviour of the function when the $quote_style is not an accepted value
	if ( empty( $quote_style ) )
		$quote_style = ENT_NOQUOTES;
	elseif ( ! in_array( $quote_style, array( 0, 2, 3, 'single', 'double' ), true ) )
		$quote_style = ENT_QUOTES;

	$charset = 'UTF-8';

	$_quote_style = $quote_style;

	if ( $quote_style === 'double' ) {
		$quote_style = ENT_COMPAT;
		$_quote_style = ENT_COMPAT;
	} elseif ( $quote_style === 'single' ) {
		$quote_style = ENT_NOQUOTES;
	}

	// Handle double encoding ourselves
	if ( $double_encode ) {
		$string = @htmlspecialchars( $string, $quote_style, $charset );
	} else {
		// Decode &amp; into &
		$string = dcrm_specialchars_decode( $string, $_quote_style );

		// Guarantee every &entity; is valid or re-encode the &
		$string = dcrm_kses_normalize_entities( $string );

		// Now re-encode everything except &entity;
		$string = preg_split( '/(&#?x?[0-9a-z]+;)/i', $string, -1, PREG_SPLIT_DELIM_CAPTURE );

		for ( $i = 0; $i < count( $string ); $i += 2 )
			$string[$i] = @htmlspecialchars( $string[$i], $quote_style, $charset );

		$string = implode( '', $string );
	}

	// Backwards compatibility
	if ( 'single' === $_quote_style )
		$string = str_replace( "'", '&#039;', $string );

	return $string;
}

$allowedentitynames = array(
	'nbsp',    'iexcl',  'cent',    'pound',  'curren', 'yen',
	'brvbar',  'sect',   'uml',     'copy',   'ordf',   'laquo',
	'not',     'shy',    'reg',     'macr',   'deg',    'plusmn',
	'acute',   'micro',  'para',    'middot', 'cedil',  'ordm',
	'raquo',   'iquest', 'Agrave',  'Aacute', 'Acirc',  'Atilde',
	'Auml',    'Aring',  'AElig',   'Ccedil', 'Egrave', 'Eacute',
	'Ecirc',   'Euml',   'Igrave',  'Iacute', 'Icirc',  'Iuml',
	'ETH',     'Ntilde', 'Ograve',  'Oacute', 'Ocirc',  'Otilde',
	'Ouml',    'times',  'Oslash',  'Ugrave', 'Uacute', 'Ucirc',
	'Uuml',    'Yacute', 'THORN',   'szlig',  'agrave', 'aacute',
	'acirc',   'atilde', 'auml',    'aring',  'aelig',  'ccedil',
	'egrave',  'eacute', 'ecirc',   'euml',   'igrave', 'iacute',
	'icirc',   'iuml',   'eth',     'ntilde', 'ograve', 'oacute',
	'ocirc',   'otilde', 'ouml',    'divide', 'oslash', 'ugrave',
	'uacute',  'ucirc',  'uuml',    'yacute', 'thorn',  'yuml',
	'quot',    'amp',    'lt',      'gt',     'apos',   'OElig',
	'oelig',   'Scaron', 'scaron',  'Yuml',   'circ',   'tilde',
	'ensp',    'emsp',   'thinsp',  'zwnj',   'zwj',    'lrm',
	'rlm',     'ndash',  'mdash',   'lsquo',  'rsquo',  'sbquo',
	'ldquo',   'rdquo',  'bdquo',   'dagger', 'Dagger', 'permil',
	'lsaquo',  'rsaquo', 'euro',    'fnof',   'Alpha',  'Beta',
	'Gamma',   'Delta',  'Epsilon', 'Zeta',   'Eta',    'Theta',
	'Iota',    'Kappa',  'Lambda',  'Mu',     'Nu',     'Xi',
	'Omicron', 'Pi',     'Rho',     'Sigma',  'Tau',    'Upsilon',
	'Phi',     'Chi',    'Psi',     'Omega',  'alpha',  'beta',
	'gamma',   'delta',  'epsilon', 'zeta',   'eta',    'theta',
	'iota',    'kappa',  'lambda',  'mu',     'nu',     'xi',
	'omicron', 'pi',     'rho',     'sigmaf', 'sigma',  'tau',
	'upsilon', 'phi',    'chi',     'psi',    'omega',  'thetasym',
	'upsih',   'piv',    'bull',    'hellip', 'prime',  'Prime',
	'oline',   'frasl',  'weierp',  'image',  'real',   'trade',
	'alefsym', 'larr',   'uarr',    'rarr',   'darr',   'harr',
	'crarr',   'lArr',   'uArr',    'rArr',   'dArr',   'hArr',
	'forall',  'part',   'exist',   'empty',  'nabla',  'isin',
	'notin',   'ni',     'prod',    'sum',    'minus',  'lowast',
	'radic',   'prop',   'infin',   'ang',    'and',    'or',
	'cap',     'cup',    'int',     'sim',    'cong',   'asymp',
	'ne',      'equiv',  'le',      'ge',     'sub',    'sup',
	'nsub',    'sube',   'supe',    'oplus',  'otimes', 'perp',
	'sdot',    'lceil',  'rceil',   'lfloor', 'rfloor', 'lang',
	'rang',    'loz',    'spades',  'clubs',  'hearts', 'diams',
	'sup1',    'sup2',   'sup3',    'frac14', 'frac12', 'frac34',
	'there4',
);

/**
 * Callback for dcrm_kses_normalize_entities() regular expression.
 *
 * This function only accepts valid named entity references, which are finite,
 * case-sensitive, and highly scrutinized by HTML and XML validators.
 *
 * @param array $matches preg_replace_callback() matches array
 * @return string Correctly encoded entity
 */
function dcrm_kses_named_entities($matches) {
	global $allowedentitynames;

	if ( empty($matches[1]) )
		return '';

	$i = $matches[1];
	return ( ( ! in_array($i, $allowedentitynames) ) ? "&amp;$i;" : "&$i;" );
}

/**
 * Helper function to determine if a Unicode value is valid.
 *
 * @param int $i Unicode value
 * @return bool True if the value was a valid Unicode number
 */
function valid_unicode($i) {
	return ( $i == 0x9 || $i == 0xa || $i == 0xd ||
			($i >= 0x20 && $i <= 0xd7ff) ||
			($i >= 0xe000 && $i <= 0xfffd) ||
			($i >= 0x10000 && $i <= 0x10ffff) );
}

/**
 * Converts and fixes HTML entities.
 *
 * This function normalizes HTML entities. It will convert `AT&T` to the correct
 * `AT&amp;T`, `&#00058;` to `&#58;`, `&#XYZZY;` to `&amp;#XYZZY;` and so on.
 *
 * @param string $string Content to normalize entities
 * @return string Content with normalized entities
 */
function dcrm_kses_normalize_entities($string) {
	# Disarm all entities by converting & to &amp;

	$string = str_replace('&', '&amp;', $string);

	# Change back the allowed entities in our entity whitelist

	$string = preg_replace_callback('/&amp;([A-Za-z]{2,8}[0-9]{0,2});/', 'dcrm_kses_named_entities', $string);
	$string = preg_replace_callback('/&amp;#(0*[0-9]{1,7});/', 'dcrm_kses_normalize_entities2', $string);
	$string = preg_replace_callback('/&amp;#[Xx](0*[0-9A-Fa-f]{1,6});/', 'dcrm_kses_normalize_entities3', $string);

	return $string;
}

/**
 * Callback for dcrm_kses_normalize_entities() regular expression.
 *
 * This function helps {@see dcrm_kses_normalize_entities()} to only accept 16-bit
 * values and nothing more for `&#number;` entities.
 *
 * @access private
 *
 * @param array $matches preg_replace_callback() matches array
 * @return string Correctly encoded entity
 */
function dcrm_kses_normalize_entities2($matches) {
	if ( empty($matches[1]) )
		return '';

	$i = $matches[1];
	if (valid_unicode($i)) {
		$i = str_pad(ltrim($i,'0'), 3, '0', STR_PAD_LEFT);
		$i = "&#$i;";
	} else {
		$i = "&amp;#$i;";
	}

	return $i;
}

/**
 * Callback for dcrm_kses_normalize_entities() for regular expression.
 *
 * This function helps dcrm_kses_normalize_entities() to only accept valid Unicode
 * numeric entities in hex form.
 *
 * @access private
 *
 * @param array $matches preg_replace_callback() matches array
 * @return string Correctly encoded entity
 */
function dcrm_kses_normalize_entities3($matches) {
	if ( empty($matches[1]) )
		return '';

	$hexchars = $matches[1];
	return ( ( ! valid_unicode(hexdec($hexchars)) ) ? "&amp;#x$hexchars;" : '&#x'.ltrim($hexchars,'0').';' );
}

/**
 * Escaping for HTML blocks.
 *
 * @param string $text
 * @return string
 */
function esc_html( $text ) {
	$safe_text = check_invalid_utf8( $text );
	$safe_text = _dcrm_specialchars( $safe_text, ENT_QUOTES );

	return $safe_text;
}
?>
