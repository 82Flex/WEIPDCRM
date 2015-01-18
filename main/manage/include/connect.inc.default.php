<?php
	if (!defined("DCRM")) {
		exit();
	}

/**
 * DCRM基础配置文件。
 *
 * 本文件包含以下配置选项：MySQL设置、数据库表名前缀。
 * MySQL设置具体信息请咨询您的空间提供商。
 *
 * 这个文件被安装程序用于自动生成connect.inc.php配置文件，
 * 您可以手动复制这个文件，并重命名为“connect.inc.php”，然后填入相关信息。
 *
 */

// ** MySQL 设置 - 具体信息来自您正在使用的主机 ** //
/** DCRM数据库的名称 */
define('DCRM_CON_DATABASE', 'cydia');

/** MySQL数据库用户名 */
define('DCRM_CON_USERNAME', 'root');

/** MySQL数据库密码 */
define('DCRM_CON_PASSWORD', '');

/** MySQL主机 */
define('DCRM_CON_SERVER', 'localhost');

/**
 * DCRM数据表前缀。
 *
 * 如果您有在同一数据库内安装多个DCRM的需求，请为每个DCRM设置
 * 不同的数据表前缀。前缀名只能为数字、字母加下划线。
 */
define('DCRM_CON_PREFIX', 'apt_');

?>
