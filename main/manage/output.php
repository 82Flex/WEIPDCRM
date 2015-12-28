<?php
/**
 * DCRM Debian Output
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
class_loader('tar');
$activeid = 'output';
$highactiveid = 'center';
$alert = "";

if (!isset($_SESSION['connected']) || $_SESSION['connected'] != true) {
	$_SESSION['referer'] = $_SERVER['REQUEST_URI'];
	header("Location: login.php");
	exit();
}
$request_id = (int)$_GET['id'];
if ($request_id <= 0) {
	$alert = __('Invalid arguments!');
	goto endlabel;
}
$m_array = DB::fetch_first("SELECT `Package`, `Source`, `Version`, `Priority`, `Section`, `Essential`, `Maintainer`, `Pre-Depends`, `Depends`, `Recommends`, `Suggests`, `Conflicts`, `Provides`, `Replaces`, `Enhances`, `Architecture`, `Installed-Size`, `Origin`, `Bugs`, `Name`, `Author`, `Sponsor`, `Icon`, `Tag`, `Filename` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . (string)$request_id . "' LIMIT 1");
if ($m_array == false) {
	$alert = __('Cannot find the content specified!');
	goto endlabel;
}
$deb_path = $m_array['Filename'];
if (!file_exists($deb_path)) {
	$alert = __('Cannot find the file specified!');
	goto endlabel;
}
unset($m_array['Filename']);
$f_Package = "";
foreach ($m_array as $m_key => $m_value) {
	if (strlen($m_value) > 0 AND $m_value != NULL) {
		$f_Package .= $m_key . ": " . trim(str_replace("\n","\n ",$m_value)) . "\n";
	}
}
$r_id = randstr(40);
if (!is_dir("../tmp/")) {
	mkdir("../tmp/");
}
if (!is_dir("../tmp/" . $r_id)) {
	mkdir("../tmp/" . $r_id);
}
$raw_data = new phpAr($deb_path);
$filename_array = $raw_data -> listfiles();
foreach ($filename_array as $filename) {
	if (is_int(stripos($filename, 'control.tar.gz'))) {
		$control_c_raw_data = $raw_data -> getfile($filename);
		$innername = $filename;
		goto nextstep;
	}
}
nextstep:
if (is_int(stripos($control_c_raw_data[0][0], 'control.tar.gz'))) {
	$control_tar_path = "../tmp/" . $r_id . "/old.tar.gz";
	$control_tar_handle = fopen($control_tar_path, 'w');
	fputs($control_tar_handle, $control_c_raw_data[0][6]);
	fclose($control_tar_handle);
	$control_tar = new Tar();
	$new_tar = new Tar();
	$control_tar -> load($control_tar_path);
	$control_array = $control_tar -> contents();
	foreach ($control_array as $c_key => $c_value) {
		$alert .= "· ".sprintf(__('Processing file: %s'), htmlspecialchars($c_key))."<br />";
		if ($c_key != "control") {
			$new_tar -> add_file($c_key, "", $control_array[$c_key]['data']);
		}
	}
	$new_path = "../tmp/" . $r_id . "/control.tar.gz";
	$new_tar -> add_file("control", "", $f_Package);
	$new_tar -> save($new_path);
}
$replace_result = $raw_data -> replace($innername,$new_path);
$success = true;
if ($replace_result) {
	$alert .= "· ".__('Package has been successfully written!')."<br />";
} else {
	$alert .= "· ".__('Warning: Writting to package failed!')."<br />";
	$success = false;
}
$chk_success = true;
if ((int)DCRM_CHECK_METHOD != 0) {
	$new_md5 = md5_file($deb_path);
	if (!$new_md5) {
		$chk_success = false;
	} else {
		$md5_query = DB::query("UPDATE `".DCRM_CON_PREFIX."Packages` SET `MD5sum` = '" . $new_md5 . "' WHERE `ID` = '" . (string)$request_id . "'");
	}
}
if ((int)DCRM_CHECK_METHOD == 2 || (int)DCRM_CHECK_METHOD == 3) {
	$new_sha1 = sha1_file($deb_path);
	if (!$new_sha1) {
		$chk_success = false;
	} else {
		$sha1_query = DB::query("UPDATE `".DCRM_CON_PREFIX."Packages` SET `SHA1` = '" . $new_sha1 . "' WHERE `ID` = '" . (string)$request_id . "'");
	}
}
if ((int)DCRM_CHECK_METHOD == 3) {
	$new_sha256 = hash("sha256",file_get_contents($deb_path));
	if (!$new_sha256) {
		$chk_success = false;
	} else {
		$sha256_query = DB::query("UPDATE `".DCRM_CON_PREFIX."Packages` SET `SHA256` = '" . $new_sha256 . "' WHERE `ID` = '" . (string)$request_id . "'");
	}
}
$new_size = filesize($deb_path);
$size_query = DB::query("UPDATE `".DCRM_CON_PREFIX."Packages` SET `Size` = '" . $new_size . "' WHERE `ID` = '" . (string)$request_id . "'");
if ($chk_success == false || $size_query == false) {
	$alert .= "· ".__('Verification information update failed! Please check the package is successfully generated!')."<br />";
	$success = false;
} else {
	$alert .= "· ".__('Verification information has been successfully updated!')."<br />";
}

endlabel:
require_once("header.php");
?>
			<h2><?php _e('Update Packages'); ?></h2>
			<br />
			<h4 class="alert alert-<?php if($success) echo("success"); else echo("error"); ?>"><?php _e('Tip: This operation does not automatically modify the software package file name, please upload the new name package when upgrade.'); ?><br /><br />
			<?php echo $alert; ?><br />
			<a href="javascript:history.go(-2);"><?php _e('Back'); ?></a></h4>
			</div>
		</div>
	</div>
	</div>
</body>
</html>