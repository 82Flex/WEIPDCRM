<?php
/**
 * DCRM APT Information Settings
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
define("DCRM",true);
$activeid = 'release';

if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
	require_once("header.php");

	if (!isset($_GET['action'])) {
		if (file_exists(CONF_PATH.'release.save')) {
			$release_file = file(CONF_PATH.'release.save');
			$release = array();
			foreach ($release_file as $line) {
				if(preg_match("#^Origin|Label|Version|Codename|Description#", $line)) {
					$release[trim(preg_replace("#^(.+):\\s*(.+)#","$1", $line))] = trim(preg_replace("#^(.+):\\s*(.+)#","$2", $line));
				}
			}
		}
?>
				<h2><?php _e('Repository Settings'); ?></h2>
				<br />
				<form class="form-horizontal" method="POST" enctype="multipart/form-data" action="release.php?action=set">
					<fieldset>
						<div class="group-control">
							<label class="control-label"><?php _e('Origin'); ?></label>
							<div class="controls">
								<input type="text" required="required" name="origin" value="<?php if (!empty($release['Origin'])) {echo htmlspecialchars($release['Origin']);} ?>"/>
								<p class="help-block"><?php _e('This name will be displayed in the sources interface of Cydia.'); ?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Label'); ?></label>
							<div class="controls">
								<input type="text" required="required" name="label" value="<?php if (!empty($release['Label'])) {echo htmlspecialchars($release['Label']);} ?>"/>
								<p class="help-block"><?php _e('This name will be displayed at the top of the package list interface.'); ?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Codename'); ?></label>
							<div class="controls">
								<input type="text" required="required" name="codename" value="<?php if (!empty($release['Codename'])) {echo htmlspecialchars($release['Codename']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Description'); ?></label>
							<div class="controls">
								<textarea type="text" style="height: 40px;" required="required" name="description" ><?php if (!empty($release['Description'])) {echo $release['Description'];} ?></textarea>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Version'); ?></label>
							<div class="controls">
								<input type="text" required="required" name="version" value="<?php if (!empty($release['Version'])) {echo htmlspecialchars($release['Version']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Repository Icon'); ?></label>
							<div class="controls">
								<input type="file" class="span6" name="icon" accept="image/x-png" />
								<p class="help-block"><?php printf(__('Allowed upload format: png, save as %s in root directory.'), '<a href="'.base64_decode(DCRM_REPOURL).'/CydiaIcon.png">CydiaIcon.png</a>'); ?></p>
							</div>
						</div>
						<br />
						<div class="form-actions">
							<div class="controls">
								<button type="submit" class="btn btn-success"><?php _e('Save'); ?></button>
							</div>
						</div>
					</fieldset>
				</form>
<?php
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "set") {
		$release_text = "Origin: ".stripslashes($_POST['origin']);
		$release_text .= "\nLabel: ".$_POST['label'];
		$release_text .= "\nSuite: stable";
		$release_text .= "\nVersion: ".$_POST['version'];
		$release_text .= "\nCodename: ".$_POST['codename'];
		$release_text .= "\nArchitectures: iphoneos-arm";
		$release_text .= "\nComponents: main";
		$release_text .= "\nDescription: ".str_replace("\n","<br />",$_POST['description']);
		$release_text .= "\n";
		$release_handle = fopen(CONF_PATH.'release.save',"w");
		fputs($release_handle,stripslashes($release_text));
		fclose($release_handle);
		if (pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION) == "png") {
			if (file_exists("../CydiaIcon.png")) {
				$result_1 = unlink("../CydiaIcon.png");
			}
			$result_2 = rename($_FILES['icon']['tmp_name'], "../CydiaIcon.png");
			if (!$result_1 OR !$result_2) {
				echo '<h3 class="alert alert-error">'.__('Upload failed, please check the file permissions.').'<br /><a href="release.php">'.__('Back').'</a></h3>';
			} else {
				echo '<h3 class="alert alert-success">'.__('Upload icon completed.').'<br />'.__('Repository settings save complete, rebuild the list to apply the changes.').'<br /><a href="release.php">'.__('Back').'</a></h3>';
			}
		} else {
			echo '<h3 class="alert alert-success">'.__('Repository settings save complete, rebuild the list to apply the changes.').'<br /><a href="release.php">'.__('Back').'</a></h3>';
		}
	}
?>
			</div>
		</div>
	</div>
</body>
</html>
<?php
} else {
	$_SESSION['referer'] = $_SERVER['REQUEST_URI'];
	header("Location: login.php");
	exit();
}
?>