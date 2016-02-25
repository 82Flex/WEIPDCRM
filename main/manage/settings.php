<?php
/**
 * DCRM System Settings
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

session_start();
define("DCRM", true);
$activeid = 'settings';

if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
	require_once("header.php");

	if (!isset($_GET['action'])) {

		function is_eng_array( $text , $context = null, $connector = '<br/>' ) {
			global $locale;

			$return = _x( $text, $context );
			if ( substr( $locale, 0, 2 ) != 'en' )
				$return .= $connector . $text;
			return $return;
		}

		function languages_options() {
			// Initialization
			global $locale;
			$return = array( is_eng_array('Detect', 'language' , ' - ' ) => 'Detect');
			$languages = get_available_languages();
			$languages_list = languages_list();
			$languages_self_list = languages_self_list();
			$is_en = substr( $locale, 0, 2 ) == 'en';

			foreach( $languages as $language ) {
				$return[] = array(
					isset($languages_self_list[$language]) ? $languages_self_list[$language] : $languages_list[$language] => array(
						(isset($languages_list[$language]) ? $languages_list[$language] : $language) . ($is_en ? '' : ' - '.$language ) => $language
					)
				);
			}
			if(!in_array('en', $languages) && !in_array('en_US', $languages) && !in_array('en_GB', $languages)){
				$return[] = array('English' => array(is_eng_array('English', 'language', ' - ') => 'en_US'));
			}

			return $return;
		}

		function get_option_value($option, $default){
			$value = get_option($option);
			return empty($value) ? $default : $value;
		}

		// TODO: Array for Settings.
		$options = array(
			array(
				'title'	=> __('General'),
				'id'	=> 'general',
				'type'	=> 'panelstart'
			),
			array(
				'title'	=> __('General'),
				'type'	=> 'subtitle'
			),
			array(
				'name'	=> is_eng_array('Language'),
				'desc'	=> is_eng_array('If you want the system auto detect users browser language to show pages please select "Detect" option.'),
				'id'	=> 'language',
				'type'	=> 'select',
				'options' => languages_options(),
				'optgroup'=> true,
				'std'	=> defined("DCRM_LANG") ? DCRM_LANG : 'Detect',
			),
			array(
				'name'	=> __('Rewrite Mod'),
				'desc'	=> sprintf(__('<b>Elegant Mod</b> - Enable all rewrite rules, the url will show like %s.<br/><b>Normal Mod</b> - Compatible earlier than v1.7 configuration, only enbale a part of rewrite rules for HotLinks.<br/><b>Disabled</b> - This will disable all rewrite rules, so HotLinks will not work.<br/>Notice: You should update your rewrite config first if you want to use Elegant Mod.'), '<code>'.htmlspecialchars(base64_decode(DCRM_REPOURL)).'packages/1</code>'),
				'id'	=> 'rewrite_mod',
				'type'	=> 'select',
				'options' => array(
					1 => __('Disable'),
					2 => __('Normal'),
					3 => __('Elegant')
				),
				'std'	=> get_option_value('rewrite_mod', 2),
			),
			array(
				'title'	=> __('Login Information'),
				'type'	=> 'subtitle'
			),
			array(
				'name'	=> __('Username'),
				'id'	=> 'username',
				'type'	=> 'text',
				'attributes' => array(
					'required' => 'required',
					'minlength' => 4,
					'maxlength' => 20,
					'data-validation-minlength-message' => __('Username length must be between 4-20 characters!'),
					'data-validation-regex-regex' => '^[0-9a-zA-Z\_]*$',
					'data-validation-regex-message' => __('Username can only use numbers, letters and underline!')
				),
				'std'	=> htmlspecialchars($_SESSION['username']),
			),
			array(
				'name'	=> __('New Password'),
				'id'	=> 'pass1',
				'type'	=> 'text',
				'desc'	=> __('If you would like to change the password type a new one. Otherwise leave this blank.'),
			),
			array(
				'name'	=> __('Repeat New Password'),
				'id'	=> 'pass2',
				'type'	=> 'text',
				'desc'	=> __('Type your new password again.'),
				'attributes' => array(
					'data-validation-match-match' => 'pass1',
					'data-validation-match-message' => __('Your password do not match.'),
				),
				'special' => 'pass-strength-result',
			),
		);

		function show_select($variable, $false_text = '', $true_text = ''){
			$select_array = array(($_false ? $_false : 1) => ($false_text ? $false_text : __('Disabled')), ($_true ? $_true : 2) => ($true_text ? $true_text : __('Enabled')));

			foreach($select_array as $key => $value) {
				echo "<option value=\"$key\" ".($key == $variable ? 'selected="selected"' : '').">$value</option>\n";
			}
		}
?>
				<h2><?php _e( 'Preferences' ); ?></h2>
				<br />
				<form class="form-horizontal settingsform" method="POST" action="settings.php?action=set">
					<fieldset>
						<h3><?php _e( 'General' ); ?></h3>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e( 'Language' ); if ( substr( $locale, 0, 2 ) != 'en' ) { ?><br /> Language<?php } ?></label>
							<div class="controls">
								<select name="language" class="language">
<?php
		$languages = get_available_languages();
		$langtext = '<option value="Detect"';
		if (defined("DCRM_LANG") && DCRM_LANG == 'Detect')
			$langtext .= ' selected="selected"';
		$langtext .= '>'._x( 'Detect', 'language' );
		if ( substr( $locale, 0, 2 ) != 'en' )
			$langtext .= ' - Detect';
		$langtext .= "</option>\n";

		$languages_list = languages_list();
		$languages_self_list = languages_self_list();
		foreach( $languages as $language ) {
			$langtext .= '<optgroup label="';
			$langtext .= isset($languages_self_list[$language]) ? $languages_self_list[$language] : $languages_list[$language];
			$langtext .= '">';
			$langtext .= "<option value=\"$language\"";
			if (defined("DCRM_LANG") && DCRM_LANG == $language)
				$langtext .= ' selected="selected"';
			$langtext .= '>';
			$langtext .= isset($languages_list[$language]) ? $languages_list[$language] : $language;
			$langtext .= " - " . $language . "</option></optgroup>\n";
		}

		if(!in_array('en', $languages) && !in_array('en_US', $languages) && !in_array('en_GB', $languages)){
			$langtext .= '<optgroup label="English"><option value="en_US"';
			if (defined("DCRM_LANG") && DCRM_LANG == 'en_US')
				$langtext .= ' selected="selected"';
			$langtext .= '>'._x('English', 'language')." - en_US</option></optgroup>\n";
		}
		echo $langtext;
?>
								</select>
								<p class="help-block"><?php _e('If you want the system auto detect users browser language to show pages please select "Detect" option.'); if ( substr( $locale, 0, 2 ) != 'en' ) { ?><br />If you want the system auto detect users browser language to show pages please select "Detect" option.<?php } ?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Rewrite Mod');?></label>
							<div class="controls">
								<select name="rewrite_mod">
<?php
		$options = array(1 => __('Disable'), 2 => __('Normal'), 3 => __('Elegant'));
		$rewrite_mod = get_option('rewrite_mod');
		if(empty($rewrite_mod)) $rewrite_mod = 2;
		foreach($options as $key => $value){
			echo '<option value="' . $key . '"'.($rewrite_mod == $key ? ' selected="selected"' : '').'>' . htmlspecialchars($value) . "</option>\n";
		}
?>
								</select>
								<p class="help-block"><?php printf(__('<b>Elegant Mod</b> - Enable all rewrite rules, the url will show like %s.<br/><b>Normal Mod</b> - Compatible earlier than v1.7 configuration, only enbale a part of rewrite rules for HotLinks.<br/><b>Disabled</b> - This will disable all rewrite rules, so HotLinks will not work.<br/>Notice: You should update your rewrite config first if you want to use Elegant Mod.'), '<code>'.htmlspecialchars(base64_decode(DCRM_REPOURL)).'packages/1</code>'); ?></p>
								</div>
						</div>
						<br />
						<h3><?php _e('Login Information');?></h3>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Username');?></label>
							<div class="controls">
								<input type="text" required="required" name="username" minlength="4" maxlength="20" data-validation-minlength-message="<?php _e('Username length must be between 4-20 characters!'); ?>" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" data-validation-regex-regex="^[0-9a-zA-Z\_]*$" data-validation-regex-message="<?php _e('Username can only use numbers, letters and underline!'); ?>" />
								<p class="help-block"></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('New Password');?></label>
							<div class="controls">
								<input type="password" name="pass1" id="pass1"/>
								<p class="help-block"><?php _e('If you would like to change the password type a new one. Otherwise leave this blank.'); ?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Repeat New Password');?></label>
							<div class="controls">
								<input type="password" name="pass2" id="pass2" data-validation-match-match="pass1" data-validation-match-message="<?php _e('Your password do not match.'); ?>"/>
								<p class="help-block"><?php _e('Type your new password again.');?></p>
								<div id="pass-strength-result" style="display: block;"><?php _e('Strength indicator'); ?></div>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('The maximum number of attempts');?></label>
							<div class="controls">
								<input type="number" required="required" name="trials" value="<?php echo htmlspecialchars(DCRM_MAXLOGINFAIL); ?>"/>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Login Fail Reset Time');?></label>
							<div class="controls">
								<input type="number" required="required" name="resettime" value="<?php if(defined(DCRM_LOGINFAILRESETTIME)){echo(htmlspecialchars(DCRM_LOGINFAILRESETTIME)/60);}else{echo(10);} ?>"/>
								<p class="help-block"><?php _e('Unit: Minutes');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label" style="color: red;"><?php _e( 'Repository URL' ); ?></label>
							<div class="controls">
								<input type="text" required="required" name="url_repo" style="width: 400px;" data-validation-regex-regex="((https?):\/\/)?([a-z]([a-z0-9\-]*[\.])+([a-z]*)|(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))(\/[a-z0-9_\-\.~]+)*(\/([a-z0-9_\-\.]*)(\?[a-z0-9+_\-\.%=&]*)?)?" data-validation-regex-message="<?php _e('Not a valid website address'); ?>" value="<?php echo htmlspecialchars(base64_decode(DCRM_REPOURL)); ?>"/>
								<p class="help-block"><?php _e( 'Displayed on the homepage for the user to add, and used for the autofill package\'s Depiction when import.' ); ?></p>
							</div>
						</div>
						<br />
						<h3><?php _e('PC Site');?></h3>
						<br />
						<div class="control-group">
							<label class="control-label" style="color: red;"><?php _e('Master Switch');?></label>
							<div class="controls">
								<select name="pcindex">
									<?php show_select(DCRM_PCINDEX); ?>
								</select>
								<p class="help-block"><?php _e('Non-apple owners will automatically jump if enabled.');?></p>
							</div>
						</div>
						<br />
						<h3><?php _e('Mobile Site');?></h3>
						<br />
						<div class="control-group">
							<label class="control-label" style="color: red;"><?php _e('Master Switch');?></label>
							<div class="controls">
								<select name="mobile">
									<?php show_select(DCRM_MOBILE); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Show Latest List');?></label>
							<div class="controls">
								<select name="list">
									<?php show_select(DCRM_SHOWLIST); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Number of Latest List');?></label>
							<div class="controls">
								<input type="number" name="listnum" max="20" data-validation-max-message="<?php printf(__('Too high: Maximum of \'%s\''), '20'); ?>" value="<?php echo htmlspecialchars(DCRM_SHOW_NUM); ?>"/>
								<p class="help-block"><?php _e('Must be lower than 20.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Full List of Sections');?></label>
							<div class="controls">
								<select name="allowfulllist">
									<?php show_select(DCRM_ALLOW_FULLLIST); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _ex('Screenshorts', 'Settings');?></label>
							<div class="controls">
								<select name="screenshots">
									<?php show_select(DCRM_SCREENSHOTS); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Report Problems');?></label>
							<div class="controls">
								<select name="reporting">
									<?php show_select(DCRM_REPORTING); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Limit Number of Report');?></label>
							<div class="controls">
								<input type="number" name="reportlimit" max="10" data-validation-max-message="<?php printf(__('Too high: Maximum of \'%s\''), '10'); ?>" value="<?php echo htmlspecialchars(DCRM_REPORT_LIMIT); ?>"/>
								<p class="help-block"><?php _e('The maximum not more than 10 times.'); ?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Update Logs');?></label>
							<div class="controls">
								<select name="updatelogs">
									<?php show_select(DCRM_UPDATELOGS); ?>
								</select>
								<p class="help-block"><?php _e('Displayed in the Version History.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('More Information');?></label>
							<div class="controls">
								<select name="moreinfo">
									<?php show_select(DCRM_MOREINFO); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Description'); ?></label>
							<div class="controls">
								<select name="description">
									<?php show_select((defined(DCRM_DESCRIPTION)?DCRM_DESCRIPTION:2), __('Hide'), __('Show')); ?>
								</select>
								<p class="help-block"><?php _e('It will not hide if \'Detailed Description\' is enabled and empty.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group" id="multiinfo">
							<label class="control-label"><?php _e('Detailed Description'); ?></label>
							<div class="controls">
								<select name="multiinfo">
									<?php show_select(DCRM_MULTIINFO, __('Hide'), __('Show')); ?>
								</select>
							</div>
						</div>
						<br />
						<h3><?php _e('Download');?></h3>
						<br />
						<div class="control-group">
							<label class="control-label" style="color: red;"><?php _e('PHP Forward');?></label>
							<div class="controls">
								<select name="php_forward">
									<?php $php_forward = get_option('php_forward'); if(empty($php_forward)) $php_forward = 2; show_select($php_forward); ?>
								</select>
								<p class="help-block"><?php _e('Disable PHP forward will reduce server pressure and effectively avoid \'Sum Mismatch Hash\' error.<br/>But it will make the speed limit function to invalid and may exposure real package address.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Module Enabled');?></label>
							<div class="controls">
								<select name="module_enabled">
									<?php $module_enabled = get_option('module_enabled'); if(empty($module_enabled)) $module_enabled = 1; show_select($module_enabled, __('No'), __('Yes')); ?>
								</select>
								<p class="help-block"><?php _e('Using this switch, the x-sendfile feature on DCRM can be manually enabled if it does not do so automatically <br/>when you have enabled mod_xsendfile on Apache or configured the "allow-x-send-file" option on Lighttpd. <br><b>WARNING:</b> If you don\'t know what this is, please turn this off; otherwise, package downloads may not work.'); ?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('HotLink Protection');?></label>
							<div class="controls">
								<select name="directdown">
									<?php show_select(DCRM_DIRECT_DOWN); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label" style="color: red;"><?php _e('Max Download Speed');?></label>
							<div class="controls">
								<input type="number" required="required" name="speedlimit" value="<?php echo htmlspecialchars(DCRM_SPEED_LIMIT); ?>"/>
								<p class="help-block"><?php _e('B/s, please input \'0\' if you do not want limit.');?></p>
							</div>
						</div>
						<br />
						<h3><?php _e('Lists');?></h3>
						<br />
						<div class="control-group">
							<label class="control-label" style="color: red;"><?php _e('Packages File Compression');?></label>
							<div class="controls">
								<select name="listsmethod">
<?php
		$options = array(__('Hide list'), __('Only text'), __('Only gz'), __('Text and gz'),
						 __('Only bz2'), __('Text and bz2'), __('gz and bz2'), __('All'));
		foreach($options as $key => $value){
			echo '<option value="' . $key . '"'.(DCRM_LISTS_METHOD === $key ? ' selected="selected"' : '').'>' . htmlspecialchars($value) . "</option>\n";
		}
?>
								</select>
								<p class="help-block"><?php _e('Please change the compression method if error occurred trying rebuild the list.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label" style="color: red;"><?php _e('Packages Validation ');?></label>
							<div class="controls">
								<select name="checkmethod">
<?php
		$options = array(__('No validation'), "MD5Sum", "MD5Sum & SHA1", "MD5Sum & SHA1 & SHA256");
		foreach($options as $key => $value){
			echo '<option value="' . $key . '"'.(DCRM_CHECK_METHOD === $key ? ' selected="selected"' : '').'>' . htmlspecialchars($value) . "</option>\n";
		}
?>
								</select>
								<p class="help-block"><?php _e('Take effect when write to Packages.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label" style="color: red;"><?php _e('Downgrade Support');?></label>
							<div class="controls">
								<select name="downgrade">
									<?php show_select(DCRM_DOWNGRADE); ?>
								</select>
								<p class="help-block"><?php _e('Enable this function will cause a long-term traffic consumption.');?></p>
							</div>
						</div>
						<br />
						<h3><?php _e('Autofill');?></h3>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Autofill Depiction');?></label>
							<div class="controls">
								<select name="autofill_depiction">
									<?php $autofill_depiction = get_option('autofill_depiction'); if(empty($autofill_depiction)) $autofill_depiction = '2';show_select($autofill_depiction); ?>
								</select>
								<p class="help-block"><?php _e('Autofill package\'s Depiction when import if enabled.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Default Identifier of Packages');?></label>
							<div class="controls">
								<input type="text" name="PRE" style="width: 400px;" value="<?php if(defined("AUTOFILL_PRE")){echo(htmlspecialchars(stripslashes(AUTOFILL_PRE)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Default Name of Packages');?></label>
							<div class="controls">
								<input type="text" name="NONAME" style="width: 400px;" value="<?php if(defined("AUTOFILL_NONAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_NONAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Default Depiction of Packages');?></label>
							<div class="controls">
								<textarea type="text" name="DESCRIPTION" style="height: 40px; width: 400px;"><?php if(defined("AUTOFILL_DESCRIPTION")){echo(htmlspecialchars(stripslashes(AUTOFILL_DESCRIPTION)));} ?></textarea>
							</div>
						</div>
						<br />
						<h3><?php _e('SEO');?></h3>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _ex('Site Name', 'SEO');?></label>
							<div class="controls">
								<input type="text" name="SEO" value="<?php if(defined("AUTOFILL_SEO")){echo(htmlspecialchars(stripslashes(AUTOFILL_SEO)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _ex('Site Address', 'SEO');?></label>
							<div class="controls">
								<input type="text" name="SITE" style="width: 400px;" value="<?php if(defined("AUTOFILL_SITE")){echo(htmlspecialchars(stripslashes(AUTOFILL_SITE)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _ex('Site Keyword', 'SEO');?></label>
							<div class="controls">
								<textarea type="text" name="KEYWORDS" style="height: 40px; width: 400px;"><?php if(defined("AUTOFILL_KEYWORDS")){echo(htmlspecialchars(stripslashes(AUTOFILL_KEYWORDS)));} ?></textarea>
								<p class="help-block"><?php _e('Separated by commas.');?></p>
							</div>
						</div>
						<br />
						<h3><?php _e('Administrator Informations');?></h3>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _ex('Name', 'Administrator');?></label>
							<div class="controls">
								<input type="text" name="MASTER" value="<?php if(defined("AUTOFILL_MASTER")){echo(htmlspecialchars(stripslashes(AUTOFILL_MASTER)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _ex('Full Name', 'Administrator');?></label>
							<div class="controls">
								<input type="text" name="FULLNAME" style="width: 400px;" value="<?php if(defined("AUTOFILL_FULLNAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_FULLNAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _ex('Email Address', 'Administrator');?></label>
							<div class="controls">
								<input type="email" name="EMAIL" style="width: 400px;" data-validation-email-message="<?php _e('Not a valid email address'); ?>" value="<?php if(defined("AUTOFILL_EMAIL")){echo(htmlspecialchars(stripslashes(AUTOFILL_EMAIL)));} ?>"/>
							</div>
						</div>
						<br />
						<h3><?php _e('Footer');?></h3>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Starting Year of Copyright');?></label>
							<div class="controls">
								<input type="number" name="FOOTER_YEAR" style="width: 65px;" value="<?php if(defined("AUTOFILL_FOOTER_YEAR")){echo(htmlspecialchars(stripslashes(AUTOFILL_FOOTER_YEAR)));} ?>" />
								<p class="help-block"><?php printf(__('If input 2010, the final display is © 2010-%d, leave a blank will only displays the current year.'), date("Y"));?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Copyright Name');?></label>
							<div class="controls">
								<input type="text" name="FOOTER_NAME" value="<?php if(defined("AUTOFILL_FOOTER_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_FOOTER_NAME)));} ?>"/>
								<p class="help-block"><?php _e('Displayed on the website at the bottom. If you not input anything will display the repository name.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Footer Code');?></label>
							<div class="controls">
								<textarea cols="50" rows="10" name="FOOTER_CODE" style="height: 80px; width: 400px;"><?php if(defined("AUTOFILL_FOOTER_CODE")){echo(htmlspecialchars(stripslashes(AUTOFILL_FOOTER_CODE)));} ?></textarea>
								<p class="help-block"><?php _e('You can input ICP number, site information etc. Please use <code>·</code> as the separator. ');?></p>
							</div>
						</div>
						<br />
						<h3><?php _e('Social');?></h3>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Duoshuo Social Comments KEY');?></label>
							<div class="controls">
								<input type="text" name="DUOSHUO_KEY" value="<?php if(defined("AUTOFILL_DUOSHUO_KEY")){echo(htmlspecialchars(stripslashes(AUTOFILL_DUOSHUO_KEY)));} ?>"/>
								<p class="help-block"><?php printf(__('Please goto %s for get key (registration required), leave a blank will disabled comment function.'), '<a href="http://duoshuo.com/">http://duoshuo.com</a>'); ?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('QQ Group Name');?></label>
							<div class="controls">
								<input type="text" name="TENCENT_NAME" value="<?php if(defined("AUTOFILL_TENCENT_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_TENCENT_NAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('QQ Group Number');?></label>
							<div class="controls">
								<input type="text" name="TENCENT" style="width: 400px;" value="<?php if(defined("AUTOFILL_TENCENT")){echo(htmlspecialchars(stripslashes(AUTOFILL_TENCENT)));} ?>"/>
								<p class="help-block"><?php _e('Need the new mobile client.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Weibo Name');?></label>
							<div class="controls">
								<input type="text" name="WEIBO_NAME" value="<?php if(defined("AUTOFILL_WEIBO_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_WEIBO_NAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Weibo Address');?></label>
							<div class="controls">
								<input type="text" name="WEIBO" style="width: 400px;" data-validation-regex-regex="((https?):\/\/)?((weibo\.cn\/))([a-z0-9_\-\.~]+)*(\/([a-z0-9_\-\.]*)(\?[a-z0-9+_\-\.%=&]*)?)?(#[a-z][a-z0-9_]*)?" data-validation-regex-message="<?php printf(__('Not a valid mobile weibo address. Example: %s'), '<code>http://weibo.cn/hintay</code>'); ?>" value="<?php if(defined("AUTOFILL_WEIBO")){echo(htmlspecialchars(stripslashes(AUTOFILL_WEIBO)));} ?>"/>
								<p class="help-block"><?php _e('Please input the mobile version homepage.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Twitter Name');?></label>
							<div class="controls">
								<input type="text" name="TWITTER_NAME" value="<?php if(defined("AUTOFILL_TWITTER_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_TWITTER_NAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Twitter Address');?></label>
							<div class="controls">
								<input type="text" name="TWITTER" style="width: 400px;" data-validation-regex-regex="((https?):\/\/)?((www.)*(twitter.com\/))([a-z0-9_\-\.~]+)*(\/([a-z0-9_\-\.]*)(\?[a-z0-9+_\-\.%=&]*)?)?(#[a-z][a-z0-9_]*)?" data-validation-regex-message="<?php _e('Not a valid website address'); ?>" value="<?php if(defined("AUTOFILL_TWITTER")){echo(htmlspecialchars(stripslashes(AUTOFILL_TWITTER)));} ?>"/>
								<p class="help-block"><?php _e('Need login for visit.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Facebook Name');?></label>
							<div class="controls">
								<input type="text" name="FACEBOOK_NAME" value="<?php if(defined("AUTOFILL_FACEBOOK_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_FACEBOOK_NAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Facebook Address');?></label>
							<div class="controls">
								<input type="text" name="FACEBOOK" style="width: 400px;" data-validation-regex-regex="^((https?):\/\/)?((www.)*(facebook.com\/))([a-z0-9_\-\.~]+)*(\/([a-z0-9_\-\.]*)(\?[a-z0-9+_\-\.%=&]*)?)?(#[a-z][a-z0-9_]*)?$" data-validation-regex-message="<?php _e('Not a valid website address'); ?>" value="<?php if(defined("AUTOFILL_FACEBOOK")){echo(htmlspecialchars(stripslashes(AUTOFILL_FACEBOOK)));} ?>"/>
								<p class="help-block"><?php _e('Need login for visit.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Paypal Donate Address');?></label>
							<div class="controls">
								<input type="text" name="PAYPAL" style="width: 400px;" data-validation-regex-regex="((https?):\/\/)?([a-z]([a-z0-9\-]*[\.])+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel)|(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))((\/)?[\w\-\.~]{0,1})*(\/([\w\-\.]*)(\?[\w+_\-\.%=&@]*)*)?(#\w*)?" data-validation-regex-message="<?php _e('Not a valid website address'); ?>" value="<?php if(defined("AUTOFILL_PAYPAL")){echo(htmlspecialchars(stripslashes(AUTOFILL_PAYPAL)));} ?>"/>
								<p class="help-block"></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Alipay Donate Address');?></label>
							<div class="controls">
								<input type="text" name="ALIPAY" style="width: 400px;" data-validation-regex-regex="((https?):\/\/)?([a-z]([a-z0-9\-]*[\.])+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel)|(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))(\/[a-z0-9_\-\.~]+)*(\/([a-z0-9_\-\.]*)(\?[a-z0-9+_\-\.%=&]*)?)?(#[a-z][a-z0-9_]*)?" data-validation-regex-message="<?php _e('Not a valid website address'); ?>" value="<?php if(defined("AUTOFILL_ALIPAY")){echo(htmlspecialchars(stripslashes(AUTOFILL_ALIPAY)));} ?>"/>
								<p class="help-block"></p>
							</div>
						</div>
						<br />
						<h3><?php _e('Statistics And Advertisement');?></h3>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('External Statistics');?></label>
							<div class="controls">
								<textarea type="text" style="height: 80px; width: 400px;" name="STATISTICS" ><?php if(defined("AUTOFILL_STATISTICS")){echo(htmlspecialchars(stripslashes(AUTOFILL_STATISTICS)));} ?></textarea>
								<p class="help-block"><?php _e('Invisible statistic code.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Internal Statistics');?></label>
							<div class="controls">
								<textarea type="text" style="height: 80px; width: 400px;" name="STATISTICS_INFO" ><?php if(defined("AUTOFILL_STATISTICS_INFO")){echo(htmlspecialchars(stripslashes(AUTOFILL_STATISTICS_INFO)));} ?></textarea>
								<p class="help-block"><?php _e('Statistics code of view information at Running Status.');?></p>
							</div>
						</div>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Advertisement');?></label>
							<div class="controls">
								<textarea type="text" style="height: 80px; width: 400px;" name="ADVERTISEMENT" ><?php if(defined("AUTOFILL_ADVERTISEMENT")){echo(htmlspecialchars(stripslashes(AUTOFILL_ADVERTISEMENT)));} ?></textarea>
							</div>
						</div>
						<br />
						<h3><?php _e('Notice');?></h3>
						<br />
						<div class="control-group">
							<label class="control-label"><?php _e('Emergency Notice');?></label>
							<div class="controls">
								<textarea id="Emergency" type="text" style="height: 80px; width: 400px;" name="EMERGENCY" ><?php if(defined("AUTOFILL_EMERGENCY")){echo(htmlspecialchars(stripslashes(AUTOFILL_EMERGENCY)));} ?></textarea>
							</div>
						</div>
						<br />
						<div class="form-actions control-group">
							<div class="controls">
								<button type="submit" class="btn btn-success"><?php _e('Save');?></button>
								<p class="help-block button_error"></p>
							</div>
						</div>
					</fieldset>
				</form>
<?php
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "set") {
		$error_text = '';
		if (!isset($_POST['username']) OR empty($_POST['username']))
			$error_text .= __('Please provide a valid username.').'<br/>';
		if (strlen($_POST['username']) > 20 || strlen($_POST['username']) < 4)
			$error_text .= __('Username length must be between 4-20 characters!').'<br/>';
		if (!preg_match("/^[0-9a-zA-Z\_]*$/", $_POST['username']))
			$error_text .= __('Username can only use numbers, letters and underline!').'<br/>';
		if ( !empty($_POST['pass1']) && empty($_POST['pass2']) )
			$error_text .= __( 'You entered your new password only once.' ).'<br/>';
		elseif ( $_POST['pass1'] != $_POST['pass2'] )
			$error_text .= __( 'Your passwords do not match. Please try again.' ).'<br/>';
		if (!isset($_POST['trials']) OR !ctype_digit($_POST['trials']))
			$error_text .= __('The maximum number of attempts must be an integer!').'<br/>';
		if (!isset($_POST['resettime']) OR !ctype_digit($_POST['resettime']))
			$error_text .= __('Login fail reset time must be an integer!').'<br/>';
		if (!isset($_POST['speedlimit']) OR !ctype_digit($_POST['speedlimit']))
			$error_text .= __('Max download speed must be an integer!').'<br/>';
		if (!isset($_POST['directdown']) OR !ctype_digit($_POST['directdown']))
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('HotLink Protection')).'<br/>';
		if (!isset($_POST['pcindex']) OR !ctype_digit($_POST['pcindex']))
			$error_text .= __('Please set the correct PC Site Master Switch!').'<br/>';
		if (!isset($_POST['mobile']) OR !ctype_digit($_POST['mobile']))
			$error_text .= __('Please set the correct Mobile Site Master Switch!').'<br/>';
		if (!isset($_POST['screenshots']) OR !ctype_digit($_POST['screenshots']))
			$error_text .= sprintf(__('Please set the correct %s switch!'), _x('Screenshorts', 'Settings')).'<br/>';
		if (!isset($_POST['reporting']) OR !ctype_digit($_POST['reporting']))
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('Report Problems')).'<br/>';
		if (!isset($_POST['reportlimit']) OR !ctype_digit($_POST['reportlimit']) OR (int)$_POST['reportlimit'] > 20)
			$error_text .= __('Please set the correct Limit Number of Report, the maximum not more than 10 times!').'<br/>';
		if (!isset($_POST['updatelogs']) OR !ctype_digit($_POST['updatelogs']))
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('Update Logs')).'<br/>';
		if (!isset($_POST['moreinfo']) OR !ctype_digit($_POST['moreinfo']))
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('More Information')).'<br/>';
		if (!isset($_POST['multiinfo']) OR !ctype_digit($_POST['multiinfo']))
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('Multiple Information')).'<br/>';
		if (!isset($_POST['listsmethod']) OR !ctype_digit($_POST['listsmethod']) OR (int)$_POST['listsmethod'] > 7)
			$error_text .= __('Please set the correct Packages compression method!').'<br/>';
		if (!isset($_POST['list']) OR !ctype_digit($_POST['list']))
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('Show Latest List')).'<br/>';
		elseif (!isset($_POST['listnum']) OR !ctype_digit($_POST['listnum']) OR (int)$_POST['listnum'] > 20)
			$error_text .= __('Please set the correct number of latest list, the maximum must not exceed 20 !').'<br/>';
		if (!isset($_POST['downgrade']) OR !ctype_digit($_POST['downgrade']))
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('Downgrade Support')).'<br/>';
		if (!isset($_POST['allowfulllist']) OR !ctype_digit($_POST['allowfulllist']))
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('Full List of Sections')).'<br/>';
		if (!isset($_POST['url_repo']) OR empty($_POST['url_repo']))
			$error_text .= __('Please provide a valid repository URL!').'<br/>';
		if (empty($error_text)) {
			$result = DB::query("SELECT `ID` FROM `".DCRM_CON_PREFIX."Users` WHERE (`Username` = '".DB::real_escape_string($_POST['username'])."' AND `ID` != '".$_SESSION['userid']."')");
			if (!$result OR DB::affected_rows() != 0) {
				$error_text .= __('There is a same username!').'<br/>';
			} else {
				$result = DB::update(DCRM_CON_PREFIX.'Users', array('Username' => DB::real_escape_string($_POST['username'])), array('ID' => $_SESSION['userid']));
				if (!empty($_POST['pass1'])) {
					$logout = true;
					$result = DB::update(DCRM_CON_PREFIX.'Users', array('SHA1' => sha1($_POST['pass1'])), array('ID' => $_SESSION['userid']));
				}
			}
		}

		/* Rewrite Check */
		base_url(true);
		switch($_POST['rewrite_mod']){
			case 3:
				$check_url =url_scheme().SITE_URL.'misc';
				$rewrite_code = url_code($check_url);
				break;
			case 2:
				$check_url = url_scheme().SITE_URL.'rewritetest';
				$rewrite_code = url_code($check_url);
				break;
		}
		if(isset($rewrite_code) && $rewrite_code !== 200)
			$error_text .= sprintf(__('Cannot access %s, you might need update your rewrite config to use this rewrite mod!'), $check_url).'<br/>';

		if (!empty($error_text)) {
			echo '<h3 class="alert alert-error">';
			echo $error_text;
			echo '<br /><a href="settings.php" onclick="javascript:history.go(-1);return false;">'.__('Back').'</a></h3>';
		} else {
			/* Update Options */
			if(isset($_POST['autofill_depiction']))
				update_option('autofill_depiction', $_POST['autofill_depiction']);
			if(isset($_POST['php_forward'])){
				if($_POST['php_forward'] == 2){
					$htaccess_file = ROOT.'downloads/.htaccess';
					if(!file_exists($htaccess_file)){
						@touch($htaccess_file);
						$htaccess_text = "\tORDER ALLOW,DENY\n\tDENY FROM ALL";
						file_put_contents($htaccess_file, $htaccess_text);
					}
				} else {
					if(file_exists(ROOT.'downloads/.htaccess'))
						unlink(ROOT.'downloads/.htaccess');
				}
				update_option('php_forward', $_POST['php_forward']);
			}
			if(isset($_POST['module_enabled']))
				update_option('module_enabled', $_POST['module_enabled']);
			if(isset($_POST['rewrite_mod']))
				update_option('rewrite_mod', $_POST['rewrite_mod']);

			$config_text = "<?php\nif (!defined(\"DCRM\")) exit();\n";
			$config_text .= "define(\"DCRM_LANG\", \"".$_POST['language']."\");\n";
			$config_text .= "define(\"DCRM_MAXLOGINFAIL\", ".$_POST['trials'].");\n";
			$config_text .= "define(\"DCRM_SHOWLIST\", ".$_POST['list'].");\n";
			$config_text .= "define(\"DCRM_SHOW_NUM\", ".$_POST['listnum'].");\n";
			$config_text .= "define(\"DCRM_ALLOW_FULLLIST\", ".$_POST['allowfulllist'].");\n";
			$config_text .= "define(\"DCRM_SPEED_LIMIT\", ".$_POST['speedlimit'].");\n";
			$config_text .= "define(\"DCRM_DIRECT_DOWN\", ".$_POST['directdown'].");\n";
			$config_text .= "define(\"DCRM_DOWNGRADE\", ".$_POST['downgrade'].");\n";
			$config_text .= "define(\"DCRM_PCINDEX\", ".$_POST['pcindex'].");\n";
			$config_text .= "define(\"DCRM_MOBILE\", ".$_POST['mobile'].");\n";
			$config_text .= "define(\"DCRM_SCREENSHOTS\", ".$_POST['screenshots'].");\n";
			$config_text .= "define(\"DCRM_REPORTING\", ".$_POST['reporting'].");\n";
			$config_text .= "define(\"DCRM_REPORT_LIMIT\", ".$_POST['reportlimit'].");\n";
			$config_text .= "define(\"DCRM_UPDATELOGS\", ".$_POST['updatelogs'].");\n";
			$config_text .= "define(\"DCRM_MOREINFO\", ".$_POST['moreinfo'].");\n";
			$config_text .= "define(\"DCRM_DESCRIPTION\", ".$_POST['description'].");\n";
			$config_text .= "define(\"DCRM_MULTIINFO\", ".$_POST['multiinfo'].");\n";
			$config_text .= "define(\"DCRM_LISTS_METHOD\", ".$_POST['listsmethod'].");\n";
			$config_text .= "define(\"DCRM_CHECK_METHOD\", ".$_POST['checkmethod'].");\n";
			// 检测源地址最后一位是否为'/'，若不是则自动添加
			$repo_url = $_POST['url_repo'];
			if(substr($repo_url, -1) != '/')
				$repo_url .= '/';
			$config_text .= "define(\"DCRM_REPOURL\", \"".base64_encode($repo_url)."\");\n";
			$config_text .= "define(\"DCRM_LOGINFAILRESETTIME\", ".($_POST['resettime']*60).");\n";
			$config_text .= "?>";
			$autofill_text = "<?php\nif (!defined(\"DCRM\")) exit();\n";
			$autofill_list = array("EMERGENCY", "PRE", "NONAME", "MASTER", "FULLNAME", "EMAIL", "SITE", "WEIBO", "WEIBO_NAME", "TWITTER", "TWITTER_NAME", "FACEBOOK", "FACEBOOK_NAME", "DESCRIPTION", "SEO", "KEYWORDS", "PAYPAL", "ALIPAY", "STATISTICS", "STATISTICS_INFO", "ADVERTISEMENT", "TENCENT", "TENCENT_NAME", "DUOSHUO_KEY", "FOOTER_YEAR", "FOOTER_CODE", "FOOTER_NAME");
			foreach ($autofill_list as $value) {
				if (!empty($_POST[$value])) {
					$autofill_text .= "define(\"AUTOFILL_".$value."\", \"".addslashes(str_replace(array("\r","\n"), '',nl2br(htmlspecialchars_decode($_POST[$value]))))."\");\n";
				}
			}
			$autofill_text .= "?>";
			$config_handle = fopen(CONF_PATH.'config.inc.php', "w");
			fputs($config_handle,stripslashes($config_text));
			fclose($config_handle);
			$autofill_handle = fopen(CONF_PATH.'autofill.inc.php', "w");
			fputs($autofill_handle,$autofill_text);
			fclose($autofill_handle);
			unset($_SESSION['language']);
			echo '<h3 class="alert alert-success">'.__('Amendments to the success of settings!').'<br/><a href="settings.php">'.__('Back').'</a></h3>';
			if (isset($logout) && $logout) {
				header("Location: ./login.php?action=logout");
			}
		}
	}
?>
			</div>
		</div>
	</div>
	</div>
	<script src="../js/password-strength.min.js" type="text/javascript"></script>
	<script src="../js/zxcvbn-async.min.js" type="text/javascript"></script>
	<script src="../js/zxcvbn.min.js" type="text/javascript"></script>
	<script src="./plugins/jqBootstrapValidation/jqBootstrapValidation.min.js"></script>
	<script charset="utf-8" src="./plugins/kindeditor/kindeditor.min.js"></script>
	<script charset="utf-8" src="./plugins/kindeditor/lang/<?php echo $kdlang = check_languages(array($locale), 'kind');?>.js"></script>
	<script type='text/javascript'>
	var pwsL10n = {"empty":"<?php echo( utf8_unicode( __( 'Strength indicator' ) ) ); ?>","short":"<?php echo( utf8_unicode( _x( 'Short', 'Password' ) ) ); ?>","bad":"<?php echo( utf8_unicode( _x( 'Bad', 'Password' ) ) ); ?>","good":"<?php echo( _x( 'Good', 'Password' ) ); ?>","strong":"<?php echo( utf8_unicode( _x( 'Strong', 'Password' ) ) ); ?>","mismatch":"<?php echo( utf8_unicode( _x( 'Mismatch', 'Password' ) ) ); ?>"};
	$(function () { $("input,select,textarea").not("[type=submit]").jqBootstrapValidation(
		{
			submitError: function ($form, event, errors){
				$(".form-actions").addClass("error");
				$(".button_error").html('<ul role="alert"><li><?php _e('You have some errors, please check the form.'); ?></li></ul>');
			}
		}
	); } );
	KindEditor.ready(function(K) {
		K.each({
			'plug-align' : {
				name : '<?php _e('Align'); ?>',
				method : {
					'justifyleft' : '<?php _ex('Left', 'Align'); ?>',
					'justifycenter' : '<?php _ex('Center', 'Align'); ?>',
					'justifyright' : '<?php _ex('Right', 'Align'); ?>'
				}
			},
			'plug-order' : {
				name : '<?php _ex('List', 'Edit'); ?>',
				method : {
					'insertorderedlist' : '<?php _e('Ordered list'); ?>',
					'insertunorderedlist' : '<?php _e('Unordered list'); ?>'
				}
			},
			'plug-indent' : {
				name : '<?php _e('Indent'); ?>',
				method : {
					'indent' : '<?php _e('Increase indent'); ?>',
					'outdent' : '<?php _e('Decrease indent'); ?>'
				}
			}
		},function( pluginName, pluginData ){
			var lang = {};
			lang[pluginName] = pluginData.name;
			KindEditor.lang( lang );
			KindEditor.plugin( pluginName, function(K) {
				var self = this;
				self.clickToolbar( pluginName, function() {
					var menu = self.createMenu({
							name : pluginName,
							width : pluginData.width || 100
						});
					K.each( pluginData.method, function( i, v ){
						menu.addItem({
							title : v,
							checked : false,
							iconClass : pluginName+'-'+i,
							click : function() {
								self.exec(i).hideMenu();
							}
						});
					})
				});
			});
		});
		K.create('#Emergency', {
			langType : '<?php echo $kdlang; ?>',
			themeType : 'qq',
			newlineTag : 'br',
			items : [
				'bold','italic','underline','fontname','fontsize','forecolor','hilitecolor','plug-align','plug-order','plug-indent','link','removeformat','|','source'
			]
		});
	});
	</script>
</body>
</html>
<?php
} else {
	$_SESSION['referer'] = $_SERVER['REQUEST_URI'];
	header("Location: login.php");
	exit();
}
?>