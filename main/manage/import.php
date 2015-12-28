<?php
/**
 * DCRM Debian Read
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
$activeid = 'import';
$highactiveid = 'manage';

if (!isset($_SESSION['connected']) || $_SESSION['connected'] != true) {
	$_SESSION['referer'] = $_SERVER['REQUEST_URI'];
	header("Location: login.php");
	exit();
}
$diff = false;
$replace = false;
$success = true;
if (empty($_GET['filename'])) {
	$alert = __('Invalid argument: `Filename`');
	$success = false;
	goto endlabel;
}

$r_path = "../upload/" . str_replace("\\", "",str_replace("/", "",$_GET['filename']));
if (pathinfo($r_path, PATHINFO_EXTENSION) != "deb") {
	$alert = __('Invalid file type!');
	$success = false;
	goto endlabel;
}
$r_id = randstr(40);
if (!$r_path || !$r_id) {
	$alert = __('Invalid arguments!');
	$success = false;
	goto endlabel;
}
if (!file_exists($r_path)) {
	$alert = sprintf(__('Cannot find the file: %s'), $r_path);
	$success = false;
	goto endlabel;
}
$file_md5 = md5_file($r_path);
$md5_exists = DB::result_first("SELECT `MD5sum` FROM `".DCRM_CON_PREFIX."Packages` WHERE `MD5sum` = '" . $file_md5 . "'");
if ($md5_exists) {
	$alert = sprintf(__('File already exists: %s'), $file_md5);
	$success = false;
	goto endlabel;
}

$raw_data = new phpAr($r_path);
$filename_array = $raw_data -> listfiles();

foreach ($filename_array as $filename) {
	if (is_int(stripos($filename, 'control.tar.gz'))) {
		$control_c_raw_data = $raw_data -> getfile($filename);
		goto nextstep;
	}
}

nextstep:
if (is_int(stripos($control_c_raw_data[0][0], 'control.tar.gz'))) {
	if (!is_dir("../tmp/")) {
		mkdir("../tmp/");
	}
	if (!is_dir("../tmp/" . $r_id)) {
		mkdir("../tmp/" . $r_id);
	}
	$t_path = "../tmp/" . $r_id . '/control.tar.gz';
	if (file_exists($t_path)) {
		unlink($t_path);
	}
	$control_tar_handle = fopen($t_path, 'w');
	fputs($control_tar_handle,$control_c_raw_data[0][6]);
	fclose($control_tar_handle);
	$control_tar = new Tar();
	$control_tar -> load($t_path);
	$control_array = $control_tar -> contents();
	$control_data = $control_array['control']['data'];

	$plain_array = explode("\n",$control_data);
	foreach ($plain_array as $line) {
		if (strlen(trim(substr($line, 0, 1))) == 0) {
			$t_value = trim($line);
			$t_package[$t_key] .= "\n".$t_value;
		} else {
			if(preg_match("#^Package|Source|Version|Priority|Section|Essential|Maintainer|Pre-Depends|Depends|Recommends|Suggests|Conflicts|Provides|Replaces|Enhances|Architecture|Filename|Size|Installed-Size|Description|Origin|Bugs|Name|Author|Homepage|Website|Depiction|Icon|Tag|Sponsor#",$line)) {
				preg_match("#^([^:]*?):(.*)#", $line, $t_matches);
				$t_key = trim($t_matches[1]);
				$t_value = trim($t_matches[2]);
				$t_package[$t_key] = $t_value;
			}
		}
	}

	if (strlen((string)$t_package['Package']) > 0 && strlen((string)$t_package['Version']) > 0 && strlen((string)$t_package['Architecture']) > 0) {
		goto vaildPackage;
	} else {
		goto invaildPackage;
	}
} else {
	goto invaildControl;
}
goto endlabel;

invaildControl:
$alert = __('Invalid package information!') . "<br />" . json_encode($filename_array) . "<br />" . json_encode($control_c_raw_data);
$success = false;
goto endlabel;

vaildPackage:
if (isset($_GET['force'])) {
	if (is_numeric($_GET['force'])) {
		$same_row = DB::fetch_first("SELECT `ID`, `Package`, `Name`, `Version`, `CreateStamp`, `MD5sum` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . DB::real_escape_string($_GET['force']) . "' LIMIT 1");
	} else {
		$alert = __('Forced inherit failed: invalid inherited package ID.');
		$success = false;
		goto endlabel;
	}
} else {
	$same_row = DB::fetch_first("SELECT `ID`, `Package`, `Name`, `Version`, `CreateStamp`, `MD5sum` FROM `".DCRM_CON_PREFIX."Packages` WHERE `Package` = '" . DB::real_escape_string($t_package['Package']) . "' ORDER BY `ID` DESC LIMIT 1");
}
if ($same_row != false) {
	if (!isset($_GET['type'])) {
		$ver_compare = version_compare($t_package['Version'], $same_row['Version']);
		if ($ver_compare == 0) {
			$alert = __('A package with the same name and version already exists, please select an operation.') . "<br />";
		} elseif ($ver_compare == 1) {
			$alert = __('Early versions of this package already exists, please select an operation.') . "<br />";
		} else {
			$alert = __('An updated version of this package already exists, please select an operation.') . "<br />";
		}
		$alert .= sprintf( __( 'Original package: %1$s &amp; %2$s' ) , $same_row['Package'], $same_row['Version'] ) . "<br />" . __('Import time: ') . $same_row['CreateStamp'] . "<br />MD5sum：" . $same_row['MD5sum'] . "<br /><br />" . sprintf( __( 'New package: %1$s &amp; %2$s' ) , $t_package['Package'], $t_package['Version'] ) . "<br />MD5sum：" . $file_md5;
		$diff = true;
		$success = false;
		goto endlabel;
	} else {
		if ($_GET['type'] == '1') {
			$p_row = DB::fetch_first("SELECT `Package`, `Source`, `Priority`, `Section`, `Essential`, `Maintainer`, `Pre-Depends`, `Depends`, `Recommends`, `Suggests`, `Conflicts`, `Provides`, `Replaces`, `Enhances`, `Architecture`, `Description`, `Origin`, `Bugs`, `Name`, `Author`, `Sponsor`, `Homepage`, `Website`, `Icon`, `Tag`, `Multi`, `Level`, `Price`, `Purchase_Link`, `Purchase_Link_Stat`, `Video_Preview`, `System_Support`, `ScreenShots` FROM `".DCRM_CON_PREFIX."Packages` WHERE `Package` = '" . $same_row['Package'] . "' ORDER BY `ID` DESC LIMIT 1");
			foreach ($p_row as $p_key => $p_value) {
				$t_package[$p_key] = $p_value;
			}
			$replace = true;
			goto importnow;
		} elseif ($_GET['type'] == '2') {
			$replace = true;
			goto importnow;
		} elseif ($_GET['type'] == '3') {
			goto importnow;
		} else {
			$alert = __('Error importing type!');
			$success = false;
			goto endlabel;
		}
	}
} else {
	if (isset($_GET['force'])) {
		$alert = __('Forced inherit failed: invalid inherited package ID.');
		$success = false;
		goto endlabel;
	}
}

importnow:
$new_daily = "../downloads/" . date("Ymd");
$new_path = $new_daily . "/" . $r_id . ".deb";
if (!is_dir($new_daily)) {
	$mkdir = mkdir($new_daily);
	if (!$mkdir) {
		$alert = sprintf(__('Create directory failed: %s'), $new_daily);
		$success = false;
		goto endlabel;
	}
}
if (file_exists($new_path)) {
	$alert = sprintf(__('File already exists: %s'), $new_path);
	$success = false;
	goto endlabel;
}
if (!rename($r_path,$new_path)) {
	$alert = sprintf(__('Move file failed: %s'), $new_path);
	$success = false;
	goto endlabel;
}
if (file_exists($r_path)) {
	unlink($r_path);
}
$new_id = DB::insert(DCRM_CON_PREFIX.'Packages', array('UUID' => $r_id));
if ($new_id != false) {
	$t_package['Size'] = filesize($new_path);
	$t_package['Filename'] = $new_path;
	$t_package['MD5sum'] = $file_md5;
	$t_package['CreateStamp'] = date('Y-m-d H:i:s');
	$t_package['Stat'] = 2;
	$autofill_depiction = get_option('autofill_depiction');
	if(defined('DCRM_REPOURL') && (!isset($t_package['Depiction']) || empty($t_package['Depiction'])) && ($autofill_depiction === '2' || empty($autofill_depiction))){
		$repourl = base64_decode(DCRM_REPOURL);
		$repourl = substr($repourl, -1) == '/' ? $repourl : $repourl.'/'; 
		$t_package['Depiction'] = $repourl . 'index.php?pid='.$new_id;
	}
	DB::update(DCRM_CON_PREFIX.'Packages', $t_package, array('ID' => $new_id));
} else {
	$alert = __('Import failed! Please check the database configuration!');
	$success = false;
}
if ($replace == true) {
	DB::update(DCRM_CON_PREFIX.'Packages', array('Stat' => '-1'), array('Package' => $same_row['Package'], 'Version' => $same_row['Version']));
	header("Location: output.php?id=".(string)$new_id);
	exit();
} else {
	$alert = __('Import was successful! Now you can enter the Manage Packages page to review!').'<br />'.sprintf(__('Package address: %s'), $new_path);
}
goto endlabel;

invaildPackage:
$alert = __('Invalid package: Unable to find `Package`, `Version`, `Architecture` fields in package information.');
$success = false;
goto endlabel;

endlabel:
require_once("header.php");
?>
			<h2><?php _e('Import Package'); ?></h2>
			<br />
<?php
if ($diff == false) {
	if ($success) {
?>
				<h4 class="alert alert-success">
<?php
	} else {
?>
				<h4 class="alert alert-error">
<?php
	}
	echo $alert;
?>
				<br /><a href="manage.php"><?php _e('Back'); ?></a></h4>
<?php
} else {
	$url_addr .= '&filename='.urlencode($_GET['filename']);
	if (isset($_GET['force']) && is_numeric($_GET['force'])) {
		$url_addr .= '&force='.$_GET['force'];
	}
?>
				<h4 class="alert alert-info">
					<?php echo $alert; ?>
				</h4>
				<a class="btn btn-warning" href="import.php?type=1<?php echo($url_addr); ?>"><?php _e('Inherit And Replace'); ?></a>　
				<a class="btn btn-warning" href="import.php?type=2<?php echo($url_addr); ?>"><?php _e('Direct Replace'); ?></a>　
				<a class="btn btn-warning" href="import.php?type=3<?php echo($url_addr); ?>"><?php _e('Add New'); ?></a>　
				<a class="btn btn-success" href="manage.php"><?php _e('Cancel'); ?></a>
<?php
}
?>
			</div>
		</div>
	</div>
	</div>
</body>
</html>