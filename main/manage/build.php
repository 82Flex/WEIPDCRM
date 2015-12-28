<?php
/**
 * DCRM Packages Output
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
$localetype = 'manage';
define('MANAGE_ROOT', dirname(__FILE__).'/');
define('ABSPATH', dirname(MANAGE_ROOT).'/');
require_once ABSPATH.'system/common.inc.php';
require_once(CONF_PATH.'gnupg.inc.php');
$activeid = 'build';
$alert = "";

if (!isset($_SESSION['connected']) || $_SESSION['connected'] != true) {
	$_SESSION['referer'] = $_SERVER['REQUEST_URI'];
	header("Location: login.php");
	exit();
}
$parts = "`ID`, `Package`, `Source`, `Version`, `Priority`, `Section`, `Essential`, `Maintainer`, `Pre-Depends`, `Depends`, `Recommends`, `Suggests`, `Conflicts`, `Provides`, `Replaces`, `Enhances`, `Architecture`, `Filename`, `Size`, `Installed-Size`, `Description`, `Origin`, `Bugs`, `Name`, `Author`, `Sponsor`, `Homepage`, `Website`, `Depiction`, `Icon`, `Tag`";
if (DCRM_CHECK_METHOD != 0)
	$parts .= ", `MD5sum`";
if (DCRM_CHECK_METHOD == 2 || DCRM_CHECK_METHOD == 3)
	$parts .= ", `SHA1`";
if (DCRM_CHECK_METHOD == 3)
	$parts .= ", `SHA256`";

if (DCRM_DOWNGRADE == 2)
	$packages_info = DB::fetch_all("SELECT ".$parts." FROM `".DCRM_CON_PREFIX."Packages` WHERE `Stat` = '1' ORDER BY `Package`, `ID` DESC");
else
	$packages_info = DB::fetch_all("SELECT ".$parts." FROM `".DCRM_CON_PREFIX."Packages` WHERE `Stat` = '1' GROUP BY `Package` ORDER BY `ID` DESC");

$rewrite_mod = get_option('rewrite_mod');
if(empty($rewrite_mod) || $rewrite_mod != 3)
	$filepath_format = './downloads.php?request=%d.deb';
else
	$filepath_format = './debs/%d.deb';

$Packages = "";
foreach($packages_info as $package_info){
	$Package = "";
	$package_info['Filename'] = sprintf($filepath_format, $package_info['ID']);
	unset($package_info['ID']);
	foreach($package_info as $m_key => $m_value) {
		if (!empty($m_value) && !empty($m_key))
			$Package .= $m_key . ": " . trim(str_replace("\n", "\n ", $m_value)) . "\n";
	}
	$Packages .= $Package . "\n";
}

if (($i = count($packages_info)) > 0) {
	$alert .= __('Find the number of records: ').$i;
} else {
	$alert .= __('No record in data table ` Packages `! Please import package and allow it to display first.');
	goto norecord;
}

// Build Packages List & Release
if (!file_exists(CONF_PATH.'release.save')) {
	$alert .= "\n".__('Warning: ').__('release.save file does not exist! Please config release information.');
} else {
	$alert .= "\n".__('The Release configuration file exists.');
	$verify_text = "MD5Sum:\n";
	if (file_exists("../Packages")) {
		unlink("../Packages");
	}
	if (DCRM_LISTS_METHOD == 1 OR DCRM_LISTS_METHOD == 3 OR DCRM_LISTS_METHOD == 5 OR DCRM_LISTS_METHOD == 7) {
		$handle = fopen("../Packages", "w");
		$size = fwrite($handle, $Packages);
		fclose($handle);
		$r_array['Packages'] = filesize("../Packages");
		$md5_array['Packages'] = md5_file("../Packages");
		$verify_text .= " " . $md5_array['Packages'] . " " . $r_array['Packages'] . " " . "Packages\n";
		$alert .= "\n".__('Write to the Packages file: ').$r_array['Packages'];
	}
	if (file_exists("../Packages.gz")) {
		unlink("../Packages.gz");
	}
	if (DCRM_LISTS_METHOD == 2 OR DCRM_LISTS_METHOD == 3 OR DCRM_LISTS_METHOD == 6 OR DCRM_LISTS_METHOD == 7) {
		$g_handle = gzopen("../Packages.gz", "w");
		$size = gzwrite($g_handle, $Packages);
		gzclose($g_handle);
		$r_array['Packages.gz'] = filesize("../Packages.gz");
		$md5_array['Packages.gz'] = md5_file("../Packages.gz");
		$verify_text .= " " . $md5_array['Packages.gz'] . " " . $r_array['Packages.gz'] . " " . "Packages.gz\n";
		$alert .= "\n".__('Write to the Packages.bz file: ').$r_array['Packages.gz'];
	}
	if (file_exists("../Packages.bz2")) {
		unlink("../Packages.bz2");
	}
	if (DCRM_LISTS_METHOD == 4 OR DCRM_LISTS_METHOD == 5 OR DCRM_LISTS_METHOD == 6 OR DCRM_LISTS_METHOD == 7) {
		$b_handle = bzopen("../Packages.bz2", "w");
		$size = bzwrite($b_handle, $Packages);
		bzclose($b_handle);
		$r_array['Packages.bz2'] = filesize("../Packages.bz2");
		$md5_array['Packages.bz2'] = md5_file("../Packages.bz2");
		$verify_text .= " " . $md5_array['Packages.bz2'] . " " . $r_array['Packages.bz2'] . " " . "Packages.bz2\n";
		$alert .= "\n".__('Write to the Packages.bz2 file: ').$r_array['Packages.bz2'];
	}
	if (DCRM_GNUPG_ENABLED == 1) {
		$alert .= "\n".__('GnuPG has been configured, sign the Packages now.');
		file_put_contents("../Release", file_get_contents(CONF_PATH.'release.save') . $verify_text);
		$alert .= "\n".__('Write to the Release file: ').filesize('../Release');
		$gpg_cmd = escapeshellcmd(DCRM_GNUPG_PATH . ' -abqs --no-tty --yes --passphrase "' . DCRM_GNUPG_PASS . '" -r "' . DCRM_GNUPG_NAME . '" -o "../Release.gpg" "../Release"');
		exec($gpg_cmd);
		$alert .= "\n".__('GnuPG signature has been successful, the signature length is: ').filesize('../Release.gpg');
	} else {
		file_put_contents("../Release", file_get_contents(CONF_PATH.'release.save'));
		$alert .= "\n".__('Write to the Release file: ').filesize('../Release');
	}
}

norecord:
$alert .= "\n";

endlabel:
require_once("header.php");
?>
			<h2><?php _e('Rebuild the list') ?></h2>
			<br>
			<h4 class="alert alert-success"><?php _e('List rebuild request submitted successfully, it is recommended that you <a href="stats.php?action=clean">Clear the Cache</a> to free up disk space.'); ?></h4>
				<div class="wrapper">
					<ul class="breadcrumb" onclick="wrapper('triangle_errors','item_errors'); return false;"><i class="icon" id="triangle_errors">▼</i>　<?php _ex('Information', 'Rebuild the list') ?></ul>
					<div class="item" style="display:block;" id="item_errors">
						<?php
							if (!empty($alert)) {echo nl2br($alert);}
						?>
					</div>
				</div>
				<div class="wrapper">
					<ul class="breadcrumb" onclick="wrapper('triangle_packages','item_packages'); return false;"><i class="icon" id="triangle_packages">▼</i>　<?php _e('Preview of Packages file'); ?></ul>
					<div class="item" style="display:block;" id="item_packages">
						<?php if (!empty($Packages)) echo nl2br(htmlspecialchars(mb_substr($Packages,0,2048,"UTF-8"))); else _e('File does not exist.'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	<script src="javascript/backend/misc.js" type="text/javascript"></script>
</body>
</html>