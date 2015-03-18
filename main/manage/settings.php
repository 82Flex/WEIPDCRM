<?php
/**
 * This file is part of WEIPDCRM.
 * 
 * WEIPDCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * WEIPDCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with WEIPDCRM.  If not, see <http://www.gnu.org/licenses/>.
 */

/* DCRM System Settings */

session_start();
define("DCRM",true);
$activeid = 'settings';

if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
	require_once("header.php");

	if (!isset($_GET['action'])) {

		function show_select($variable){
			if ($variable == 2) {
				echo '<option value="2" selected="selected">'.__('Enabled')."</option>\n<option value=\"1\">".__('Disabled')."</option>\n";
			} else {
				echo '<option value="1" selected="selected">'.__('Disabled')."</option>\n<option value=\"2\">".__('Enabled')."</option>\n";
			}
		}
?>
				<h2><?php _e( 'Preferences' ); ?></h2>
				<br />
				<form class="form-horizontal" method="POST" action="settings.php?action=set">
					<fieldset>
						<h3><?php _e( 'General' ); ?></h3>
						<br />
						<div class="group-control">
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
								<p class="help-block"><?php _e('If you want system auto detect users browser language to show pages please select "Detect" option.'); if ( substr( $locale, 0, 2 ) != 'en' ) { ?><br />If you want system auto detect users browser language to show pages please select "Detect" option.<?php } ?></p>
							</div>
						</div>
						<br />
						<h3><?php _e('Login Information');?></h3>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Username');?></label>
							<div class="controls">
								<input type="text" required="required" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('New Password');?></label>
							<div class="controls">
								<input type="password" name="pass1" id="pass1"/>
								<p class="help-block"><?php _e('If you would like to change the password type a new one. Otherwise leave this blank.'); ?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Repeat New Password');?></label>
							<div class="controls">
								<input type="password" name="pass2" id="pass2"/>
								<p class="help-block"><?php _e('Type your new password again.');?></p>
								<div id="pass-strength-result" style="display: block;"><?php _e('Strength indicator'); ?></div>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('The maximum number of attempts');?></label>
							<div class="controls">
								<input type="text" required="required" name="trials" value="<?php echo htmlspecialchars(DCRM_MAXLOGINFAIL); ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Login Fail Reset Time');?></label>
							<div class="controls">
								<input type="text" required="required" name="resettime" value="<?php if(defined(DCRM_LOGINFAILRESETTIME)){echo(htmlspecialchars(DCRM_LOGINFAILRESETTIME)/60);}else{echo(10);} ?>"/>
								<p class="help-block"><?php _e('Unit: Minutes');?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label" style="color: red;"><?php _e( 'Repository URL' ); ?></label>
							<div class="controls">
								<input type="text" required="required" name="url_repo" style="width: 400px;" value="<?php echo htmlspecialchars(base64_decode(DCRM_REPOURL)); ?>"/>
								<p class="help-block"><?php _e( 'Displayed on the homepage for the user to add.' ); ?></p>
							</div>
						</div>
						<br />
						<h3><?php _e('PC Site');?></h3>
						<br />
						<div class="group-control">
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
						<div class="group-control">
							<label class="control-label" style="color: red;"><?php _e('Master Switch');?></label>
							<div class="controls">
								<select name="mobile">
									<?php show_select(DCRM_MOBILE); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Show Latest List');?></label>
							<div class="controls">
								<select name="list">
									<?php show_select(DCRM_SHOWLIST); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Number of Latest List');?></label>
							<div class="controls">
								<input type="text"  name="listnum" value="<?php echo htmlspecialchars(DCRM_SHOW_NUM); ?>"/>
								<p class="help-block"><?php _e('The maximum must not exceed 20.');?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Full List of Sections');?></label>
							<div class="controls">
								<select name="allowfulllist">
									<?php show_select(DCRM_ALLOW_FULLLIST); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _ex('Screenshorts', 'Settings');?></label>
							<div class="controls">
								<select name="screenshots">
									<?php show_select(DCRM_SCREENSHOTS); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Report Problems');?></label>
							<div class="controls">
								<select name="reporting">
									<?php show_select(DCRM_REPORTING); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Limit Number of Report');?></label>
							<div class="controls">
								<input type="text"  name="reportlimit" value="<?php echo htmlspecialchars(DCRM_REPORT_LIMIT); ?>"/>
								<p class="help-block"><?php _e('The maximum not more than 10 times.'); ?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Update Logs');?></label>
							<div class="controls">
								<select name="updatelogs">
									<?php show_select(DCRM_UPDATELOGS); ?>
								</select>
								<p class="help-block"><?php _e('Displayed in the Version History.');?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('More Information');?></label>
							<div class="controls">
								<select name="moreinfo">
									<?php show_select(DCRM_MOREINFO); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Detailed Description'); ?></label>
							<div class="controls">
								<select name="multiinfo">
									<?php show_select(DCRM_MULTIINFO); ?>
								</select>
							</div>
						</div>
						<br />
						<h3><?php _e('Download');?></h3>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('HotLink Protection');?></label>
							<div class="controls">
								<select name="directdown">
									<?php show_select(DCRM_DIRECT_DOWN); ?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label" style="color: red;"><?php _e('Max Download Speed');?></label>
							<div class="controls">
								<input type="text" required="required" name="speedlimit" value="<?php echo htmlspecialchars(DCRM_SPEED_LIMIT); ?>"/>
								<p class="help-block"><?php _e('B/s, please input \'0\' if you do not want limit.');?></p>
							</div>
						</div>
						<br />
						<h3><?php _e('Lists');?></h3>
						<br />
						<div class="group-control">
							<label class="control-label" style="color: red;"><?php _e('Packages File Compression');?></label>
							<div class="controls">
								<select name="listsmethod">
<?php
		function getzmethod($opt) {
			switch ($opt) {
				case 0:
					$opt_text = __('Hide list');
					break;
				case 1:
					$opt_text = __('Only text');
					break;
				case 2:
					$opt_text = __('Only gz');
					break;
				case 3:
					$opt_text = __('Text and gz');
					break;
				case 4:
					$opt_text = __('Only bz2');
					break;
				case 5:
					$opt_text = __('Text and bz2');
					break;
				case 6:
					$opt_text = __('gz and bz2');
					break;
				case 7:
					$opt_text = __('All');
					break;
				default:
					$opt_text = "";
			}
			return $opt_text;
		}
		for ($opt = 0; $opt <= 7; $opt++) {
			if (DCRM_LISTS_METHOD == $opt) {
				echo '<option value="' . $opt . '" selected="selected">' . htmlspecialchars(getzmethod($opt)) . "</option>\n";
			} else {
				echo '<option value="' . $opt . '">' . htmlspecialchars(getzmethod($opt)) . "</option>\n";
			}
		}
?>
								</select>
								<p class="help-block"><?php _e('Please change the compression method if error occurred trying rebuild the list.');?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label" style="color: red;"><?php _e('Packages Validation ');?></label>
							<div class="controls">
								<select name="checkmethod">
<?php
		function getsmethod($opt) {
			switch ($opt) {
				case 0:
					$opt_text = __('No validation');
					break;
				case 1:
					$opt_text = "MD5Sum";
					break;
				case 2:
					$opt_text = "MD5Sum & SHA1";
					break;
				case 3:
					$opt_text = "MD5Sum & SHA1 & SHA256";
					break;
				default:
					$opt_text = "";
			}
			return $opt_text;
		}
		for ($opt = 0; $opt <= 3; $opt++) {
			if (DCRM_CHECK_METHOD == $opt) {
				echo '<option value="' . $opt . '" selected="selected">' . htmlspecialchars(getsmethod($opt)) . "</option>\n";
			} else {
				echo '<option value="' . $opt . '">' . htmlspecialchars(getsmethod($opt)) . "</option>\n";
			}
		}
?>
								</select>
								<p class="help-block"><?php _e('Take effect when write to Packages.');?></p>
							</div>
						</div>
						<br />
						<h3><?php _e('Autofill');?></h3>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Default Identifier of Packages');?></label>
							<div class="controls">
								<input type="text" name="PRE" style="width: 400px;" value="<?php if(defined("AUTOFILL_PRE")){echo(htmlspecialchars(stripslashes(AUTOFILL_PRE)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Default Name of Packages');?></label>
							<div class="controls">
								<input type="text" name="NONAME" style="width: 400px;" value="<?php if(defined("AUTOFILL_NONAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_NONAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Default Depiction of Packages');?></label>
							<div class="controls">
								<textarea type="text" name="DESCRIPTION" style="height: 40px; width: 400px;"><?php if(defined("AUTOFILL_DESCRIPTION")){echo(htmlspecialchars(stripslashes(AUTOFILL_DESCRIPTION)));} ?></textarea>
							</div>
						</div>
						<br />
						<h3><?php _e('SEO');?></h3>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _ex('Site Name', 'SEO');?></label>
							<div class="controls">
								<input type="text" name="SEO" value="<?php if(defined("AUTOFILL_SEO")){echo(htmlspecialchars(stripslashes(AUTOFILL_SEO)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _ex('Site Address', 'SEO');?></label>
							<div class="controls">
								<input type="text" name="SITE" style="width: 400px;" value="<?php if(defined("AUTOFILL_SITE")){echo(htmlspecialchars(stripslashes(AUTOFILL_SITE)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _ex('Site Keyword', 'SEO');?></label>
							<div class="controls">
								<textarea type="text" name="KEYWORDS" style="height: 40px; width: 400px;"><?php if(defined("AUTOFILL_KEYWORDS")){echo(htmlspecialchars(stripslashes(AUTOFILL_KEYWORDS)));} ?></textarea>
								<p class="help-block"><?php _e('Separated by commas.');?></p>
							</div>
						</div>
						<br />
						<h3><?php _e('Administrator Informations');?></h3>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _ex('Name', 'Administrator');?></label>
							<div class="controls">
								<input type="text" name="MASTER" value="<?php if(defined("AUTOFILL_MASTER")){echo(htmlspecialchars(stripslashes(AUTOFILL_MASTER)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _ex('Full Name', 'Administrator');?></label>
							<div class="controls">
								<input type="text" name="FULLNAME" style="width: 400px;" value="<?php if(defined("AUTOFILL_FULLNAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_FULLNAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _ex('Email Address', 'Administrator');?></label>
							<div class="controls">
								<input type="text" name="EMAIL" style="width: 400px;" value="<?php if(defined("AUTOFILL_EMAIL")){echo(htmlspecialchars(stripslashes(AUTOFILL_EMAIL)));} ?>"/>
							</div>
						</div>
						<br />
						<h3><?php _e('Footer');?></h3>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Starting Year of Copyright');?></label>
							<div class="controls">
								<input type="number" name="FOOTER_YEAR" style="width: 65px;" value="<?php if(defined("AUTOFILL_FOOTER_YEAR")){echo(htmlspecialchars(stripslashes(AUTOFILL_FOOTER_YEAR)));} ?>" />
								<p class="help-block"><?php printf(__('If input 2010, the final display is © 2010-%d, leave a blank will only displays the current year.'), date("Y"));?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Copyright Name');?></label>
							<div class="controls">
								<input type="text" name="FOOTER_NAME" value="<?php if(defined("AUTOFILL_FOOTER_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_FOOTER_NAME)));} ?>"/>
								<p class="help-block"><?php _e('Displayed on the website at the bottom. If you not input anything will display the repository name.');?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Footer Code');?></label>
							<div class="controls">
								<textarea cols="50" rows="10" name="FOOTER_CODE" style="height: 80px; width: 400px;"><?php if(defined("AUTOFILL_FOOTER_CODE")){echo(htmlspecialchars(stripslashes(AUTOFILL_FOOTER_CODE)));} ?></textarea>
								<p class="help-block"><?php _e('You can input ICP number, site information etc. Please use <code>·</code> as the separator. ');?></p>
							</div>
						</div>
						<br />
						<h3><?php _e('Social');?></h3>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Duoshuo Social Comments KEY');?></label>
							<div class="controls">
								<input type="text" name="DUOSHUO_KEY" value="<?php if(defined("AUTOFILL_DUOSHUO_KEY")){echo(htmlspecialchars(stripslashes(AUTOFILL_DUOSHUO_KEY)));} ?>"/>
								<p class="help-block"><?php _e('Please goto <a href="http://duoshuo.com/">http://duoshuo.com</a> for get key (registration required), leave a blank will disabled comment function.');?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('QQ Group Name');?></label>
							<div class="controls">
								<input type="text" name="TENCENT_NAME" value="<?php if(defined("AUTOFILL_TENCENT_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_TENCENT_NAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('QQ Group Number');?></label>
							<div class="controls">
								<input type="text" name="TENCENT" style="width: 400px;" value="<?php if(defined("AUTOFILL_TENCENT")){echo(htmlspecialchars(stripslashes(AUTOFILL_TENCENT)));} ?>"/>
								<p class="help-block"><?php _e('Need the new mobile client.');?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Weibo Name');?></label>
							<div class="controls">
								<input type="text" name="WEIBO_NAME" value="<?php if(defined("AUTOFILL_WEIBO_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_WEIBO_NAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Weibo Address');?></label>
							<div class="controls">
								<input type="text" name="WEIBO" style="width: 400px;" value="<?php if(defined("AUTOFILL_WEIBO")){echo(htmlspecialchars(stripslashes(AUTOFILL_WEIBO)));} ?>"/>
								<p class="help-block"><?php _e('Please input the mobile version homepage.');?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Twitter Name');?></label>
							<div class="controls">
								<input type="text" name="TWITTER_NAME" value="<?php if(defined("AUTOFILL_TWITTER_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_TWITTER_NAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Twitter Address');?></label>
							<div class="controls">
								<input type="text" name="TWITTER" style="width: 400px;" value="<?php if(defined("AUTOFILL_TWITTER")){echo(htmlspecialchars(stripslashes(AUTOFILL_TWITTER)));} ?>"/>
								<p class="help-block"><?php _e('Need login for visit.');?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Facebook Name');?></label>
							<div class="controls">
								<input type="text" name="FACEBOOK_NAME" value="<?php if(defined("AUTOFILL_FACEBOOK_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_FACEBOOK_NAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Facebook Address');?></label>
							<div class="controls">
								<input type="text" name="FACEBOOK" style="width: 400px;" value="<?php if(defined("AUTOFILL_FACEBOOK")){echo(htmlspecialchars(stripslashes(AUTOFILL_FACEBOOK)));} ?>"/>
								<p class="help-block"><?php _e('Need login for visit.');?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Paypal Donate Address');?></label>
							<div class="controls">
								<input type="text" name="PAYPAL" style="width: 400px;" value="<?php if(defined("AUTOFILL_PAYPAL")){echo(htmlspecialchars(stripslashes(AUTOFILL_PAYPAL)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Alipay Donate Address');?></label>
							<div class="controls">
								<input type="text" name="ALIPAY" style="width: 400px;" value="<?php if(defined("AUTOFILL_ALIPAY")){echo(htmlspecialchars(stripslashes(AUTOFILL_ALIPAY)));} ?>"/>
							</div>
						</div>
						<br />
						<h3><?php _e('Statistics And Advertisement');?></h3>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('External Statistics');?></label>
							<div class="controls">
								<textarea type="text" style="height: 80px; width: 400px;" name="STATISTICS" ><?php if(defined("AUTOFILL_STATISTICS")){echo(htmlspecialchars(stripslashes(AUTOFILL_STATISTICS)));} ?></textarea>
								<p class="help-block"><?php _e('Invisible statistic code.');?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Internal Statistics');?></label>
							<div class="controls">
								<textarea type="text" style="height: 80px; width: 400px;" name="STATISTICS_INFO" ><?php if(defined("AUTOFILL_STATISTICS_INFO")){echo(htmlspecialchars(stripslashes(AUTOFILL_STATISTICS_INFO)));} ?></textarea>
								<p class="help-block"><?php _e('Statistics code of view information at Running Status.');?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Advertisement');?></label>
							<div class="controls">
								<textarea type="text" style="height: 80px; width: 400px;" name="ADVERTISEMENT" ><?php if(defined("AUTOFILL_ADVERTISEMENT")){echo(htmlspecialchars(stripslashes(AUTOFILL_ADVERTISEMENT)));} ?></textarea>
							</div>
						</div>
						<br />
						<h3><?php _e('Notice');?></h3>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Emergency Notice');?></label>
							<div class="controls">
								<textarea type="text" style="height: 80px; width: 400px;" name="EMERGENCY" ><?php if(defined("AUTOFILL_EMERGENCY")){echo(htmlspecialchars(stripslashes(AUTOFILL_EMERGENCY)));} ?></textarea>
							</div>
						</div>
						<br />
						<div class="form-actions">
							<div class="controls">
								<button type="submit" class="btn btn-success"><?php _e('Save');?></button>
							</div>
						</div>
					</fieldset>
				</form>
<?php
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "set") {
		$error_stat = false;
		$logout = false;
		$error_text = '';
		if (!isset($_POST['username']) OR empty($_POST['username'])) {
			$error_text .= __('Please provide a valid username.')."\n";
			$error_stat = true;
		}
		if (strlen($_POST['username']) > 20 || strlen($_POST['username']) < 4) {
			$error_text .= __('Username length must be between 4-20 characters!')."\n";
			$error_stat = true;
		}
		if (!preg_match("/^[0-9a-zA-Z\_]*$/", $_POST['username'])) {
			$error_text .= __('Username can only use numbers, letters and underline!')."\n";
			$error_stat = true;
		}
		if ( !empty($_POST['pass1']) && empty($_POST['pass2']) ) {
			$error_text .= __( 'You entered your new password only once.' )."\n";
			$error_stat = true;
		} elseif ( $_POST['pass1'] != $_POST['pass2'] ) {
			$error_text .= __( 'Your passwords do not match. Please try again.' )."\n";
			$error_stat = true;
		}
		if (!isset($_POST['trials']) OR !ctype_digit($_POST['trials'])) {
			$error_text .= __('The maximum number of attempts must be an integer!')."\n";
			$error_stat = true;
		}
		if (!isset($_POST['resettime']) OR !ctype_digit($_POST['resettime'])) {
			$error_text .= __('Login fail reset time must be an integer!')."\n";
			$error_stat = true;
		}
		if (!isset($_POST['speedlimit']) OR !ctype_digit($_POST['speedlimit'])) {
			$error_text .= __('Max download speed must be an integer!')."\n";
			$error_stat = true;
		}
		if (!isset($_POST['directdown']) OR !ctype_digit($_POST['directdown'])) {
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('HotLink Protection'))."\n";
			$error_stat = true;
		}
		if (!isset($_POST['pcindex']) OR !ctype_digit($_POST['pcindex'])) {
			$error_text .= __('Please set the correct PC Site Master Switch!')."\n";
			$error_stat = true;
		}
		if (!isset($_POST['mobile']) OR !ctype_digit($_POST['mobile'])) {
			$error_text .= __('Please set the correct Mobile Site Master Switch!')."\n";
			$error_stat = true;
		}
		if (!isset($_POST['screenshots']) OR !ctype_digit($_POST['screenshots'])) {
			$error_text .= sprintf(__('Please set the correct %s switch!'), _x('Screenshorts', 'Settings'))."\n";
			$error_stat = true;
		}
		if (!isset($_POST['reporting']) OR !ctype_digit($_POST['reporting'])) {
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('Report Problems'))."\n";
			$error_stat = true;
		}
		if (!isset($_POST['reportlimit']) OR !ctype_digit($_POST['reportlimit']) OR (int)$_POST['reportlimit'] > 20) {
			$error_text .= __('Please set the correct Limit Number of Report, the maximum not more than 10 times!')."\n";
			$error_stat = true;
		}
		if (!isset($_POST['updatelogs']) OR !ctype_digit($_POST['updatelogs'])) {
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('Update Logs'))."\n";
			$error_stat = true;
		}
		if (!isset($_POST['moreinfo']) OR !ctype_digit($_POST['moreinfo'])) {
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('More Information'))."\n";
			$error_stat = true;
		}
		if (!isset($_POST['multiinfo']) OR !ctype_digit($_POST['multiinfo'])) {
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('Multiple Information'))."\n";
			$error_stat = true;
		}
		if (!isset($_POST['listsmethod']) OR !ctype_digit($_POST['listsmethod']) OR (int)$_POST['listsmethod'] > 7) {
			$error_text .= __('Please set the correct Packages compression method!')."\n";
			$error_stat = true;
		}
		if (!isset($_POST['list']) OR !ctype_digit($_POST['list'])) {
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('Show Latest List'))."\n";
			$error_stat = true;
		} else {
			if (!isset($_POST['listnum']) OR !ctype_digit($_POST['listnum']) OR (int)$_POST['listnum'] > 20) {
				$error_text .= __('Please set the correct number of latest list, the maximum must not exceed 20 !')."\n";
				$error_stat = true;
			}
		}
		if (!isset($_POST['allowfulllist']) OR !ctype_digit($_POST['allowfulllist'])) {
			$error_text .= sprintf(__('Please set the correct %s switch!'), __('Full List of Sections'))."\n";
			$error_stat = true;
		}
		if (!isset($_POST['url_repo']) OR empty($_POST['url_repo'])) {
			$error_text .= __('Please provide a valid repository URL!')."\n";
			$error_stat = true;
		}
		if ($error_stat === false) {
			$result = DB::query("SELECT `ID` FROM `".DCRM_CON_PREFIX."Users` WHERE (`Username` = '".DB::real_escape_string($_POST['username'])."' AND `ID` != '".$_SESSION['userid']."')");
			if (!$result OR DB::affected_rows() != 0) {
				$error_text .= __('There is a same username!')."\n";
				$error_stat = true;
			} else {
				$result = DB::query("UPDATE `".DCRM_CON_PREFIX."Users` SET `Username` = '".DB::real_escape_string($_POST['username'])."' WHERE `ID` = '".$_SESSION['userid']."'");
				if (!empty($_POST['pass1'])) {
					$logout = true;
					$result = DB::query("UPDATE `".DCRM_CON_PREFIX."Users` SET `SHA1` = '".sha1($_POST['pass1'])."' WHERE `ID` = '".$_SESSION['userid']."'");
				}
			}
		}
		if ($error_stat == true) {
			echo '<h3 class="alert alert-error">';
			echo $error_text;
			echo '<br /><a href="settings.php" onclick="javascript:history.go(-1);return false;">'.__('Back').'</a></h3>';
		} else {
			$config_text = "<?php\nif (!defined(\"DCRM\")) {\n\texit;\n}\n";
			$config_text .= "define(\"DCRM_LANG\", \"".$_POST['language']."\");\n";
			$config_text .= "define(\"DCRM_MAXLOGINFAIL\", ".$_POST['trials'].");\n";
			$config_text .= "define(\"DCRM_SHOWLIST\", ".$_POST['list'].");\n";
			$config_text .= "define(\"DCRM_SHOW_NUM\", ".$_POST['listnum'].");\n";
			$config_text .= "define(\"DCRM_ALLOW_FULLLIST\", ".$_POST['allowfulllist'].");\n";
			$config_text .= "define(\"DCRM_SPEED_LIMIT\", ".$_POST['speedlimit'].");\n";
			$config_text .= "define(\"DCRM_DIRECT_DOWN\", ".$_POST['directdown'].");\n";
			$config_text .= "define(\"DCRM_PCINDEX\", ".$_POST['pcindex'].");\n";
			$config_text .= "define(\"DCRM_MOBILE\", ".$_POST['mobile'].");\n";
			$config_text .= "define(\"DCRM_SCREENSHOTS\", ".$_POST['screenshots'].");\n";
			$config_text .= "define(\"DCRM_REPORTING\", ".$_POST['reporting'].");\n";
			$config_text .= "define(\"DCRM_REPORT_LIMIT\", ".$_POST['reportlimit'].");\n";
			$config_text .= "define(\"DCRM_UPDATELOGS\", ".$_POST['updatelogs'].");\n";
			$config_text .= "define(\"DCRM_MOREINFO\", ".$_POST['moreinfo'].");\n";
			$config_text .= "define(\"DCRM_MULTIINFO\", ".$_POST['multiinfo'].");\n";
			$config_text .= "define(\"DCRM_LISTS_METHOD\", ".$_POST['listsmethod'].");\n";
			$config_text .= "define(\"DCRM_CHECK_METHOD\", ".$_POST['checkmethod'].");\n";
			$config_text .= "define(\"DCRM_REPOURL\", \"".base64_encode($_POST['url_repo'])."\");\n";
			$config_text .= "define(\"DCRM_LOGINFAILRESETTIME\", ".($_POST['resettime']*60).");\n";
			$config_text .= "?>";
			$autofill_text = "<?php\nif (!defined(\"DCRM\")) {\n\texit;\n}\n";
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
			echo '<h3 class="alert alert-success">'.__('Amendments to the success of settings!').'<br/><a href="settings.php">'.__('Back').'</a></h3>';
			if ($logout) {
				header("Location: login.php?action=logout");
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
	<script type='text/javascript'>
	var pwsL10n = {"empty":"<?php echo( utf8_unicode( __( 'Strength indicator' ) ) ); ?>","short":"<?php echo( utf8_unicode( _x( 'Short', 'Password' ) ) ); ?>","bad":"<?php echo( utf8_unicode( _x( 'Bad', 'Password' ) ) ); ?>","good":"<?php echo( _x( 'Good', 'Password' ) ); ?>","strong":"<?php echo( utf8_unicode( _x( 'Strong', 'Password' ) ) ); ?>","mismatch":"<?php echo( utf8_unicode( _x( 'Mismatch', 'Password' ) ) ); ?>"};
	</script>
</body>
</html>
<?php
} else {
	header("Location: login.php");
	exit();
}
?>