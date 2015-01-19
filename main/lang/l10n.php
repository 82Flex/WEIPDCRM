<?php
/**
 * DCRM Localization System
 * Base On WordPress 4.1 Translation API
 *
 * Modify by Hintay
 * 
 * Keywords: __ , _e , _n:1,2 , _x:1,2c , _ex:1,2c , _nx:4c,1,2 , esc_attr__ , 
 * esc_attr_e , esc_attr_x:1,2c , esc_html__ , esc_html_e , esc_html_x:1,2c , 
 * _n_noop:1,2 , _nx_noop:3c,1,2 , __ngettext_noop:1,2
 *
 * More about gettext keywords: http://poedit.net/trac/wiki/Doc/Keywords
 */

define('LANG_DIR', dirname(__FILE__));
/**
 * Get the current locale.
 *
 * If the locale is set, then it will filter the locale in the 'locale' filter
 * hook and return the value.
 *
 * If the locale is not set already, then the WPLANG constant is used if it is
 * defined. Then it is filtered through the 'locale' filter hook and the value
 * for the locale global set and the locale is returned.
 *
 * The process to get the locale should only be done once, but the locale will
 * always be filtered using the 'locale' hook.
 *
 * @since 1.5.0
 *
 * @return string The locale of the blog or from the 'locale' hook.
 */
if ( !function_exists( 'get_locale' ) ):
function get_locale() {
	global $locale;
	global $localetype;

	if ( isset( $locale ) ) {
		return $locale;
	}

	if ( defined( 'DCRM_LANG' ) ) {
		$locale = DCRM_LANG;
		return $locale;
	}

	if ( !isset( $localetype ) ) {
		$localetype = '';
	} else {
		$localetype = $localetype . '-';
	}

	$localelist = get_browser_languages();

	if ( !empty( $localelist ) ){
		// 先按浏览器语言列表匹配文件
		foreach ( $localelist as $singlelocale ) {
			if( file_exists( LANG_DIR . '/' . $localetype . $singlelocale . '.mo' ) ) {
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
			if( file_exists( LANG_DIR . '/' . $localetype . $singlelang . '.mo' ) ) {
				$locale = $singlelang;
				return $locale;
			}
		}

		// 检查近似语言，例如找不到 zh_TW 时寻找 zh_CN
		$files = scandir( LANG_DIR . '/' );
		foreach ( $files as $file ) {
			if ( '.' === $file[0] || is_dir( $file ) ) {
				continue;
			}
			foreach ( $langlist as $singlelang ) {
				if ( preg_match( '/(?:(.+)-)?' . $localetype . $singlelang . '_([A-Za-z_]{1,4}).mo/', $file, $match ) ) {
					$similarlang = $match;
					$locale = $singlelang . '_' . $similarlang;
					return $locale;
				}
			}
		}
	}

	$locale = 'zh_CN';
	return $locale;
}
endif;

/**
 * Get the browser languages.
 * 获取浏览器设定的语言列表，并做预处理
 *
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
 * Retrieve the translation of $text.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * *Note:* Don't use {@see translate()} directly, use `{@see __()} or related functions.
 *
 * @since 2.2.0
 *
 * @param string $text   Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
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
	if ( false == $last_bar )
		return $string;
	else
		return substr( $string, 0, $last_bar );
}

/**
 * Retrieve the translation of $text in the context defined in $context.
 *
 * If there is no translation, or the text domain isn't loaded the original
 * text is returned.
 *
 * @since 2.8.0
 *
 * @param string $text    Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text on success, original text on failure.
 */
function translate_with_gettext_context( $text, $context, $domain = 'default' ) {
	$translations = get_translations_for_domain( $domain );
	$translations = $translations->translate( $text, $context );

	return $translations;
}

/**
 * Retrieve the translation of $text. If there is no translation,
 * or the text domain isn't loaded, the original text is returned.
 *
 * @since 2.1.0
 *
 * @param string $text   Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
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
 * @return string Translated text.
 */
function esc_html_x( $text, $context, $domain = 'default' ) {
	return esc_html( translate_with_gettext_context( $text, $context, $domain ) );
}

/**
 * Retrieve the plural or single form based on the supplied amount.
 *
 * If the text domain is not set in the $l10n list, then a comparison will be made
 * and either $plural or $single parameters returned.
 *
 * If the text domain does exist, then the parameters $single, $plural, and $number
 * will first be passed to the text domain's ngettext method. Then it will be passed
 * to the 'ngettext' filter hook along with the same parameters. The expected
 * type will be a string.
 *
 * @since 2.8.0
 *
 * @param string $single The text that will be used if $number is 1.
 * @param string $plural The text that will be used if $number is not 1.
 * @param int    $number The number to compare against to use either $single or $plural.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Either $single or $plural translated text.
 */
function _n( $single, $plural, $number, $domain = 'default' ) {
	$translations = get_translations_for_domain( $domain );
	$translation = $translations->translate_plural( $single, $plural, $number );

	return $translation;
}

/**
 * Retrieve the plural or single form based on the supplied amount with gettext context.
 *
 * This is a hybrid of _n() and _x(). It supports contexts and plurals.
 *
 * @since 2.8.0
 *
 * @param string $single  The text that will be used if $number is 1.
 * @param string $plural  The text that will be used if $number is not 1.
 * @param int    $number  The number to compare against to use either $single or $plural.
 * @param string $context Context information for the translators.
 * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Either $single or $plural translated text with context.
 */
function _nx($single, $plural, $number, $context, $domain = 'default') {
	$translations = get_translations_for_domain( $domain );
	$translation = $translations->translate_plural( $single, $plural, $number, $context );

	return $translation;
}

/**
 * Register plural strings in POT file, but don't translate them.
 *
 * Used when you want to keep structures with translatable plural
 * strings and use them later.
 *
 * Example:
 *
 *     $messages = array(
 *      	'post' => _n_noop( '%s post', '%s posts' ),
 *      	'page' => _n_noop( '%s pages', '%s pages' ),
 *     );
 *     ...
 *     $message = $messages[ $type ];
 *     $usable_text = sprintf( translate_nooped_plural( $message, $count ), $count );
 *
 * @since 2.5.0
 *
 * @param string $singular Single form to be i18ned.
 * @param string $plural   Plural form to be i18ned.
 * @param string $domain   Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return array array($singular, $plural)
 */
function _n_noop( $singular, $plural, $domain = null ) {
	return array( 0 => $singular, 1 => $plural, 'singular' => $singular, 'plural' => $plural, 'context' => null, 'domain' => $domain );
}

/**
 * Register plural strings with context in POT file, but don't translate them.
 *
 * @since 2.8.0
 * @param string $singular
 * @param string $plural
 * @param string $context
 * @param string|null $domain
 * @return array
 */
function _nx_noop( $singular, $plural, $context, $domain = null ) {
	return array( 0 => $singular, 1 => $plural, 2 => $context, 'singular' => $singular, 'plural' => $plural, 'context' => $context, 'domain' => $domain );
}

/**
 * Translate the result of _n_noop() or _nx_noop().
 *
 * @since 3.1.0
 *
 * @param array  $nooped_plural Array with singular, plural and context keys, usually the result of _n_noop() or _nx_noop()
 * @param int    $count         Number of objects
 * @param string $domain        Optional. Text domain. Unique identifier for retrieving translated strings. If $nooped_plural contains
 *                              a text domain passed to _n_noop() or _nx_noop(), it will override this value.
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

	$return = load_textdomain( 'default', LANG_DIR . "/$locale.mo" );

	if ( defined( 'DCRM_INSTALLING' ) || $localetype == 'init' ) {
		load_textdomain( 'default', LANG_DIR . "/init-$locale.mo" );
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
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @return NOOP_Translations A Translations instance.
 */
function get_translations_for_domain( $domain ) {
	global $l10n;
	if ( !isset( $l10n[$domain] ) ) {
		$l10n[$domain] = new NOOP_Translations;
	}
	return $l10n[$domain];
}

/**
 * Whether there are translations for the text domain.
 *
 * @since 3.0.0
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @return bool Whether there are translations.
 */
function is_textdomain_loaded( $domain ) {
	global $l10n;
	return isset( $l10n[$domain] );
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
		if ( 0 !== strpos( $lang_file, 'continents-cities' ) && 0 !== strpos( $lang_file, 'ms-' ) &&
			0 !== strpos( $lang_file, 'admin-' ))
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

include_once LANG_DIR . '/pomo/translations.php';
include_once LANG_DIR . '/pomo/mo.php';
load_default_textdomain();
