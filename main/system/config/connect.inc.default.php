<?php
if (!defined("DCRM")) {
	exit();
}

/**
 * The base configurations of the DCRM.
 * DCRM基础配置文件。
 *
 * This file has the following configurations: MySQL settings, Table Prefix.
 * 本文件包含以下配置选项：MySQL设置、数据库表名前缀。
 * You can get the MySQL settings from your web host.
 * MySQL设置具体信息请咨询您的空间提供商。
 *
 * This file is used by the connect.inc.php creation script during the installation.
 * 这个文件被安装程序用于自动生成connect.inc.php配置文件，
 * You can just copy this file to "connect.inc.php" and fill in the values.
 * 您可以手动复制这个文件，并重命名为“connect.inc.php”，然后填入相关信息。
 *
 */

// ** MySQL settings - You can get this info from your web host ** //
// ** MySQL 设置 - 具体信息来自您正在使用的主机 ** //
/** The name of the database for DCRM */
/** DCRM数据库的名称 */
define('DCRM_CON_DATABASE', 'cydia');

/** MySQL database username */
/** MySQL数据库用户名 */
define('DCRM_CON_USERNAME', 'root');

/** MySQL database password */
/** MySQL数据库密码 */
define('DCRM_CON_PASSWORD', '');

/** MySQL hostname */
/** MySQL主机 */
define('DCRM_CON_SERVER', 'localhost');

/** MySQL server port */
/** MySQL主机端口 */
define('DCRM_CON_SERVER_PORT', '3306');

/** Keep the connection to the database server? */
/** 保持与数据库服务器的连接? */
define('DCRM_CON_PCONNECT', false);

/**
 * DCRM Database Table prefix.
 * DCRM数据表前缀。
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 * 如果您有在同一数据库内安装多个DCRM的需求，请为每个DCRM设置
 * 不同的数据表前缀。前缀名只能为数字、字母加下划线。
 */
define('DCRM_CON_PREFIX', 'apt_');

/**
 * If the automatic detection function of the site directory is invalid, you can define it here.
 * Remember to add '/' before and after path. Example: /dcrm/
 * 若自动检测网站目录失效，您可以在此处定义。不要忘了在路径前后添加'/'。例子：/dcrm/
 */
//define('CUSTOM_SITEPATH', '/');

?>
