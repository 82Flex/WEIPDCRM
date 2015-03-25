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

/* DCRM Section Manage */

session_start();
define("DCRM",true);
$localetype = 'manage';
define('MANAGE_ROOT', dirname(__FILE__).'/');
define('ABSPATH', dirname(MANAGE_ROOT).'/');
require_once ABSPATH.'system/common.inc.php';
class_loader('tar');
class_loader('CorePage');
$activeid = 'sections';

if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
	require_once("header.php");

	if (!isset($_GET['action'])) {
?>
				<h2><?php _e('Manage Sections'); ?></h2>
				<br />
				<h3 class="navbar"><span><?php _e('Sections List'); ?></span>　<span><a href="sections.php?action=add"><?php _e('Add Section'); ?></a></span>　<span><a href="sections.php?action=create"><?php _e('Create Icon Package'); ?></a></span></h3>
<?php
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		} else {
			$page = 1;
		}
		if ($page <= 0 OR $page >= 6) {
			$page = 1;
		}
		$page_a = $page * 10 - 10;
		if ($page == 1) {
			$page_b = $page;
		} else {
			$page_b = $page - 1;
		}
		$list_query = DB::query("SELECT * FROM `".DCRM_CON_PREFIX."Sections` ORDER BY `ID` DESC LIMIT ".(string)$page_a.",10");
		if ($list_query == FALSE) {
			goto endlabel;
		} else {
?>
					<table class="table"><thead><tr>
					<th><ul class="ctl"><?php _e('Delete'); ?></ul></th>
					<th><ul class="ctl"><?php _e('Name'); ?></ul></th>
					<th><ul class="ctl"><?php _e('Icon'); ?></ul></th>
					<th><ul class="ctl"><?php _e('Last Change'); ?></ul></th>
					</tr></thead><tbody>
<?php
			while ($list = mysql_fetch_assoc($list_query)) {
?>
					<tr>
					<td><a href="sections.php?action=delete_confirmation&amp;id=<?php echo($list['ID']); ?>&amp;name=<?php echo($list['Name']); ?>" class="close" style="line-height: 12px;">&times;</a></td>
					<td><ul class="ctl" style="width:400px;"><a href="center.php?action=search&amp;contents=<?php echo(urlencode($list['Name'])); ?>&amp;type=7"><?php echo(htmlspecialchars($list['Name'])); ?></a></ul></td>
<?php
				if ($list['Icon'] != "") {
?>
					<td><ul class="ctl" style="width:150px;"><a href="<?php echo(base64_decode(DCRM_REPOURL)); ?>/icons/<?php echo($list['Icon']); ?>"><?php echo($list['Icon']); ?></a></ul></td>
<?php
				} else {
?>
					<td><ul class="ctl" style="width:150px;"><?php _e('No Icon'); ?></ul></td>
<?php
				}
?>
					<td><ul class="ctl" style="width:150px;"><?php echo($list['TimeStamp']); ?></ul></td>
					</tr>
<?php
			}
?>
					</tbody></table>
<?php
			$q_info = DB::query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Sections`");
			$info = DB::fetch_row($q_info);
			$totalnum = (int)$info[0];
			$params = array('total_rows'=>$totalnum, 'method'=>'html', 'parameter' =>'sections.php?page=%page', 'now_page'  =>$page, 'list_rows' =>10);
			$page = new Core_Lib_Page($params);
			echo '<div class="page">' . $page->show(2) . '</div>';
		}
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "add") {
?>
						<h2><?php _e('Manage Sections'); ?></h2>
						<br />
						<h3 class="navbar"><span><a href="sections.php"><?php _e('Sections List'); ?></a></span>　<span><?php _e('Add Section'); ?></span>　<span><a href="sections.php?action=create"><?php _e('Create Icon Package'); ?></a></span></h3>
						<br />
						<form class="form-horizontal" method="POST" enctype="multipart/form-data" action="sections.php?action=add_now" >
						<div class="group-control">
							<label class="control-label"><?php _e('Section Name'); ?></label>
							<div class="controls">
								<input class="input-xlarge" name="contents" required="required" />
								<input type="hidden" name="action" value="add_now" />
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Section Icon'); ?></label>
							<div class="controls">
								<input type="file" class="span6" name="icon" accept="image/x-png" />
								<p class="help-block"><?php printf(__('Allowed upload format: png, save in %s directory of root directory.'), 'icons'); ?></p>
							</div>
						</div>
						<br />
						<div class="form-actions">
							<div class="controls">
								<button type="submit" class="btn btn-success"><?php _e('Submit'); ?></button>
							</div>
						</div>
						</form>
<?php
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "add_now" AND !empty($_POST['contents'])) {
		$new_name = DB::real_escape_string($_POST['contents']);
		$q_info = DB::query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Sections`");
		if (!$q_info) {
			goto endlabel;
		}
		$info = DB::fetch_row($q_info);
		$num = (int)$info[0];
		if ($num <= 50) {
			if (pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION) == "png") {
				if (file_exists("../icons/" . $_FILES['icon']['name'])) {
					unlink("../icons/" . $_FILES['icon']['name']);
				}
				$move = rename($_FILES['icon']['tmp_name'],"../icons/" . $_FILES['icon']['name']);
				if (!$move) {
					$alert = __('Upload failed, please check the file permissions.');
					goto endlabel;
				} else {
					$n_query = DB::query("INSERT INTO `".DCRM_CON_PREFIX."Sections`(`Name`, `Icon`) VALUES('" . $new_name . "', '" . $_FILES['icon']['name'] . "')");
				}
			} else {
				$n_query = DB::query("INSERT INTO `".DCRM_CON_PREFIX."Sections`(`Name`) VALUES('" . $new_name . "')");
			}
		} else {
			$alert = __('You can add a maximum of 50 sections!');
			goto endlabel;
		}
		if (!$n_query) {
			goto endlabel;
		} else {
			header("Location: sections.php");
			exit();
		}
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "create") {
		if (defined("AUTOFILL_SEO") && defined("AUTOFILL_PRE")) {
			$alert = sprintf(__('Are you sure want to create the %s icon package?'), AUTOFILL_SEO) . '<br /><a href="sections.php?action=createnow">'.__('Create Now').'</a>';
		} else {
			$alert = __('You have not filled in SEO and autofill information, unable to use this function!');
		}
		goto endlabel;
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "createnow") {
		$new_name = DB::real_escape_string($_POST['contents']);
		$q_info = DB::query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Sections` WHERE `Icon` != ''");
		if (!$q_info) {
			goto endlabel;
		}
		$info = DB::fetch_row($q_info);
		$num = (int)$info[0];
		if ($num < 1) {
			$alert = __('Cannot find any existing icon of section, please add an icon first, then create icon package.');
			goto endlabel;
		}
		if (file_exists(CONF_PATH.'empty_icon.deb')) {
			$r_id = randstr(40);
			if (!is_dir("../tmp/")) {
				$result = mkdir("../tmp/");
			}
			if (!is_dir("../tmp/" . $r_id)) {
				$result = mkdir("../tmp/" . $r_id);
				if (!$result) {
					$alert = __('Cannot create temporary directory, please check the file permissions!');
					goto endlabel;
				}
			}
			$deb_path = "../tmp/" . $r_id . "/icon_" . time() . ".deb";
			$result = copy(CONF_PATH.'empty_icon.deb', $deb_path);
			if (!$result) {
				$alert = __('Icon package template copy failed, please check the file permissions!');
				goto endlabel;
			}
			$raw_data = new phpAr($deb_path);
			$new_tar = new Tar();
			$new_path = "../tmp/" . $r_id . "/data.tar.gz";
			$icon_query = DB::query("SELECT * FROM `".DCRM_CON_PREFIX."Sections`");
			while ($icon_assoc = mysql_fetch_assoc($icon_query)) {
				mkdir("../tmp/" . $r_id . "/Applications");
				mkdir("../tmp/" . $r_id . "/Applications/Cydia.app");
				mkdir("../tmp/" . $r_id . "/Applications/Cydia.app/Sections");
				if ($icon_assoc['Icon'] != "") {
					$new_filename = str_replace("[", "", str_replace("]", "", str_replace(" ", "_", $icon_assoc['Name']))) . ".png";
					$new_filepath = "../tmp/" . $r_id . "/Applications/Cydia.app/Sections/" . $new_filename;
					copy("../icons/" . $icon_assoc['Icon'], $new_filepath);
					$new_tar -> add_file("/Applications/Cydia.app/Sections/" . $new_filename, "", file_get_contents($new_filepath));
				}
			}
			$new_tar -> save($new_path);
			$result = $raw_data -> replace("data.tar.gz", $new_path);
			if (!$result) {
				$alert = __('Icon package template rewriting failed!');
				goto endlabel;
			} else {
				$result = rename($deb_path, "../upload/" . AUTOFILL_PRE."sourceicon_" . time() . ".deb");
				if (!$result) {
					$alert = __('Icon package template repositioning failed!');
					goto endlabel;
				}
				header("Location: manage.php");
				exit();
			}
		} else {
			$alert = __('Icon package template missing, please upload DCRM Pro again!');
			goto endlabel;
		}
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "delete_confirmation" AND !empty($_GET['id']) AND !empty($_GET['name'])) {
?>
						<h3 class="alert"><?php printf(__('Are you sure delete: %s ?'), htmlspecialchars($_GET['name'])); ?></h3>
						<a class="btn btn-warning" href="sections.php?action=delete&amp;id=<?php echo($_GET['id']); ?>"><?php _e('Confirm'); ?></a>　
						<a class="btn btn-success" href="sections.php"><?php _e('Cancel'); ?></a>
<?php
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "delete" AND !empty($_GET['id'])) {
		$delete_id = (int)$_GET['id'];
		DB::delete(DCRM_CON_PREFIX.'Sections', array('ID' => $delete_id));
		header("Location: sections.php");
		exit();
	}
	endlabel:
?>
			</div>
		</div>
	</div>
	</div>
</body>
</html>
<?php
} else {
	header("Location: login.php");
	exit();
}
?>