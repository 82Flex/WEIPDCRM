<?php
/**
 * DCRM Upload List
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
$activeid = 'manage';

if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
	require_once("header.php");

	if (!isset($_GET['action'])) {
?>
				<h2><?php _e('Import Packages'); ?></h2>
				<br />
<?php
		$folder = opendir("../upload/");
		$files = array();
		while ($element = readdir($folder)) {
			if (preg_match("#.\.deb#", $element)) {
				$files[] = $element;
			}
		}
		if (empty($files)) {
?>
						<h3 class="alert alert-info">
							<?php _e('The upload directory is empty.'); ?><br />
							<?php _e('You can upload by web or upload packages to the upload directory in root by FTP.'); ?>
						</h3>
<?php
		} else {
			sort($files);
?>
						<h3 class="navbar"><span><?php _e('Files List'); ?></span>　<span><a href="manage.php?action=force"><?php _e('Forced Inherit'); ?></a></span></h3>
						<table class="table"><thead><tr>
						<th><ul class="ctl"><?php _e('Delete'); ?></ul></th>
						<th><ul class="ctl"><?php _e('Inherit'); ?></ul></th>
						<th><ul class="ctl" style="width:20%;"><?php _e('Name'); ?></ul></th>
						<th><ul class="ctl" style="width:100px;"><?php _e('Size'); ?></ul></th>
						</tr></thead><tbody>
<?php
			foreach ($files as $file) {
				$filesize = filesize("../upload/" . $file);
				$filesize_withext = sizeext($filesize);
?>
						<tr>
						<td><a href="manage.php?action=delete_confirmation&amp;file=<?php echo(urlencode($file)); ?>" class="close" style="line-height: 12px;">&times;</a></td>
						<td><a href="manage.php?action=force&amp;file=<?php echo(urlencode($file)); ?>" class="close" style="line-height: 12px;">&equiv;</a></td>
						<td><a href = "import.php?filename=<?php echo(urlencode($file)); ?>"><ul class="ctl"><?php echo($file); ?></a></ul></td>
						<td><ul class="ctl"><?php echo($filesize_withext); ?></ul></td>
						</tr>
<?php
			}
?>
						</tbody></table>
<?php
		}
	} elseif ($_GET['action'] == "force") {
?>
						<h2><?php _e('Import Packages'); ?></h2>
						<br />
						<h3 class="navbar"><span><a href="manage.php"><?php _e('Files List'); ?></a></span>　<span><?php _e('Forced Inherit'); ?></span></h3>
						<div class="form-horizontal">
							<div class="group-control">
								<label class="control-label"><?php _e('File Name'); ?></label>
								<div class="controls">
									<input class="input-xlarge" id="filename" required="required" value="<?php if(!empty($_GET['file']) AND file_exists("../upload/" . urldecode($_GET['file']))){echo(htmlspecialchars(urldecode($_GET['file'])));} ?>" />
								</div>
							</div>
							<br />
							<div class="group-control">
								<label class="control-label"><?php _e('Target ID'); ?></label>
								<div class="controls">
									<input class="input-xlarge" id="forceid" required="required" />
								</div>
							</div>
							<br />
							<div class="form-actions">
								<div class="controls">
									<button type="submit" class="btn btn-success" onclick="javascript:jump();"><?php _e('Submit'); ?></button>
								</div>
							</div>
						</div>
<?php
	} elseif ($_GET['action'] == "delete_confirmation" AND !empty($_GET['file']) AND file_exists("../upload/" . urldecode($_GET['file']))) {
?>
						<h3 class="alert"><?php printf(__('Are you sure delete: %s ?'), urldecode($_GET['file'])); ?><br /><?php _e('You will not be able to undo this operation!'); ?></h3>
						<a class="btn btn-warning" href="manage.php?action=delete&file=<?php echo($_GET['file']); ?>"><?php _e('Confirm'); ?></a>　
						<a class="btn btn-success" href="manage.php"><?php _e('Cancel'); ?></a>
<?php
	} elseif ($_GET['action'] == "delete" AND !empty($_GET['file']) AND file_exists("../upload/" . urldecode($_GET['file']))) {
		if (is_writable("../upload/" . urldecode($_GET['file']))) {
			unlink("../upload/" . urldecode($_GET['file']));
			header("Location: manage.php");
			exit();
		} else {
?>
						<h3 class="alert alert-error">
							<?php _e('Unable to delete, please check the file permissions.'); ?><br />
							<a href="sections.php"><?php _e('Back'); ?></a>
						</h3>
<?php
		}
	} else {
?>
						<h3 class="alert alert-error">
							<?php _e('Invalid request.'); ?><br />
							<a href="sections.php"><?php _e('Back'); ?></a>
						</h3>
<?php
	}
?>
			</div>
		</div>
	</div>
	</div>
	<script type="text/javascript">
		function jump() {
			var fname = document.getElementById("filename");
			var fid = document.getElementById("forceid");
			if (fname.value.length > 0 && fid.value.length > 0) {
				window.location.href = "import.php?filename=" + fname.value + "&force=" + fid.value;
			}
			return 0;
		}
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